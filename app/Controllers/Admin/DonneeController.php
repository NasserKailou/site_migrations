<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database as DB;
use App\Models\Observation;

/** Contrôleur de saisie et gestion des données */
class DonneeController
{
    public function index(array $params = []): void
    {
        Auth::require();
        $filters = [
            'statut'        => Request::get('statut', ''),
            'indicateur_id' => Request::get('ind_id', ''),
        ];
        // Les points focaux ne voient que leurs données
        if (Auth::hasRole('point_focal')) {
            $filters['user_id'] = Auth::id();
        }
        $page   = max(1, (int)Request::get('page', 1));
        $donnees = Observation::allAdmin($filters, $page);
        $total   = Observation::countAdmin($filters);
        $indicateurs = DB::query("SELECT id, libelle_fr FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr");
        $totalPages = (int)ceil($total / 25);

        View::renderWithLayout('admin/donnees/index', compact(
            'donnees','total','page','totalPages','filters','indicateurs'
        ), 'layouts/admin');
    }

    public function saisie(array $params = []): void
    {
        Auth::requirePermission('create_data');
        $indicateurs   = DB::query("SELECT id, libelle_fr, entite_id, thematique_id FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr");
        $entites       = DB::query("SELECT id, acronyme FROM entites ORDER BY acronyme");
        $niveaux       = DB::query("SELECT id, libelle FROM niveaux_desagregation ORDER BY libelle");
        $geo_entites   = DB::query("SELECT id, libelle, type FROM geo_entites ORDER BY type, libelle");

        View::renderWithLayout('admin/donnees/saisie', compact(
            'indicateurs','entites','niveaux','geo_entites'
        ), 'layouts/admin');
    }

    public function store(array $params = []): void
    {
        Auth::requirePermission('create_data');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/donnees/saisie'));
        }

        $data = $this->validateAndBuild();
        if (!$data) {
            Response::redirect(url('admin/donnees/saisie'));
        }

        $data['created_by'] = Auth::id();
        $data['statut']     = Request::post('submit_type') === 'soumettre' ? 'soumis' : 'brouillon';

        // Gestion upload document source
        $upload = $this->handleUpload();
        if ($upload) {
            $data['document_source_path'] = $upload['path'];
            $data['document_source_nom']  = $upload['nom'];
        }

        $id = Observation::create($data);

