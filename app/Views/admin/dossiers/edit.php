<?php
/**
 * Vue édition dossier (admin) — création & modification
 * @var array  $dossier     Données du dossier (vide pour création)
 * @var array  $documents   Documents liés
 * @var array  $indicateurs Indicateurs actifs
 */
use App\Core\Session;
use App\Core\View;
$isNew = empty($dossier['id']);
$title = $isNew ? 'Nouveau dossier' : 'Modifier : ' . ($dossier['titre_fr'] ?? '');
?>

<div class="admin-page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('admin') ?>">Tableau de bord</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/dossiers') ?>">Dossiers</a></li>
                <li class="breadcrumb-item active"><?= $isNew ? 'Nouveau' : 'Modifier' ?></li>
            </ol>
        </nav>
        <h1 class="admin-page-title"><?= esc($title) ?></h1>
    </div>
    <div class="d-flex gap-2">
        <?php if (!$isNew && !empty($dossier['slug'])): ?>
        <a href="<?= url('agadez') ?>" target="_blank" class="btn btn-outline-secondary">
            Voir sur le site →
        </a>
        <?php endif; ?>
        <a href="<?= url('admin/dossiers') ?>" class="btn btn-outline-secondary">← Retour</a>
    </div>
</div>

<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>

<form method="POST"
      action="<?= $isNew ? url('admin/dossiers') : url('admin/dossiers/' . $dossier['id']) ?>"
      enctype="multipart/form-data"
      id="dossierForm" novalidate>
    <?= csrf_field() ?>
    <?php if (!$isNew): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row g-4">
        <!-- COLONNE PRINCIPALE -->
        <div class="col-lg-8">

            <!-- Informations générales -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Informations générales</h2></div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="titre_fr" class="form-label required">Titre (Français)</label>
                        <input type="text" id="titre_fr" name="titre_fr" class="form-control" required
                               value="<?= esc($dossier['titre_fr'] ?? '') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="titre_en" class="form-label">Title (English)</label>
                        <input type="text" id="titre_en" name="titre_en" class="form-control"
                               value="<?= esc($dossier['titre_en'] ?? '') ?>">
                    </div>
                    <div class="form-group mt-3">
                        <label for="description_fr" class="form-label">Description / Introduction</label>
                        <textarea id="description_fr" name="description_fr" class="form-control" rows="5"
                                  placeholder="Texte introductif affiché sur la page publique…"><?= esc($dossier['description_fr'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Intégration Power BI -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Intégration Power BI</h2>
                    <span class="admin-card-subtitle">Dashboard interactif embarqué sur la page publique</span>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="powerbi_url" class="form-label">URL Power BI Embed</label>
                        <input type="url" id="powerbi_url" name="powerbi_url" class="form-control"
                               placeholder="https://app.powerbi.com/reportEmbed?reportId=…"
                               value="<?= esc($dossier['powerbi_url'] ?? '') ?>">
                        <small class="text-muted">
                            Format attendu : URL d'intégration depuis Power BI Service →
                            Fichier → Publier → Intégrer. Laisser vide pour désactiver.
                        </small>
                    </div>

                    <?php if (!empty($dossier['powerbi_url'])): ?>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="previewPbiBtn">
                            Prévisualiser le dashboard
                        </button>
                        <div id="pbiPreview" class="mt-3" style="display:none;">
                            <div class="ratio ratio-16x9" style="max-height:500px;">
                                <iframe src="<?= esc($dossier['powerbi_url']) ?>"
                                        title="Power BI Preview"
                                        allowfullscreen
                                        style="border:1px solid #e5e7eb; border-radius:8px;">
                                </iframe>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documents -->
            <?php if (!$isNew): ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Documents téléchargeables</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($documents)): ?>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Libellé</th>
                                    <th>Fichier</th>
                                    <th>Taille</th>
                                    <th>Supprimer</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?= esc($doc['libelle_fr']) ?></td>
                                <td>
                                    <a href="<?= url($doc['chemin_fichier']) ?>" target="_blank"
                                       class="small text-truncate d-inline-block" style="max-width:200px;">
                                        <?= esc(basename($doc['chemin_fichier'])) ?>
                                    </a>
                                </td>
                                <td class="small text-muted">
                                    <?= $doc['taille_octets'] ? number_format($doc['taille_octets'] / 1024, 0) . ' Ko' : '—' ?>
                                </td>
                                <td>
                                    <label class="d-flex align-items-center gap-1 text-danger small" style="cursor:pointer">
                                        <input type="checkbox" name="delete_docs[]" value="<?= $doc['id'] ?>">
                                        Supprimer
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted small">Aucun document pour l'instant.</p>
                    <?php endif; ?>

                    <!-- Upload nouveaux documents -->
                    <div class="upload-zone" id="dropZone">
                        <div class="upload-zone-inner">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="mt-2 mb-0">Glissez vos fichiers ici ou <label for="nouveaux_documents" class="text-primary" style="cursor:pointer">parcourez</label></p>
                            <small class="text-muted">PDF, Excel, CSV, Word, ZIP — max 10 Mo par fichier</small>
                            <input type="file" id="nouveaux_documents" name="nouveaux_documents[]"
                                   multiple accept=".pdf,.xlsx,.csv,.docx,.zip"
                                   class="d-none">
                        </div>
                    </div>
                    <div id="fileList" class="mt-3"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- COLONNE LATÉRALE -->
        <div class="col-lg-4">

            <!-- Statut & Publication -->
            <div class="admin-card mb-4">
                <div class="admin-card-header"><h2 class="admin-card-title">Publication</h2></div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="statut" class="form-label">Statut</label>
                        <select id="statut" name="statut" class="form-control">
                            <option value="brouillon" <?= ($dossier['statut'] ?? 'brouillon') === 'brouillon' ? 'selected' : '' ?>>
                                📝 Brouillon
                            </option>
                            <option value="publie"    <?= ($dossier['statut'] ?? '') === 'publie' ? 'selected' : '' ?>>
                                🌐 Publié
                            </option>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label for="image_couverture" class="form-label">Image de couverture</label>
                        <?php if (!empty($dossier['image_couverture'])): ?>
                        <div class="mb-2">
                            <img src="<?= url($dossier['image_couverture']) ?>"
                                 alt="Couverture actuelle"
                                 style="max-width:100%; border-radius:6px; max-height:120px; object-fit:cover;">
                        </div>
                        <?php endif; ?>
                        <input type="file" id="image_couverture" name="image_couverture"
                               class="form-control form-control-sm"
                               accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">JPG, PNG, WebP — recommandé 1200×630px</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mt-4">
                        <?= $isNew ? 'Créer le dossier' : 'Enregistrer les modifications' ?>
                    </button>
                </div>
            </div>

            <?php if (!$isNew): ?>
            <!-- Méta -->
            <div class="admin-card">
                <div class="admin-card-header"><h2 class="admin-card-title">Informations</h2></div>
                <div class="admin-card-body">
                    <dl class="meta-grid small">
                        <dt>ID</dt><dd><?= $dossier['id'] ?></dd>
                        <dt>Slug</dt><dd><code><?= esc($dossier['slug']) ?></code></dd>
                        <dt>Créé le</dt><dd><?= date_fr($dossier['created_at'] ?? '') ?></dd>
                        <dt>Modifié</dt><dd><?= date_fr($dossier['updated_at'] ?? '') ?></dd>
                        <dt>Documents</dt><dd><?= count($documents) ?></dd>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
(function () {
    // Toggle Power BI preview
    const previewBtn = document.getElementById('previewPbiBtn');
    const pbiPreview = document.getElementById('pbiPreview');
    previewBtn?.addEventListener('click', () => {
        pbiPreview.style.display = pbiPreview.style.display === 'none' ? 'block' : 'none';
        previewBtn.textContent = pbiPreview.style.display === 'none'
            ? 'Prévisualiser le dashboard' : 'Masquer la prévisualisation';
    });

    // Upload drag & drop
    const dropZone  = document.getElementById('dropZone');
    const fileInput = document.getElementById('nouveaux_documents');
    const fileList  = document.getElementById('fileList');

    if (dropZone && fileInput) {
        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            fileInput.files = e.dataTransfer.files;
            renderFiles(fileInput.files);
        });
        fileInput.addEventListener('change', () => renderFiles(fileInput.files));
    }

    function renderFiles(files) {
        if (!fileList) return;
        fileList.innerHTML = '';
        [...files].forEach((f, i) => {
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center gap-2 mb-2 p-2 bg-light rounded';
            div.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <div style="flex:1; min-width:0;">
                    <div class="fw-medium small text-truncate">${f.name}</div>
                    <div class="text-muted" style="font-size:.75rem;">${(f.size/1024).toFixed(0)} Ko</div>
                </div>
                <input type="text" name="doc_libelle_${i}" placeholder="Libellé…"
                       class="form-control form-control-sm" style="max-width:180px;"
                       value="${f.name.replace(/\.[^.]+$/, '')}">
            `;
            fileList.appendChild(div);
        });
    }
})();
</script>
