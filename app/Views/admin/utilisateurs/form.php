<?php
/**
 * Formulaire création/édition utilisateur (admin)
 * @var array $user  Données utilisateur (vide pour création)
 * @var array $roles Liste des rôles — contient id, libelle (pas slug, pas libelle_fr)
 */
use App\Core\Auth;
use App\Core\Session;
$isNew = empty($user['id']);
$title = $isNew ? 'Nouvel utilisateur' : 'Modifier : ' . trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
$old   = Session::flash('old') ?? [];
?>

<div class="admin-page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('admin') ?>">Tableau de bord</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/utilisateurs') ?>">Utilisateurs</a></li>
                <li class="breadcrumb-item active"><?= $isNew ? 'Nouveau' : 'Modifier' ?></li>
            </ol>
        </nav>
        <h1 class="admin-page-title"><?= esc($title) ?></h1>
    </div>
    <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-outline-secondary">← Retour</a>
</div>

<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>

<form method="POST"
      action="<?= $isNew ? url('admin/utilisateurs') : url('admin/utilisateurs/' . $user['id']) ?>"
      id="userForm" novalidate>
    <?= csrf_field() ?>
    <?php if (!$isNew): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row g-4">
        <!-- COLONNE PRINCIPALE -->
        <div class="col-lg-8">

            <!-- Informations personnelles -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Informations personnelles</h2></div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="prenom" class="form-label required">Prénom</label>
                                <input type="text" id="prenom" name="prenom" class="form-control" required
                                       autocomplete="given-name"
                                       value="<?= esc($old['prenom'] ?? $user['prenom'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="nom" class="form-label required">Nom</label>
                                <input type="text" id="nom" name="nom" class="form-control" required
                                       autocomplete="family-name"
                                       value="<?= esc($old['nom'] ?? $user['nom'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="email" class="form-label required">Adresse email</label>
                        <input type="email" id="email" name="email" class="form-control" required
                               autocomplete="email"
                               value="<?= esc($old['email'] ?? $user['email'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Mot de passe -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        Mot de passe
                        <?php if (!$isNew): ?>
                        <span class="admin-card-subtitle ms-1">(laisser vide pour ne pas changer)</span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="password" class="form-label <?= $isNew ? 'required' : '' ?>">
                            <?= $isNew ? 'Mot de passe' : 'Nouveau mot de passe' ?>
                        </label>
                        <div class="position-relative">
                            <input type="password" id="password" name="password" class="form-control"
                                   autocomplete="new-password"
                                   <?= $isNew ? 'required minlength="12"' : '' ?>
                                   placeholder="<?= $isNew ? 'Minimum 12 caractères' : 'Laisser vide pour ne pas changer' ?>">
                            <button type="button" class="btn-pwd-toggle" aria-label="Afficher/masquer">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <div class="pwd-strength-bar mt-1" id="pwdBar" style="height:3px; border-radius:2px; background:#e5e7eb;"></div>
                        <ul class="small text-muted mt-2 ps-3" id="pwdReqs">
                            <li id="req-len">12 caractères minimum</li>
                            <li id="req-upper">Une lettre majuscule</li>
                            <li id="req-digit">Un chiffre</li>
                            <li id="req-spec">Un caractère spécial</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- COLONNE LATÉRALE -->
        <div class="col-lg-4">

            <!-- Rôle & Statut -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Accès & Rôle</h2></div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="role_id" class="form-label required">Rôle</label>
                        <select id="role_id" name="role_id" class="form-control" required>
                            <option value="">— Sélectionner —</option>
                            <?php foreach ($roles as $r): ?>
                                <?php
                                // Cacher super_admin pour les non-super_admin
                                if ($r['libelle'] === 'super_admin' && !Auth::hasRole('super_admin')) continue;
                                $selected = (int)($old['role_id'] ?? $user['role_id'] ?? 0) === (int)$r['id'];
                                ?>
                                <option value="<?= $r['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                    <?= esc($r['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="roleDesc" class="small text-muted mt-1"></div>
                    </div>

                    <?php if (!$isNew): ?>
                    <div class="form-group mt-3">
                        <label for="actif" class="form-label">Statut du compte</label>
                        <?php $isSelf = ($user['id'] ?? 0) === Auth::id(); ?>
                        <select id="actif" name="actif" class="form-control"
                                <?= $isSelf ? 'disabled' : '' ?>>
                            <option value="1" <?= (int)($user['actif'] ?? 1) === 1 ? 'selected' : '' ?>>✓ Actif</option>
                            <option value="0" <?= (int)($user['actif'] ?? 1) === 0 ? 'selected' : '' ?>>⏸ Suspendu</option>
                        </select>
                        <?php if ($isSelf): ?>
                            <input type="hidden" name="actif" value="1">
                            <small class="text-muted">Vous ne pouvez pas modifier votre propre statut.</small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Actions</h2></div>
                <div class="admin-card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?= $isNew ? "Créer l'utilisateur" : 'Enregistrer les modifications' ?>
                    </button>
                    <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>

            <!-- 2FA (édition uniquement) -->
            <?php if (!$isNew && Auth::hasRole('super_admin')): ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Authentification 2FA</h2></div>
                <div class="admin-card-body">
                    <?php if ($user['totp_enabled'] ?? false): ?>
                        <p class="text-success small">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            2FA activé sur ce compte.
                        </p>
                        <form method="POST" action="<?= url('admin/utilisateurs/' . $user['id'] . '/reset-2fa') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline-warning btn-sm w-100"
                                    onclick="return confirm('Réinitialiser le 2FA de cet utilisateur ? Il devra le reconfigurer.')">
                                Réinitialiser le 2FA
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-muted small">2FA non activé sur ce compte.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Méta -->
            <?php if (!$isNew): ?>
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Informations</h2></div>
                <div class="admin-card-body">
                    <dl class="meta-grid small">
                        <dt>ID</dt><dd><?= $user['id'] ?></dd>
                        <dt>Créé le</dt><dd><?= date_fr($user['created_at'] ?? '') ?></dd>
                        <dt>Modifié</dt><dd><?= date_fr($user['updated_at'] ?? '') ?></dd>
                        <dt>Dernière connexion</dt>
                        <dd><?= !empty($user['dernier_login']) ? ago($user['dernier_login']) : 'Jamais' ?></dd>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<style>