        // Log audit
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,created_at) VALUES (?,?,?,?,?,NOW())",
            [Auth::id(), 'create_observation', 'observations', $id, Request::ip()]);

        if ($data['statut'] === 'soumis') {
            Session::flash('success', 'Données soumises pour validation avec succès.');
        } else {
            Session::flash('success', 'Brouillon enregistré. Soumettez-le quand vous êtes prêt(e).');
        }
        Response::redirect(url('admin/donnees'));
    }

    /** Autosave AJAX (brouillon) */
    public function autosave(array $params = []): void
    {
        Auth::require();
        if (!Request::post('autosave')) {
            Response::json(['error' => 'Not autosave'], 400);
        }
        $data = $this->validateAndBuild(strict: false);
        if (!$data) {
            Response::json(['error' => 'invalid data'], 422);
        }
        $existingId = (int)Request::post('observation_id');
        if ($existingId) {
            $obs = Observation::findById($existingId);
            if ($obs && (int)$obs['created_by'] === Auth::id() && $obs['statut'] === 'brouillon') {
                $data['updated_by'] = Auth::id();
                Observation::update($existingId, $data);
                Response::json(['id' => $existingId, 'status' => 'updated']);
            }
        }
        $data['created_by'] = Auth::id();
        $data['statut']     = 'brouillon';
        $id = Observation::create($data);
        Response::json(['id' => $id, 'status' => 'created']);
    }

    public function edit(array $params): void
    {
        Auth::require();
        $id  = (int)($params['id'] ?? 0);
        $obs = Observation::findById($id);
        if (!$obs) Response::notFound();

        // Vérifier droits : point focal ne peut éditer que ses brouillons
        if (Auth::hasRole('point_focal') && ((int)$obs['created_by'] !== Auth::id() || $obs['statut'] !== 'brouillon')) {
            throw new \App\Core\ForbiddenException();
        }

        $indicateurs = DB::query("SELECT id, libelle_fr FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr");
        $niveaux     = DB::query("SELECT id, libelle FROM niveaux_desagregation ORDER BY libelle");
        $geo_entites = DB::query("SELECT id, libelle, type FROM geo_entites ORDER BY type, libelle");
        $frequences  = DB::query("SELECT id, libelle AS libelle_fr FROM frequences ORDER BY libelle");
        View::renderWithLayout('admin/donnees/edit', compact('obs','indicateurs','niveaux','geo_entites','frequences'), 'layouts/admin');
    }

    public function update(array $params): void
    {
        Auth::require();
        $id  = (int)($params['id'] ?? 0);
        $obs = Observation::findById($id);
        if (!$obs) Response::notFound();
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url("admin/donnees/{$id}/edit"));
        }
        $data = $this->validateAndBuild();
        if (!$data) Response::redirect(url("admin/donnees/{$id}/edit"));
        $data['updated_by'] = Auth::id();
        Observation::update($id, $data);
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,created_at) VALUES (?,?,?,?,?,NOW())",
            [Auth::id(), 'update_observation', 'observations', $id, Request::ip()]);
        Session::flash('success', 'Données mises à jour.');
        Response::redirect(url('admin/donnees'));
    }

    public function submit(array $params): void
    {
        Auth::require();
        $id = (int)($params['id'] ?? 0);
        $obs = Observation::findById($id);
        if (!$obs || (Auth::hasRole('point_focal') && (int)$obs['created_by'] !== Auth::id())) {
            Response::notFound();
        }
        if (!in_array($obs['statut'], ['brouillon', 'rejete'])) {
            Session::flash('error', 'Cette observation ne peut pas être soumise.');
            Response::redirect(url('admin/donnees'));
        }
        Observation::changeStatut($id, 'soumis', Auth::id());
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,created_at) VALUES (?,?,?,?,?,NOW())",
            [Auth::id(), 'submit_observation', 'observations', $id, Request::ip()]);
        Session::flash('success', 'Soumis pour validation.');
        Response::redirect(url('admin/donnees'));
    }

    public function validate(array $params): void
    {
        Auth::requirePermission('validate');
        $id = (int)($params['id'] ?? 0);
        $obs = Observation::findById($id);
        if (!$obs || $obs['statut'] !== 'soumis') Response::notFound();
        Observation::changeStatut($id, 'valide', Auth::id());
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,created_at) VALUES (?,?,?,?,?,NOW())",
            [Auth::id(), 'validate_observation', 'observations', $id, Request::ip()]);
        Session::flash('success', 'Observation validée.');
        Response::redirect(url('admin/donnees'));
    }

    public function publish(array $params): void
    {
        Auth::requirePermission('publish');
        $id = (int)($params['id'] ?? 0);
        $obs = Observation::findById($id);
        if (!$obs || $obs['statut'] !== 'valide') Response::notFound();
        Observation::changeStatut($id, 'publie', Auth::id());
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,created_at) VALUES (?,?,?,?,?,NOW())",
            [Auth::id(), 'publish_observation', 'observations', $id, Request::ip()]);
        Session::flash('success', 'Observation publiée.');
        Response::redirect(url('admin/donnees'));
    }

    public function reject(array $params): void
    {
        Auth::requirePermission('validate');
        $id = (int)($params['id'] ?? 0);
        $commentaire = Request::post('commentaire', '');
        Observation::changeStatut($id, 'rejete', Auth::id(), $commentaire);
        DB::execute("INSERT INTO audit_log (user_id,action,table_cible,record_id,ip,details,created_at) VALUES (?,?,?,?,?,?,NOW())",
            [Auth::id(), 'reject_observation', 'observations', $id, Request::ip(), json_encode(['raison' => $commentaire])]);
        Session::flash('info', 'Observation rejetée.');
        Response::redirect(url('admin/donnees'));
    }

    private function validateAndBuild(bool $strict = true): array|false
    {
        $indicateur_id = (int)Request::post('indicateur_id');
        $periode_debut = Request::post('periode_debut', '');
        $masculin  = Request::post('masculin', null);
        $feminin   = Request::post('feminin', null);
        $trans     = Request::post('trans_autre', null);
        $total     = Request::post('total', null);

        if ($strict && (!$indicateur_id || !$periode_debut)) {
            Session::flash('error', 'Indicateur et période requis.');
            return false;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $periode_debut)) {
            $periode_debut = $periode_debut ? $periode_debut . '-01-01' : null;
        }

        // Validation cohérence masculin + féminin + trans = total (tolérance 1%)
        if ($masculin !== null && $feminin !== null && $total !== null) {
            $m = (float)$masculin; $f = (float)$feminin; $t_sum = $m + $f + (float)($trans ?? 0);
            $tot = (float)$total;
            if ($tot > 0 && abs($t_sum - $tot) / $tot > 0.01) {
                Session::flash('error', "Incohérence : masculin ({$m}) + féminin ({$f}) ≠ total ({$tot}) (tolérance 1%).");
                return false;
            }
        }
        // Calculer le total si non fourni
        if ($total === null && $masculin !== null && $feminin !== null) {
            $total = (float)$masculin + (float)$feminin + (float)($trans ?? 0);
        }

        return [
            'indicateur_id'            => $indicateur_id,
            'geo_entite_id'            => (int)Request::post('geo_entite_id') ?: null,
            'niveau_desagregation_id'  => (int)Request::post('niveau_desagregation_id') ?: null,
            'niveau_desag_valeur'      => Request::post('niveau_desag_valeur', null),
            'niveau_desag_valeur2'     => Request::post('niveau_desag_valeur2', null),
            'periode_debut'            => $periode_debut,
            'periode_fin'              => Request::post('periode_fin', null),
            'periodicite'              => Request::post('periodicite', 'annuelle'),
            'masculin'                 => $masculin !== '' ? $masculin : null,
            'feminin'                  => $feminin  !== '' ? $feminin  : null,
            'trans_autre'              => $trans    !== '' ? $trans    : null,
            'total'                    => $total    !== '' ? $total    : null,
            'commentaire_interne'      => Request::post('commentaire_interne', ''),
        ];
    }

    private function handleUpload(): ?array
    {
        $file = Request::file('document_source');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

        $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];
        $mime    = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed)) {
            Session::flash('error', 'Type de fichier non autorisé (PDF, JPG, PNG, WebP).');
            return null;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            Session::flash('error', 'Fichier trop lourd (max 10 Mo).');
            return null;
        }
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest     = APP_ROOT . '/storage/uploads/' . $filename;
        move_uploaded_file($file['tmp_name'], $dest);
        return ['path' => $filename, 'nom' => $file['name']];
    }
}
