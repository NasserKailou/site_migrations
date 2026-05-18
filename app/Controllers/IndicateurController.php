<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database as DB;
use App\Models\Indicateur;

/** Contrôleur des indicateurs publics */
class IndicateurController
{
    /** Liste des indicateurs avec filtres */
    public function index(array $params = []): void
    {
        $filters = [
            'q'             => Request::get('q',    ''),
            'thematique_id' => Request::get('them_id', ''),
            'entite_id'     => Request::get('entite_id',''),
            'frequence_id'  => Request::get('freq_id', ''),
            'limit'         => 20,
            'offset'        => (max(1, (int)Request::get('page', 1)) - 1) * 20,
        ];

        // Aussi filtrer par slug thématique
        $themSlug = Request::get('them', '');
        if ($themSlug) {
            $them = DB::queryOne('SELECT id FROM thematiques WHERE slug = ?', [$themSlug]);
            if ($them) $filters['thematique_id'] = $them['id'];
        }

        $indicateurs = Indicateur::all($filters);
        $total       = Indicateur::count($filters);
        $page        = max(1, (int)Request::get('page', 1));
        $totalPages  = (int)ceil($total / 20);

        $thematiques = DB::query(
            "SELECT t.id, t.slug, t.libelle_fr, t.couleur, COUNT(i.id) AS nb
             FROM thematiques t
             LEFT JOIN indicateurs i ON i.thematique_id = t.id AND i.statut='actif'
             GROUP BY t.id ORDER BY t.ordre"
        );
        $entites     = DB::query("SELECT id, acronyme, libelle FROM entites ORDER BY acronyme");
        $frequences  = DB::query("SELECT id, libelle FROM frequences ORDER BY id");

        $metaTitle = 'Indicateurs — Données sur la migration au Niger';
        $metaDesc  = 'Explorez les ' . $total . ' indicateurs officiels sur la migration au Niger. Filtrez par thématique, source, période.';

        View::renderWithLayout('public/indicateurs_body', compact(
            'indicateurs','total','page','totalPages','thematiques',
            'entites','frequences','filters','themSlug',
            'metaTitle','metaDesc'
        ));
    }

    /** Fiche détaillée d'un indicateur */
    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $indicateur = Indicateur::findBySlug($slug);
        if (!$indicateur) {
            Response::notFound("Indicateur introuvable : {$slug}");
        }

        // Données pour graphique initial (toutes les données publiées)
        $donnees  = Indicateur::donnees((int)$indicateur['id']);
        $niveaux  = Indicateur::niveauxDisponibles((int)$indicateur['id']);

        // Années disponibles
        $annees = array_unique(array_map(fn($d) => (int)substr($d['periode_debut'], 0, 4), $donnees));
        sort($annees);

        // JSON-LD Dataset pour Google Dataset Search
        $jsonLd = json_encode([
            '@context'   => 'https://schema.org',
            '@type'      => 'Dataset',
            'name'       => $indicateur['libelle_fr'],
            'description'=> $indicateur['definition_fr'] ?? '',
            'url'        => View::url('indicateurs/' . $slug),
            'creator'    => ['@type' => 'Organization', 'name' => 'Institut National de la Statistique — Niger'],
            'publisher'  => ['@type' => 'Organization', 'name' => 'INS Niger', 'url' => 'https://www.ins.niger.ne'],
            'license'    => 'https://opendatacommons.org/licenses/odbl/',
            'distribution' => [
                ['@type' => 'DataDownload', 'encodingFormat' => 'text/csv',
                 'contentUrl' => View::url('api/v1/indicateurs/' . $slug . '/export?format=csv')],
                ['@type' => 'DataDownload', 'encodingFormat' => 'application/json',
                 'contentUrl' => View::url('api/v1/indicateurs/' . $slug . '/export?format=json')],
            ],
            'temporalCoverage' => implode('/', array_filter([
                $annees ? (string)min($annees) : null,
                $annees ? (string)max($annees) : null,
            ])),
            'spatialCoverage' => ['@type' => 'Place', 'name' => 'Niger', 'geo' => ['@type' => 'GeoShape', 'box' => '11.693 0.166 23.518 15.996']],
            'keywords' => ['migration', 'Niger', $indicateur['thematique'] ?? '', $indicateur['source_acronyme'] ?? ''],
            'variableMeasured' => $indicateur['libelle_fr'],
            'measurementTechnique' => $indicateur['methode_calcul'] ?? '',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $metaTitle = esc($indicateur['libelle_fr']) . ' — PNDM Niger';
        $metaDesc  = mb_substr(strip_tags($indicateur['definition_fr'] ?? $indicateur['libelle_fr']), 0, 155);

        $extraJs = '<script>document.addEventListener("DOMContentLoaded",()=>{initIndicatorCharts("' . addslashes($slug) . '");});</script>';
        View::renderWithLayout('public/indicateur_show_body', compact(
            'indicateur','donnees','niveaux','annees','jsonLd','metaTitle','metaDesc','slug','extraJs'
        ));
    }

    /** Données JSON pour graph AJAX */
    public function data(array $params): void
    {
        $slug = $params['slug'] ?? '';
        $ind  = Indicateur::findBySlug($slug);
        if (!$ind) Response::json(['error' => 'Not found'], 404);

        $filters = [
            'niveau_id'  => Request::get('niveau_id'),
            'valeur'     => Request::get('valeur'),
            'year_start' => Request::get('year_start'),
            'year_end'   => Request::get('year_end'),
            'geo_id'     => Request::get('geo_id'),
        ];

        $donnees = Indicateur::donnees((int)$ind['id'], array_filter($filters));
        Response::json(['data' => $donnees, 'indicateur' => ['id' => $ind['id'], 'slug' => $ind['slug']]]);
    }
}
