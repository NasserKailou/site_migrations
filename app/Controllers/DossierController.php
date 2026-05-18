<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Core\Database as DB;
use App\Core\Response;
use App\Models\Indicateur;

class DossierController
{
    public function agadez(array $params = []): void
    {
        $dossier = DB::queryOne("SELECT * FROM dossiers WHERE slug='agadez' AND statut='publie'");
        if (!$dossier) {
            // On affiche quand même la page avec le contenu par défaut
            $dossier = ['titre_fr' => 'Agadez, carrefour migratoire', 'powerbi_url' => ''];
        }

        // Indicateurs Agadez (entité géographique Agadez)
        $donnees_agadez = DB::query(
            "SELECT o.periode_debut, o.total, o.masculin, o.feminin,
                    i.libelle_fr AS indicateur, i.slug AS ind_slug,
                    t.libelle_fr AS thematique, t.couleur AS them_couleur
             FROM observations o
             JOIN indicateurs i ON i.id = o.indicateur_id
             JOIN thematiques t ON t.id = i.thematique_id
             JOIN geo_entites g ON g.id = o.geo_entite_id
             WHERE g.code = 'AG' AND o.statut = 'publie' AND o.total IS NOT NULL
             ORDER BY o.periode_debut DESC
             LIMIT 50"
        );

        // Documents du dossier
        $documents = [];
        if (!empty($dossier['id'])) {
            $documents = DB::query(
                "SELECT * FROM dossier_documents WHERE dossier_id = ? ORDER BY ordre, titre",
                [(int)$dossier['id']]
            );
        }

        $metaTitle = 'Agadez, carrefour migratoire — PNDM Niger';
        $metaDesc  = 'Données et analyses sur les flux migratoires de la région d\'Agadez et du corridor Tamanrasset-Assamaka-Agadez. Tableau de bord Power BI.';

        View::renderWithLayout('public/agadez_body', compact(
            'dossier','donnees_agadez','documents','metaTitle','metaDesc'
        ));
    }

    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $dossier = DB::queryOne("SELECT * FROM dossiers WHERE slug=? AND statut='publie'", [$slug]);
        if (!$dossier) Response::notFound("Dossier introuvable.");

        $documents = DB::query("SELECT * FROM dossier_documents WHERE dossier_id=? ORDER BY ordre", [(int)$dossier['id']]);
        View::renderWithLayout('public/dossier_show', compact('dossier','documents'));
    }
}
