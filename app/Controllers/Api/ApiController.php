<?php
declare(strict_types=1);
namespace App\Controllers\Api;

use App\Core\Response;
use App\Core\Request;
use App\Core\Database as DB;
use App\Core\RateLimit;
use App\Models\Indicateur;
use App\Models\Observation;

/**
 * API REST publique — /api/v1/
 * Format : JSON (default), CSV et XLSX via ?format=
 * Auth optionnelle : header X-Api-Key pour quota augmenté
 */
class ApiController
{
    private function checkRateLimit(): void
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        $hasKey = !empty($apiKey);
        $keyId  = $hasKey ? 'key:' . $apiKey : 'ip:' . Request::ip();

        if (!RateLimit::limitApi($keyId, $hasKey)) {
            Response::json([
                'error'   => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Try again in 60 seconds.',
                'limits'  => ['anonymous' => 60, 'with_api_key' => 600],
            ], 429);
        }
    }

    private function wrap(array $data, int $total = 0, int $page = 1, int $perPage = 20): array
    {
        return [
            'success'     => true,
            'data'        => $data,
            'meta' => [
                'total'       => $total,
                'page'        => $page,
                'per_page'    => $perPage,
                'total_pages' => $perPage ? (int)ceil($total / $perPage) : 1,
                'generated'   => date('c'),
                'source'      => 'Institut National de la Statistique — Niger',
                'license'     => 'Open Data Commons ODbL',
            ]
        ];
    }

    /** GET /api/v1/indicateurs */
    public function indicateurs(array $params = []): void
    {
        $this->checkRateLimit();
        $filters = [
            'q'             => Request::get('q', ''),
            'thematique_id' => Request::get('them_id', ''),
            'entite_id'     => Request::get('entite_id', ''),
            'frequence_id'  => Request::get('freq_id', ''),
            'limit'         => min((int)Request::get('limit', 20), 100),
            'offset'        => (max(1, (int)Request::get('page', 1)) - 1) * min((int)Request::get('limit', 20), 100),
        ];
        $themSlug = Request::get('them', '');
        if ($themSlug) {
            $t = DB::queryOne('SELECT id FROM thematiques WHERE slug=?', [$themSlug]);
            if ($t) $filters['thematique_id'] = $t['id'];
        }
        $data  = Indicateur::all($filters);
        $total = Indicateur::count($filters);
        $page  = max(1, (int)Request::get('page', 1));
        Response::json($this->wrap($data, $total, $page, $filters['limit']));
    }

    /** GET /api/v1/indicateurs/{slug} */
    public function indicateur(array $params): void
    {
        $this->checkRateLimit();
        $slug = $params['slug'] ?? '';
        $ind  = Indicateur::findBySlug($slug);
        if (!$ind) Response::json(['error' => 'Not Found'], 404);
        Response::json(['success' => true, 'data' => $ind]);
    }

    /** GET /api/v1/indicateurs/{slug}/donnees */
    public function donnees(array $params): void
    {
        $this->checkRateLimit();
        $slug = $params['slug'] ?? '';
        $ind  = Indicateur::findBySlug($slug);
        if (!$ind) Response::json(['error' => 'Not Found'], 404);

        $filters = array_filter([
            'niveau_id'  => Request::get('niveau_id'),
            'valeur'     => Request::get('valeur'),
            'year_start' => Request::get('year_start'),
            'year_end'   => Request::get('year_end'),
            'geo_id'     => Request::get('geo_id'),
        ]);
        $data = Indicateur::donnees((int)$ind['id'], $filters);
        Response::json($this->wrap($data, count($data)));
    }

    /** GET /api/v1/indicateurs/{slug}/export */
    public function export(array $params): void
    {
        $this->checkRateLimit();
        $slug   = $params['slug'] ?? '';
        $ind    = Indicateur::findBySlug($slug);
        if (!$ind) Response::json(['error' => 'Not Found'], 404);

        $format = Request::get('format', 'csv');
        $data   = Indicateur::donnees((int)$ind['id']);

        // Log téléchargement
        DB::execute("INSERT INTO telechargements (indicateur_id, format, ip, created_at) VALUES (?,?,?,NOW())",
            [(int)$ind['id'], $format, Request::ip()]);

        if ($format === 'json') {
            Response::json(['data' => $data, 'indicateur' => $ind['libelle_fr']]);
        }

        // CSV
        $filename = 'pndm_' . $slug . '_' . date('Ymd') . '.csv';
        $out = fopen('php://output', 'wb');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
        fputcsv($out, ['indicateur','slug','periode_debut','annee','geo_region',
                       'niveau_desagreg','valeur_desagreg','masculin','feminin','trans_autre','total']);
        foreach ($data as $row) {
            fputcsv($out, [
                $ind['libelle_fr'],
                $ind['slug'],
                $row['periode_debut'],
                $row['annee'],
                $row['geo_region']       ?? '',
                $row['niveau_libelle']   ?? '',
                $row['niveau_desag_valeur'] ?? '',
                $row['masculin']         ?? '',
                $row['feminin']          ?? '',
                $row['trans_autre']      ?? '',
                $row['total']            ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    /** GET /api/v1/thematiques */
    public function thematiques(array $params = []): void
    {
        $this->checkRateLimit();
        $data = DB::query(
            "SELECT t.id, t.slug, t.libelle_fr, t.libelle_en, t.description_fr,
                    t.icone, t.couleur, t.ordre,
                    COUNT(i.id) AS nb_indicateurs
             FROM thematiques t
             LEFT JOIN indicateurs i ON i.thematique_id = t.id AND i.statut='actif'
             GROUP BY t.id ORDER BY t.ordre"
        );
        Response::json($this->wrap($data, count($data)));
    }

    /** GET /api/v1/geo */
    public function geo(array $params = []): void
    {
        $this->checkRateLimit();
        $data = DB::query("SELECT id, libelle, code, type, parent_id, lat, lng FROM geo_entites ORDER BY type, libelle");
        Response::json($this->wrap($data, count($data)));
    }

    /** GET /api/v1/meta */
    public function meta_endpoint(array $params = []): void
    {
        $this->checkRateLimit();
        $stats = Indicateur::stats();
        Response::json([
            'success' => true,
            'platform' => [
                'name'        => 'PNDM',
                'description' => 'Plateforme Nationale des Données sur la Migration — Niger',
                'publisher'   => 'Institut National de la Statistique — République du Niger',
                'url'         => url(''),
                'api_version' => '1.0',
                'license'     => 'ODbL',
            ],
            'stats' => $stats,
            'endpoints' => [
                ['path' => '/api/v1/indicateurs',                    'method' => 'GET', 'desc' => 'Liste des indicateurs'],
                ['path' => '/api/v1/indicateurs/{slug}',             'method' => 'GET', 'desc' => 'Fiche indicateur'],
                ['path' => '/api/v1/indicateurs/{slug}/donnees',     'method' => 'GET', 'desc' => 'Données d\'un indicateur'],
                ['path' => '/api/v1/indicateurs/{slug}/export',      'method' => 'GET', 'desc' => 'Export CSV/JSON'],
                ['path' => '/api/v1/thematiques',                    'method' => 'GET', 'desc' => 'Liste des thématiques'],
                ['path' => '/api/v1/geo',                            'method' => 'GET', 'desc' => 'Entités géographiques'],
            ],
        ]);
    }

    /** Alias pour la route /api/v1/meta */
    public function meta(array $params = []): void
    {
        $this->meta_endpoint($params);
    }
}
