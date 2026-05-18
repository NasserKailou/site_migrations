<!-- ═══════════════════════════════════════════════════════════════
     PNDM — Dossier Agadez
════════════════════════════════════════════════════════════════ -->

<!-- ── HERO ─────────────────────────────────────────────────── -->
<section style="position:relative;min-height:500px;display:flex;align-items:center;background:linear-gradient(135deg,#1a3a2a 0%,#0d2b3e 50%,var(--pndm-blue-dark) 100%);color:#fff;overflow:hidden"
         aria-labelledby="agadez-hero-title">
  <div style="position:absolute;inset:0;background-image:url('<?= url('assets/images/agadez-hero.jpg') ?>');background-size:cover;background-position:center;opacity:.25"></div>
  <div class="container" style="position:relative;z-index:1;padding:5rem 0">
    <nav aria-label="Fil d'Ariane" style="font-size:.8rem;opacity:.7;margin-bottom:1.5rem">
      <a href="<?= url() ?>" style="color:inherit">Accueil</a> › Dossiers › Agadez
    </nav>
    <span class="hero-badge" style="background:rgba(244,161,29,.2);border-color:var(--pndm-orange);color:var(--pndm-orange);margin-bottom:1.5rem">
      <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
      Dossier spécial — Région d'Agadez
    </span>
    <h1 id="agadez-hero-title" style="font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:1rem">
      Agadez,<br>carrefour migratoire
    </h1>
    <p style="max-width:600px;opacity:.85;font-size:1.1rem;line-height:1.7;margin-bottom:2rem">
      Données, analyses et visualisations sur les flux migratoires de la région d'Agadez
      et du corridor Tamanrasset–Assamaka–Agadez.
    </p>
    <div style="display:flex;gap:1rem;flex-wrap:wrap">
      <a href="#tableau-bord" class="btn btn-accent btn-lg">
        <i class="fa-solid fa-chart-line" aria-hidden="true"></i> Tableau de bord
      </a>
      <a href="#donnees" class="btn btn-white btn-lg">
        <i class="fa-solid fa-database" aria-hidden="true"></i> Données
      </a>
    </div>
  </div>
</section>

