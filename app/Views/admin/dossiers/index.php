<?php
/**
 * Vue liste des dossiers (admin)
 * @var array $dossiers
 */
use App\Core\Auth;
use App\Core\View;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dossiers thématiques</h1>
        <p class="admin-page-subtitle"><?= count($dossiers) ?> dossier(s)</p>
    </div>
    <?php if (Auth::hasRole('admin', 'super_admin')): ?>
    <a href="<?= url('admin/dossiers/nouveau') ?>" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau dossier
    </a>
    <?php endif; ?>
</div>

<?php if ($flash = \App\Core\Session::flash('success')): ?>
    <div class="alert alert-success"><?= esc($flash) ?></div>
<?php endif; ?>

<div class="admin-card">
    <?php if (empty($dossiers)): ?>
    <div class="text-center py-5 text-muted">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" aria-hidden="true"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        <p class="mt-2">Aucun dossier créé pour l'instant.</p>
        <?php if (Auth::hasRole('admin', 'super_admin')): ?>
        <a href="<?= url('admin/dossiers/nouveau') ?>" class="btn btn-primary btn-sm mt-1">Créer le premier dossier</a>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover" aria-label="Liste des dossiers">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Slug</th>
                    <th class="text-center">Power BI</th>
                    <th class="text-center">Documents</th>
                    <th class="text-center">Statut</th>
                    <th>Dernière modif.</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dossiers as $d): ?>
            <tr>
                <td>
                    <strong><?= esc($d['titre_fr']) ?></strong>
                    <?php if ($d['titre_en']): ?>
                        <div class="text-muted small"><?= esc($d['titre_en']) ?></div>
                    <?php endif; ?>
                </td>
                <td><code class="small"><?= esc($d['slug']) ?></code></td>
                <td class="text-center">
                    <?php if ($d['powerbi_url']): ?>
                        <span class="badge badge-success" title="URL configurée">✓ Configuré</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Non configuré</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php
                    $nbDocs = \App\Core\Database::count(
                        "SELECT COUNT(*) FROM dossier_documents WHERE dossier_id = :id",
                        [':id' => $d['id']]
                    );
                    ?>
                    <span class="badge badge-light"><?= $nbDocs ?></span>
                </td>
                <td class="text-center">
                    <span class="badge <?= $d['statut'] === 'publie' ? 'badge-success' : 'badge-secondary' ?>">
                        <?= $d['statut'] === 'publie' ? 'Publié' : 'Brouillon' ?>
                    </span>
                </td>
                <td class="text-muted small"><?= ago($d['updated_at']) ?></td>
                <td class="text-end">
                    <div class="btn-group btn-group-sm">
                        <a href="<?= url('admin/dossiers/' . $d['id'] . '/modifier') ?>"
                           class="btn btn-outline-primary" title="Modifier">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <a href="<?= url('dossiers/' . $d['slug']) ?>" target="_blank"
                           class="btn btn-outline-secondary" title="Voir">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
