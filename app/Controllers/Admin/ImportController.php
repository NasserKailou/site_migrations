<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database as DB;

/**
 * Contrôleur admin – importation de données (CSV/Excel dry-run + commit)
 */
class ImportController
{
    /* ------------------------------------------------------------------ */
    /*  PAGE PRINCIPALE                                                     */
    /* ------------------------------------------------------------------ */
    public function index(array $params = []): void
    {
        Auth::requirePermission('import_data');

        $indicateurs  = DB::query(
            "SELECT id, libelle_fr, code, slug FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr"
        );
        $recentImports = DB::query(
            "SELECT al.created_at, al.detail, u.nom AS auteur
             FROM audit_log al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.action IN ('IMPORT_DATA','IMPORT_DRY_RUN')
             ORDER BY al.created_at DESC
             LIMIT 10"
        );

        View::renderWithLayout('admin/import/index', compact(
            'indicateurs', 'recentImports'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  DRY-RUN (preview avant commit)                                      */
    /* ------------------------------------------------------------------ */
    public function dryRun(array $params = []): void
    {
        Auth::requirePermission('import_data');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Response::json(['error' => 'Token CSRF invalide.'], 403);
            return;
        }

        $file = Request::file('fichier');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            Response::json(['error' => 'Aucun fichier reçu.'], 400);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'xlsx'])) {
            Response::json(['error' => 'Format non supporté. Utilisez CSV ou XLSX.'], 400);
            return;
        }

        $indicateurId = (int) Request::post('indicateur_id', 0);
        $indicateur   = $indicateurId > 0
            ? DB::queryOne("SELECT * FROM indicateurs WHERE id = :id", [':id' => $indicateurId])
            : null;

        try {
            if ($ext === 'csv') {
                $rows = $this->parseCSV($file['tmp_name']);
            } else {
                $rows = $this->parseXLSX($file['tmp_name']);
            }

            $result = $this->validateRows($rows, $indicateur);

            $this->audit('IMPORT_DRY_RUN', sprintf(
                "Dry-run import: %d lignes, %d valides, %d erreurs, fichier=%s",
                count($rows), count($result['valid']), count($result['errors']), $file['name']
            ));

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['error' => 'Erreur de lecture du fichier : ' . $e->getMessage()], 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  COMMIT (import réel)                                                */
    /* ------------------------------------------------------------------ */
    public function commit(array $params = []): void
    {
        Auth::requirePermission('import_data');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/import'));
            return;
        }

        $rawJson = Request::post('rows_json', '');
        if ($rawJson === '') {
            Session::flash('error', 'Aucune donnée à importer.');
            Response::redirect(url('admin/import'));
            return;
        }

        $rows = json_decode($rawJson, true);
        if (!is_array($rows) || empty($rows)) {
            Session::flash('error', 'Données JSON invalides.');
            Response::redirect(url('admin/import'));
            return;
        }

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                $result = $this->insertRow($row);
                if ($result === true) {
                    $imported++;
                } elseif ($result === 'skip') {
                    $skipped++;
                } else {
                    $errors[] = "Ligne " . ($i + 2) . " : $result";
                }
            }
            DB::commit();

            $this->audit('IMPORT_DATA', sprintf(
                "Import effectué: %d insérées, %d ignorées (doublons), %d erreurs",
                $imported, $skipped, count($errors)
            ));

            $msg = "$imported observation(s) importée(s)";
            if ($skipped > 0) $msg .= ", $skipped doublon(s) ignoré(s)";
            if (!empty($errors)) $msg .= '. Erreurs : ' . implode(' | ', array_slice($errors, 0, 5));

            Session::flash($errors ? 'warning' : 'success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }

        Response::redirect(url('admin/import'));
    }

