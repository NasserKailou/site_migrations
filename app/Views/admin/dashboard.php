<?php $pageTitle = 'Tableau de bord'; ?>

<!-- ── KPI CARDS ─────────────────────────────────────────────── -->
<div class="kpi-grid">
  <div class="kpi-card" style="--kpi-color:var(--pndm-blue)">
    <div class="kpi-icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></div>
    <div class="kpi-value"><?= (int)$kpis['indicateurs_actifs'] ?></div>
    <div class="kpi-label">Indicateurs publiés</div>
  </div>
  <div class="kpi-card" style="--kpi-color:var(--pndm-green)">
    <div class="kpi-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
    <div class="kpi-value"><?= (int)$kpis['publie'] ?></div>
    <div class="kpi-label">Observations publiées</div>
  </div>
  <div class="kpi-card" style="--kpi-color:var(--pndm-orange)">
    <div class="kpi-icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></div>
    <div class="kpi-value"><?= (int)$kpis['soumis'] ?></div>
    <div class="kpi-label">En attente validation</div>
  </div>
  <div class="kpi-card" style="--kpi-color:var(--gray-600)">
    <div class="kpi-icon"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></div>
    <div class="kpi-value"><?= (int)$kpis['brouillon'] ?></div>
    <div class="kpi-label">Brouillons</div>
  </div>
  <div class="kpi-card" style="--kpi-color:var(--color-danger)">
    <div class="kpi-icon"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></div>
    <div class="kpi-value"><?= (int)$kpis['rejete'] ?></div>
    <div class="kpi-label">Rejetées</div>
  </div>
</div>

