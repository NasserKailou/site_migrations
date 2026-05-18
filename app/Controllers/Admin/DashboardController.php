<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;
use App\Core\Database as DB;
use App\Models\Indicateur;
use App\Models\Observation;

/** Tableau de bord admin */
class DashboardController
{
    public function index(array $params = []): void
    {
        Auth::require();

        // KPIs observations
        $kpis = Observation::kpis();

        // KPIs indicateurs
        $kpis['indicateurs_actifs'] = DB::count('indicateurs', "statut='actif'");
        $kpis['indicateurs_draft']  = DB::count('indicateurs', "statut='brouillon'");

        // En attente de validation
        $a_valider = Observation::aValider();

        // Saisies par mois
        $par_mois = Observation::parMois();

        // Indicateurs en retard de MAJ
        $en_retard = DB::query(
            "SELECT id, slug, libelle_fr, prochaine_maj
             FROM indicateurs
             WHERE prochaine_maj < CURDATE() AND statut = 'actif'
             ORDER BY prochaine_maj ASC
             LIMIT 10"
        );

        // Activité récente (audit log)
        $activite = DB::query(
            "SELECT a.action, a.created_at, a.details, a.table_cible,
                    u.prenom, u.nom
             FROM audit_log a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT 20"
        );

        // Complétude par thématique
        $completude = DB::query(
            "SELECT t.libelle_fr, t.couleur,
                    COUNT(i.id) AS total_ind,
                    SUM(CASE WHEN EXISTS(
                        SELECT 1 FROM observations o
                        WHERE o.indicateur_id = i.id AND o.statut = 'publie'
                        AND o.periode_debut >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR)
                    ) THEN 1 ELSE 0 END) AS ind_avec_donnees
             FROM thematiques t
             LEFT JOIN indicateurs i ON i.thematique_id = t.id AND i.statut = 'actif'
             GROUP BY t.id
             ORDER BY t.ordre"
        );

        View::renderWithLayout('admin/dashboard', compact(
            'kpis','a_valider','par_mois','en_retard','activite','completude'
        ), 'layouts/admin');
    }
}
