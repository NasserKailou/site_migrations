<?php
/**
 * Vue liste des observations (admin)
 * @var array  $donnees
 * @var int    $total
 * @var int    $page
 * @var int    $totalPages
 * @var array  $filters
 * @var array  $indicateurs
 */
use App\Core\Auth;
use App\Core\View;
$title       = 'Gestion des données';
$breadcrumbs = [['Tableau de bord', url('admin')], ['Données', '']];
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Données & Observations</h1>
        <p class="admin-page-subtitle">Total : <?= number_format($total) ?> observation(s)</p>
    </div>
    <?php if (Auth::can('create_data')): ?>
    <a href="<?= url('admin/donnees/saisie') ?>" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle saisie
    </a>
    <?php endif; ?>
</div>

<!-- FILTRES -->
<form method="GET" action="<?= url('admin/donnees') ?>" class="admin-filter-bar mb-4">
    <div class="filter-row">
        <div class="form-group mb-0">
            <label for="f_statut" class="form-label">Statut</label>
            <select id="f_statut" name="statut" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <?php foreach (['brouillon'=>'Brouillon','soumis'=>'Soumis','valide'=>'Validé','publie'=>'Publié','rejete'=>'Rejeté'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $filters['statut'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label for="f_ind" class="form-label">Indicateur</label>
            <select id="f_ind" name="ind_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous les indicateurs</option>
                <?php foreach ($indicateurs as $ind): ?>
                    <option value="<?= $ind['id'] ?>" <?= $filters['indicateur_id'] == $ind['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ind['libelle_fr']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-outline-primary btn-sm">Filtrer</button>
        <a href="<?= url('admin/donnees') ?>" class="btn btn-outline-secondary btn-sm">Réinitialiser</a>
    </div>
</form>

<!-- TABLEAU -->
<div class="admin-card">
    <div class="table-responsive">
        <table class="table table-hover table-striped" aria-label="Liste des observations">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Indicateur</th>
                    <th>Période</th>
                    <th>Valeur totale</th>
                    <th>Statut</th>
                    <th>Saisi par</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($donnees)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Aucune observation trouvée.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($donnees as $obs): ?>
                <tr>
                    <td class="text-muted"><?= $obs['id'] ?></td>
                    <td>
                        <a href="<?= url('admin/donnees/' . $obs['id'] . '/modifier') ?>" class="fw-medium">
                            <?= htmlspecialchars($obs['indicateur_libelle'] ?? 'N/A') ?>
                        </a>
                        <?php if ($obs['geo_libelle']): ?>
                            <div class="text-muted small"><?= htmlspecialchars($obs['geo_libelle']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= esc(date('d/m/Y', strtotime($obs['periode_debut']))) ?>
                        <?php if ($obs['periode_fin'] && $obs['periode_fin'] !== $obs['periode_debut']): ?>
                            → <?= esc(date('d/m/Y', strtotime($obs['periode_fin']))) ?>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold">
                        <?php if ($obs['valeur_total'] !== null): ?>
                            <?= format_number((float)$obs['valeur_total']) ?>
                            <?= htmlspecialchars($obs['unite_symbole'] ?? '') ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $badges = [
                            'brouillon' => 'secondary',
                            'soumis'    => 'warning',
                            'valide'    => 'info',
                            'publie'    => 'success',
                            'rejete'    => 'danger',
                        ];
                        $labels = [
                            'brouillon' => 'Brouillon',
                            'soumis'    => 'Soumis',
                            'valide'    => 'Validé',
                            'publie'    => 'Publié',
                            'rejete'    => 'Rejeté',
                        ];
                        $st = $obs['statut'];
                        ?>
                        <span class="badge badge-<?= $badges[$st] ?? 'secondary' ?>">
                            <?= $labels[$st] ?? $st ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= esc($obs['nom'] ?? 'N/A') ?></td>
                    <td class="text-muted small"><?= ago($obs['created_at']) ?></td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <?php if (Auth::can('create_data')): ?>
                            <a href="<?= url('admin/donnees/' . $obs['id'] . '/modifier') ?>"
                               class="btn btn-outline-primary" title="Modifier">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <?php endif; ?>

                            <?php if (Auth::can('validate_data') && $obs['statut'] === 'soumis'): ?>
                            <form method="POST" action="<?= url('admin/donnees/' . $obs['id'] . '/valider') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-info" title="Valider"
                                        onclick="return confirm('Valider cette observation ?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if (Auth::can('publish_data') && $obs['statut'] === 'valide'): ?>
                            <form method="POST" action="<?= url('admin/donnees/' . $obs['id'] . '/publier') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-success" title="Publier"
                                        onclick="return confirm('Publier cette observation ?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if (Auth::can('validate_data') && in_array($obs['statut'], ['soumis','valide'])): ?>
                            <form method="POST" action="<?= url('admin/donnees/' . $obs['id'] . '/rejeter') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-danger" title="Rejeter"
                                        onclick="return confirm('Rejeter cette observation ?')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navigation" class="d-flex justify-content-between align-items-center px-3 pb-3">
        <span class="text-muted small">Page <?= $page ?> / <?= $totalPages ?></span>
        <div class="btn-group btn-group-sm">
            <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>"
                   class="btn btn-outline-secondary">← Préc.</a>
            <?php endif; ?>
            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            for ($p = $start; $p <= $end; $p++): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
                   class="btn <?= $p === $page ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>"
                   class="btn btn-outline-secondary">Suiv. →</a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>
</div>
