<?php /** @var string $token */ use App\Core\Session; use App\Core\View; ?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe — PNDM Admin</title>
    <link rel="stylesheet" href="<?= View::asset('assets/css/pndm.css') ?>">
    <style>
        body { background: linear-gradient(135deg, #005B9A 0%, #003d6b 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .auth-card { background:#fff; border-radius:12px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .auth-logo { text-align:center; margin-bottom:1.5rem; }
        .auth-logo img { height:60px; }
        .auth-title { text-align:center; font-size:1.4rem; font-weight:700; color:#1a2a3a; margin-bottom:.5rem; }
        .auth-subtitle { text-align:center; color:#6b7280; font-size:.9rem; margin-bottom:1.75rem; }
        .password-wrapper { position:relative; }
        .password-toggle { position:absolute; right:.75rem; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; padding:.25rem; }
        .strength-bar { height:4px; border-radius:2px; margin-top:.4rem; transition:all .3s; background:#e5e7eb; }
        .strength-bar.weak   { background:#ef4444; width:33%; }
        .strength-bar.medium { background:#f59e0b; width:66%; }
        .strength-bar.strong { background:#10b981; width:100%; }
        .strength-label { font-size:.75rem; color:#6b7280; margin-top:.25rem; }
        .back-link { display:block; text-align:center; margin-top:1rem; color:#6b7280; font-size:.85rem; text-decoration:none; }
        .back-link:hover { color:#005B9A; }
        .req-list { list-style:none; padding:0; margin:.5rem 0; font-size:.8rem; color:#6b7280; }
        .req-list li::before { content:'✗ '; color:#ef4444; }
        .req-list li.met::before { content:'✓ '; color:#10b981; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <img src="<?= View::asset('assets/images/pndm-logo.svg') ?>" alt="PNDM" onerror="this.style.display='none'">
    </div>
    <h1 class="auth-title">Nouveau mot de passe</h1>
    <p class="auth-subtitle">Choisissez un mot de passe fort d'au moins 12 caractères.</p>

    <?php if ($flash = Session::flash('error')): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/reset/confirm') ?>" id="resetForm" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div class="form-group">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Minimum 12 caractères" required autocomplete="new-password"
                       minlength="12">
                <button type="button" class="password-toggle" aria-label="Afficher/masquer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <div class="strength-bar" id="strengthBar"></div>
            <div class="strength-label" id="strengthLabel"></div>
            <ul class="req-list" id="reqList">
                <li id="req-len">Au moins 12 caractères</li>
                <li id="req-upper">Une majuscule</li>
                <li id="req-num">Un chiffre</li>
                <li id="req-special">Un caractère spécial</li>
            </ul>
        </div>

        <div class="form-group">
            <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
            <div class="password-wrapper">
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       placeholder="Répétez le mot de passe" required autocomplete="new-password">
                <button type="button" class="password-toggle" aria-label="Afficher/masquer">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <div id="matchMsg" style="font-size:.8rem; margin-top:.25rem;"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3" id="submitBtn" disabled>
            Enregistrer le nouveau mot de passe
        </button>
    </form>

    <a href="<?= url('admin/login') ?>" class="back-link">← Retour à la connexion</a>
</div>

<script>
(function () {
    const pwd    = document.getElementById('password');
    const conf   = document.getElementById('password_confirm');
    const bar    = document.getElementById('strengthBar');
    const lbl    = document.getElementById('strengthLabel');
    const submit = document.getElementById('submitBtn');
    const match  = document.getElementById('matchMsg');

    const reqs = {
        len:     { el: document.getElementById('req-len'),     re: /.{12,}/ },
        upper:   { el: document.getElementById('req-upper'),   re: /[A-Z]/ },
        num:     { el: document.getElementById('req-num'),     re: /\d/ },
        special: { el: document.getElementById('req-special'), re: /[^a-zA-Z0-9]/ },
    };

    const labels = ['', 'Faible', 'Moyen', 'Fort'];

    function checkStrength(v) {
        let score = 0;
        Object.entries(reqs).forEach(([k, r]) => {
            const ok = r.re.test(v);
            r.el.classList.toggle('met', ok);
            if (ok) score++;
        });
        bar.className = 'strength-bar ' + ['','weak','medium','medium','strong'][score];
        lbl.textContent = v.length > 0 ? labels[Math.min(3, score)] : '';
        return score;
    }

    function checkMatch() {
        if (!conf.value) { match.textContent = ''; return false; }
        const ok = pwd.value === conf.value;
        match.style.color = ok ? '#10b981' : '#ef4444';
        match.textContent = ok ? '✓ Les mots de passe correspondent' : '✗ Les mots de passe ne correspondent pas';
        return ok;
    }

    function updateSubmit() {
        submit.disabled = !(checkStrength(pwd.value) >= 3 && checkMatch());
    }

    pwd.addEventListener('input', () => { checkStrength(pwd.value); updateSubmit(); });
    conf.addEventListener('input', updateSubmit);

    // Toggle visibilité
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
})();
</script>
</body>
</html>
