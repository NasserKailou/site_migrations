<?php
declare(strict_types=1);
/**
 * PNDM — Point d'entrée unique (Front Controller)
 * Toutes les requêtes sont routées ici via .htaccess
 */

// Sécurité : interdire accès direct aux dossiers sensibles
define('PNDM_ROOT',   dirname(__DIR__));
define('APP_ROOT',    PNDM_ROOT . '/app');
define('PUB_ROOT',    __DIR__);
define('PUBLIC_PATH', __DIR__); // alias for upload handlers

// Chargement de la configuration
require_once APP_ROOT . '/Core/helpers.php';
require_once APP_ROOT . '/Core/Config.php';
require_once APP_ROOT . '/Core/Database.php';
require_once APP_ROOT . '/Core/Session.php';
require_once APP_ROOT . '/Core/Request.php';
require_once APP_ROOT . '/Core/Response.php';
require_once APP_ROOT . '/Core/View.php';
require_once APP_ROOT . '/Core/Router.php';
require_once APP_ROOT . '/Core/Auth.php';
require_once APP_ROOT . '/Core/RateLimit.php';

// Autoloader PSR-4 simple
spl_autoload_register(function (string $class): void {
    $map = [
        'App\\Controllers\\'       => APP_ROOT . '/Controllers/',
        'App\\Controllers\\Admin\\'=> APP_ROOT . '/Controllers/Admin/',
        'App\\Controllers\\Api\\'  => APP_ROOT . '/Controllers/Api/',
        'App\\Models\\'            => APP_ROOT . '/Models/',
        'App\\Services\\'          => APP_ROOT . '/Services/',
    ];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Initialisation session sécurisée
\App\Core\Session::start();

// Gestion des erreurs
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) return false;
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function (\Throwable $e): void {
    error_log('[PNDM] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (\App\Core\Config::get('app.debug', false) === 'true') {
        http_response_code(500);
        echo '<pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        \App\Core\View::render('errors/500');
    }
});

// Initialisation et dispatch
$router = new \App\Core\Router();

// ── Routes publiques ──────────────────────────────────────────
$router->get('/',                        'HomeController@index');
$router->get('/indicateurs',             'IndicateurController@index');
$router->get('/indicateurs/{slug}',      'IndicateurController@show');
$router->get('/indicateurs/{slug}/data', 'IndicateurController@data');
$router->get('/agadez',                  'DossierController@agadez');
$router->get('/dossiers/agadez',         'DossierController@agadez');
$router->get('/dossiers/{slug}',         'DossierController@show');
$router->get('/a-propos',                'PageController@aPropos');
$router->get('/contact',                 'PageController@contact');
$router->post('/contact',                'PageController@contactPost');
$router->post('/newsletter',             'PageController@newsletter');
$router->get('/sitemap.xml',             'PageController@sitemap');
$router->get('/robots.txt',              'PageController@robots');

// ── API publique ──────────────────────────────────────────────
$router->get('/api/v1/indicateurs',                      'Api\ApiController@indicateurs');
$router->get('/api/v1/indicateurs/{slug}',               'Api\ApiController@indicateur');
$router->get('/api/v1/indicateurs/{slug}/donnees',       'Api\ApiController@donnees');
$router->get('/api/v1/indicateurs/{slug}/export',        'Api\ApiController@export');
$router->get('/api/v1/thematiques',                      'Api\ApiController@thematiques');
$router->get('/api/v1/geo',                              'Api\ApiController@geo');
$router->get('/api/v1/meta',                             'Api\ApiController@meta');

// ── Administration ────────────────────────────────────────────
$router->get('/admin/login',             'Admin\AuthController@loginForm');
$router->post('/admin/login',            'Admin\AuthController@login');
$router->get('/admin/logout',            'Admin\AuthController@logout');
$router->get('/admin/2fa',               'Admin\AuthController@twoFactorForm');
$router->post('/admin/2fa',              'Admin\AuthController@twoFactor');
$router->get('/admin/reset-password',    'Admin\AuthController@resetForm');
$router->post('/admin/reset-password',   'Admin\AuthController@resetRequest');
$router->get('/admin/reset/{token}',     'Admin\AuthController@resetConfirmForm');
$router->post('/admin/reset/{token}',    'Admin\AuthController@resetConfirm');

