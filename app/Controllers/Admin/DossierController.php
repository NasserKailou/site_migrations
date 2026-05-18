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
 * Contrôleur admin – gestion des dossiers (Agadez, etc.)
 */
class DossierController
{
    /* ------------------------------------------------------------------ */
    /*  LISTE                                                               */
    /* ------------------------------------------------------------------ */
    public function index(array $params = []): void
    {
        Auth::requireRole('validateur', 'admin', 'super_admin');

        $dossiers = DB::query(
            "SELECT d.*, u.nom AS auteur_nom
             FROM dossiers d
             LEFT JOIN users u ON u.id = d.created_by
             ORDER BY d.updated_at DESC"
        );

        View::renderWithLayout('admin/dossiers/index', compact('dossiers'), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE ÉDITION                                                  */
    /* ------------------------------------------------------------------ */
    public function edit(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);
        $dossier = DB::queryOne("SELECT * FROM dossiers WHERE id = :id", [':id' => $id]);

        if (!$dossier) {
            Response::notFound();
            return;
        }

        $documents = DB::query(
            "SELECT * FROM dossier_documents WHERE dossier_id = :id ORDER BY ordre, libelle_fr",
            [':id' => $id]
        );

        $indicateurs = DB::query(
            "SELECT id, libelle_fr, slug FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr"
        );

        View::renderWithLayout('admin/dossiers/edit', compact(
            'dossier', 'documents', 'indicateurs'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE (POST)                                                       */
    /* ------------------------------------------------------------------ */
    public function update(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url("admin/dossiers/$id/modifier"));
            return;
        }

        $dossier = DB::queryOne("SELECT id FROM dossiers WHERE id = :id", [':id' => $id]);
        if (!$dossier) {
            Response::notFound();
            return;
        }

        $titre_fr    = trim(Request::post('titre_fr', ''));
        $titre_en    = trim(Request::post('titre_en', ''));
        $description = trim(Request::post('description_fr', ''));
        $powerbi_url = trim(Request::post('powerbi_url', ''));
        $statut      = Request::post('statut', 'publie');

        // Valider URL Power BI si fournie
        if ($powerbi_url !== '' && !filter_var($powerbi_url, FILTER_VALIDATE_URL)) {
            Session::flash('error', "L'URL Power BI n'est pas valide.");
            Response::redirect(url("admin/dossiers/$id/modifier"));
            return;
        }

        if ($titre_fr === '') {
            Session::flash('error', 'Le titre français est obligatoire.');
            Response::redirect(url("admin/dossiers/$id/modifier"));
            return;
        }

        // Upload image de couverture si fournie
        $imageCover = null;
        $file = Request::file('image_couverture');
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                Session::flash('error', 'Format image non supporté (jpg, png, webp).');
                Response::redirect(url("admin/dossiers/$id/modifier"));
                return;
            }
            $filename   = "dossier_{$id}_" . time() . ".$ext";
            $uploadPath = dirname(__DIR__, 3) . "/public/assets/images/dossiers/$filename";
            @mkdir(dirname($uploadPath), 0755, true);
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $imageCover = "assets/images/dossiers/$filename";
            }
        }

        $updateFields = [
            ':tf'    => $titre_fr,
            ':te'    => $titre_en,
            ':desc'  => $description,
            ':pb'    => $powerbi_url ?: null,
            ':st'    => in_array($statut, ['brouillon','publie']) ? $statut : 'publie',
            ':id'    => $id,
        ];

        $imageSQL = '';
        if ($imageCover !== null) {
            $imageSQL                  = ', image_couverture = :img';
            $updateFields[':img']      = $imageCover;
        }

        DB::execute(
            "UPDATE dossiers SET
                titre_fr = :tf, titre_en = :te,
                description_fr = :desc,
                powerbi_url = :pb,
                statut = :st
                $imageSQL,
                updated_at = NOW()
             WHERE id = :id",
            $updateFields
        );

