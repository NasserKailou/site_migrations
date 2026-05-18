<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Core\Database as DB;
use App\Models\Indicateur;

/** Contrôleur de la page d'accueil */
class HomeController
{
    public function index(array $params = []): void
    {
        // Stats globales
        $stats = Indicateur::stats();

        // Indicateurs phares
        $indicateurs_phares = Indicateur::phares();

        // Données sparkline pour chaque indicateur phare
        $sparklines_data = [];
        foreach ($indicateurs_phares as $ind) {
            $data = Indicateur::sparkline((int)$ind['id']);
            if ($data) {
                $sparklines_data[$ind['id']] = array_reverse($data);
            }
        }

        // Thématiques avec compteur
        $thematiques = DB::query(
            "SELECT t.id, t.slug, t.libelle_fr, t.libelle_en, t.description_fr,
                    t.icone, t.couleur, t.ordre,
                    COUNT(i.id) AS nb_indicateurs
             FROM thematiques t
             LEFT JOIN indicateurs i ON i.thematique_id = t.id AND i.statut='actif'
             GROUP BY t.id
             ORDER BY t.ordre ASC"
        );

        // Dernières mises à jour (10 dernières publications)
        $derniers_updates = DB::query(
            "SELECT o.id, o.periode_debut, o.total, o.publie_le, o.updated_at,
                    i.libelle_fr AS indicateur, i.slug AS indicateur_slug,
                    t.libelle_fr AS thematique
             FROM observations o
             JOIN indicateurs i ON i.id = o.indicateur_id
             JOIN thematiques t ON t.id = i.thematique_id
             WHERE o.statut = 'publie'
             ORDER BY COALESCE(o.publie_le, o.updated_at) DESC
             LIMIT 10"
        );

        View::renderWithLayout('public/home_body', compact(
            'stats', 'indicateurs_phares', 'sparklines_data', 'thematiques', 'derniers_updates'
        ));
    }
}
