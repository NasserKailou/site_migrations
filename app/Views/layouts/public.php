<?php
declare(strict_types=1);
/** Layout public principal */
$lang  = \App\Core\View::getLang();
$siteNom = 'PNDM — Plateforme Nationale des Données sur la Migration';
$metaTitle = $metaTitle ?? $siteNom;
$metaDesc  = $metaDesc  ?? 'Données officielles sur la migration au Niger publiées par l\'Institut National de la Statistique.';
$metaUrl   = $metaUrl   ?? (\App\Core\View::baseUrl() . $_SERVER['REQUEST_URI']);
$metaImg   = $metaImg   ?? url('assets/images/og-default.png');
?>
<!DOCTYPE html>
<html lang="<?= esc($lang) ?>" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f7a13e">
  <title><?= esc($metaTitle) ?></title>
  <meta name="description" content="<?= esc($metaDesc) ?>">
  <link rel="canonical" href="<?= esc($metaUrl) ?>">
  <?php if (isset($hreflang)): foreach ($hreflang as $hl): ?>
  <link rel="alternate" hreflang="<?= esc($hl['lang']) ?>" href="<?= esc($hl['url']) ?>">
  <?php endforeach; endif; ?>

  <!-- Open Graph -->
  <meta property="og:type"        content="<?= esc($ogType ?? 'website') ?>">
  <meta property="og:title"       content="<?= esc($metaTitle) ?>">
  <meta property="og:description" content="<?= esc($metaDesc) ?>">
  <meta property="og:url"         content="<?= esc($metaUrl) ?>">
  <meta property="og:image"       content="<?= esc($metaImg) ?>">
  <meta property="og:locale"      content="<?= $lang === 'fr' ? 'fr_FR' : 'en_US' ?>">
  <meta property="og:site_name"   content="PNDM">
  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= esc($metaTitle) ?>">
  <meta name="twitter:description" content="<?= esc($metaDesc) ?>">
  <meta name="twitter:image"       content="<?= esc($metaImg) ?>">

  <!-- JSON-LD Organisation -->
  <script type="application/ld+json">
  {"@context":"https://schema.org","@type":"Organization",
   "name":"Institut National de la Statistique — Niger",
   "alternateName":"INS Niger","url":"https://www.ins.niger.ne",
   "logo":"<?= url('assets/images/ins-logo.png') ?>",
   "sameAs":["https://stat-niger.org"]}
  </script>
  <?php if (isset($jsonLd)): ?>
  <script type="application/ld+json"><?= $jsonLd ?></script>
  <?php endif; ?>

  <!-- Fonts (preconnect + chargement asynchrone) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" onload="this.onload=null;this.rel='stylesheet'"
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lora:wght@600;700&display=swap">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"></noscript>

  <!-- Font Awesome (CDN avec fallback local) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- CSS principal (critique en-tête) -->
  <link rel="stylesheet" href="<?= asset('css/pndm.css') ?>">

  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">

  <!-- Favicons -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= url('assets/images/favicon.svg') ?>">
  <link rel="apple-touch-icon" href="<?= url('assets/images/favicon.svg') ?>">
</head>
<body>
<a href="#main-content" class="skip-link">Aller au contenu principal</a>

<!-- ── HEADER ─────────────────────────────────────────────────── -->
<header class="site-header" role="banner">
  <div class="container">
    <a href="<?= url() ?>" class="site-logo" aria-label="PNDM — Accueil">
      <img src="<?= url('assets/images/ins-logo.png') ?>" alt="Logo INS Niger" width="44" height="44">
      <span class="site-logo-text">
        <span>PNDM</span>
        <span>Institut National de la Statistique</span>
      </span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-expanded="false" aria-controls="siteNav" aria-label="Menu">
      <i class="fa-solid fa-bars" aria-hidden="true"></i>
    </button>

    <nav class="site-nav" id="siteNav" aria-label="Navigation principale">
      <a href="<?= url() ?>"               <?= $_SERVER['REQUEST_URI'] === '/' ? 'class="active" aria-current="page"' : '' ?>>Accueil</a>
      <a href="<?= url('indicateurs') ?>"  <?= str_starts_with($_SERVER['REQUEST_URI'],'/indicateurs') ? 'class="active" aria-current="page"' : '' ?>>Thématiques</a>
      <a href="<?= url('indicateurs') ?>#metadonnees"  >Métadonnées</a>
      <a href="<?= url('indicateurs') ?>#extraction"  >Extraction</a>
      <a href="<?= url('dossiers/agadez') ?>" <?= str_starts_with($_SERVER['REQUEST_URI'],'/dossiers/agadez') ? 'class="active" aria-current="page"' : '' ?>>Agadez</a>
      <a href="<?= url('a-propos') ?>" <?= str_starts_with($_SERVER['REQUEST_URI'],'/a-propos') ? 'class="active" aria-current="page"' : '' ?>>À propos</a>
      <a href="<?= url('contact') ?>" <?= str_starts_with($_SERVER['REQUEST_URI'],'/contact') ? 'class="active" aria-current="page"' : '' ?>>Contact</a>
      <a href="<?= url('admin') ?>" class="nav-cta"><i class="fa-solid fa-lock fa-xs" aria-hidden="true"></i> Admin</a>

      <div class="lang-switcher" role="group" aria-label="Langue">
        <button onclick="setLang('fr')" <?= $lang==='fr' ? 'class="active" aria-pressed="true"' : 'aria-pressed="false"' ?>>FR</button>
        <button onclick="setLang('en')" <?= $lang==='en' ? 'class="active" aria-pressed="true"' : 'aria-pressed="false"' ?>>EN</button>
      </div>
    </nav>
  </div>
</header>