<!-- ── LAYOUT AVEC SOMMAIRE ──────────────────────────────────── -->
<div class="container" style="display:grid;grid-template-columns:240px 1fr;gap:3rem;padding-top:3rem;padding-bottom:5rem;align-items:start">

  <!-- Sommaire sticky -->
  <aside aria-label="Sommaire" style="position:sticky;top:calc(var(--header-h) + 1rem);background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem">
    <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-400);margin-bottom:1rem">
      Sommaire
    </div>
    <nav aria-label="Sections de la page Agadez">
      <ul style="list-style:none;display:flex;flex-direction:column;gap:.25rem">
        <?php
        $sections = [
          ['contexte',   'fa-globe',           'Contexte'],
          ['operations', 'fa-calendar-days',   'Genèse des opérations'],
          ['acteurs',    'fa-users',            'Acteurs'],
          ['corridor',   'fa-road',             'Corridor migratoire'],
          ['tableau-bord','fa-chart-mixed',     'Tableau de bord'],
          ['indicateurs','fa-chart-line',       'Indicateurs Agadez'],
          ['documents',  'fa-file-pdf',         'Documents'],
        ];
        ?>
        <?php foreach ($sections as [$id, $icon, $label]): ?>
        <li>
          <a href="#<?= esc($id) ?>"
             style="display:flex;align-items:center;gap:.5rem;padding:.4rem .5rem;border-radius:6px;font-size:.85rem;color:var(--gray-700);text-decoration:none;transition:background .2s,color .2s"
             onmouseover="this.style.background='var(--pndm-blue-light)';this.style.color='var(--pndm-blue)'"
             onmouseout="this.style.background='';this.style.color='var(--gray-700)'">
            <i class="fa-solid fa-<?= esc($icon) ?> fa-xs" style="width:14px;color:var(--pndm-orange)" aria-hidden="true"></i>
            <?= esc($label) ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </aside>

  <!-- Contenu principal -->
  <main aria-label="Contenu Agadez">

    <!-- ── Contexte ───────────────────────────────────────── -->
    <section id="contexte" aria-labelledby="contexte-title" style="margin-bottom:4rem">
      <h2 id="contexte-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:1rem">
        Contexte régional
      </h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;align-items:start">
        <div>
          <p style="line-height:1.8;color:var(--gray-700);margin-bottom:1rem">
            La région d'<strong>Agadez</strong>, située au nord du Niger, représente un carrefour
            migratoire majeur en Afrique de l'Ouest. Elle constitue un point de transit central
            sur la route migratoire <em>Afrique subsaharienne — Afrique du Nord — Méditerranée</em>.
          </p>
          <p style="line-height:1.8;color:var(--gray-700);margin-bottom:1rem">
            Le corridor <strong>Tamanrasset (Algérie) → Assamaka → Agadez</strong> concentre
            des flux importants de migrations de transit vers la Libye, l'Algérie et, au-delà,
            vers l'Europe.
          </p>
          <p style="line-height:1.8;color:var(--gray-700)">
            Depuis 2015, le Programme de gestion des migrations mixtes (POMM) coordonne
            la réponse institutionnelle avec l'appui de l'OIM, du UNHCR et d'autres partenaires.
          </p>
        </div>
        <div>
          <img src="<?= url('assets/images/map-corridor-agadez.jpg') ?>"
               alt="Carte du corridor migratoire Tamanrasset-Assamaka-Agadez"
               style="border-radius:12px;box-shadow:var(--shadow-md);width:100%">
          <p style="font-size:.7rem;color:var(--gray-400);text-align:center;margin-top:.5rem">
            Source : IOM / OIM
          </p>
        </div>
      </div>
    </section>

    <!-- ── Timeline Opérations ──────────────────────────── -->
    <section id="operations" aria-labelledby="operations-title" style="margin-bottom:4rem">
      <h2 id="operations-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:2rem">
        Genèse des opérations de refoulement
      </h2>
      <div class="timeline">
        <?php
        $timeline = [
          ['2014-12-08', 'Lancement du Programme POMM', 'Démarrage du Programme de gestion des migrations mixtes (POMM) avec l\'appui de l\'OIM et le financement de l\'Italie.'],
          ['2015-05-01', 'Mise en place du dispositif de collecte', 'Installation des points de collecte de données à Agadez, Assamaka et le long du corridor.'],
          ['2016-01-15', 'Phase II du programme', 'Extension du programme à de nouvelles zones géographiques et renforcement du partenariat institutionnel.'],
          ['2017-03-10', 'Pic des flux observés', 'Enregistrement de pics importants de flux migratoires sur le corridor Tamanrasset-Agadez.'],
          ['2019-09-01', 'Plateforme PNDM v1', 'Mise en ligne de la première version de la Plateforme Nationale des Données sur la Migration.'],
          ['2022-07-01', 'Enrichissement des données', 'Intégration de nouveaux indicateurs et mise à jour des séries historiques.'],
          ['2026-05-18', 'Refonte PNDM v2', 'Lancement de la nouvelle version de la plateforme avec visualisations avancées et API ouverte.'],
        ];
        ?>
        <?php foreach ($timeline as [$date, $titre, $desc]): ?>
        <div class="timeline-item">
          <div class="timeline-dot" aria-hidden="true"></div>
          <div class="timeline-date"><?= date_fr($date) ?></div>
          <div class="timeline-content">
            <h4><?= esc($titre) ?></h4>
            <p><?= esc($desc) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ── Acteurs ───────────────────────────────────────── -->
    <section id="acteurs" aria-labelledby="acteurs-title" style="margin-bottom:4rem">
      <h2 id="acteurs-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:1.5rem">
        Acteurs institutionnels
      </h2>
      <?php
      $acteurs = [
        ['CAB/PM',      'Cabinet du Premier Ministre', 'Coordination nationale du POMM',      '#005B9A'],
        ['OIM',         'Organisation Internationale pour les Migrations', 'Appui technique et opérationnel', '#0082C8'],
        ['UNHCR',       'Haut Commissariat aux Réfugiés', 'Protection des réfugiés et demandeurs d\'asile', '#00A896'],
        ['UNICEF',      'Fonds des Nations Unies pour l\'Enfance', 'Protection des enfants migrants', '#1DA462'],
        ['INS',         'Institut National de la Statistique', 'Production et diffusion des données', '#F4A11D'],
        ['DREC/MR',     'Direction Régionale de l\'État Civil', 'Enregistrement des flux', '#8E44AD'],
        ['DR/INS',      'Direction Régionale INS Agadez', 'Collecte de données locales', '#E74C3C'],
        ['Gouvernorat', 'Gouvernorat d\'Agadez', 'Coordination locale et sécurité', '#D4870A'],
      ];
      ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
        <?php foreach ($acteurs as [$acro, $nom, $role, $color]): ?>
        <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.25rem;border-left:4px solid <?= esc($color) ?>">
          <div style="font-size:1rem;font-weight:800;color:<?= esc($color) ?>;margin-bottom:.25rem"><?= esc($acro) ?></div>
          <div style="font-size:.75rem;font-weight:600;color:var(--gray-700);margin-bottom:.5rem;line-height:1.3"><?= esc($nom) ?></div>
          <div style="font-size:.75rem;color:var(--gray-500);line-height:1.4"><?= esc($role) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- ── Corridor Carte ────────────────────────────────── -->
    <section id="corridor" aria-labelledby="corridor-title" style="margin-bottom:4rem">
      <h2 id="corridor-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:1rem">
        Corridor Tamanrasset → Assamaka → Agadez
      </h2>
      <p style="color:var(--gray-600);margin-bottom:1.5rem">
        Carte interactive du corridor migratoire principal avec les points de passage et de collecte.
      </p>
      <div class="map-container" id="corridor-map" style="height:400px" aria-label="Carte du corridor migratoire"></div>
    </section>

    <!-- ── Tableau de bord Power BI ─────────────────────── -->
    <section id="tableau-bord" aria-labelledby="powerbi-title" style="margin-bottom:4rem">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
        <h2 id="powerbi-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin:0">
          Tableau de bord interactif
        </h2>
        <?php if (!empty($dossier['powerbi_url'])): ?>
        <a href="<?= esc($dossier['powerbi_url']) ?>" target="_blank" rel="noopener"
           class="btn btn-primary btn-sm" aria-label="Ouvrir le tableau de bord en plein écran">
          <i class="fa-solid fa-expand" aria-hidden="true"></i> Plein écran
        </a>
        <?php endif; ?>
      </div>

      <?php if (!empty($dossier['powerbi_url'])): ?>
      <div class="powerbi-wrapper">
        <iframe
          src="<?= esc($dossier['powerbi_url']) ?>"
          title="<?= esc($dossier['powerbi_titre'] ?? 'Tableau de bord Agadez') ?>"
          allowfullscreen
          loading="lazy"
          aria-label="Tableau de bord Power BI — Agadez">
        </iframe>
      </div>
      <?php else: ?>
      <div style="background:var(--gray-50);border:2px dashed var(--gray-300);border-radius:12px;padding:3rem;text-align:center;color:var(--gray-500)">
        <i class="fa-solid fa-chart-mixed fa-3x" style="margin-bottom:1rem;opacity:.4" aria-hidden="true"></i>
        <p>Le tableau de bord Power BI sera disponible prochainement.</p>
        <p style="font-size:.8rem;margin-top:.5rem">Configuration à effectuer depuis l'espace d'administration.</p>
      </div>
      <?php endif; ?>
    </section>

    <!-- ── Indicateurs Agadez ────────────────────────────── -->
    <section id="indicateurs" aria-labelledby="ind-agadez-title" style="margin-bottom:4rem">
      <h2 id="ind-agadez-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:1.5rem">
        Indicateurs de la région d'Agadez
      </h2>

      <?php if (empty($donnees_agadez)): ?>
      <div class="alert alert-info">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Aucune donnée régionale disponible pour Agadez. Les données seront publiées après validation.
      </div>
      <?php else: ?>
      <!-- Graphiques des 4 principaux indicateurs Agadez -->
      <?php
        $inds_agadez = [];
        foreach ($donnees_agadez as $d) {
          $k = $d['ind_slug'];
          if (!isset($inds_agadez[$k])) {
            $inds_agadez[$k] = ['libelle' => $d['indicateur'], 'slug' => $k, 'couleur' => $d['them_couleur'], 'data' => []];
          }
          $inds_agadez[$k]['data'][] = $d;
        }
        $inds_agadez = array_slice(array_values($inds_agadez), 0, 4);
      ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
        <?php foreach ($inds_agadez as $i => $ind_ag): ?>
        <div style="background:#fff;border:1px solid var(--gray-200);border-radius:12px;padding:1.5rem;border-top:3px solid <?= esc($ind_ag['couleur'] ?? '#005B9A') ?>">
          <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;line-height:1.4">
            <a href="<?= url('indicateurs/'.esc($ind_ag['slug'])) ?>" style="color:var(--gray-800)">
              <?= esc($ind_ag['libelle']) ?>
            </a>
          </h3>
          <div class="chart-wrapper" style="height:180px">
            <canvas id="agadez-chart-<?= $i ?>" aria-label="Graphique <?= esc($ind_ag['libelle']) ?>"></canvas>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <script>
      const agadezData = <?= json_safe($inds_agadez) ?>;
      document.addEventListener('DOMContentLoaded', () => {
        agadezData.forEach((ind, i) => {
          const canvas = document.getElementById('agadez-chart-' + i);
          if (!canvas || !ind.data.length) return;
          const sorted = ind.data.sort((a, b) => a.periode_debut.localeCompare(b.periode_debut));
          new Chart(canvas, {
            type: 'bar',
            data: {
              labels: sorted.map(d => new Date(d.periode_debut).getFullYear()),
              datasets: [{
                data: sorted.map(d => parseFloat(d.total || 0)),
                backgroundColor: ind.couleur + '99',
                borderColor: ind.couleur,
                borderWidth: 1.5, borderRadius: 4,
              }]
            },
            options: {
              responsive: true, maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: {
                y: { ticks: { font: { size: 10 }, callback: v => v.toLocaleString('fr-FR') } },
                x: { ticks: { font: { size: 10 } } }
              }
            }
          });
        });
      });
      </script>
      <?php endif; ?>
    </section>

    <!-- ── Documents ─────────────────────────────────────── -->
    <?php if (!empty($documents)): ?>
    <section id="documents" aria-labelledby="docs-title" style="margin-bottom:3rem">
      <h2 id="docs-title" style="font-size:1.75rem;font-weight:800;color:var(--pndm-blue);margin-bottom:1.5rem">
        Documents officiels
      </h2>
      <div style="display:flex;flex-direction:column;gap:.75rem">
        <?php foreach ($documents as $doc): ?>
        <a href="<?= url('storage/uploads/'.esc($doc['fichier'])) ?>"
           download
           class="card"
           style="padding:1rem 1.5rem;display:flex;align-items:center;gap:1rem;text-decoration:none;color:inherit"
           aria-label="Télécharger <?= esc($doc['titre']) ?>">
          <i class="fa-solid fa-file-pdf fa-2x" style="color:#E74C3C;flex-shrink:0" aria-hidden="true"></i>
          <div>
            <div style="font-weight:600"><?= esc($doc['titre']) ?></div>
            <?php if ($doc['taille']): ?>
            <div style="font-size:.75rem;color:var(--gray-400)"><?= number_format($doc['taille']/1024, 0, ',', ' ') ?> Ko</div>
            <?php endif; ?>
          </div>
          <i class="fa-solid fa-download" style="margin-left:auto;color:var(--gray-400)" aria-hidden="true"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </main>
