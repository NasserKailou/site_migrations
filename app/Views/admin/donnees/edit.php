<?php
/**
 * Vue édition observation (admin)
 * @var array $obs         L'observation à modifier
 * @var array $indicateurs Liste des indicateurs actifs
 * @var array $niveaux     Niveaux de désagrégation
 * @var array $geo_entites Entités géographiques
 * @var array $documents   Documents liés
 */
use App\Core\Auth;
use App\Core\View;
use App\Core\Session;
$isNew   = empty($obs['id']);
$title   = $isNew ? 'Nouvelle observation' : 'Modifier l\'observation #' . $obs['id'];
$old     = Session::flash('old') ?? [];
?>

<div class="admin-page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('admin') ?>">Tableau de bord</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/donnees') ?>">Données</a></li>
                <li class="breadcrumb-item active"><?= $title ?></li>
            </ol>
        </nav>
        <h1 class="admin-page-title"><?= $title ?></h1>
        <?php if (!$isNew): ?>
        <div class="mt-1">
            <?php
            $badges = ['brouillon'=>'secondary','soumis'=>'warning','valide'=>'info','publie'=>'success','rejete'=>'danger'];
            $labels = ['brouillon'=>'Brouillon','soumis'=>'Soumis','valide'=>'Validé','publie'=>'Publié','rejete'=>'Rejeté'];
            $st = $obs['statut'] ?? 'brouillon';
            ?>
            <span class="badge badge-<?= $badges[$st] ?? 'secondary' ?> badge-lg">
                <?= $labels[$st] ?? $st ?>
            </span>
        </div>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/donnees') ?>" class="btn btn-outline-secondary">← Retour</a>
    </div>
</div>

<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>