<!-- ── FLASH MESSAGES ─────────────────────────────────────────── -->
<?php if ($flash['success'] || $flash['error'] || $flash['info']): ?>
<div class="container" style="margin-top:1rem" role="alert" aria-live="polite">
  <?php if ($flash['success']): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?= esc($flash['success']) ?></div><?php endif; ?>
  <?php if ($flash['error']):   ?><div class="alert alert-error"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i><?= esc($flash['error']) ?></div><?php endif; ?>
  <?php if ($flash['info']):    ?><div class="alert alert-info"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><?= esc($flash['info']) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<!-- ── CONTENU PRINCIPAL ──────────────────────────────────────── -->
<main id="main-content" tabindex="-1">
<?= $content ?>
</main>

<!-- ── FOOTER ─────────────────────────────────────────────────── -->
<footer class="site-footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <!-- Col 1 : À propos -->
      <div class="footer-col">
        <div class="footer-logo">
          <img src="<?= url('assets/images/ins-logo.png') ?>" alt="INS Niger" height="50">
        </div>
        <p class="footer-desc">
          La PNDM est la plateforme officielle de l'Institut National de la Statistique du Niger
          pour la diffusion des données sur la migration, développée avec l'appui de l'OIM.
        </p>
        <div class="footer-partners" aria-label="Partenaires">
          <img src="<?= url('assets/images/iom-logo.png') ?>"  alt="OIM / IOM" height="32">
          <img src="<?= url('assets/images/maeci-logo.png') ?>" alt="Ministère Affaires Étrangères Italie" height="28">
          <img src="<?= url('assets/images/maeci-logo.png') ?>" alt="UNHCR" height="28">
        </div>
      </div>
      <!-- Col 2 : Navigation -->
      <div class="footer-col">
        <h4>Navigation</h4>
        <ul>
          <li><a href="<?= url() ?>">Accueil</a></li>
          <li><a href="<?= url('indicateurs') ?>">Indicateurs</a></li>
          <li><a href="<?= url('dossiers/agadez') ?>">Dossier Agadez</a></li>
          <li><a href="<?= url('a-propos') ?>">À propos du PNDM</a></li>
          <li><a href="<?= url('contact') ?>">Contact</a></li>
        </ul>
      </div>
      <!-- Col 3 : Thématiques -->
      <div class="footer-col">
        <h4>Thématiques</h4>
        <ul>
          <li><a href="<?= url('indicateurs?them=main-oeuvre') ?>">Main-d'œuvre</a></li>
          <li><a href="<?= url('indicateurs?them=flux') ?>">Flux migratoires</a></li>
          <li><a href="<?= url('indicateurs?them=stock') ?>">Stock de migrations</a></li>
          <li><a href="<?= url('indicateurs?them=pdis') ?>">PDIs</a></li>
          <li><a href="<?= url('indicateurs?them=transferts') ?>">Transferts de fonds</a></li>
          <li><a href="<?= url('indicateurs?them=vulnerabilite') ?>">Vulnérabilité</a></li>
        </ul>
      </div>
      <!-- Col 4 : Ressources -->
      <div class="footer-col">
        <h4>Ressources</h4>
        <ul>
          <li><a href="/api/v1/" target="_blank" rel="noopener">API REST v1</a></li>
          <li><a href="<?= url('sitemap.xml') ?>">Plan du site</a></li>
          <li><a href="https://www.ins.niger.ne" target="_blank" rel="noopener">Site INS Niger</a></li>
          <li><a href="https://www.iom.int" target="_blank" rel="noopener">OIM</a></li>
          <li><a href="<?= url('contact') ?>">Signaler un problème</a></li>
        </ul>
        <!-- Newsletter -->
        <form action="<?= url('newsletter') ?>" method="POST" style="margin-top:1.5rem">
          <?= csrf_field() ?>
          <label for="newsletter_email" style="font-size:.75rem;font-weight:700;color:rgba(255,255,255,.6);display:block;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.05em">Newsletter</label>
          <div style="display:flex;gap:.5rem">
            <input type="email" name="email" id="newsletter_email" placeholder="Votre email"
                   class="form-control" style="font-size:.8rem;padding:.5rem .75rem;background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.2);color:#fff"
                   required aria-label="Adresse email pour la newsletter">
            <button type="submit" class="btn btn-accent btn-sm">OK</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- Footer bottom -->
  <div style="border-top:1px solid rgba(255,255,255,.1)">
    <div class="container">
      <div class="footer-bottom">
        <span>© <?= date('Y') ?> Institut National de la Statistique — République du Niger. Tous droits réservés.</span>
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
          <a href="<?= url('mentions-legales') ?>">Mentions légales</a>
          <a href="<?= url('accessibilite') ?>">Accessibilité</a>
          <a href="<?= url('donnees-personnelles') ?>">Données personnelles</a>
          <span>Données ouvertes — Licence ODbL</span>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- ── TOAST CONTAINER ────────────────────────────────────────── -->
<div class="toast-container" id="toastContainer" aria-live="polite" aria-atomic="true"></div>

<!-- ── JS PRINCIPAL ──────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer crossorigin=""></script>
<script src="<?= asset('js/pndm.js') ?>" defer></script>

<script>
// Langue
function setLang(l) {
  fetch('/api/v1/lang?lang=' + l).then(() => location.reload());
}
// Nav mobile
const toggle = document.getElementById('navToggle');
const nav    = document.getElementById('siteNav');
if (toggle && nav) {
  toggle.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
}
// Fermer nav sur click lien mobile
nav && nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
  nav.classList.remove('open');
  toggle && toggle.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}));
</script>
<?php if (isset($extraJs)): ?><?= $extraJs ?><?php endif; ?>
</body>
</html>
