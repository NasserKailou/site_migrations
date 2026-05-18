<div style="width:100%;max-width:440px;padding:1rem">
  <!-- Logo -->
  <div style="text-align:center;margin-bottom:2rem">
    <img src="<?= url('assets/images/ins-logo.png') ?>" alt="INS Niger" style="height:64px;margin-bottom:1rem">
    <h1 style="color:#fff;font-size:1.5rem;font-weight:800;margin:0">PNDM Administration</h1>
    <p style="color:rgba(255,255,255,.7);font-size:.875rem;margin-top:.35rem">Institut National de la Statistique — Niger</p>
  </div>

  <!-- Card -->
  <div style="background:#fff;border-radius:16px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,.3)">
    <h2 style="font-size:1.25rem;font-weight:700;margin-bottom:1.5rem;color:var(--gray-800)">Connexion</h2>

    <?php $err = \App\Core\Session::flash('error'); ?>
    <?php if ($err): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive">
      <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i><?= esc($err) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/login') ?>" novalidate>
      <?= csrf_field() ?>
      <div class="form-group" style="margin-bottom:1.25rem">
        <label class="form-label" for="email">
          Adresse email <span class="required" aria-hidden="true">*</span>
        </label>
        <input type="email" name="email" id="email" class="form-control"
               required autocomplete="email" autofocus
               placeholder="votre@email.ne"
               aria-required="true"
               value="<?= esc(\App\Core\Request::post('email', '')) ?>">
      </div>

      <div class="form-group" style="margin-bottom:1.5rem">
        <label class="form-label" for="password">
          Mot de passe <span class="required" aria-hidden="true">*</span>
        </label>
        <div style="position:relative">
          <input type="password" name="password" id="password" class="form-control"
                 required autocomplete="current-password"
                 placeholder="••••••••"
                 aria-required="true">
          <button type="button" id="togglePwd"
                  style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--gray-400);padding:.25rem"
                  aria-label="Afficher/masquer le mot de passe">
            <i class="fa-solid fa-eye" aria-hidden="true"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.875rem">
        <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
        Se connecter
      </button>
    </form>

    <div style="margin-top:1.25rem;text-align:center">
      <a href="<?= url('admin/reset-password') ?>"
         style="font-size:.8rem;color:var(--gray-400);text-decoration:none"
         onmouseover="this.style.color='var(--pndm-blue)'" onmouseout="this.style.color='var(--gray-400)'">
        Mot de passe oublié ?
      </a>
    </div>
  </div>

  <!-- Lien retour site -->
  <div style="text-align:center;margin-top:1.5rem">
    <a href="<?= url() ?>" style="color:rgba(255,255,255,.6);font-size:.8rem;text-decoration:none"
       onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.6)'">
      <i class="fa-solid fa-arrow-left fa-xs" aria-hidden="true"></i> Retour au site public
    </a>
  </div>
</div>

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
</script>