</div>

<!-- Init carte corridor -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const map = L.map('corridor-map').setView([22, 5], 5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);

  // Points du corridor
  const points = [
    { name: 'Tamanrasset (Algérie)', lat: 22.785, lng: 5.523, type: 'transit' },
    { name: 'Assamaka (poste frontière)', lat: 20.717, lng: 7.450, type: 'frontiere' },
    { name: 'Agadez', lat: 16.974, lng: 7.989, type: 'hub' },
    { name: 'Dirkou',   lat: 18.983, lng: 12.891, type: 'transit' },
    { name: 'Niamey',   lat: 13.513, lng: 2.110, type: 'capitale' },
  ];

  const icons = {
    hub:      L.divIcon({ html: '<div style="background:#F4A11D;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>', iconSize: [16,16] }),
    frontiere:L.divIcon({ html: '<div style="background:#E74C3C;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3)"></div>', iconSize: [14,14] }),
    transit:  L.divIcon({ html: '<div style="background:#005B9A;width:12px;height:12px;border-radius:50%;border:2px solid #fff;box-shadow:0 2px 4px rgba(0,0,0,.3)"></div>', iconSize: [12,12] }),
    capitale: L.divIcon({ html: '<div style="background:#1DA462;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.3)"></div>', iconSize: [14,14] }),
  };

  points.forEach(p => {
    L.marker([p.lat, p.lng], { icon: icons[p.type] || icons.transit })
      .addTo(map)
      .bindTooltip('<strong>' + p.name + '</strong>', { permanent: false });
  });

  // Ligne du corridor
  const corridor = [[22.785, 5.523], [20.717, 7.450], [16.974, 7.989]];
  L.polyline(corridor, { color: '#F4A11D', weight: 3, dashArray: '8 6', opacity: 0.9 })
    .addTo(map)
    .bindTooltip('Corridor Tamanrasset → Assamaka → Agadez');
});
</script>