        // Documents : traiter les suppressions demandées
        $docsToDelete = Request::post('delete_docs', []);
        foreach ($docsToDelete as $docId) {
            $doc = DB::queryOne(
                "SELECT chemin_fichier FROM dossier_documents WHERE id = :did AND dossier_id = :did2",
                [':did' => (int)$docId, ':did2' => $id]
            );
            if ($doc && $doc['chemin_fichier']) {
                @unlink(dirname(__DIR__, 3) . '/public/' . $doc['chemin_fichier']);
            }
            DB::execute("DELETE FROM dossier_documents WHERE id = :did", [':did' => (int)$docId]);
        }

        // Nouveaux documents uploadés
        $newDocs = $_FILES['nouveaux_documents'] ?? [];
        if (!empty($newDocs['name']) && is_array($newDocs['name'])) {
            $docDir = dirname(__DIR__, 3) . '/public/assets/docs/dossiers/';
            @mkdir($docDir, 0755, true);
            $count = count($newDocs['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($newDocs['error'][$i] !== UPLOAD_ERR_OK) continue;
                $origName = $newDocs['name'][$i];
                $ext2     = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext2, ['pdf','xlsx','csv','docx','zip'])) continue;
                $safeName = time() . '_' . $i . '_' . preg_replace('/[^a-z0-9._-]/', '_', strtolower($origName));
                if (move_uploaded_file($newDocs['tmp_name'][$i], $docDir . $safeName)) {
                    $libelle = trim(Request::post("doc_libelle_$i", $origName));
                    DB::execute(
                        "INSERT INTO dossier_documents
                            (dossier_id, libelle_fr, libelle_en, chemin_fichier, type_mime, taille_octets, ordre, created_at)
                         VALUES (:did, :lf, :le, :cf, :tm, :sz, :ord, NOW())",
                        [
                            ':did'  => $id,
                            ':lf'   => $libelle,
                            ':le'   => trim(Request::post("doc_libelle_en_$i", '')),
                            ':cf'   => "assets/docs/dossiers/$safeName",
                            ':tm'   => $newDocs['type'][$i] ?? 'application/octet-stream',
                            ':sz'   => $newDocs['size'][$i] ?? 0,
                            ':ord'  => $i + 100,
                        ]
                    );
                }
            }
        }

        $this->audit('UPDATE_DOSSIER', "Mise à jour dossier id=$id");

        Session::flash('success', 'Dossier mis à jour avec succès.');
        Response::redirect(url("admin/dossiers/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  CRÉATION                                                            */
    /* ------------------------------------------------------------------ */
    public function create(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $indicateurs = DB::query(
            "SELECT id, libelle_fr, slug FROM indicateurs WHERE statut='actif' ORDER BY libelle_fr"
        );

        $dossier   = [];
        $documents = [];

        View::renderWithLayout('admin/dossiers/edit', compact(
            'dossier', 'documents', 'indicateurs'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  STORE                                                               */
    /* ------------------------------------------------------------------ */
    public function store(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/dossiers/nouveau'));
            return;
        }

        $titre_fr = trim(Request::post('titre_fr', ''));
        $slug     = \slugify($titre_fr);

        if ($titre_fr === '') {
            Session::flash('error', 'Le titre est obligatoire.');
            Response::redirect(url('admin/dossiers/nouveau'));
            return;
        }

        DB::execute(
            "INSERT INTO dossiers
                (slug, titre_fr, titre_en, description_fr, powerbi_url, statut, created_by, created_at, updated_at)
             VALUES (:sl, :tf, :te, :desc, :pb, :st, :uid, NOW(), NOW())",
            [
                ':sl'   => $slug,
                ':tf'   => $titre_fr,
                ':te'   => trim(Request::post('titre_en', '')),
                ':desc' => trim(Request::post('description_fr', '')),
                ':pb'   => trim(Request::post('powerbi_url', '')) ?: null,
                ':st'   => 'brouillon',
                ':uid'  => Auth::id(),
            ]
        );
        $id = DB::lastInsertId();

        $this->audit('CREATE_DOSSIER', "Création dossier id=$id");

        Session::flash('success', 'Dossier créé. Vous pouvez maintenant l\'éditer.');
        Response::redirect(url("admin/dossiers/$id/modifier"));
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
