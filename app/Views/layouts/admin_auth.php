<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>Connexion — PNDM Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/pndm.css') ?>">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

    .login-page { min-height: 100vh; display: flex; }

    /* Panneau gauche décoratif */
    .login-panel-left {
      flex: 1;
      display: none;
      position: relative;
      background: linear-gradient(160deg, #1a3a2a 0%, #2a5a3e 50%, #c56e10 100%);
      overflow: hidden;
    }
    @media (min-width: 900px) {
      .login-panel-left { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; }
    }
    .login-panel-left::before {
      content: '';
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 25% 35%, rgba(247,161,62,.18) 0%, transparent 55%),
        radial-gradient(circle at 75% 65%, rgba(58,173,110,.12) 0%, transparent 55%);
    }
    .lp-deco { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.08); }
    .lp-deco:nth-child(1) { width: 380px; height: 380px; top: -100px; right: -100px; }
    .lp-deco:nth-child(2) { width: 240px; height: 240px; bottom: 50px; left: -80px; }
    .lp-deco:nth-child(3) { width: 140px; height: 140px; bottom: 220px; right: 30px; border-color: rgba(247,161,62,.2); }
    .lp-deco:nth-child(4) { width: 60px; height: 60px; top: 40%; left: 10%; border-color: rgba(58,173,110,.25); }

    .lp-content { position: relative; z-index: 1; color: #fff; text-align: center; width: 100%; max-width: 400px; }

    .lp-logos {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }
    .lp-logos img { filter: brightness(0) invert(1); height: 60px; object-fit: contain; }
    .lp-sep { width: 2px; height: 52px; background: rgba(255,255,255,.2); border-radius: 2px; }

    .lp-live {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .35rem 1rem;
      border-radius: 50px;
      border: 1px solid rgba(255,255,255,.2);
      font-size: .75rem;
      background: rgba(255,255,255,.08);
      margin-bottom: 1.5rem;
      backdrop-filter: blur(4px);
    }
    .lp-live .dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #3aad6e;
      box-shadow: 0 0 0 3px rgba(58,173,110,.3);
      animation: lpulse 2s infinite;
    }
    @keyframes lpulse {
      0%,100% { box-shadow: 0 0 0 3px rgba(58,173,110,.3); }
      50%      { box-shadow: 0 0 0 7px rgba(58,173,110,.08); }
    }

    .lp-title { font-size: 2.25rem; font-weight: 800; line-height: 1.2; margin-bottom: .75rem; }
    .lp-title span { color: #f7a13e; }
    .lp-sub { font-size: .9rem; opacity: .78; line-height: 1.65; max-width: 320px; margin: 0 auto 2.5rem; }

    .lp-stats { display: flex; gap: 2rem; justify-content: center; margin-bottom: 3rem; }
    .lp-stat-v { font-size: 1.9rem; font-weight: 800; color: #f7a13e; }
    .lp-stat-l { font-size: .65rem; opacity: .65; text-transform: uppercase; letter-spacing: .07em; margin-top: .2rem; }

    .lp-partners { display: flex; align-items: center; gap: 1.25rem; justify-content: center; opacity: .45; margin-top: auto; padding-top: 2rem; }
    .lp-partners img { height: 20px; object-fit: contain; filter: brightness(0) invert(1); }

    /* Panneau droit formulaire */
    .login-panel-right {
      width: 100%;
      max-width: 500px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 2rem;
      background: #f4f6f8;
      overflow-y: auto;
    }
    @media (min-width: 900px) { .login-panel-right { max-width: 460px; } }

    .login-form-box { width: 100%; max-width: 390px; }

    /* Logo mobile */
    .mobile-logos {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    @media (min-width: 900px) { .mobile-logos { display: none; } }
    .mobile-logos img { height: 42px; object-fit: contain; }
    .mobile-logos .msep { width: 1px; height: 34px; background: #dee2e6; }

    .login-welcome h1 { font-size: 1.65rem; font-weight: 800; color: #1a3a2a; margin: 0 0 .35rem; }
    .login-welcome p  { font-size: .85rem; color: #6c757d; margin: 0 0 1.75rem; line-height: 1.5; }

    .lf-group { margin-bottom: 1.2rem; }
    .lf-label {
      display: block;
      font-size: .72rem;
      font-weight: 700;
      color: #495057;
      margin-bottom: .45rem;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .lf-input-wrap { position: relative; }
    .lf-input-wrap .lf-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #adb5bd;
      font-size: .9rem;
    }
    .lf-input-wrap input {
      display: block;
      width: 100%;
      padding: .82rem 1rem .82rem 2.75rem;
      font-size: .95rem;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      background: #fff;
      color: #212529;
      font-family: 'Inter', sans-serif;
      transition: border-color .2s, box-shadow .2s;
      outline: none;
    }
    .lf-input-wrap input:focus {
      border-color: #f7a13e;
      box-shadow: 0 0 0 4px rgba(247,161,62,.13);
    }
    .lf-input-wrap .eye-btn {
      position: absolute;
      right: .9rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: #adb5bd;
      cursor: pointer;
      padding: .25rem;
      font-size: .9rem;
      transition: color .2s;
    }
    .lf-input-wrap .eye-btn:hover { color: #f7a13e; }

    .btn-login {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      width: 100%;
      padding: .9rem 1.5rem;
      font-size: .98rem;
      font-weight: 700;
      color: #fff;
      background: linear-gradient(135deg, #f7a13e 0%, #d97706 100%);
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
      box-shadow: 0 4px 18px rgba(247,161,62,.38);
      font-family: 'Inter', sans-serif;
      margin-top: 1.5rem;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(247,161,62,.45); }
    .btn-login:active { transform: translateY(0); }
    .btn-login:disabled { opacity: .7; cursor: not-allowed; transform: none; }

    .lf-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 1.5rem;
      flex-wrap: wrap;
      gap: .5rem;
    }
    .lf-footer a { font-size: .78rem; color: #6c757d; text-decoration: none; transition: color .2s; }
    .lf-footer a:hover { color: #f7a13e; }

    .lf-error {
      display: flex;
      align-items: center;
      gap: .6rem;
      padding: .8rem 1rem;
      background: #fff5f5;
      border: 1.5px solid #f5c6cb;
      border-radius: 10px;
      color: #721c24;
      font-size: .83rem;
      margin-bottom: 1.25rem;
      animation: shake .35s ease;
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%,60%  { transform: translateX(-5px); }
      40%,80%  { transform: translateX(5px); }
    }

    .lf-terms { margin-top: 1.75rem; text-align: center; font-size: .7rem; color: #adb5bd; line-height: 1.55; }
    .lf-terms a { color: #f7a13e; text-decoration: none; }

    .lf-copy { margin-top: 2rem; text-align: center; font-size: .68rem; color: #ced4da; }
  </style>
</head>
<body>

<div class="login-page">

  <!-- ══ PANNEAU DÉCORATIF GAUCHE ══════════════════════════════ -->
  <aside class="login-panel-left" aria-hidden="true">
    <div class="lp-deco"></div>
    <div class="lp-deco"></div>
    <div class="lp-deco"></div>
    <div class="lp-deco"></div>

    <div class="lp-content">
      <!-- Logos fusionnés INS + PNDM -->
      <div class="lp-logos">
        <img src="<?= asset('assets/images/img/ins.png') ?>" alt="INS Niger">
        <div class="lp-sep"></div>
        <img src="<?= asset('assets/images/img/logo.png') ?>" alt="PNDM">
      </div>

      <div class="lp-live">
        <span class="dot"></span>
        Plateforme active — INS Niger
      </div>

      <div class="lp-title">
        Espace<br>Administration<br><span>PNDM</span>
      </div>

      <p class="lp-sub">
        Plateforme Nationale des Données sur la Migration.<br>
        Institut National de la Statistique — République du Niger.
      </p>

      <div class="lp-stats">
        <div>
          <div class="lp-stat-v">5</div>
          <div class="lp-stat-l">Rôles</div>
        </div>
        <div>
          <div class="lp-stat-v">2FA</div>
          <div class="lp-stat-l">Sécurisé</div>
        </div>
        <div>
          <div class="lp-stat-v">∞</div>
          <div class="lp-stat-l">Données</div>
        </div>
      </div>

      <div class="lp-partners">
        <img src="<?= asset('assets/images/img/danida.png') ?>" alt="DANIDA">
        <img src="<?= asset('assets/images/img/logoAvenir3.png') ?>" alt="Avenir">
        <img src="<?= asset('assets/images/iom-logo.png') ?>" alt="OIM">
        <img src="<?= asset('assets/images/maeci-logo.png') ?>" alt="MAECI">
      </div>
    </div>
  </aside>

  <!-- ══ PANNEAU FORMULAIRE DROIT ══════════════════════════════ -->
  <main class="login-panel-right" role="main">
    <div class="login-form-box">

      <!-- Logos mobiles -->
      <div class="mobile-logos">
        <img src="<?= asset('assets/images/img/ins.png') ?>" alt="INS Niger">
        <div class="msep"></div>
        <img src="<?= asset('assets/images/img/logo.png') ?>" alt="PNDM">
      </div>

      <?= $content ?>

      <div class="lf-copy">© <?= date('Y') ?> Institut National de la Statistique — République du Niger</div>
    </div>
  </main>

</div>
</body>
</html>
