<?php
/**
 * Vue liste des indicateurs (admin)
 * @var array  $indicateurs
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $thematiques
 * @var string $search
 * @var string $thematique
 * @var string $statut
 */
use App\Core\Auth;
use App\Core\View;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Indicateurs</h1>
        <p class="admin-page-subtitle">Total : <?= number_format($total) ?> indicateur(s)</p>
    </div>
    <?php if (Auth::hasRole('admin', 'super_admin')): ?>
    <a href="<?= url('admin/indicateurs/nouveau') ?>" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel indicateur
    </a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<form method="GET" action="<?= url('admin/indicateurs') ?>" class="admin-filter-bar mb-4">
    <div class="filter-row">
        <div class="form-group mb-0" style="flex:1; min-width:180px;">
            <label for="f_q" class="form-label">Recherche</label>
            <input type="text" id="f_q" name="q" class="form-control form-control-sm"
                   placeholder="Libellé, code, slug…"
                   value="<?= esc($search) ?>">
        </div>
        <div class="form-group mb-0">
            <label for="f_theme" class="form-label">Thématique</label>
            <select id="f_theme" name="thematique_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Toutes</option>
                <?php foreach ($thematiques as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $thematique == $t['id'] ? 'selected' : '' ?>>
                        <?= esc($t['libelle_fr']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label for="f_statut" class="form-label">Statut</label>
            <select id="f_statut" name="statut" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="actif"   <?= $statut === 'actif'   ? 'selected' : '' ?>>Actif</option>
                <option value="archive"  <?= $statut === 'archive'  ? 'selected' : '' ?>>Archivé</option>
                <option value="brouillon" <?= $statut === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
        <a href="<?= url('admin/indicateurs') ?>" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
    </div>
</form>

<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover" aria-label="Liste des indicateurs">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Thématique</th>
                    <th>Entité</th>
                    <th class="text-center">Obs.</th>
                    <th class="text-center">Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($indicateurs)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        Aucun indicateur trouvé.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($indicateurs as $ind): ?>
                <?php
                $statutColors = ['actif'=>'success','archive'=>'secondary','brouillon'=>'warning'];
                $statutLabels = ['actif'=>'Actif','archive'=>'Archivé','brouillon'=>'Brouillon'];
                $curSt = $ind['statut'] ?? 'actif';
                ?>
                <tr>
                    <td>
                        <a href="<?= url('admin/indicateurs/' . $ind['id'] . '/modifier') ?>" class="fw-medium">
                            <?= esc($ind['libelle_fr']) ?>
                        </a>
                        <div class="text-muted small"><?= esc($ind['slug']) ?></div>
                    </td>
                    <td>
                        <span class="badge badge-light"><?= esc($ind['thematique'] ?? '—') ?></span>
                    </td>
                    <td class="text-muted small"><?= esc($ind['entite'] ?? '—') ?></td>
                    <td class="text-center">
                        <a href="<?= url('admin/donnees?indicateur_id=' . $ind['id']) ?>"
                           class="badge badge-primary" title="Voir les observations">
                            <?= (int)$ind['nb_obs'] ?>
                        </a>
                    </td>
                    <td class="text-center">
                        <?php if (Auth::hasRole('admin', 'super_admin')): ?>
                        <button type="button"
                                class="badge badge-<?= $statutColors[$curSt] ?? 'secondary' ?> border-0 btn-toggle-statut"
                                data-id="<?= $ind['id'] ?>"
                                data-statut="<?= esc($curSt) ?>"
                                title="Cliquer pour basculer actif/archivé">
                            <?= $statutLabels[$curSt] ?? esc($curSt) ?>
                        </button>
                        <?php else: ?>
                            <span class="badge badge-<?= $statutColors[$curSt] ?? 'secondary' ?>">
                                <?= $statutLabels[$curSt] ?? esc($curSt) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= url('indicateurs/' . $ind['slug']) ?>" target="_blank"
                               class="btn btn-outline-secondary" title="Voir sur le site">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                            <?php if (Auth::hasRole('admin', 'super_admin')): ?>
                            <a href="<?= url('admin/indicateurs/' . $ind['id'] . '/modifier') ?>"
                               class="btn btn-outline-primary" title="Modifier">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <?php if (Auth::hasRole('super_admin')): ?>
                            <form method="POST" action="<?= url('admin/indicateurs/' . $ind['id'] . '/supprimer') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger" title="Supprimer"
                                        onclick="return confirm('Supprimer cet indicateur ? Cette action est irréversible.')">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
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
                <a href="?<?= http_build_query(['q'=>$search,'thematique_id'=>$thematique,'statut'=>$statut,'page'=>$page-1]) ?>"
                   class="btn btn-outline-secondary">← Préc.</a>
            <?php endif; ?>
            <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
                <a href="?<?= http_build_query(['q'=>$search,'thematique_id'=>$thematique,'statut'=>$statut,'page'=>$p]) ?>"
                   class="btn <?= $p===$page?'btn-primary':'btn-outline-secondary' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(['q'=>$search,'thematique_id'=>$thematique,'statut'=>$statut,'page'=>$page+1]) ?>"
                   class="btn btn-outline-secondary">Suiv. →</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.btn-toggle-statut').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        fetch(`<?= url('admin/indicateurs/') ?>${id}/toggle-statut`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_csrf=<?= urlencode(\App\Core\Session::getCsrfToken()) ?>'
        })
        .then(r => r.json())
        .then(data => {
            const labels = {actif:'Actif',archive:'Archivé',brouillon:'Brouillon'};
            const colors = {actif:'success',archive:'secondary',brouillon:'warning'};
            const st = data.statut;
            this.textContent    = labels[st] || st;
            this.className      = `badge badge-${colors[st]||'secondary'} border-0 btn-toggle-statut`;
            this.dataset.statut = st;
        })
        .catch(() => alert('Erreur lors du changement de statut.'));
    });
});
</script>