<form method="POST"
      action="<?= $isNew ? url('admin/donnees') : url('admin/donnees/' . $obs['id']) ?>"
      enctype="multipart/form-data"
      id="editForm"
      novalidate>
    <?= csrf_field() ?>
    <?php if (!$isNew): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <div class="row g-4">
        <!-- COLONNE PRINCIPALE -->
        <div class="col-lg-8">

            <!-- Indicateur & Période -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Indicateur & Période</h2>
                </div>
                <div class="admin-card-body">
                    <div class="form-group">
                        <label for="indicateur_id" class="form-label required">Indicateur</label>
                        <select id="indicateur_id" name="indicateur_id" class="form-control" required
                                <?= !$isNew ? 'disabled' : '' ?>>
                            <option value="">— Sélectionner un indicateur —</option>
                            <?php foreach ($indicateurs as $ind): ?>
                                <option value="<?= $ind['id'] ?>"
                                    <?= ($obs['indicateur_id'] ?? $old['indicateur_id'] ?? '') == $ind['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ind['libelle_fr']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$isNew): ?>
                            <input type="hidden" name="indicateur_id" value="<?= $obs['indicateur_id'] ?>">
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="periode_debut" class="form-label required">Début de période</label>
                                <input type="date" id="periode_debut" name="periode_debut"
                                       class="form-control" required
                                       value="<?= esc($obs['periode_debut'] ?? $old['periode_debut'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="periode_fin" class="form-label">Fin de période</label>
                                <input type="date" id="periode_fin" name="periode_fin"
                                       class="form-control"
                                       value="<?= esc($obs['periode_fin'] ?? $old['periode_fin'] ?? '') ?>">
                                <small class="text-muted">Laisser vide si identique au début</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="frequence_id" class="form-label">Fréquence</label>
                                <select id="frequence_id" name="frequence_id" class="form-control">
                                    <option value="">— Non précisée —</option>
                                    <?php foreach ($frequences ?? [] as $f): ?>
                                        <option value="<?= $f['id'] ?>"
                                            <?= ($obs['frequence_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                            <?= esc($f['libelle_fr']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="geo_entite_id" class="form-label">Zone géographique</label>
                                <select id="geo_entite_id" name="geo_entite_id" class="form-control">
                                    <option value="">— National —</option>
                                    <?php foreach ($geo_entites as $geo): ?>
                                        <option value="<?= $geo['id'] ?>"
                                            <?= ($obs['geo_entite_id'] ?? '') == $geo['id'] ? 'selected' : '' ?>>
                                            <?= esc($geo['libelle']) ?>
                                            (<?= esc($geo['type']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valeurs -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Valeurs numériques</h2>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="valeur_masculin" class="form-label">Hommes</label>
                                <input type="number" id="valeur_masculin" name="valeur_masculin"
                                       class="form-control val-component" step="any" min="0"
                                       value="<?= esc($obs['valeur_masculin'] ?? $old['valeur_masculin'] ?? '') ?>"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="valeur_feminin" class="form-label">Femmes</label>
                                <input type="number" id="valeur_feminin" name="valeur_feminin"
                                       class="form-control val-component" step="any" min="0"
                                       value="<?= esc($obs['valeur_feminin'] ?? $old['valeur_feminin'] ?? '') ?>"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="valeur_autre" class="form-label">Autre</label>
                                <input type="number" id="valeur_autre" name="valeur_autre"
                                       class="form-control val-component" step="any" min="0"
                                       value="<?= esc($obs['valeur_autre'] ?? $old['valeur_autre'] ?? '') ?>"
                                       placeholder="0">
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label for="valeur_total" class="form-label required">Total</label>
                                <input type="number" id="valeur_total" name="valeur_total"
                                       class="form-control fw-bold" step="any" min="0"
                                       value="<?= esc($obs['valeur_total'] ?? $old['valeur_total'] ?? '') ?>"
                                       placeholder="Total">
                            </div>
                        </div>
                    </div>

                    <div id="totalWarning" class="alert alert-warning small mt-2 d-none" role="alert">
                        ⚠️ La somme des composantes (H + F + Autre) diffère du total de plus de 1 %.
                    </div>

                    <div class="form-group mt-3">
                        <label for="source_donnee" class="form-label">Source</label>
                        <input type="text" id="source_donnee" name="source_donnee"
                               class="form-control"
                               placeholder="Ex: OIM, INS, UNHCR…"
                               value="<?= esc($obs['source_donnee'] ?? $old['source_donnee'] ?? '') ?>">
                    </div>

                    <div class="form-group mt-3">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea id="commentaire" name="commentaire" class="form-control" rows="3"
                                  placeholder="Notes, précisions sur les données…"><?= esc($obs['commentaire'] ?? $old['commentaire'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Désagrégation -->
            <?php if (!empty($niveaux)): ?>
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Désagrégation</h2>
                    <span class="admin-card-subtitle">Optionnel — données ventilées</span>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="niveau_id" class="form-label">Niveau de désagrégation</label>
                                <select id="niveau_id" name="niveau_id" class="form-control">
                                    <option value="">— Aucun —</option>
                                    <?php foreach ($niveaux as $niv): ?>
                                        <option value="<?= $niv['id'] ?>"
                                            <?= ($obs['niveau_id'] ?? '') == $niv['id'] ? 'selected' : '' ?>>
                                            <?= esc($niv['libelle']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="niveau_valeur_id" class="form-label">Valeur du niveau</label>
                                <select id="niveau_valeur_id" name="niveau_valeur_id" class="form-control">
                                    <option value="">— Sélectionner d'abord le niveau —</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- COLONNE LATÉRALE -->
        <div class="col-lg-4">

            <!-- Actions workflow -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Actions</h2>
                </div>
                <div class="admin-card-body d-grid gap-2">
                    <?php if ($isNew || in_array($obs['statut'] ?? '', ['brouillon', 'rejete'])): ?>
                        <button type="submit" name="action" value="brouillon" class="btn btn-outline-secondary">
                            💾 Enregistrer brouillon
                        </button>
                        <?php if (Auth::can('create_data')): ?>
                        <button type="submit" name="action" value="soumettre" class="btn btn-warning">
                            📤 Soumettre pour validation
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!$isNew && Auth::can('validate_data') && $obs['statut'] === 'soumis'): ?>
                        <button type="submit" name="action" value="valider" class="btn btn-info"
                                onclick="return confirm('Valider cette observation ?')">
                            ✓ Valider
                        </button>
                        <button type="submit" name="action" value="rejeter" class="btn btn-outline-danger"
                                onclick="return confirm('Rejeter cette observation ?')">
                            ✗ Rejeter
                        </button>
                    <?php endif; ?>

                    <?php if (!$isNew && Auth::can('publish_data') && $obs['statut'] === 'valide'): ?>
                        <button type="submit" name="action" value="publier" class="btn btn-success"
                                onclick="return confirm('Publier cette observation sur le site ?')">
                            🌐 Publier
                        </button>
                    <?php endif; ?>

                    <?php if (!$isNew && in_array($obs['statut'] ?? '', ['brouillon','soumis','rejete'])): ?>
                        <button type="submit" name="action" value="save" class="btn btn-primary">
                            Enregistrer les modifications
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Documents -->
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Documents justificatifs</h2>
                </div>
                <div class="admin-card-body">
                    <?php if (!empty($documents)): ?>
                    <ul class="list-unstyled mb-3">
                        <?php foreach ($documents as $doc): ?>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <a href="<?= url($doc['chemin_fichier']) ?>" target="_blank" rel="noopener" class="text-truncate small">
                                <?= esc($doc['libelle_fr']) ?>
                            </a>
                            <label class="ms-auto d-flex align-items-center gap-1 small text-danger" style="cursor:pointer">
                                <input type="checkbox" name="delete_docs[]" value="<?= $doc['id'] ?>">
                                Suppr.
                            </label>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="document" class="form-label">Ajouter un document</label>
                        <input type="file" id="document" name="document"
                               class="form-control form-control-sm"
                               accept=".pdf,.xlsx,.csv,.docx,.zip">
                        <small class="text-muted">PDF, Excel, CSV, Word, ZIP — max 10 Mo</small>
                    </div>
                </div>
            </div>

            <!-- Métadonnées -->
            <?php if (!$isNew): ?>
            <div class="admin-card">
                <div class="admin-card-header">
                    <h2 class="admin-card-title">Métadonnées</h2>
                </div>
                <div class="admin-card-body">
                    <dl class="meta-grid small">
                        <dt>ID</dt><dd><?= $obs['id'] ?></dd>
                        <dt>Créé le</dt><dd><?= date_fr($obs['created_at']) ?></dd>
                        <dt>Modifié</dt><dd><?= date_fr($obs['updated_at']) ?></dd>
                        <?php if (!empty($obs['saisi_nom'])): ?>
                        <dt>Saisi par</dt><dd><?= esc($obs['saisi_nom']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($obs['valide_par_nom'])): ?>
                        <dt>Validé par</dt><dd><?= esc($obs['valide_par_nom']) ?></dd>
                        <?php endif; ?>
                        <?php if (!empty($obs['publie_par_nom'])): ?>
                        <dt>Publié par</dt><dd><?= esc($obs['publie_par_nom']) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
(function () {
    // Auto-calcul total + avertissement écart
    const vm = document.getElementById('valeur_masculin');
    const vf = document.getElementById('valeur_feminin');
    const va = document.getElementById('valeur_autre');
    const vt = document.getElementById('valeur_total');
    const warn = document.getElementById('totalWarning');

    function checkTotal() {
        const m = parseFloat(vm?.value) || 0;
        const f = parseFloat(vf?.value) || 0;
        const a = parseFloat(va?.value) || 0;
        const t = parseFloat(vt?.value) || 0;
        if (m === 0 && f === 0 && a === 0) { warn?.classList.add('d-none'); return; }
        const sum = m + f + a;
        if (!vt?.value) { vt.value = sum; warn?.classList.add('d-none'); return; }
        if (t > 0 && Math.abs(sum - t) / t > 0.01) {
            warn?.classList.remove('d-none');
        } else {
            warn?.classList.add('d-none');
        }
    }

    [vm, vf, va].forEach(el => el?.addEventListener('input', () => {
        const m = parseFloat(vm?.value) || 0;
        const f = parseFloat(vf?.value) || 0;
        const a = parseFloat(va?.value) || 0;
        vt.value = (m + f + a) || '';
        checkTotal();
    }));
    vt?.addEventListener('input', checkTotal);

    // Chargement dynamique des valeurs de niveau
    const niveauSel = document.getElementById('niveau_id');
    const valeurSel = document.getElementById('niveau_valeur_id');
    const currentValId = <?= json_encode($obs['niveau_valeur_id'] ?? null) ?>;

    if (niveauSel && valeurSel) {
        niveauSel.addEventListener('change', function () {
            const nid = this.value;
            valeurSel.innerHTML = '<option value="">Chargement…</option>';
            if (!nid) { valeurSel.innerHTML = '<option value="">— Aucun —</option>'; return; }
            fetch(`<?= url('api/v1/niveau-valeurs') ?>?niveau_id=${nid}`)
                .then(r => r.json())
                .then(data => {
                    valeurSel.innerHTML = '<option value="">— Sélectionner —</option>';
                    (data.data || []).forEach(v => {
                        const opt = new Option(v.valeur, v.id);
                        if (currentValId && v.id == currentValId) opt.selected = true;
                        valeurSel.add(opt);
                    });
                })
                .catch(() => { valeurSel.innerHTML = '<option value="">Erreur de chargement</option>'; });
        });

        // Déclencher si un niveau est déjà sélectionné
        if (niveauSel.value) niveauSel.dispatchEvent(new Event('change'));
    }
})();
</script>
