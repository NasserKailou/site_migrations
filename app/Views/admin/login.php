<!-- ── Accueil connexion ──────────────────────────────────────── -->
<div class="login-welcome">
  <h1>Connexion</h1>
  <p>Bienvenue — veuillez vous authentifier pour accéder à l'espace d'administration PNDM.</p>
</div>

<?php $err = \App\Core\Session::flash('error'); ?>
<?php if ($err): ?>
<div class="lf-error" role="alert" aria-live="assertive">
  <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
  <?= esc($err) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('admin/login') ?>" novalidate id="loginForm">
  <?= csrf_field() ?>

  <!-- Email -->
  <div class="lf-group">
    <label class="lf-label" for="email">
      Adresse email
    </label>
    <div class="lf-input-wrap">
      <i class="fa-solid fa-envelope lf-icon" aria-hidden="true"></i>
      <input type="email" name="email" id="email"
             required autocomplete="email" autofocus
             placeholder="votre@ins.ne"
             aria-required="true"
             value="<?= esc(\App\Core\Request::post('email', '')) ?>">
    </div>
  </div>

  <!-- Mot de passe -->
  <div class="lf-group">
    <label class="lf-label" for="password">
      Mot de passe
    </label>
    <div class="lf-input-wrap">
      <i class="fa-solid fa-lock lf-icon" aria-hidden="true"></i>
      <input type="password" name="password" id="password"
             required autocomplete="current-password"
             placeholder="••••••••"
             aria-required="true">
      <button type="button" id="togglePwd" class="eye-btn" aria-label="Afficher le mot de passe">
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  <!-- Bouton connexion -->
  <button type="submit" class="btn-login" id="submitBtn">
    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
    Se connecter
  </button>
</form>

<div class="lf-footer">
  <a href="<?= url() ?>">
    <i class="fa-solid fa-arrow-left fa-xs" aria-hidden="true"></i>
    Retour au site public
  </a>
  <a href="<?= url('admin/reset-password') ?>">
    Mot de passe oublié ?
  </a>
</div>

<p class="lf-terms">
  Accès réservé aux agents autorisés de l'INS Niger.<br>
  Toute connexion est tracée conformément à la politique de sécurité.
</p>

<script>
document.getElementById('togglePwd')?.addEventListener('click', function() {
  const pwd = document.getElementById('password');
  const icon = this.querySelector('i');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.className = 'fa-solid fa-eye-slash';
    this.setAttribute('aria-label', 'Masquer le mot de passe');
  } else {
    pwd.type = 'password';
    icon.className = 'fa-solid fa-eye';
    this.setAttribute('aria-label', 'Afficher le mot de passe');
  }
});
document.getElementById('loginForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Connexion en cours…';
  }
});
</script>
