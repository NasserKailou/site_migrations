<?php
/**
 * Formulaire création/édition indicateur (admin)
 * Colonnes réelles : slug, libelle_fr, libelle_en, definition_fr, definition_en,
 *   methode_calcul (pas _fr/_en), donnees_requises, source_details,
 *   thematique_id, entite_id, frequence_id, unite_id,
 *   statut ENUM('actif','archive','brouillon'), notes
 * indicateur_niveaux : indicateur_id, niveau_desagregation_id
 *
 * @var array  $indicateur  Données de l'indicateur (vide pour création)
 * @var array  $thematiques
 * @var array  $entites
 * @var array  $frequences
 * @var array  $unites
 * @var array  $niveaux      SELECT id, libelle FROM niveaux_desagregation
 * @var array  $niveauxSel   IDs (niveau_desagregation_id) déjà associés
 */
use App\Core\Session;
$isNew = empty($indicateur['id']);
$title = $isNew ? 'Nouvel indicateur' : 'Modifier : ' . ($indicateur['libelle_fr'] ?? '');
$old   = Session::flash('old') ?? [];

// Helpers valeur
$v = static function (string $field) use ($old, $indicateur): string {
    return esc($old[$field] ?? $indicateur[$field] ?? '');
};
?>

<div class="admin-page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('admin') ?>">Tableau de bord</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/indicateurs') ?>">Indicateurs</a></li>
                <li class="breadcrumb-item active"><?= $isNew ? 'Nouveau' : 'Modifier' ?></li>
            </ol>
        </nav>
        <h1 class="admin-page-title"><?= esc($title) ?></h1>
    </div>
    <a href="<?= url('admin/indicateurs') ?>" class="btn btn-outline-secondary">← Retour</a>
</div>

<?php if ($flash = Session::flash('error')): ?>
    <div class="alert alert-danger"><?= $flash ?></div>
<?php endif; ?>
<?php if ($flash = Session::flash('success')): ?>
    <div class="alert alert-success"><?= $flash ?></div>
<?php endif; ?>

