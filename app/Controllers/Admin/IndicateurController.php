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
 * Contrôleur admin – gestion des indicateurs (CRUD complet)
 * Colonnes réelles : slug, libelle_fr, libelle_en, definition_fr, definition_en,
 *   methode_calcul, donnees_requises, source_details, entite_id, thematique_id,
 *   unite_id, frequence_id, type_graphes, statut, prochaine_maj,
 *   contact_nom, contact_email, licence, notes, ordre
 * indicateur_niveaux : indicateur_id, niveau_desagregation_id
 */
class IndicateurController
{
    /* ------------------------------------------------------------------ */
    /*  LISTE                                                               */
    /* ------------------------------------------------------------------ */
    public function index(array $params = []): void
    {
        Auth::requireRole('validateur', 'admin', 'super_admin');

        $search     = Request::get('q', '');
        $thematique = Request::get('thematique_id', '');
        $statut     = Request::get('statut', '');
        $page       = max(1, (int) Request::get('page', 1));
        $perPage    = 25;
        $offset     = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $binds  = [];

        if ($search !== '') {
            $where[]      = '(i.libelle_fr LIKE :q OR i.slug LIKE :q2)';
            $binds[':q']  = "%$search%";
            $binds[':q2'] = "%$search%";
        }
        if ($thematique !== '') {
            $where[]       = 'i.thematique_id = :tid';
            $binds[':tid'] = (int) $thematique;
        }
        if ($statut !== '') {
            $where[]      = 'i.statut = :st';
            $binds[':st'] = $statut;
        }

        $whereStr = implode(' AND ', $where);

        $total = DB::count(
            "SELECT COUNT(*) FROM indicateurs i WHERE $whereStr",
            $binds
        );

        $indicateurs = DB::query(
            "SELECT i.*, t.libelle_fr AS thematique, e.acronyme AS entite,
                    (SELECT COUNT(*) FROM observations o WHERE o.indicateur_id = i.id) AS nb_obs
             FROM indicateurs i
             LEFT JOIN thematiques t ON t.id = i.thematique_id
             LEFT JOIN entites e     ON e.id = i.entite_id
             WHERE $whereStr
             ORDER BY t.libelle_fr, i.libelle_fr
             LIMIT :lim OFFSET :off",
            array_merge($binds, [':lim' => $perPage, ':off' => $offset])
        );

        $thematiques = DB::query("SELECT id, libelle_fr FROM thematiques ORDER BY libelle_fr");
        $totalPages  = (int) ceil($total / $perPage);

        View::renderWithLayout('admin/indicateurs/index', compact(
            'indicateurs', 'total', 'page', 'totalPages',
            'thematiques', 'search', 'thematique', 'statut'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE CRÉATION                                                 */
    /* ------------------------------------------------------------------ */
    public function create(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $thematiques = DB::query("SELECT id, libelle_fr FROM thematiques ORDER BY libelle_fr");
        $entites     = DB::query("SELECT id, acronyme, libelle AS libelle_fr FROM entites ORDER BY acronyme");
        $frequences  = DB::query("SELECT id, libelle AS libelle_fr FROM frequences ORDER BY libelle");
        $unites      = DB::query("SELECT id, libelle AS libelle_fr, symbole FROM unites ORDER BY libelle");
        $niveaux     = DB::query("SELECT id, libelle FROM niveaux_desagregation ORDER BY libelle");

        $indicateur = [];   // vide pour le formulaire création
        $niveauxSel = [];

        View::renderWithLayout('admin/indicateurs/form', compact(
            'indicateur', 'thematiques', 'entites', 'frequences', 'unites', 'niveaux', 'niveauxSel'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  STORE (POST création)                                               */
    /* ------------------------------------------------------------------ */
    public function store(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/indicateurs/nouveau'));
            return;
        }

        $data   = $this->validateForm();
        $errors = $data['errors'];
        unset($data['errors']);

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            Session::flash('old', Request::post());
            Response::redirect(url('admin/indicateurs/nouveau'));
            return;
        }

        // Vérif unicité slug
        $exists = DB::count(
            "SELECT COUNT(*) FROM indicateurs WHERE slug = :slug",
            [':slug' => $data[':slug']]
        );
        if ($exists > 0) {
            Session::flash('error', 'Ce slug est déjà utilisé. Choisissez-en un autre.');
            Session::flash('old', Request::post());
            Response::redirect(url('admin/indicateurs/nouveau'));
            return;
        }

        DB::execute(
            "INSERT INTO indicateurs
                (slug, libelle_fr, libelle_en, definition_fr, definition_en,
                 methode_calcul, donnees_requises, source_details,
                 thematique_id, entite_id, frequence_id, unite_id,
                 statut, notes, created_at, updated_at)
             VALUES
                (:slug, :lf, :le, :df, :de,
                 :mc, :dr, :sd,
                 :tid, :eid, :fid, :uid,
                 :statut, :notes, NOW(), NOW())",
            $data
        );
        $id = DB::lastInsertId();

        // Niveaux de désagrégation
        $niveaux = Request::post('niveaux', []);
        if (is_array($niveaux)) {
            foreach ($niveaux as $nid) {
                DB::execute(
                    "INSERT IGNORE INTO indicateur_niveaux
                        (indicateur_id, niveau_desagregation_id)
                     VALUES (:iid, :nid)",
                    [':iid' => $id, ':nid' => (int) $nid]
                );
            }
        }

        $this->audit('CREATE_INDICATEUR', "Création indicateur id=$id slug={$data[':slug']}");

        Session::flash('success', 'Indicateur créé avec succès.');
        Response::redirect(url("admin/indicateurs/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE ÉDITION                                                  */
    /* ------------------------------------------------------------------ */
    public function edit(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);
        $indicateur = DB::queryOne(
            "SELECT * FROM indicateurs WHERE id = :id",
            [':id' => $id]
        );
        if (!$indicateur) {
            Response::notFound();
            return;
        }

        $thematiques = DB::query("SELECT id, libelle_fr FROM thematiques ORDER BY libelle_fr");
        $entites     = DB::query("SELECT id, acronyme, libelle AS libelle_fr FROM entites ORDER BY acronyme");
        $frequences  = DB::query("SELECT id, libelle AS libelle_fr FROM frequences ORDER BY libelle");
        $unites      = DB::query("SELECT id, libelle AS libelle_fr, symbole FROM unites ORDER BY libelle");
        $niveaux     = DB::query("SELECT id, libelle FROM niveaux_desagregation ORDER BY libelle");

        $niveauxSel = array_column(
            DB::query(
                "SELECT niveau_desagregation_id FROM indicateur_niveaux
                 WHERE indicateur_id = :id",
                [':id' => $id]
            ),
            'niveau_desagregation_id'
        );

        View::renderWithLayout('admin/indicateurs/form', compact(
            'indicateur', 'thematiques', 'entites', 'frequences', 'unites', 'niveaux', 'niveauxSel'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE (POST édition)                                               */
    /* ------------------------------------------------------------------ */
    public function update(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url("admin/indicateurs/$id/modifier"));
            return;
        }

        $indicateur = DB::queryOne(
            "SELECT id FROM indicateurs WHERE id = :id",
            [':id' => $id]
        );
        if (!$indicateur) {
            Response::notFound();
            return;
        }

        $data   = $this->validateForm();
        $errors = $data['errors'];
        unset($data['errors']);

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            Response::redirect(url("admin/indicateurs/$id/modifier"));
            return;
        }

        // Vérif unicité slug (hors lui-même)
        $exists = DB::count(
            "SELECT COUNT(*) FROM indicateurs WHERE slug = :slug AND id != :id",
            [':slug' => $data[':slug'], ':id' => $id]
        );
        if ($exists > 0) {
            Session::flash('error', 'Ce slug est déjà utilisé.');
            Response::redirect(url("admin/indicateurs/$id/modifier"));
            return;
        }

        DB::execute(
            "UPDATE indicateurs SET
                slug = :slug, libelle_fr = :lf, libelle_en = :le,
                definition_fr = :df, definition_en = :de,
                methode_calcul = :mc, donnees_requises = :dr, source_details = :sd,
                thematique_id = :tid, entite_id = :eid, frequence_id = :fid, unite_id = :uid,
                statut = :statut, notes = :notes,
                updated_at = NOW()
             WHERE id = :iid",
            array_merge($data, [':iid' => $id])
        );

        // Mettre à jour les niveaux de désagrégation
        DB::execute(
            "DELETE FROM indicateur_niveaux WHERE indicateur_id = :id",
            [':id' => $id]
        );
        $niveaux = Request::post('niveaux', []);
        if (is_array($niveaux)) {
            foreach ($niveaux as $nid) {
                DB::execute(
                    "INSERT IGNORE INTO indicateur_niveaux
                        (indicateur_id, niveau_desagregation_id)
                     VALUES (:iid, :nid)",
                    [':iid' => $id, ':nid' => (int) $nid]
                );
            }
        }

        $this->audit('UPDATE_INDICATEUR', "Mise à jour indicateur id=$id");

        Session::flash('success', 'Indicateur mis à jour.');
        Response::redirect(url("admin/indicateurs/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  TOGGLE STATUT                                                       */
    /* ------------------------------------------------------------------ */
    public function toggleStatut(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id  = (int) ($params['id'] ?? 0);
        $ind = DB::queryOne(
            "SELECT id, statut FROM indicateurs WHERE id = :id",
            [':id' => $id]
        );

        if (!$ind) {
            Response::json(['error' => 'Not found'], 404);
            return;
        }

        // statut ENUM : 'actif','archive','brouillon'
        $nouveau = $ind['statut'] === 'actif' ? 'archive' : 'actif';
        DB::execute(
            "UPDATE indicateurs SET statut = :s, updated_at = NOW() WHERE id = :id",
            [':s' => $nouveau, ':id' => $id]
        );

        $this->audit('TOGGLE_INDICATEUR', "Indicateur id=$id statut→$nouveau");

        if (Request::isAjax()) {
            Response::json(['statut' => $nouveau]);
            return;
        }
        Session::flash('success', "Statut changé : $nouveau");
        Response::redirect(url('admin/indicateurs'));
    }

    /* ------------------------------------------------------------------ */
    /*  SUPPRESSION                                                         */
    /* ------------------------------------------------------------------ */
    public function delete(array $params = []): void
    {
        Auth::requireRole('super_admin');

        $id = (int) ($params['id'] ?? 0);

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/indicateurs'));
            return;
        }

        $nbObs = DB::count(
            "SELECT COUNT(*) FROM observations WHERE indicateur_id = :id",
            [':id' => $id]
        );
        if ($nbObs > 0) {
            Session::flash('error', "Impossible de supprimer : $nbObs observation(s) liée(s). Archivez l'indicateur à la place.");
            Response::redirect(url('admin/indicateurs'));
            return;
        }

        DB::execute("DELETE FROM indicateur_niveaux WHERE indicateur_id = :id", [':id' => $id]);
        DB::execute("DELETE FROM indicateurs WHERE id = :id", [':id' => $id]);

        $this->audit('DELETE_INDICATEUR', "Suppression indicateur id=$id");

        Session::flash('success', 'Indicateur supprimé.');
        Response::redirect(url('admin/indicateurs'));
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS PRIVÉS                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Valide et retourne les binds PDO pour INSERT / UPDATE.
     * Clés : errors, :slug, :lf, :le, :df, :de, :mc, :dr, :sd,
     *        :tid, :eid, :fid, :uid, :statut, :notes
     */
    private function validateForm(): array
    {
        $errors = [];

        // Slug : auto-généré depuis libelle_fr si absent
        $slug = trim(Request::post('slug', ''));
        if ($slug === '') {
            $slug = slugify(Request::post('libelle_fr', ''));
        }
        if ($slug === '') {
            $errors[] = 'Impossible de générer un slug (libellé FR manquant).';
        }

        $libelleFr = trim(Request::post('libelle_fr', ''));
        if ($libelleFr === '') {
            $errors[] = 'Le libellé français est obligatoire.';
        }

        $tid = (int) Request::post('thematique_id', 0);
        if ($tid <= 0) {
            $errors[] = 'La thématique est obligatoire.';
        }

        $statut = Request::post('statut', 'actif');
        if (!in_array($statut, ['actif', 'archive', 'brouillon'], true)) {
            $statut = 'actif';
        }

        return [
            'errors'  => $errors,
            ':slug'   => $slug,
            ':lf'     => $libelleFr,
            ':le'     => trim(Request::post('libelle_en', '')),
            ':df'     => trim(Request::post('definition_fr', '')),
            ':de'     => trim(Request::post('definition_en', '')),
            ':mc'     => trim(Request::post('methode_calcul', '')),
            ':dr'     => trim(Request::post('donnees_requises', '')),
            ':sd'     => trim(Request::post('source_details', '')),
            ':tid'    => $tid,
            ':eid'    => (int) Request::post('entite_id', 0) ?: null,
            ':fid'    => (int) Request::post('frequence_id', 0) ?: null,
            ':uid'    => (int) Request::post('unite_id', 0) ?: null,
            ':statut' => $statut,
            ':notes'  => trim(Request::post('notes', '')),
        ];
    }

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