<!-- ── GRILLE PRINCIPALE ──────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

  <!-- Graphique activité -->
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.5rem;color:var(--gray-800)">
      <i class="fa-solid fa-chart-area" aria-hidden="true" style="color:var(--pndm-blue)"></i>
      Observations saisies — 12 derniers mois
    </h3>
    <div style="height:250px">
      <canvas id="activity-chart" aria-label="Graphique d'activité mensuelle"></canvas>
    </div>
  </div>

  <!-- Complétude par thématique -->
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:1.5rem;color:var(--gray-800)">
      <i class="fa-solid fa-circle-half-stroke" aria-hidden="true" style="color:var(--pndm-orange)"></i>
      Complétude par thématique
    </h3>
    <div style="display:flex;flex-direction:column;gap:.75rem">
      <?php foreach ($completude as $c):
        $pct = $c['total_ind'] > 0 ? round($c['ind_avec_donnees'] / $c['total_ind'] * 100) : 0;
      ?>
      <div>
        <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.3rem">
          <span style="font-weight:600;color:var(--gray-700)"><?= esc($c['libelle_fr']) ?></span>
          <span style="color:var(--gray-400)"><?= $pct ?>%</span>
        </div>
        <div style="height:6px;background:var(--gray-100);border-radius:999px;overflow:hidden">
          <div style="height:100%;width:<?= $pct ?>%;background:<?= esc($c['couleur'] ?? '#005B9A') ?>;border-radius:999px;transition:width .6s ease"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── LIGNE 2 ────────────────────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">

  <!-- À valider -->
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
      <h3 style="font-size:1rem;font-weight:700;color:var(--gray-800);margin:0">
        <i class="fa-solid fa-clipboard-check" style="color:var(--pndm-orange)" aria-hidden="true"></i>
        À valider
      </h3>
      <a href="<?= url('admin/donnees?statut=soumis') ?>" class="btn btn-ghost btn-sm">Tout voir</a>
    </div>
    <?php if (empty($a_valider)): ?>
    <p style="color:var(--gray-400);font-size:.875rem;text-align:center;padding:1.5rem 0">
      <i class="fa-solid fa-check-circle" style="color:var(--pndm-green)" aria-hidden="true"></i><br>
      Aucune observation en attente.
    </p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.75rem">
      <?php foreach ($a_valider as $obs): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem;background:var(--gray-50);border-radius:8px;gap:.5rem">
        <div style="min-width:0">
          <div style="font-size:.85rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= esc(mb_substr($obs['indicateur'], 0, 45)) ?>
          </div>
          <div style="font-size:.75rem;color:var(--gray-400)">
            <?= esc($obs['prenom']) ?> <?= esc($obs['nom']) ?> · <?= ago($obs['created_at']) ?>
          </div>
        </div>
        <div style="display:flex;gap:.35rem;flex-shrink:0">
          <form method="POST" action="<?= url('admin/donnees/'.(int)$obs['id'].'/validate') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-success btn-sm" title="Valider" aria-label="Valider l'observation">
              <i class="fa-solid fa-check" aria-hidden="true"></i>
            </button>
          </form>
          <form method="POST" action="<?= url('admin/donnees/'.(int)$obs['id'].'/reject') ?>">
            <?= csrf_field() ?>
            <button class="btn btn-danger btn-sm" title="Rejeter" aria-label="Rejeter l'observation">
              <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Indicateurs en retard -->
  <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
    <h3 style="font-size:1rem;font-weight:700;color:var(--gray-800);margin-bottom:1rem">
      <i class="fa-solid fa-triangle-exclamation" style="color:var(--color-danger)" aria-hidden="true"></i>
      Indicateurs en retard de MAJ
    </h3>
    <?php if (empty($en_retard)): ?>
    <p style="color:var(--gray-400);font-size:.875rem;text-align:center;padding:1.5rem 0">
      <i class="fa-solid fa-calendar-check" style="color:var(--pndm-green)" aria-hidden="true"></i><br>
      Tous les indicateurs sont à jour.
    </p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.5rem">
      <?php foreach ($en_retard as $ind): ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem;background:var(--gray-50);border-radius:8px">
        <a href="<?= url('admin/indicateurs/'.(int)$ind['id']) ?>"
           style="font-size:.8rem;font-weight:600;color:var(--pndm-blue);text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          <?= esc(mb_substr($ind['libelle_fr'], 0, 50)) ?>
        </a>
        <span class="badge badge-danger" style="flex-shrink:0"><?= date_fr($ind['prochaine_maj']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── JOURNAL D'ACTIVITÉ ─────────────────────────────────────── -->
<div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
  <h3 style="font-size:1rem;font-weight:700;color:var(--gray-800);margin-bottom:1rem">
    <i class="fa-solid fa-history" style="color:var(--pndm-blue)" aria-hidden="true"></i>
    Activité récente
  </h3>
  <div class="table-container">
    <table class="data-table" aria-label="Journal d'activité">
      <thead><tr>
        <th scope="col">Action</th>
        <th scope="col">Utilisateur</th>
        <th scope="col">Date</th>
      </tr></thead>
      <tbody>
        <?php if (empty($activite)): ?>
        <tr><td colspan="3" class="text-center" style="padding:1.5rem;color:var(--gray-400)">Aucune activité enregistrée.</td></tr>
        <?php else: foreach ($activite as $log): ?>
        <tr>
          <td>
            <?php
            $actionLabels = [
              'login'               => ['fa-right-to-bracket','text-success','Connexion'],
              'logout'              => ['fa-right-from-bracket','text-muted','Déconnexion'],
              'create_observation'  => ['fa-plus','text-blue','Saisie observation'],
              'update_observation'  => ['fa-pen','text-blue','Modification'],
              'submit_observation'  => ['fa-paper-plane','text-orange','Soumission'],
              'validate_observation'=> ['fa-check','text-success','Validation'],
              'publish_observation' => ['fa-globe','text-success','Publication'],
              'reject_observation'  => ['fa-xmark','text-danger','Rejet'],
              'login_failed'        => ['fa-triangle-exclamation','text-danger','Tentative échouée'],
            ];
            $al = $actionLabels[$log['action']] ?? ['fa-circle','text-muted', $log['action']];
            ?>
            <span class="<?= $al[1] ?>">
              <i class="fa-solid fa-<?= $al[0] ?> fa-xs" aria-hidden="true"></i>
              <?= esc($al[2]) ?>
            </span>
          </td>
          <td style="font-size:.85rem"><?= esc(($log['prenom'] ?? '') . ' ' . ($log['nom'] ?? 'Système')) ?></td>
          <td style="font-size:.8rem;color:var(--gray-400)">
            <time datetime="<?= esc($log['created_at']) ?>" title="<?= date_fr($log['created_at']) ?>">
              <?= ago($log['created_at']) ?>
            </time>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Liens rapides -->
<div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:1.5rem">
  <a href="<?= url('admin/donnees/saisie') ?>" class="btn btn-primary">
    <i class="fa-solid fa-plus" aria-hidden="true"></i> Nouvelle saisie
  </a>
  <a href="<?= url('admin/import') ?>" class="btn btn-ghost">
    <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i> Import Excel
  </a>
  <a href="<?= url('indicateurs') ?>" class="btn btn-ghost" target="_blank" rel="noopener">
    <i class="fa-solid fa-eye" aria-hidden="true"></i> Voir le site public
  </a>
</div>

<!-- Graphique JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const months = <?= json_safe(array_column($par_mois, 'mois')) ?>;
  const counts = <?= json_safe(array_column($par_mois, 'n')) ?>;
  new Chart(document.getElementById('activity-chart'), {
    type: 'bar',
    data: {
      labels: months.map(m => {
        const [y, mo] = m.split('-');
        return new Date(y, mo-1).toLocaleString('fr-FR', { month:'short', year:'2-digit' });
      }),
      datasets: [{
        label: 'Observations saisies',
        data: counts.map(Number),
        backgroundColor: '#005B9A33',
        borderColor: '#005B9A',
        borderWidth: 1.5,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });
});
</script>