.btn-pwd-toggle {
    position:absolute; right:.75rem; top:50%; transform:translateY(-50%);
    background:none; border:none; cursor:pointer; color:#6b7280; padding:.25rem;
}
#pwdReqs li { transition: color .2s; }
#pwdReqs li.ok { color: #10b981; list-style-type: '✓ '; }
</style>

<script>
(function () {
    const pwd    = document.getElementById('password');
    const bar    = document.getElementById('pwdBar');
    const toggle = document.querySelector('.btn-pwd-toggle');
    const reqs   = {
        len:   document.getElementById('req-len'),
        upper: document.getElementById('req-upper'),
        digit: document.getElementById('req-digit'),
        spec:  document.getElementById('req-spec'),
    };

    pwd?.addEventListener('input', function () {
        const v = this.value;
        const checks = {
            len:   v.length >= 12,
            upper: /[A-Z]/.test(v),
            digit: /\d/.test(v),
            spec:  /[^a-zA-Z0-9]/.test(v),
        };
        let score = Object.values(checks).filter(Boolean).length;
        const colors = ['#e5e7eb','#ef4444','#f59e0b','#3b82f6','#10b981'];
        if (bar) { bar.style.background = colors[score]; bar.style.width = (score * 25) + '%'; }
        Object.entries(checks).forEach(([k, ok]) => {
            reqs[k]?.classList.toggle('ok', ok);
        });
    });

    toggle?.addEventListener('click', () => {
        if (!pwd) return;
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
    });

    // Description du rôle
    const roleDescs = {
        'super_admin': 'Accès complet à toutes les fonctionnalités.',
        'admin':       'Gestion des indicateurs, utilisateurs et données.',
        'validateur':  'Peut valider et publier les données soumises.',
        'point_focal': 'Peut saisir et soumettre des données.',
        'lecteur':     'Accès en lecture seule au back-office.',
    };
    const roleSelect = document.getElementById('role_id');
    const roleDesc   = document.getElementById('roleDesc');
    roleSelect?.addEventListener('change', function () {
        const libelle = this.options[this.selectedIndex]?.text?.trim() ?? '';
        if (roleDesc) roleDesc.textContent = roleDescs[libelle] ?? '';
    });
})();
</script>
