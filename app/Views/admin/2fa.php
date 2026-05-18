<?php /** @var string $redirect */ ?>
<?php use App\Core\Session; use App\Core\View; ?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification 2FA — PNDM Admin</title>
    <link rel="stylesheet" href="<?= View::asset('assets/css/pndm.css') ?>">
    <style>
        body { background: linear-gradient(135deg, #005B9A 0%, #003d6b 100%); min-height: 100vh; display:flex; align-items:center; justify-content:center; }
        .auth-card { background:#fff; border-radius:12px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .auth-logo { text-align:center; margin-bottom:1.5rem; }
        .auth-logo img { height:60px; }
        .auth-title { text-align:center; font-size:1.4rem; font-weight:700; color:#1a2a3a; margin-bottom:.5rem; }
        .auth-subtitle { text-align:center; color:#6b7280; font-size:.9rem; margin-bottom:1.75rem; }
        .otp-inputs { display:flex; gap:.5rem; justify-content:center; margin:1.5rem 0; }
        .otp-inputs input { width:48px; height:56px; border:2px solid #d1d5db; border-radius:8px; text-align:center; font-size:1.5rem; font-weight:700; color:#1a2a3a; transition:border-color .2s; }
        .otp-inputs input:focus { outline:none; border-color:#005B9A; }
        .divider { text-align:center; color:#9ca3af; margin:.5rem 0; font-size:.85rem; }
        .back-link { display:block; text-align:center; margin-top:1rem; color:#6b7280; font-size:.85rem; text-decoration:none; }
        .back-link:hover { color:#005B9A; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <img src="<?= View::asset('assets/images/pndm-logo.svg') ?>" alt="PNDM" onerror="this.style.display='none'">
    </div>
    <h1 class="auth-title">Vérification en deux étapes</h1>
    <p class="auth-subtitle">Saisissez le code à 6 chiffres généré par votre application d'authentification (Google Authenticator, Authy…).</p>

    <?php if ($flash = Session::flash('error')): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/2fa/verify') ?>" id="form2fa" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect ?? '') ?>">

        <div class="otp-inputs" aria-label="Code TOTP à 6 chiffres">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" autofocus autocomplete="one-time-code" aria-label="Chiffre 1">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" aria-label="Chiffre 2">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" aria-label="Chiffre 3">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" aria-label="Chiffre 4">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" aria-label="Chiffre 5">
            <input type="text" inputmode="numeric" pattern="\d" maxlength="1" class="otp-digit" aria-label="Chiffre 6">
        </div>
        <input type="hidden" name="totp_code" id="totpHidden">

        <div class="divider">— ou —</div>

        <div class="form-group">
            <label for="totp_direct" class="form-label">Saisir le code directement</label>
            <input type="text" id="totp_direct" inputmode="numeric" pattern="\d{6}"
                   maxlength="6" class="form-control text-center fs-4 fw-bold"
                   placeholder="000000" autocomplete="one-time-code" style="letter-spacing:.5rem; font-size:1.5rem;">
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">
            Vérifier le code
        </button>
    </form>

    <a href="<?= url('admin/logout') ?>" class="back-link">← Se déconnecter et retourner à la connexion</a>
</div>

<script>
(function () {
    const digits  = document.querySelectorAll('.otp-digit');
    const hidden  = document.getElementById('totpHidden');
    const direct  = document.getElementById('totp_direct');
    const form    = document.getElementById('form2fa');

    function updateHidden() {
        hidden.value = Array.from(digits).map(d => d.value).join('');
        if (direct.value.length === 0) direct.value = hidden.value;
    }

    digits.forEach((d, i) => {
        d.addEventListener('input', () => {
            d.value = d.value.replace(/\D/g, '').slice(-1);
            if (d.value && i < digits.length - 1) digits[i + 1].focus();
            updateHidden();
            if (hidden.value.length === 6) form.submit();
        });
        d.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !d.value && i > 0) digits[i - 1].focus();
        });
        d.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            [...text.slice(0, 6)].forEach((ch, j) => { if (digits[j]) digits[j].value = ch; });
            updateHidden();
            if (hidden.value.length === 6) form.submit();
        });
    });

    direct.addEventListener('input', () => {
        direct.value = direct.value.replace(/\D/g, '').slice(0, 6);
        hidden.value = direct.value;
        [...direct.value].forEach((ch, j) => { if (digits[j]) digits[j].value = ch; });
        if (direct.value.length === 6) form.submit();
    });

    form.addEventListener('submit', () => {
        if (direct.value.length === 6) hidden.value = direct.value;
    });
})();
</script>
</body>
</html>
