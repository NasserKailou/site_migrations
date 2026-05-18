<?php $pageTitle = 'Saisie de données'; ?>

<div class="admin-page-header">
  <div class="admin-page-title">
    <h1>Nouvelle saisie</h1>
    <p>Saisissez une nouvelle observation. Les champs marqués <span style="color:var(--color-danger)">*</span> sont obligatoires.</p>
  </div>
  <div>
    <a href="<?= url('admin/donnees') ?>" class="btn btn-ghost">
      <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour
    </a>
  </div>
</div>

<!-- Autosave indicator -->
<div id="autosave-indicator" style="font-size:.75rem;color:var(--gray-400);margin-bottom:1rem;opacity:.5"></div>

<form id="saisie-form" method="POST" action="<?= url('admin/donnees/saisie') ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start">

    <!-- Colonne principale -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">

      <!-- Card : Indicateur & Période -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          <i class="fa-solid fa-chart-line" aria-hidden="true"></i> Indicateur & Période
        </h3>
        <div class="form-grid">
          <div class="form-group" style="grid-column:1/-1">
            <label class="form-label" for="indicateur_id">
              Indicateur <span class="required" aria-hidden="true">*</span>
            </label>
            <select name="indicateur_id" id="indicateur_id" class="form-control" required
                    aria-required="true" onchange="onIndicateurChange(this.value)">
              <option value="">— Sélectionner un indicateur —</option>
              <?php foreach ($indicateurs as $ind): ?>
              <option value="<?= (int)$ind['id'] ?>"><?= esc($ind['libelle_fr']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="periode_debut">
              Date de début <span class="required" aria-hidden="true">*</span>
            </label>
            <input type="date" name="periode_debut" id="periode_debut" class="form-control"
                   required aria-required="true" value="<?= date('Y-01-01') ?>">
            <span class="form-hint">Généralement le 1er janvier de l'année</span>
          </div>
          <div class="form-group">
            <label class="form-label" for="periode_fin">Date de fin</label>
            <input type="date" name="periode_fin" id="periode_fin" class="form-control"
                   value="<?= date('Y-12-31') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="periodicite">Périodicité</label>
            <select name="periodicite" id="periodicite" class="form-control">
              <option value="annuelle" selected>Annuelle</option>
              <option value="mensuelle">Mensuelle</option>
              <option value="trimestrielle">Trimestrielle</option>
              <option value="semestrielle">Semestrielle</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Card : Désagrégation -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Désagrégation géographique / thématique
        </h3>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="geo_entite_id">Région / Entité géographique</label>
            <select name="geo_entite_id" id="geo_entite_id" class="form-control">
              <option value="">— National (toutes régions) —</option>
              <?php foreach ($geo_entites as $g): ?>
              <option value="<?= (int)$g['id'] ?>"><?= esc($g['libelle']) ?> (<?= esc($g['type']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" for="niveau_desagregation_id">Niveau de désagrégation</label>
            <select name="niveau_desagregation_id" id="niveau_desagregation_id" class="form-control"
                    onchange="document.getElementById('valeur-desag').style.display=this.value?'block':'none'">
              <option value="">— Aucun —</option>
              <?php foreach ($niveaux as $n): ?>
              <option value="<?= (int)$n['id'] ?>"><?= esc($n['libelle']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" id="valeur-desag" style="display:none;grid-column:1/-1">
            <label class="form-label" for="niveau_desag_valeur">Valeur de désagrégation</label>
            <input type="text" name="niveau_desag_valeur" id="niveau_desag_valeur" class="form-control"
                   placeholder="ex: Agadez, Maradi, Urbain...">
          </div>
        </div>
      </div>

      <!-- Card : Valeurs -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          <i class="fa-solid fa-calculator" aria-hidden="true"></i> Valeurs
        </h3>
        <div id="validation-alert" class="alert alert-error" style="display:none" role="alert" aria-live="polite">
          <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
          <span id="validation-msg"></span>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label" for="masculin">Masculin</label>
            <input type="number" name="masculin" id="masculin" class="form-control"
                   min="0" step="any" placeholder="0" oninput="calcTotal()">
          </div>
          <div class="form-group">
            <label class="form-label" for="feminin">Féminin</label>
            <input type="number" name="feminin" id="feminin" class="form-control"
                   min="0" step="any" placeholder="0" oninput="calcTotal()">
          </div>
          <div class="form-group">
            <label class="form-label" for="trans_autre">Autre / Trans</label>
            <input type="number" name="trans_autre" id="trans_autre" class="form-control"
                   min="0" step="any" placeholder="0" oninput="calcTotal()">
          </div>
          <div class="form-group">
            <label class="form-label" for="total">
              Total <span style="font-size:.7rem;color:var(--pndm-orange)">(calculé automatiquement)</span>
            </label>
            <input type="number" name="total" id="total" class="form-control"
                   min="0" step="any" placeholder="0"
                   style="font-weight:700;border-color:var(--pndm-blue)"
                   oninput="validateTotal()">
            <span class="form-hint">Laissez vide pour calculer depuis masculin + féminin</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Colonne latérale -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">

      <!-- Source document -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          <i class="fa-solid fa-paperclip" aria-hidden="true"></i> Document source
        </h3>
        <div class="form-group">
          <label class="form-label" for="document_source">
            Pièce jointe (PDF, JPG, PNG — max 10 Mo)
          </label>
          <input type="file" name="document_source" id="document_source" class="form-control"
                 accept=".pdf,.jpg,.jpeg,.png,.webp">
        </div>
      </div>

      <!-- Commentaire -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          <i class="fa-solid fa-comment" aria-hidden="true"></i> Commentaire interne
        </h3>
        <div class="form-group">
          <textarea name="commentaire_interne" id="commentaire_interne" class="form-control"
                    rows="4" placeholder="Notes internes, précisions sur la source…"></textarea>
        </div>
      </div>

      <!-- Actions -->
      <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
        <h3 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:1.25rem">
          Actions
        </h3>
        <div style="display:flex;flex-direction:column;gap:.75rem">
          <button type="submit" name="submit_type" value="brouillon" class="btn btn-ghost" style="justify-content:center">
            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
            Enregistrer brouillon
          </button>
          <button type="submit" name="submit_type" value="soumettre" class="btn btn-primary" style="justify-content:center">
            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            Soumettre pour validation
          </button>
          <a href="<?= url('admin/donnees') ?>" class="btn btn-ghost" style="justify-content:center">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            Annuler
          </a>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
function calcTotal() {
  const m = parseFloat(document.getElementById('masculin').value) || 0;
  const f = parseFloat(document.getElementById('feminin').value) || 0;
  const t = parseFloat(document.getElementById('trans_autre').value) || 0;
  if (m || f || t) {
    document.getElementById('total').value = (m + f + t).toFixed(2);
  }
  validateTotal();
}

function validateTotal() {
  const m = parseFloat(document.getElementById('masculin').value);
  const f = parseFloat(document.getElementById('feminin').value);
  const trans = parseFloat(document.getElementById('trans_autre').value) || 0;
  const total = parseFloat(document.getElementById('total').value);
  const alert = document.getElementById('validation-alert');
  const msg   = document.getElementById('validation-msg');
  if (!isNaN(m) && !isNaN(f) && !isNaN(total)) {
    const sum = m + f + trans;
    const diff = Math.abs(sum - total) / (total || 1);
    if (diff > 0.01) {
      alert.style.display = 'flex';
      msg.textContent = `Incohérence : ${m} + ${f} + ${trans} = ${sum.toFixed(2)} ≠ ${total} (tolérance 1%)`;
    } else {
      alert.style.display = 'none';
    }
  } else {
    alert.style.display = 'none';
  }
}

function onIndicateurChange(id) {
  if (!id) return;
  // Charger les niveaux de désagrégation disponibles pour cet indicateur
  fetch('/api/v1/indicateurs/' + encodeURIComponent(id))
    .then(r => r.json())
    .then(data => {
      // On pourrait pré-remplir des infos
    }).catch(() => {});
}

// Autosave
document.addEventListener('DOMContentLoaded', () => {
  initAutosave('saisie-form', '<?= url('admin/donnees/autosave') ?>');
});
</script>
