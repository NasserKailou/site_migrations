<?php
declare(strict_types=1);
/** Layout admin authentifié */
$user = \App\Core\Auth::user();
$pageTitle = $pageTitle ?? 'Administration';
$pendingCount = \App\Core\Database::count('observations', "statut='soumis'");
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title><?= esc($pageTitle) ?> — PNDM Admin</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= asset('css/pndm.css') ?>">
  <link rel="icon" href="<?= url('assets/images/favicon-32.png') ?>">
</head>
<body>
<div class="admin-wrapper">

  <!-- ── SIDEBAR ──────────────────────────────────────────── -->
  <aside class="admin-sidebar" id="adminSidebar" role="navigation" aria-label="Navigation administration">
    <a href="<?= url('admin/dashboard') ?>" class="admin-sidebar-logo">
      <img src="<?= url('assets/images/logo-ins-white.png') ?>" alt="INS" width="36" height="36">
      <div>
        <div style="font-weight:800;font-size:.9rem">PNDM Admin</div>
        <div style="font-size:.7rem;opacity:.6">INS Niger</div>
      </div>
    </a>

    <nav class="admin-nav">
      <!-- Dashboard -->
      <div class="admin-nav-section">
        <a href="<?= url('admin/dashboard') ?>"
           class="<?= str_contains($_SERVER['REQUEST_URI'],'/admin/dashboard')||$_SERVER['REQUEST_URI']==='/admin' ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-gauge" aria-hidden="true"></i></span>
          Tableau de bord
        </a>
      </div>

      <!-- Données -->
      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Données</div>
        <a href="<?= url('admin/donnees') ?>"
           class="<?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/donnees') ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-database" aria-hidden="true"></i></span>
          Observations
          <?php if ($pendingCount): ?>
          <span class="nav-badge" aria-label="<?= $pendingCount ?> en attente"><?= $pendingCount ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= url('admin/donnees/saisie') ?>">
          <span class="nav-icon"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
          Saisie
        </a>
        <a href="<?= url('admin/import') ?>"
           class="<?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/import') ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i></span>
          Import Excel
        </a>
      </div>

      <!-- Référentiel -->
      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Référentiel</div>
        <a href="<?= url('admin/indicateurs') ?>"
           class="<?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/indicateurs') ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span>
          Indicateurs
        </a>
        <a href="<?= url('admin/dossiers') ?>"
           class="<?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/dossiers') ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-folder" aria-hidden="true"></i></span>
          Dossiers
        </a>
      </div>

      <!-- Administration -->
      <?php if (\App\Core\Auth::hasRole('super_admin','admin')): ?>
      <div class="admin-nav-section">
        <div class="admin-nav-section-title">Administration</div>
        <a href="<?= url('admin/utilisateurs') ?>"
           class="<?= str_starts_with($_SERVER['REQUEST_URI'],'/admin/utilisateurs') ? 'active' : '' ?>">
          <span class="nav-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span>
          Utilisateurs
        </a>
      </div>
      <?php endif; ?>

      <!-- Séparateur + Lien site public -->
      <div style="margin-top:auto;padding-top:1rem;border-top:1px solid rgba(255,255,255,.1);margin:1rem 0">
        <a href="<?= url() ?>" target="_blank" rel="noopener">
          <span class="nav-icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
          Voir le site public
        </a>
        <a href="<?= url('admin/logout') ?>">
          <span class="nav-icon"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></span>
          Déconnexion
        </a>
      </div>
    </nav>
  </aside>

  <!-- ── MAIN AREA ─────────────────────────────────────────── -->
  <div class="admin-main">
    <!-- Topbar -->
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:1rem">
        <button id="sidebarToggle" class="btn btn-ghost btn-icon" aria-label="Toggle menu">
          <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
        <span class="admin-topbar-title"><?= esc($pageTitle) ?></span>
      </div>
      <div class="admin-topbar-actions">
        <!-- Notifications -->
        <?php if ($pendingCount): ?>
        <a href="<?= url('admin/donnees?statut=soumis') ?>" class="btn btn-ghost btn-sm"
           aria-label="<?= $pendingCount ?> observations en attente de validation"
           style="position:relative">
          <i class="fa-solid fa-bell" aria-hidden="true"></i>
          <span style="position:absolute;top:-4px;right:-4px;background:var(--pndm-orange);color:#fff;font-size:.65rem;font-weight:700;border-radius:999px;padding:1px 5px;line-height:1.4"><?= $pendingCount ?></span>
        </a>
        <?php endif; ?>
        <!-- User menu -->
        <div class="admin-user-menu" role="button" tabindex="0" aria-label="Menu utilisateur">
          <div class="admin-user-avatar" aria-hidden="true">
            <?= strtoupper(substr($user['prenom'] ?? 'A', 0, 1)) ?>
          </div>
          <div style="display:none" id="userMenuDropdown" class="user-dropdown">
            <!-- Simplifié -->
          </div>
          <div style="display:flex;flex-direction:column;line-height:1.2">
            <span style="font-size:.85rem;font-weight:600;color:var(--gray-800)"><?= esc($user['prenom'] ?? '') ?> <?= esc($user['nom'] ?? '') ?></span>
            <span style="font-size:.75rem;color:var(--gray-400)"><?= esc($user['role_libelle'] ?? '') ?></span>
          </div>
        </div>
      </div>
    </header>

    <!-- Flash messages -->
    <?php
    $flashSuccess = \App\Core\Session::flash('success');
    $flashError   = \App\Core\Session::flash('error');
    $flashInfo    = \App\Core\Session::flash('info');
    ?>
    <?php if ($flashSuccess || $flashError || $flashInfo): ?>
    <div style="padding:1rem 2rem 0" role="alert" aria-live="polite">
      <?php if ($flashSuccess): ?><div class="alert alert-success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><?= esc($flashSuccess) ?></div><?php endif; ?>
      <?php if ($flashError):   ?><div class="alert alert-error"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i><?= esc($flashError) ?></div><?php endif; ?>
      <?php if ($flashInfo):    ?><div class="alert alert-info"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><?= esc($flashInfo) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Contenu -->
    <div class="admin-content" role="main" id="admin-main-content">
      <?= $content ?>
    </div>
  </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toastContainer" aria-live="polite"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer crossorigin="anonymous"></script>
<script src="<?= asset('js/pndm.js') ?>" defer></script>
<script>
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
  document.getElementById('adminSidebar')?.classList.toggle('open');
});
</script>
<?php if (isset($extraJs)): ?><?= $extraJs ?><?php endif; ?>
</body>
</html>