// Dashboard
$router->get('/admin',                   'Admin\DashboardController@index');
$router->get('/admin/dashboard',         'Admin\DashboardController@index');

// Indicateurs admin
$router->get('/admin/indicateurs',                    'Admin\IndicateurController@index');
$router->get('/admin/indicateurs/nouveau',            'Admin\IndicateurController@create');
$router->post('/admin/indicateurs',                   'Admin\IndicateurController@store');
$router->get('/admin/indicateurs/{id}/modifier',      'Admin\IndicateurController@edit');
$router->post('/admin/indicateurs/{id}/modifier',     'Admin\IndicateurController@update');
$router->post('/admin/indicateurs/{id}/toggle-statut','Admin\IndicateurController@toggleStatut');
$router->post('/admin/indicateurs/{id}/supprimer',    'Admin\IndicateurController@delete');

// Données admin
$router->get('/admin/donnees',                   'Admin\DonneeController@index');
$router->get('/admin/donnees/saisie',             'Admin\DonneeController@saisie');
$router->post('/admin/donnees',                  'Admin\DonneeController@store');
$router->post('/admin/donnees/autosave',         'Admin\DonneeController@autosave');
$router->get('/admin/donnees/{id}/modifier',     'Admin\DonneeController@edit');
$router->post('/admin/donnees/{id}/modifier',    'Admin\DonneeController@update');
$router->post('/admin/donnees/{id}/soumettre',   'Admin\DonneeController@submit');
$router->post('/admin/donnees/{id}/valider',     'Admin\DonneeController@validate');
$router->post('/admin/donnees/{id}/publier',     'Admin\DonneeController@publish');
$router->post('/admin/donnees/{id}/rejeter',     'Admin\DonneeController@reject');

// Import
$router->get('/admin/import',               'Admin\ImportController@index');
$router->get('/admin/import/template',      'Admin\ImportController@template');
$router->post('/admin/import/dry-run',      'Admin\ImportController@dryRun');
$router->post('/admin/import/commit',       'Admin\ImportController@commit');

// Dossiers admin
$router->get('/admin/dossiers',                     'Admin\DossierController@index');
$router->get('/admin/dossiers/nouveau',             'Admin\DossierController@create');
$router->post('/admin/dossiers',                    'Admin\DossierController@store');
$router->get('/admin/dossiers/{id}/modifier',       'Admin\DossierController@edit');
$router->post('/admin/dossiers/{id}/modifier',      'Admin\DossierController@update');

// Utilisateurs admin
$router->get('/admin/utilisateurs',                      'Admin\UserController@index');
$router->get('/admin/utilisateurs/nouveau',              'Admin\UserController@create');
$router->post('/admin/utilisateurs',                     'Admin\UserController@store');
$router->get('/admin/utilisateurs/{id}/modifier',        'Admin\UserController@edit');
$router->post('/admin/utilisateurs/{id}/modifier',       'Admin\UserController@update');
$router->post('/admin/utilisateurs/{id}/toggle-statut',  'Admin\UserController@toggleStatut');
$router->post('/admin/utilisateurs/{id}/reset-2fa',      'Admin\UserController@reset2fa');

// 2FA et reset routes (méthode POST de vérification)
$router->post('/admin/2fa/verify',        'Admin\AuthController@twoFactor');
$router->get('/admin/reset',              'Admin\AuthController@resetForm');
$router->post('/admin/reset',             'Admin\AuthController@resetRequest');
$router->get('/admin/reset/confirm',      'Admin\AuthController@resetConfirmForm');
$router->post('/admin/reset/confirm',     'Admin\AuthController@resetConfirm');

// Dispatch
try {
    $router->dispatch(\App\Core\Request::uri(), \App\Core\Request::method());
} catch (\App\Core\NotFoundException $e) {
    http_response_code(404);
    \App\Core\View::render('errors/404', ['message' => $e->getMessage()]);
} catch (\App\Core\ForbiddenException $e) {
    http_response_code(403);
    \App\Core\View::render('errors/404', ['message' => $e->getMessage()]);
}
