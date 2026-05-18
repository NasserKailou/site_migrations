<?php
/**
 * Vue liste des utilisateurs (admin)
 * @var array  $users
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $roles       — contient id, libelle (pas slug, pas libelle_fr)
 * @var string $search
 * @var string $role        — valeur filtre (libelle du rôle)
 * @var string $statut      — '0', '1' ou ''
 */
use App\Core\Auth;
use App\Core\Session;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Utilisateurs</h1>
        <p class="admin-page-subtitle">Total : <?= number_format($total) ?> utilisateur(s)</p>
    </div>
    <a href="<?= url('admin/utilisateurs/nouveau') ?>" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel utilisateur
    </a>
</div>

<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= esc($flash) ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>

<!-- Filtres -->
<form method="GET" action="<?= url('admin/utilisateurs') ?>" class="admin-filter-bar mb-4">
    <div class="filter-row">
        <div class="form-group mb-0" style="flex:1; min-width:160px;">
            <label for="f_q" class="form-label">Recherche</label>
            <input type="text" id="f_q" name="q" class="form-control form-control-sm"
                   placeholder="Nom, email…" value="<?= esc($search) ?>">
        </div>
        <div class="form-group mb-0">
            <label for="f_role" class="form-label">Rôle</label>
            <select id="f_role" name="role" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= esc($r['libelle']) ?>"
                            <?= $role === $r['libelle'] ? 'selected' : '' ?>>
                        <?= esc($r['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label for="f_statut" class="form-label">Statut</label>
            <select id="f_statut" name="statut" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="1" <?= $statut === '1' ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= $statut === '0' ? 'selected' : '' ?>>Suspendu</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
        <a href="<?= url('admin/utilisateurs') ?>" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
    </div>
</form>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover" aria-label="Liste des utilisateurs">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th class="text-center">2FA</th>
                    <th class="text-center">Statut</th>
                    <th>Dernière connexion</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">Aucun utilisateur trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar-sm" aria-hidden="true">
                                <?= mb_strtoupper(mb_substr($u['nom'] ?? '?', 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-medium">
                                    <?= esc(trim(($u['prenom'] ?? '') . ' ' . ($u['nom'] ?? ''))) ?>
                                </div>
                                <?php if ($u['id'] === Auth::id()): ?>
                                    <span class="badge badge-light small">Vous</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="small"><?= esc($u['email']) ?></td>
                    <td>
                        <?php
                        $roleColors = [
                            'super_admin' => 'danger',
                            'admin'       => 'warning',
                            'validateur'  => 'info',
                            'point_focal' => 'primary',
                            'lecteur'     => 'secondary',
                        ];
                        $libelle = $u['role_libelle'] ?? 'lecteur';
                        ?>
                        <span class="badge badge-<?= $roleColors[$libelle] ?? 'secondary' ?>">
                            <?= esc($libelle) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($u['totp_enabled'] ?? false): ?>
                            <span class="text-success" title="2FA activé">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </span>
                        <?php else: ?>
                            <span class="text-muted" title="2FA désactivé">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php
                        // actif est TINYINT : 1 = actif, 0 = suspendu
                        $isActif = (int)($u['actif'] ?? 1) === 1;
                        $sc      = $isActif ? 'success' : 'warning';
                        $sl      = $isActif ? 'Actif' : 'Suspendu';
                        ?>
                        <?php if ($u['id'] !== Auth::id()): ?>
                        <button type="button"
                                class="badge badge-<?= $sc ?> border-0 btn-toggle-user"
                                data-id="<?= $u['id'] ?>"
                                data-actif="<?= $u['actif'] ?>"
                                title="Cliquer pour basculer le statut">
                            <?= $sl ?>
                        </button>
                        <?php else: ?>
                        <span class="badge badge-<?= $sc ?>"><?= $sl ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small">
                        <?= $u['dernier_login'] ? ago($u['dernier_login']) : 'Jamais' ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= url('admin/utilisateurs/' . $u['id'] . '/modifier') ?>"
                           class="btn btn-outline-primary btn-sm" title="Modifier">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="d-flex justify-content-between align-items-center px-3 pb-3">
        <span class="text-muted small">Page <?= $page ?> / <?= $totalPages ?></span>
        <div class="btn-group btn-group-sm">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(['q'=>$search,'role'=>$role,'statut'=>$statut,'page'=>$page-1]) ?>"
                   class="btn btn-outline-secondary">← Préc.</a>
            <?php endif; ?>
            <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
                <a href="?<?= http_build_query(['q'=>$search,'role'=>$role,'statut'=>$statut,'page'=>$p]) ?>"
                   class="btn <?= $p===$page?'btn-primary':'btn-outline-secondary' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(['q'=>$search,'role'=>$role,'statut'=>$statut,'page'=>$page+1]) ?>"
                   class="btn btn-outline-secondary">Suiv. →</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>

<style>
.user-avatar-sm {
    width:32px; height:32px; border-radius:50%;
    background:var(--primary); color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:.85rem; font-weight:700; flex-shrink:0;
}
</style>

<script>
document.querySelectorAll('.btn-toggle-user').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const actif = parseInt(this.dataset.actif, 10);
        const label = actif === 1 ? 'désactiver' : 'activer';
        if (!confirm(`Voulez-vous ${label} cet utilisateur ?`)) return;
        fetch(`<?= url('admin/utilisateurs/') ?>${id}/toggle-statut`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_csrf=<?= urlencode(\App\Core\Session::getCsrfToken()) ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            const isActif = data.actif === 1;
            this.className = `badge badge-${isActif ? 'success' : 'warning'} border-0 btn-toggle-user`;
            this.textContent = isActif ? 'Actif' : 'Suspendu';
            this.dataset.actif = data.actif;
        })
        .catch(() => alert('Erreur lors du changement de statut.'));
    });
});
</script>
