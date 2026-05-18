<?php
/**
 * Vue importation de données (admin)
 * @var array $indicateurs
 * @var array $recentImports
 */
use App\Core\Session;
use App\Core\View;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Import de données</h1>
        <p class="admin-page-subtitle">CSV ou Excel (XLSX) — avec prévisualisation avant commit</p>
    </div>
    <a href="<?= url('admin/import/template') ?>" class="btn btn-outline-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Télécharger le template CSV
    </a>
</div>

<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('warning')): ?>
    <div class="alert alert-warning"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- ÉTAPE 1 : Upload & Dry-run -->
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">
                    <span class="step-badge">1</span>
                    Charger le fichier
                </h2>
            </div>
            <div class="admin-card-body">
                <form id="dryRunForm" enctype="multipart/form-data" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="indicateur_id" class="form-label">
                                    Indicateur (optionnel)
                                </label>
                                <select id="indicateur_id" name="indicateur_id" class="form-control">
                                    <option value="">— Détecté automatiquement —</option>
                                    <?php foreach ($indicateurs as $ind): ?>
                                        <option value="<?= $ind['id'] ?>">
                                            <?= esc($ind['code'] ? "[{$ind['code']}] " : '') ?><?= esc($ind['libelle_fr']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">
                                    Si sélectionné, toutes les lignes seront associées à cet indicateur.
                                </small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="fichier" class="form-label required">Fichier de données</label>
                                <input type="file" id="fichier" name="fichier" class="form-control" required
                                       accept=".csv,.xlsx">
                                <small class="text-muted">CSV ou Excel (XLSX) — max 5 Mo</small>
                            </div>
                        </div>
                    </div>

                    <!-- Zone drop -->
                    <div class="upload-zone mt-3" id="dropZone">
                        <div class="upload-zone-inner">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="mt-2 mb-0 text-muted">Glissez votre fichier CSV/XLSX ici</p>
                            <div id="dropFileName" class="fw-bold text-primary mt-1"></div>
                        </div>
                    </div>

                    <button type="button" id="btnDryRun" class="btn btn-warning mt-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Analyser le fichier (dry-run)
                    </button>
                </form>
            </div>
        </div>

        <!-- RÉSULTATS DRY-RUN -->
        <div id="dryRunResults" class="d-none">
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <span class="step-badge">2</span>
                        Résultats de l'analyse
                    </h2>
                </div>
                <div class="admin-card-body">
                    <!-- Stats globales -->
                    <div class="row g-3 mb-4" id="dryRunStats"></div>

                    <!-- Erreurs -->
                    <div id="dryRunErrors" class="d-none">
                        <h3 class="fs-6 text-danger mb-2">Lignes avec erreurs</h3>
                        <div class="table-responsive">
                            <table class="table table-sm table-danger" id="errorTable">
                                <thead><tr><th>Ligne</th><th>Erreurs</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Prévisualisation valides -->
                    <div id="dryRunValid" class="d-none">
                        <h3 class="fs-6 text-success mb-2">Lignes valides (prévisualisation)</h3>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped" id="validTable">
                                <thead>
                                    <tr>
                                        <th>Ligne</th>
                                        <th>Indicateur</th>
                                        <th>Période début</th>
                                        <th>Valeur total</th>
                                        <th>Zone</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ÉTAPE 3 : Commit -->
            <div class="admin-card mb-4" id="commitCard">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">
                        <span class="step-badge">3</span>
                        Confirmer l'importation
                    </h2>
                </div>
                <div class="admin-card-body">
                    <div class="alert alert-warning">
                        <strong>⚠️ Attention :</strong> Cette action va insérer les données valides en base.
                        Les doublons seront automatiquement ignorés. Cette opération est irréversible.
                    </div>

                    <form method="POST" action="<?= url('admin/import/commit') ?>" id="commitForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="rows_json" id="rowsJson">

                        <div class="d-flex gap-3 align-items-center">
                            <button type="submit" class="btn btn-success" id="btnCommit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                Importer les données valides
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnCancel">
                                Annuler
                            </button>
                            <span id="commitInfo" class="text-muted small"></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SIDEBAR : Guide & Historique -->
    <div class="col-lg-4">
        <!-- Format attendu -->
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h2 class="admin-card-title">Format du fichier</h2></div>
            <div class="admin-card-body">
                <p class="small text-muted mb-2">Colonnes attendues (CSV avec en-têtes) :</p>
                <div class="table-responsive">
                    <table class="table table-sm small">
                        <thead><tr><th>Colonne</th><th>Req.</th><th>Exemple</th></tr></thead>
                        <tbody>
                            <tr><td><code>indicateur_code</code></td><td>*</td><td>IND001</td></tr>
                            <tr><td><code>indicateur_slug</code></td><td></td><td>flux-retours</td></tr>
                            <tr><td><code>periode_debut</code></td><td>*</td><td>2023-01-01</td></tr>
                            <tr><td><code>periode_fin</code></td><td></td><td>2023-12-31</td></tr>
                            <tr><td><code>frequence</code></td><td></td><td>annuelle</td></tr>
                            <tr><td><code>geo_entite</code></td><td></td><td>niger</td></tr>
                            <tr><td><code>valeur_masculin</code></td><td></td><td>1200</td></tr>
                            <tr><td><code>valeur_feminin</code></td><td></td><td>800</td></tr>
                            <tr><td><code>valeur_autre</code></td><td></td><td>50</td></tr>
                            <tr><td><code>valeur_total</code></td><td></td><td>2050</td></tr>
                            <tr><td><code>source_donnee</code></td><td></td><td>OIM</td></tr>
                            <tr><td><code>commentaire</code></td><td></td><td>Provisoire</td></tr>
                        </tbody>
                    </table>
                </div>
                <a href="<?= url('admin/import/template') ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                    📥 Télécharger le template
                </a>
            </div>
        </div>

        <!-- Historique -->
        <div class="admin-card">
            <div class="admin-card-header"><h2 class="admin-card-title">Historique récent</h2></div>
            <div class="admin-card-body">
                <?php if (empty($recentImports)): ?>
                    <p class="text-muted small">Aucun import récent.</p>
                <?php else: ?>
                <ul class="list-unstyled small">
                    <?php foreach ($recentImports as $imp): ?>
                    <li class="mb-3 pb-3 border-bottom">
                        <div class="fw-medium"><?= esc($imp['auteur'] ?? 'Système') ?></div>
                        <div class="text-muted"><?= esc($imp['detail']) ?></div>
                        <div class="text-muted" style="font-size:.75rem;"><?= ago($imp['created_at']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.step-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:24px; height:24px; border-radius:50%;
    background:var(--primary); color:#fff;
    font-size:.8rem; font-weight:700; margin-right:.5rem;
}
.upload-zone {
    border:2px dashed #d1d5db; border-radius:8px;
    padding:2rem; text-align:center; cursor:pointer;
    transition:border-color .2s, background .2s;
}
.upload-zone.drag-over, .upload-zone:hover {
    border-color:var(--primary); background:rgba(0,91,154,.04);
}
.upload-zone-inner { pointer-events:none; }
</style>

<script>
(function () {
    const dropZone    = document.getElementById('dropZone');
    const fileInput   = document.getElementById('fichier');
    const dropName    = document.getElementById('dropFileName');
    const btnDryRun   = document.getElementById('btnDryRun');
    const resultsDiv  = document.getElementById('dryRunResults');
    const statsDiv    = document.getElementById('dryRunStats');
    const errorsDiv   = document.getElementById('dryRunErrors');
    const validDiv    = document.getElementById('dryRunValid');
    const errorTbody  = document.querySelector('#errorTable tbody');
    const validTbody  = document.querySelector('#validTable tbody');
    const rowsJson    = document.getElementById('rowsJson');
    const commitInfo  = document.getElementById('commitInfo');
    const btnCancel   = document.getElementById('btnCancel');

    // Drag & drop
    dropZone?.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone?.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone?.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        fileInput.files = dt.files;
        showFileName(e.dataTransfer.files[0].name);
    });
    dropZone?.addEventListener('click', () => fileInput.click());
    fileInput?.addEventListener('change', () => {
        if (fileInput.files[0]) showFileName(fileInput.files[0].name);
    });
    function showFileName(name) {
        if (dropName) dropName.textContent = '📄 ' + name;
    }

    // DRY-RUN
    btnDryRun?.addEventListener('click', async () => {
        if (!fileInput.files[0]) { alert('Veuillez sélectionner un fichier.'); return; }

        btnDryRun.disabled = true;
        btnDryRun.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Analyse en cours…';

        const formData = new FormData(document.getElementById('dryRunForm'));
        formData.append('fichier', fileInput.files[0]);

        try {
            const resp = await fetch('<?= url('admin/import/dry-run') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await resp.json();

            if (data.error) { alert('Erreur : ' + data.error); return; }

            // Stats
            statsDiv.innerHTML = `
                <div class="col-4">
                    <div class="kpi-card text-center">
                        <div class="kpi-value">${data.total}</div>
                        <div class="kpi-label">Total lignes</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="kpi-card text-center" style="border-left-color:#10b981">
                        <div class="kpi-value text-success">${data.valid.length}</div>
                        <div class="kpi-label">Valides</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="kpi-card text-center" style="border-left-color:#ef4444">
                        <div class="kpi-value text-danger">${data.errors.length}</div>
                        <div class="kpi-label">Erreurs</div>
                    </div>
                </div>
            `;

            // Erreurs
            if (data.errors.length > 0) {
                errorsDiv.classList.remove('d-none');
                errorTbody.innerHTML = data.errors.slice(0,50).map(e => `
                    <tr>
                        <td>${e.line}</td>
                        <td>${e.errors.join(', ')}</td>
                    </tr>
                `).join('');
            } else {
                errorsDiv.classList.add('d-none');
            }

            // Valides
            if (data.valid.length > 0) {
                validDiv.classList.remove('d-none');
                validTbody.innerHTML = data.valid.slice(0,100).map(r => `
                    <tr>
                        <td>${r._line}</td>
                        <td>${r.indicateur_slug || '—'}</td>
                        <td>${r.periode_debut}</td>
                        <td>${r.valeur_total ?? '—'}</td>
                        <td>${r.geo_entite_id || 'Niger'}</td>
                    </tr>
                `).join('');
                rowsJson.value = JSON.stringify(data.valid);
                commitInfo.textContent = `${data.valid.length} ligne(s) prête(s) à importer`;
            } else {
                validDiv.classList.add('d-none');
                document.getElementById('commitCard').classList.add('d-none');
            }

            resultsDiv.classList.remove('d-none');
            resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });

        } catch (err) {
            alert('Erreur réseau : ' + err.message);
        } finally {
            btnDryRun.disabled = false;
            btnDryRun.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Analyser le fichier (dry-run)`;
        }
    });

    // Annuler
    btnCancel?.addEventListener('click', () => {
        resultsDiv.classList.add('d-none');
        rowsJson.value = '';
    });
})();
</script>
