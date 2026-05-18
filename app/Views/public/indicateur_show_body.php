<?php
/** @var array $indicateur @var array $donnees @var array $niveaux @var array $annees @var string $slug */
$hexC = $indicateur['thematique_couleur'] ?? '#005B9A';
?>
<!-- Fil d'Ariane -->
<div style="background:var(--gray-50);border-bottom:1px solid var(--gray-200);padding:.75rem 0">
  <div class="container">
    <nav aria-label="Fil d'Ariane" style="font-size:.8rem;color:var(--gray-500)">
      <a href="<?= url() ?>">Accueil</a> ›
      <a href="<?= url('indicateurs') ?>">Indicateurs</a> ›
      <a href="<?= url('indicateurs?them='.esc($indicateur['thematique_slug'])) ?>"><?= esc($indicateur['thematique']) ?></a> ›
      <span aria-current="page" style="color:var(--gray-800)"><?= esc(mb_substr($indicateur['libelle_fr'],0,60)).(mb_strlen($indicateur['libelle_fr'])>60?'…':'') ?></span>
    </nav>
  </div>
</div>

<!-- Hero indicateur -->
<div style="background:linear-gradient(135deg,var(--pndm-blue-dark),var(--pndm-blue));color:#fff;padding:2.5rem 0">
  <div class="container">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1.5rem">
      <div style="flex:1;min-width:300px">
        <span class="hero-badge" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:#fff;margin-bottom:1rem">
          <i class="fa-solid fa-<?= esc($indicateur['thematique_icone'] ?? 'chart-line') ?>" aria-hidden="true"></i>
          <?= esc($indicateur['thematique']) ?>
        </span>
        <h1 style="font-size:clamp(1.5rem,3vw,2.25rem);font-weight:800;line-height:1.2;margin-bottom:.75rem">
          <?= esc($indicateur['libelle_fr']) ?>
        </h1>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;font-size:.85rem;opacity:.8">
          <span><i class="fa-solid fa-building" aria-hidden="true"></i> <?= esc($indicateur['source_acronyme']) ?></span>
          <?php if ($indicateur['frequence_libelle']): ?>
          <span><i class="fa-solid fa-clock" aria-hidden="true"></i> <?= esc($indicateur['frequence_libelle']) ?></span>
          <?php endif; ?>
          <?php if ($indicateur['unite_libelle']): ?>
          <span><i class="fa-solid fa-ruler" aria-hidden="true"></i> <?= esc($indicateur['unite_libelle']) ?></span>
          <?php endif; ?>
          <?php if ($annees): ?>
          <span><i class="fa-solid fa-calendar" aria-hidden="true"></i> <?= min($annees) ?>–<?= max($annees) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-start">
        <button class="btn btn-white btn-sm" onclick="exportData('<?= esc($slug) ?>','csv')"
                aria-label="Télécharger les données en CSV">
          <i class="fa-solid fa-download" aria-hidden="true"></i> CSV
        </button>
        <button class="btn btn-white btn-sm" onclick="exportData('<?= esc($slug) ?>','json')"
                aria-label="Télécharger les données en JSON">
          <i class="fa-solid fa-code" aria-hidden="true"></i> JSON
        </button>
        <!-- Partager -->
        <button class="btn btn-white btn-sm" id="shareBtn"
                onclick="navigator.share ? navigator.share({title:document.title,url:location.href}) : (navigator.clipboard.writeText(location.href).then(()=>showToast('Lien copié','success')))"
                aria-label="Partager cet indicateur">
          <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Partager
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ── ONGLETS ─────────────────────────────────────────────────── -->
<div class="container" style="padding-top:2rem;padding-bottom:4rem" data-tabs-container>
  <div class="tabs" role="tablist" aria-label="Sections de l'indicateur">
    <button class="tab-btn active" data-tab="graphique" role="tab" aria-selected="true" aria-controls="panel-graphique">
      <i class="fa-solid fa-chart-line" aria-hidden="true"></i> Graphique
    </button>
    <button class="tab-btn" data-tab="carte" role="tab" aria-selected="false" aria-controls="panel-carte">
      <i class="fa-solid fa-map" aria-hidden="true"></i> Carte
    </button>
    <button class="tab-btn" data-tab="desagregation" role="tab" aria-selected="false" aria-controls="panel-desagregation">
      <i class="fa-solid fa-chart-bar" aria-hidden="true"></i> Désagrégation
    </button>
    <button class="tab-btn" data-tab="donnees" role="tab" aria-selected="false" aria-controls="panel-donnees">
      <i class="fa-solid fa-table" aria-hidden="true"></i> Données
    </button>
    <button class="tab-btn" data-tab="metadonnees" role="tab" aria-selected="false" aria-controls="panel-metadonnees">
      <i class="fa-solid fa-circle-info" aria-hidden="true"></i> Métadonnées
    </button>
  </div>

  <!-- ── Onglet : Graphique ─────────────────────────────────── -->
  <div class="tab-panel active" id="panel-graphique" role="tabpanel" aria-labelledby="">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:0 0 12px 12px;border-top:none;padding:1.5rem">
      <!-- Contrôles graphique -->
      <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;margin-bottom:1.5rem">
        <div role="group" aria-label="Type de graphique">
          <button data-chart-type="line"  class="btn btn-ghost btn-sm active" title="Courbe"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></button>
          <button data-chart-type="bar"   class="btn btn-ghost btn-sm" title="Barres"><i class="fa-solid fa-chart-bar" aria-hidden="true"></i></button>
          <button data-chart-type="radar" class="btn btn-ghost btn-sm" title="Radar"><i class="fa-solid fa-chart-area" aria-hidden="true"></i></button>
        </div>

        <?php if (!empty($niveaux)): ?>
        <select id="niveau-select" class="form-control" style="max-width:220px;font-size:.85rem"
                aria-label="Niveau de désagrégation" onchange="refreshChart()">
          <option value="">— National / Total</option>
          <?php foreach ($niveaux as $n): ?>
          <option value="<?= (int)$n['id'] ?>"><?= esc($n['libelle']) ?></option>
          <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <?php if (count($annees) > 2): ?>
        <div style="display:flex;gap:.5rem;align-items:center">
          <label for="year-start" class="form-label" style="white-space:nowrap;margin:0">De</label>
          <select id="year-start" class="form-control" style="width:80px;font-size:.85rem" onchange="refreshChart()" aria-label="Année de début">
            <?php foreach ($annees as $y): ?><option value="<?= $y ?>"><?= $y ?></option><?php endforeach; ?>
          </select>
          <label for="year-end" class="form-label" style="white-space:nowrap;margin:0">à</label>
          <select id="year-end" class="form-control" style="width:80px;font-size:.85rem" onchange="refreshChart()" aria-label="Année de fin">
            <?php foreach (array_reverse($annees) as $y): ?><option value="<?= $y ?>"><?= $y ?></option><?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </div>

      <div class="chart-wrapper" style="height:400px">
        <canvas id="main-chart" role="img" aria-label="Graphique : <?= esc($indicateur['libelle_fr']) ?>"></canvas>
      </div>
    </div>
  </div>

  <!-- ── Onglet : Carte ─────────────────────────────────────── -->
  <div class="tab-panel" id="panel-carte" role="tabpanel">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:0 0 12px 12px;border-top:none;padding:1.5rem">
      <div class="map-container" id="niger-map" aria-label="Carte choroplèthe du Niger"></div>
    </div>
  </div>

  <!-- ── Onglet : Désagrégation ─────────────────────────────── -->
  <div class="tab-panel" id="panel-desagregation" role="tabpanel">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:0 0 12px 12px;border-top:none;padding:1.5rem">
      <?php
        // Séparer les données par niveau de désagrégation pour le graphique barres
        $donnees_desag = array_filter($donnees, fn($d) => $d['niveau_desag_valeur'] !== null);
      ?>
      <?php if (empty($donnees_desag)): ?>
      <div class="text-center" style="padding:3rem;color:var(--gray-500)">
        <i class="fa-solid fa-circle-info fa-2x" style="display:block;margin-bottom:1rem" aria-hidden="true"></i>
        Pas de données désagrégées disponibles pour cet indicateur.
      </div>
      <?php else: ?>
      <div class="chart-wrapper" style="height:400px">
        <canvas id="desag-chart" role="img" aria-label="Graphique désagrégation"></canvas>
      </div>
      <script>
      document.addEventListener('DOMContentLoaded', () => {
        const raw = <?= json_safe($donnees_desag) ?>;
        const groups = [...new Set(raw.map(d => d.niveau_desag_valeur))].sort();
        const years  = [...new Set(raw.map(d => d.annee))].sort();
        const lastYear = Math.max(...years);
        const filtered = raw.filter(d => d.annee == lastYear);
        const colors = ['#005B9A','#F4A11D','#1DA462','#E74C3C','#8E44AD','#0082C8','#27AE60','#D4870A','#16A085'];
        new Chart(document.getElementById('desag-chart'), {
          type: 'bar',
          data: {
            labels: filtered.map(d => d.niveau_desag_valeur),
            datasets: [{
              label: String(lastYear),
              data: filtered.map(d => parseFloat(d.total || d.masculin || 0)),
              backgroundColor: filtered.map((_, i) => colors[i % colors.length] + 'CC'),
              borderColor: filtered.map((_, i) => colors[i % colors.length]),
              borderWidth: 1.5, borderRadius: 6,
            }]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              title: { display: true, text: 'Répartition par ' + (filtered[0]?.niveau_libelle || 'niveau') + ' en ' + lastYear }
            },
            scales: {
              y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString('fr-FR') } }
            }
          }
        });
      });
      </script>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Onglet : Données ───────────────────────────────────── -->
  <div class="tab-panel" id="panel-donnees" role="tabpanel">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:0 0 12px 12px;border-top:none;padding:1.5rem">
      <div style="display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:1rem">
        <button class="btn btn-primary btn-sm" onclick="exportData('<?= esc($slug) ?>','csv')">
          <i class="fa-solid fa-download" aria-hidden="true"></i> CSV
        </button>
        <button class="btn btn-ghost btn-sm" onclick="exportData('<?= esc($slug) ?>','json')">
          <i class="fa-solid fa-code" aria-hidden="true"></i> JSON
        </button>
      </div>
      <div class="table-container">
        <table class="data-table" aria-label="Données de l'indicateur <?= esc($indicateur['libelle_fr']) ?>">
          <thead><tr>
            <th scope="col">Période</th>
            <th scope="col">Désagrégation</th>
            <th scope="col">Masculin</th>
            <th scope="col">Féminin</th>
            <th scope="col">Total</th>
          </tr></thead>
          <tbody>
            <?php if (empty($donnees)): ?>
            <tr><td colspan="5" class="text-center" style="padding:2rem;color:var(--gray-400)">Aucune donnée publiée.</td></tr>
            <?php else: foreach ($donnees as $d): ?>
            <tr>
              <td><time datetime="<?= esc($d['periode_debut']) ?>"><?= date('Y', strtotime($d['periode_debut'])) ?></time></td>
              <td><?= esc($d['niveau_desag_valeur'] ?? $d['geo_region'] ?? '—') ?></td>
              <td><?= format_number($d['masculin']) ?></td>
              <td><?= format_number($d['feminin']) ?></td>
              <td><strong><?= format_number($d['total']) ?></strong></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── Onglet : Métadonnées ───────────────────────────────── -->
  <div class="tab-panel" id="panel-metadonnees" role="tabpanel">
    <div style="background:#fff;border:1px solid var(--gray-200);border-radius:0 0 12px 12px;border-top:none;padding:2rem">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem">
        <?php
        $meta = [
          ['Libellé complet',       $indicateur['libelle_fr']],
          ['Définition',            $indicateur['definition_fr']],
          ['Méthode de calcul',     $indicateur['methode_calcul']],
          ['Données requises',      $indicateur['donnees_requises']],
          ['Source principale',     ($indicateur['source_libelle'] ?? '') . ' (' . ($indicateur['source_acronyme'] ?? '') . ')'],
          ['Thématique',            $indicateur['thematique']],
          ['Unité de mesure',       $indicateur['unite_libelle'] ?? '—'],
          ['Fréquence',             $indicateur['frequence_libelle'] ?? '—'],
          ['Prochaine mise à jour', $indicateur['prochaine_maj'] ? date_fr($indicateur['prochaine_maj']) : '—'],
          ['Contact',               ($indicateur['contact_nom'] ?? '') . ($indicateur['contact_email'] ? ' <' . $indicateur['contact_email'] . '>' : '')],
          ['Licence',               $indicateur['licence'] ?? 'Open Data Commons'],
        ];
        ?>
        <?php foreach ($meta as [$label, $value]): if (!$value) continue; ?>
        <div style="padding-bottom:1.25rem;border-bottom:1px solid var(--gray-100)">
          <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-400);margin-bottom:.35rem">
            <?= esc($label) ?>
          </div>
          <div style="font-size:.9rem;color:var(--gray-800);line-height:1.6"><?= esc($value) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Snippet iframe pour journalistes -->
      <div style="margin-top:2rem;padding:1rem;background:var(--gray-50);border-radius:8px">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin-bottom:.5rem">
          <i class="fa-solid fa-code" aria-hidden="true"></i> Intégrer ce graphique
        </div>
        <textarea class="form-control" rows="2" readonly style="font-family:var(--font-mono);font-size:.75rem"
                  aria-label="Code d'intégration iframe"
                  onclick="this.select()"><?= esc('<iframe src="' . url('indicateurs/'.esc($indicateur['slug'])) . '?embed=1" width="600" height="400" frameborder="0" allowfullscreen></iframe>') ?></textarea>
      </div>
    </div>
  </div>
</div>

<script>
async function refreshChart() {
  const niveauId  = document.getElementById('niveau-select')?.value;
  const yearStart = document.getElementById('year-start')?.value;
  const yearEnd   = document.getElementById('year-end')?.value;
  const params = new URLSearchParams();
  if (niveauId)  params.set('niveau_id',  niveauId);
  if (yearStart) params.set('year_start', yearStart);
  if (yearEnd)   params.set('year_end',   yearEnd);
  const canvas = document.getElementById('main-chart');
  const data = await (await fetch('/api/v1/indicateurs/<?= esc($slug) ?>/donnees?' + params)).json();
  renderMainChart(canvas, data);
}

// Init carte au clic sur onglet Carte
document.querySelector('[data-tab="carte"]')?.addEventListener('click', () => {
  setTimeout(() => initNigerMap('niger-map', '<?= esc($slug) ?>'), 100);
}, { once: true });
</script>