<form method="POST"
      action="<?= $isNew ? url('admin/indicateurs') : url('admin/indicateurs/' . $indicateur['id']) ?>"
      id="indForm" novalidate>
    <?= csrf_field() ?>
    <?php if (!$isNew): ?>
        <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <!-- Onglets -->
    <ul class="nav nav-tabs mb-4" role="tablist" id="indTabs">
        <li class="nav-item"><button class="nav-link active" data-tab="general"  type="button">Général</button></li>
        <li class="nav-item"><button class="nav-link"        data-tab="meta"     type="button">Métadonnées</button></li>
        <li class="nav-item"><button class="nav-link"        data-tab="niveaux"  type="button">Désagrégation</button></li>
        <li class="nav-item"><button class="nav-link"        data-tab="avance"   type="button">Avancé</button></li>
    </ul>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ONGLET GÉNÉRAL                                                      -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div id="tab-general" class="tab-pane active">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Identité</h2></div>
                    <div class="admin-card-body">

                        <!-- Slug -->
                        <div class="form-group">
                            <label for="slug" class="form-label">Slug URL
                                <small class="text-muted">(auto-généré si vide)</small>
                            </label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                   placeholder="ex: taux-mortalite-maternelle"
                                   pattern="[a-z0-9\-]+"
                                   value="<?= $v('slug') ?>">
                            <small class="text-muted">Minuscules, chiffres, tirets uniquement.</small>
                        </div>

                        <!-- Libellés -->
                        <div class="form-group mt-3">
                            <label for="libelle_fr" class="form-label required">Libellé français</label>
                            <input type="text" id="libelle_fr" name="libelle_fr" class="form-control" required
                                   value="<?= $v('libelle_fr') ?>">
                        </div>
                        <div class="form-group mt-3">
                            <label for="libelle_en" class="form-label">Label (English)</label>
                            <input type="text" id="libelle_en" name="libelle_en" class="form-control"
                                   value="<?= $v('libelle_en') ?>">
                        </div>

                        <!-- Thématique + Entité -->
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="thematique_id" class="form-label required">Thématique</label>
                                    <select id="thematique_id" name="thematique_id" class="form-control" required>
                                        <option value="">— Sélectionner —</option>
                                        <?php foreach ($thematiques as $t): ?>
                                            <option value="<?= $t['id'] ?>"
                                                <?= ($old['thematique_id'] ?? $indicateur['thematique_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                                <?= esc($t['libelle_fr']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="entite_id" class="form-label">Entité productrice</label>
                                    <select id="entite_id" name="entite_id" class="form-control">
                                        <option value="">— Non précisée —</option>
                                        <?php foreach ($entites as $e): ?>
                                            <option value="<?= $e['id'] ?>"
                                                <?= ($old['entite_id'] ?? $indicateur['entite_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
                                                <?= esc($e['acronyme']) ?> — <?= esc($e['libelle_fr']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fréquence + Unité -->
                        <div class="row g-3 mt-1">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="frequence_id" class="form-label">Fréquence de mesure</label>
                                    <select id="frequence_id" name="frequence_id" class="form-control">
                                        <option value="">— Non précisée —</option>
                                        <?php foreach ($frequences as $f): ?>
                                            <option value="<?= $f['id'] ?>"
                                                <?= ($old['frequence_id'] ?? $indicateur['frequence_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
                                                <?= esc($f['libelle_fr']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="unite_id" class="form-label">Unité de mesure</label>
                                    <select id="unite_id" name="unite_id" class="form-control">
                                        <option value="">— Non précisée —</option>
                                        <?php foreach ($unites as $u): ?>
                                            <option value="<?= $u['id'] ?>"
                                                <?= ($old['unite_id'] ?? $indicateur['unite_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                                <?= esc($u['libelle_fr']) ?><?= $u['symbole'] ? ' (' . esc($u['symbole']) . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="col-lg-4">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Options</h2></div>
                    <div class="admin-card-body">
                        <div class="form-group">
                            <label for="statut" class="form-label">Statut</label>
                            <select id="statut" name="statut" class="form-control">
                                <?php
                                $curStatut = $old['statut'] ?? $indicateur['statut'] ?? 'actif';
                                foreach (['actif' => 'Actif', 'archive' => 'Archivé', 'brouillon' => 'Brouillon'] as $val => $lbl):
                                ?>
                                <option value="<?= $val ?>" <?= $curStatut === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h2 class="admin-card-title">Actions</h2></div>
                    <div class="admin-card-body d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?= $isNew ? "Créer l'indicateur" : 'Enregistrer les modifications' ?>
                        </button>
                        <?php if (!$isNew): ?>
                        <a href="<?= url('indicateurs/' . ($indicateur['slug'] ?? '')) ?>" target="_blank"
                           class="btn btn-outline-secondary">
                            Voir sur le site →
                        </a>
                        <?php endif; ?>
                        <a href="<?= url('admin/indicateurs') ?>" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>

                <!-- Méta édition -->
                <?php if (!$isNew): ?>
                <div class="admin-card">
                    <div class="admin-card-header"><h2 class="admin-card-title">Informations</h2></div>
                    <div class="admin-card-body">
                        <dl class="meta-grid small">
                            <dt>ID</dt><dd><?= $indicateur['id'] ?></dd>
                            <dt>Observations</dt>
                            <dd>
                                <a href="<?= url('admin/donnees?indicateur_id=' . $indicateur['id']) ?>">
                                    Voir les données →
                                </a>
                            </dd>
                            <dt>Créé le</dt><dd><?= date_fr($indicateur['created_at'] ?? '') ?></dd>
                            <dt>Modifié</dt><dd><?= date_fr($indicateur['updated_at'] ?? '') ?></dd>
                        </dl>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ONGLET MÉTADONNÉES                                                  -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div id="tab-meta" class="tab-pane d-none">
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h2 class="admin-card-title">Définition</h2></div>
            <div class="admin-card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="definition_fr" class="form-label">Définition (Français)</label>
                            <textarea id="definition_fr" name="definition_fr" class="form-control" rows="6"><?= esc($indicateur['definition_fr'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="definition_en" class="form-label">Definition (English)</label>
                            <textarea id="definition_en" name="definition_en" class="form-control" rows="6"><?= esc($indicateur['definition_en'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Méthode de calcul</h2>
                <p class="admin-card-subtitle">Formule ou description de la méthode utilisée</p>
            </div>
            <div class="admin-card-body">
                <div class="form-group">
                    <textarea id="methode_calcul" name="methode_calcul" class="form-control" rows="5"
                              placeholder="Décrivez la méthode de calcul ou la formule…"><?= esc($indicateur['methode_calcul'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Données requises</h2>
            </div>
            <div class="admin-card-body">
                <div class="form-group">
                    <textarea id="donnees_requises" name="donnees_requises" class="form-control" rows="3"
                              placeholder="Données nécessaires au calcul…"><?= esc($indicateur['donnees_requises'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Sources de données</h2>
            </div>
            <div class="admin-card-body">
                <div class="form-group">
                    <textarea id="source_details" name="source_details" class="form-control" rows="4"
                              placeholder="Enquêtes, registres administratifs, publications…"><?= esc($indicateur['source_details'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ONGLET NIVEAUX DE DÉSAGRÉGATION                                     -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div id="tab-niveaux" class="tab-pane d-none">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Niveaux de désagrégation disponibles</h2>
                <p class="admin-card-subtitle">Cochez les niveaux applicables à cet indicateur</p>
            </div>
            <div class="admin-card-body">
                <?php if (empty($niveaux)): ?>
                    <p class="text-muted">Aucun niveau de désagrégation défini.</p>
                <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($niveaux as $niv): ?>
                    <div class="col-sm-6 col-md-4">
                        <div class="form-check">
                            <input type="checkbox"
                                   id="niv_<?= $niv['id'] ?>"
                                   name="niveaux[]"
                                   value="<?= $niv['id'] ?>"
                                   class="form-check-input"
                                   <?= in_array((string)$niv['id'], array_map('strval', $niveauxSel), true) ? 'checked' : '' ?>>
                            <label for="niv_<?= $niv['id'] ?>" class="form-check-label">
                                <?= esc($niv['libelle']) ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- ONGLET AVANCÉ                                                       -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <div id="tab-avance" class="tab-pane d-none">
        <div class="admin-card mb-4">
            <div class="admin-card-header"><h2 class="admin-card-title">Notes internes</h2></div>
            <div class="admin-card-body">
                <div class="form-group">
                    <label for="notes" class="form-label">Notes (visibles uniquement en back-office)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="6"
                              placeholder="Commentaires internes, historique, problèmes connus…"><?= esc($indicateur['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

</form>

<script>
(function () {
    // ── Auto-slug depuis libellé ──────────────────────────────────────────
    const libelleInput = document.getElementById('libelle_fr');
    const slugInput    = document.getElementById('slug');

    function slugify(str) {
        return str.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/\s+/g, '-').replace(/-+/g, '-');
    }

    let slugManuallyEdited = (slugInput?.value ?? '').length > 0;
    slugInput?.addEventListener('input', () => { slugManuallyEdited = true; });
    libelleInput?.addEventListener('input', function () {
        if (!slugManuallyEdited) slugInput.value = slugify(this.value);
    });

    // ── Système d'onglets ────────────────────────────────────────────────
    document.querySelectorAll('#indTabs .nav-link').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#indTabs .nav-link').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('d-none'));
            this.classList.add('active');
            const pane = document.getElementById('tab-' + this.dataset.tab);
            pane?.classList.remove('d-none');
        });
    });
})();
</script>
