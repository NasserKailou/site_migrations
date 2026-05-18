<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database as DB;
use App\Models\Indicateur;

class PageController
{
    public function aPropos(array $params = []): void
    {
        $stats = Indicateur::stats();
        View::renderWithLayout('public/a_propos_body', compact('stats'));
    }

    public function contact(array $params = []): void
    {
        View::renderWithLayout('public/contact_body');
    }

    public function contactPost(array $params = []): void
    {
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token invalide.');
            Response::redirect(url('contact'));
        }
        $nom     = trim(Request::post('nom', ''));
        $email   = trim(Request::post('email', ''));
        $sujet   = trim(Request::post('sujet', ''));
        $message = trim(Request::post('message', ''));

        if (!$nom || !$email || !$message || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Veuillez remplir tous les champs obligatoires avec des données valides.');
            Response::redirect(url('contact'));
        }
        // Log le message (TODO: envoyer email via PHPMailer)
        DB::execute("INSERT INTO audit_log (action, details, ip, created_at) VALUES ('contact_form', ?, ?, NOW())",
            [json_encode(compact('nom','email','sujet')), Request::ip()]);
        Session::flash('success', 'Votre message a été envoyé. Nous vous répondrons dans les meilleurs délais.');
        Response::redirect(url('contact'));
    }

    public function newsletter(array $params = []): void
    {
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Response::redirect(url());
        }
        $email = strtolower(trim(Request::post('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            Response::redirect(url());
        }
        try {
            DB::execute(
                "INSERT INTO newsletter_abonnes (email, token_unsub, created_at) VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE actif=1",
                [$email, bin2hex(random_bytes(16))]
            );
            Session::flash('success', 'Inscription newsletter confirmée !');
        } catch (\Throwable) {
            Session::flash('info', 'Vous êtes déjà inscrit(e) à la newsletter.');
        }
        Response::redirect(url());
    }

    public function sitemap(array $params = []): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        $baseUrl  = \App\Core\View::baseUrl();
        $indicateurs = DB::query("SELECT slug, updated_at FROM indicateurs WHERE statut='actif' ORDER BY slug");
        $dossiers    = DB::query("SELECT slug, updated_at FROM dossiers WHERE statut='publie'");
        $pages = [
            ['url' => '', 'priority' => '1.0', 'freq' => 'daily'],
            ['url' => '/indicateurs', 'priority' => '0.9', 'freq' => 'daily'],
            ['url' => '/a-propos',   'priority' => '0.5', 'freq' => 'monthly'],
            ['url' => '/contact',    'priority' => '0.4', 'freq' => 'monthly'],
        ];
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';
        foreach ($pages as $p) {
            echo "<url><loc>{$baseUrl}{$p['url']}</loc><priority>{$p['priority']}</priority><changefreq>{$p['freq']}</changefreq></url>";
        }
        foreach ($indicateurs as $ind) {
            echo "<url><loc>{$baseUrl}/indicateurs/".htmlspecialchars($ind['slug'])."</loc><lastmod>".substr($ind['updated_at'],0,10)."</lastmod><priority>0.8</priority><changefreq>weekly</changefreq></url>";
        }
        foreach ($dossiers as $d) {
            echo "<url><loc>{$baseUrl}/dossiers/".htmlspecialchars($d['slug'])."</loc><lastmod>".substr($d['updated_at'],0,10)."</lastmod><priority>0.7</priority></url>";
        }
        echo '</urlset>';
        exit;
    }

    public function robots(array $params = []): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $baseUrl = \App\Core\View::baseUrl();
        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /api/v1/\n";
        echo "Allow: /\n\n";
        echo "Sitemap: {$baseUrl}/sitemap.xml\n";
        exit;
    }
}
