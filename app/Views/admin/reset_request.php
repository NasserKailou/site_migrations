<?php use App\Core\Session; use App\Core\View; ?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation du mot de passe — PNDM Admin</title>
    <link rel="stylesheet" href="<?= View::asset('assets/css/pndm.css') ?>">
    <style>
        body { background: linear-gradient(135deg, #005B9A 0%, #003d6b 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .auth-card { background:#fff; border-radius:12px; padding:2.5rem; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .auth-logo { text-align:center; margin-bottom:1.5rem; }
        .auth-logo img { height:60px; }
        .auth-title { text-align:center; font-size:1.4rem; font-weight:700; color:#1a2a3a; margin-bottom:.5rem; }
        .auth-subtitle { text-align:center; color:#6b7280; font-size:.9rem; margin-bottom:1.75rem; }
        .back-link { display:block; text-align:center; margin-top:1rem; color:#6b7280; font-size:.85rem; text-decoration:none; }
        .back-link:hover { color:#005B9A; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <img src="<?= View::asset('assets/images/pndm-logo.svg') ?>" alt="PNDM" onerror="this.style.display='none'">
    </div>
    <h1 class="auth-title">Réinitialiser le mot de passe</h1>
    <p class="auth-subtitle">Saisissez votre adresse email. Si un compte existe, un lien de réinitialisation vous sera envoyé.</p>

    <?php if ($flash = Session::flash('success')): ?>
        <div class="alert alert-success" role="alert">
            <strong>Email envoyé !</strong> Vérifiez votre boîte de réception et vos spams.
        </div>
    <?php elseif ($flash = Session::flash('error')): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/reset') ?>" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email" class="form-label">Adresse email</label>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="votre@email.com" required autocomplete="email"
                   value="<?= htmlspecialchars(Session::flash('old_email') ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-3">
            Envoyer le lien de réinitialisation
        </button>
    </form>

    <a href="<?= url('admin/login') ?>" class="back-link">← Retour à la connexion</a>
</div>
</body>
</html>