    /* ------------------------------------------------------------------ */
    /*  TÉLÉCHARGER TEMPLATE CSV                                            */
    /* ------------------------------------------------------------------ */
    public function template(array $params = []): void
    {
        Auth::requirePermission('import_data');

        $csv = implode(',', [
            'indicateur_code', 'indicateur_slug', 'periode_debut', 'periode_fin',
            'frequence', 'geo_entite', 'niveau_desag', 'niveau_valeur',
            'valeur_masculin', 'valeur_feminin', 'valeur_autre', 'valeur_total',
            'unite', 'source_donnee', 'commentaire'
        ]) . "\r\n";
        $csv .= implode(',', [
            'IND001', 'flux-retours-volontaires', '2023-01-01', '2023-12-31',
            'annuelle', 'niger', '', '',
            '1200', '800', '50', '2050',
            'personnes', 'OIM', 'Données provisoires'
        ]) . "\r\n";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="template_import_pndm.csv"');
        // BOM UTF-8 pour Excel
        echo "\xEF\xBB\xBF" . $csv;
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  PARSING CSV                                                         */
    /* ------------------------------------------------------------------ */
    private function parseCSV(string $filepath): array
    {
        $rows   = [];
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Impossible de lire le fichier CSV.");
        }

        // Détecter et ignorer le BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        $lineNum = 0;
        while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $lineNum++;
            if ($headers === null) {
                // Normaliser les headers
                $headers = array_map(fn($h) => strtolower(trim($h)), $line);
                continue;
            }
            if (count($line) !== count($headers)) continue;
            $row = array_combine($headers, array_map('trim', $line));
            $row['_line'] = $lineNum;
            $rows[] = $row;
        }
        fclose($handle);
        return $rows;
    }

    /* ------------------------------------------------------------------ */
    /*  PARSING XLSX (sans dépendance externe – lecture ZIP/XML)            */
    /* ------------------------------------------------------------------ */
    private function parseXLSX(string $filepath): array
    {
        // Utiliser PhpSpreadsheet si disponible, sinon fallback CSV-like via ZIP
        if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = [];
            $headers     = null;
            foreach ($sheet->getRowIterator() as $lineNum => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $line = [];
                foreach ($cellIterator as $cell) {
                    $line[] = (string) $cell->getValue();
                }
                if ($headers === null) {
                    $headers = array_map(fn($h) => strtolower(trim($h)), $line);
                    continue;
                }
                // Ignorer les lignes vides
                if (empty(array_filter($line))) continue;
                $combined = array_combine(
                    $headers,
                    array_pad(array_map('trim', $line), count($headers), '')
                );
                $combined['_line'] = $lineNum;
                $rows[] = $combined;
            }
            return $rows;
        }

        // Fallback : essayer de lire comme CSV (si l'utilisateur a uploadé un CSV renommé)
        return $this->parseCSV($filepath);
    }

    /* ------------------------------------------------------------------ */
    /*  VALIDATION DES LIGNES                                               */
    /* ------------------------------------------------------------------ */
    private function validateRows(array $rows, ?array $indicateur): array
    {
        $valid  = [];
        $errors = [];

        // Cache des indicateurs, fréquences, geo_entites, niveaux
        $indicateursMap = [];
        foreach (DB::query("SELECT id, code, slug FROM indicateurs WHERE statut='actif'") as $r) {
            $indicateursMap[$r['code']] = $r;
            $indicateursMap[$r['slug']] = $r;
        }
        $frequencesMap = [];
        foreach (DB::query("SELECT id, code FROM frequences") as $r) {
            $frequencesMap[strtolower($r['code'])] = $r['id'];
        }
        $geoMap = [];
        foreach (DB::query("SELECT id, slug FROM geo_entites") as $r) {
            $geoMap[strtolower($r['slug'])] = $r['id'];
        }
        $niveauValMap = [];
        foreach (DB::query("SELECT nv.id, nv.valeur, nd.code AS nd_code FROM niveau_valeurs nv JOIN niveaux_desagregation nd ON nd.id = nv.niveau_id") as $r) {
            $key = strtolower($r['nd_code'] . ':' . $r['valeur']);
            $niveauValMap[$key] = $r['id'];
        }

        foreach ($rows as $row) {
            $line = $row['_line'] ?? '?';
            $rowErrors = [];

            // Résoudre indicateur
            $indKey = $row['indicateur_code'] ?? ($row['indicateur_slug'] ?? '');
            $ind = $indicateur;
            if ($ind === null) {
                $ind = $indicateursMap[$indKey] ?? null;
                if (!$ind) {
                    $rowErrors[] = "Indicateur '$indKey' introuvable";
                }
            }

            // Période début
            $debut = $row['periode_debut'] ?? '';
            if ($debut === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $debut)) {
                $rowErrors[] = "periode_debut invalide ('$debut')";
            }

            // Période fin
            $fin = $row['periode_fin'] ?? '';
            if ($fin !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fin)) {
                $rowErrors[] = "periode_fin invalide ('$fin')";
            }

            // Valeur total
            $valTotal = $row['valeur_total'] ?? '';
            if ($valTotal !== '' && !is_numeric($valTotal)) {
                $rowErrors[] = "valeur_total doit être numérique";
            }

            // Geo entité
            $geoSlug  = strtolower($row['geo_entite'] ?? 'niger');
            $geoId    = $geoMap[$geoSlug] ?? null;

            // Fréquence
            $freqCode = strtolower($row['frequence'] ?? '');
            $freqId   = $frequencesMap[$freqCode] ?? null;

            if (!empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors, 'data' => $row];
            } else {
                $valid[] = [
                    'indicateur_id'   => $ind['id'] ?? null,
                    'indicateur_slug' => $ind['slug'] ?? '',
                    'periode_debut'   => $debut,
                    'periode_fin'     => $fin !== '' ? $fin : $debut,
                    'frequence_id'    => $freqId,
                    'geo_entite_id'   => $geoId,
                    'valeur_masculin' => is_numeric($row['valeur_masculin'] ?? '') ? (float) $row['valeur_masculin'] : null,
                    'valeur_feminin'  => is_numeric($row['valeur_feminin'] ?? '')  ? (float) $row['valeur_feminin']  : null,
                    'valeur_autre'    => is_numeric($row['valeur_autre'] ?? '')    ? (float) $row['valeur_autre']    : null,
                    'valeur_total'    => is_numeric($valTotal)                     ? (float) $valTotal               : null,
                    'source_donnee'   => $row['source_donnee'] ?? '',
                    'commentaire'     => $row['commentaire'] ?? '',
                    '_line'           => $line,
                ];
            }
        }

        return [
            'total'  => count($rows),
            'valid'  => $valid,
            'errors' => $errors,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  INSERTION D'UNE LIGNE                                               */
    /* ------------------------------------------------------------------ */
    private function insertRow(array $row): true|string
    {
        $indicateurId = (int) ($row['indicateur_id'] ?? 0);
        if ($indicateurId <= 0) {
            return "indicateur_id manquant";
        }

        $debut = $row['periode_debut'] ?? '';
        if ($debut === '') {
            return "periode_debut manquante";
        }

        // Vérifier doublon (indicateur + période + geo)
        $geoId = $row['geo_entite_id'] ? (int) $row['geo_entite_id'] : null;
        $exists = DB::count(
            "SELECT COUNT(*) FROM observations
             WHERE indicateur_id = :iid
               AND periode_debut = :pd
               AND periode_fin   = :pf
               AND (geo_entite_id = :geo OR (:geo IS NULL AND geo_entite_id IS NULL))
               AND statut NOT IN ('rejete')",
            [
                ':iid' => $indicateurId,
                ':pd'  => $debut,
                ':pf'  => $row['periode_fin'] ?? $debut,
                ':geo' => $geoId,
            ]
        );

        if ($exists > 0) {
            return 'skip'; // doublon silencieux
        }

        DB::execute(
            "INSERT INTO observations
                (indicateur_id, periode_debut, periode_fin, frequence_id, geo_entite_id,
                 valeur_masculin, valeur_feminin, valeur_autre, valeur_total,
                 source_donnee, commentaire, statut, saisi_par, created_at, updated_at)
             VALUES
                (:iid, :pd, :pf, :fid, :geo,
                 :vm, :vf, :va, :vt,
                 :src, :com, 'publie', :uid, NOW(), NOW())",
            [
                ':iid'  => $indicateurId,
                ':pd'   => $debut,
                ':pf'   => $row['periode_fin'] ?? $debut,
                ':fid'  => $row['frequence_id'] ?? null,
                ':geo'  => $geoId,
                ':vm'   => $row['valeur_masculin'] ?? null,
                ':vf'   => $row['valeur_feminin']  ?? null,
                ':va'   => $row['valeur_autre']    ?? null,
                ':vt'   => $row['valeur_total']    ?? null,
                ':src'  => $row['source_donnee']   ?? '',
                ':com'  => $row['commentaire']     ?? '',
                ':uid'  => Auth::id(),
            ]
        );

        return true;
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */
    private function audit(string $action, string $detail): void
    {
        DB::execute(
            "INSERT INTO audit_log (user_id, action, details, ip, created_at)
             VALUES (:uid, :act, :det, :ip, NOW())",
            [
                ':uid' => Auth::id(),
                ':act' => $action,
                ':det' => $detail,
                ':ip'  => Request::ip(),
            ]
        );
    }
}
