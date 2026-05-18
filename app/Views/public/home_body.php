<!-- ═══════════════════════════════════════════════════════════════
     PNDM — Page d'accueil
════════════════════════════════════════════════════════════════ -->

<!-- ── CAROUSEL HERO ─────────────────────────────────────────── -->
<section class="home-carousel" aria-label="Diaporama PNDM" style="position:relative;overflow:hidden">
  <div class="carousel-track" id="mainCarousel">

    <!-- Slide 1 -->
    <div class="carousel-slide active" aria-roledescription="slide" aria-label="Slide 1 sur 4">
      <div class="carousel-bg" style="background-image:url('<?= url('assets/images/carousel/mig1.PNG') ?>')"></div>
      <div class="carousel-overlay"></div>
      <div class="container carousel-content animate-fade">
        <span class="hero-badge">
          <i class="fa-solid fa-globe-africa" aria-hidden="true"></i>
          République du Niger — INS
        </span>
        <h1>Les données sur<br><span>la migration</span><br>au Niger</h1>
        <p>
          Plateforme officielle de l'Institut National de la Statistique pour la
          consultation, la visualisation et le téléchargement des données sur
          les migrations internationales et internes au Niger.
        </p>
        <div class="hero-actions">
          <a href="<?= url('indicateurs') ?>" class="btn btn-accent btn-lg">
            <i class="fa-solid fa-chart-line" aria-hidden="true"></i>
            Explorer les données
          </a>
          <a href="<?= url('dossiers/agadez') ?>" class="btn btn-white btn-lg">
            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i>
            Dossier Agadez
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 2 -->
    <div class="carousel-slide" aria-roledescription="slide" aria-label="Slide 2 sur 4">
      <div class="carousel-bg" style="background-image:url('<?= url('assets/images/carousel/mig2.PNG') ?>')"></div>
      <div class="carousel-overlay"></div>
      <div class="container carousel-content">
        <span class="hero-badge">
          <i class="fa-solid fa-users" aria-hidden="true"></i>
          Migrations internes
        </span>
        <h1>Flux migratoires<br><span>interrégionaux</span></h1>
        <p>
          Analyse des déplacements de population entre les régions du Niger —
          tendances, causes et impact socio-économique.
        </p>
        <div class="hero-actions">
          <a href="<?= url('indicateurs?them=flux') ?>" class="btn btn-accent btn-lg">
            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i>
            Flux migratoires
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 3 -->
    <div class="carousel-slide" aria-roledescription="slide" aria-label="Slide 3 sur 4">
      <div class="carousel-bg" style="background-image:url('<?= url('assets/images/carousel/mig3.PNG') ?>')"></div>
      <div class="carousel-overlay"></div>
      <div class="container carousel-content">
        <span class="hero-badge">
          <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
          Dossier spécial
        </span>
        <h1>Agadez,<br><span>carrefour migratoire</span></h1>
        <p>
          Données et analyses complètes sur les flux migratoires de la région d'Agadez
          et du corridor Tamanrasset–Assamaka–Agadez.
        </p>
        <div class="hero-actions">
          <a href="<?= url('dossiers/agadez') ?>" class="btn btn-accent btn-lg">
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            Accéder au dossier
          </a>
        </div>
      </div>
    </div>

    <!-- Slide 4 -->
    <div class="carousel-slide" aria-roledescription="slide" aria-label="Slide 4 sur 4">
      <div class="carousel-bg" style="background-image:url('<?= url('assets/images/carousel/Migration.jpeg') ?>')"></div>
      <div class="carousel-overlay"></div>
      <div class="container carousel-content">
        <span class="hero-badge">
          <i class="fa-solid fa-file-chart-column" aria-hidden="true"></i>
          Données officielles
        </span>
        <h1>Statistiques<br><span>nationales</span><br>de migration</h1>
        <p>
          Accédez aux séries historiques, métadonnées et outils de téléchargement
          pour toutes les données migratoires officielles du Niger.
        </p>
        <div class="hero-actions">
          <a href="<?= url('indicateurs') ?>#metadonnees" class="btn btn-accent btn-lg">
            <i class="fa-solid fa-database" aria-hidden="true"></i>
            Métadonnées
          </a>
          <a href="<?= url('indicateurs') ?>#extraction" class="btn btn-white btn-lg">
            <i class="fa-solid fa-download" aria-hidden="true"></i>
            Télécharger
          </a>
        </div>
      </div>
    </div>

  </div><!-- /.carousel-track -->

  <!-- Contrôles navigation -->
  <button class="carousel-btn carousel-prev" onclick="carouselMove(-1)" aria-label="Slide précédent">
    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
  </button>
  <button class="carousel-btn carousel-next" onclick="carouselMove(1)" aria-label="Slide suivant">
    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
  </button>

  <!-- Points indicateurs -->
  <div class="carousel-dots" role="tablist" aria-label="Indicateurs de slide">
    <button class="carousel-dot active" onclick="carouselGoto(0)" role="tab" aria-selected="true"  aria-label="Slide 1"></button>
    <button class="carousel-dot"        onclick="carouselGoto(1)" role="tab" aria-selected="false" aria-label="Slide 2"></button>
    <button class="carousel-dot"        onclick="carouselGoto(2)" role="tab" aria-selected="false" aria-label="Slide 3"></button>
    <button class="carousel-dot"        onclick="carouselGoto(3)" role="tab" aria-selected="false" aria-label="Slide 4"></button>
  </div>
</section>

<!-- ── BANNIÈRE PRINCIPALE ────────────────────────────────────── -->
<section class="home-banner" aria-label="Bannière Institut National de la Statistique">
  <img src="<?= url('assets/images/entete/bannier.jpg') ?>"
       alt="Bannière PNDM — Institut National de la Statistique du Niger"
       class="home-banner-img">
</section>

<!-- ── COMPTEURS ─────────────────────────────────────────────── -->
<section class="counters-strip" aria-label="Chiffres clés">
  <div class="container">
    <div class="counters-grid">
      <div class="counter-item">
        <span class="counter-value" data-count="<?= (int)$stats['indicateurs'] ?>">
          <?= (int)$stats['indicateurs'] ?>
        </span>
        <span class="counter-label">Indicateurs publiés</span>
      </div>
      <div class="counter-item">
        <span class="counter-value" data-count="<?= (int)$stats['regions'] ?>">
          <?= (int)$stats['regions'] ?>
        </span>
        <span class="counter-label">Régions couvertes</span>
      </div>
      <div class="counter-item">
        <span class="counter-value" data-count="<?= (int)$stats['observations'] ?>">
          <?= number_format((int)$stats['observations']) ?>
        </span>
        <span class="counter-label">Observations</span>
      </div>
      <div class="counter-item">
        <span class="counter-value">
          <?= $stats['derniere_maj'] ? date('m/Y', strtotime($stats['derniere_maj'])) : '—' ?>
        </span>
        <span class="counter-label">Dernière mise à jour</span>
      </div>
      <div class="counter-item">
        <span class="counter-value" data-count="<?= (int)$stats['telechargements'] ?>">
          <?= number_format((int)$stats['telechargements']) ?>
        </span>
        <span class="counter-label">Téléchargements</span>
      </div>
    </div>
  </div>
</section>

<!-- ── INDICATEURS PHARES ─────────────────────────────────────── -->
<section class="section" aria-labelledby="indicateurs-phares-title">
  <div class="container">
    <div class="section-header">
      <h2 id="indicateurs-phares-title">Indicateurs phares</h2>
      <p>Les données les plus consultées sur la migration au Niger</p>
    </div>
    <div class="cards-grid">
      <?php foreach ($indicateurs_phares as $ind): ?>
      <?php
        $hexColor = $ind['thematique_couleur'] ?? '#f7a13e';
        $r = hexdec(substr($hexColor,1,2));
        $g = hexdec(substr($hexColor,3,2));
        $b = hexdec(substr($hexColor,5,2));
        $lightBg = "rgba({$r},{$g},{$b},.12)";
      ?>
      <article class="card card-indicator" style="--card-color:<?= esc($hexColor) ?>"
               aria-labelledby="ind-<?= (int)$ind['id'] ?>">
        <div class="card-indicator-header">
          <span class="card-indicator-badge" style="background:<?= $lightBg ?>;color:<?= esc($hexColor) ?>">
            <i class="fa-solid fa-<?= esc($ind['thematique_icone'] ?? 'chart-line') ?>" aria-hidden="true"></i>
            <?= esc($ind['thematique']) ?>
          </span>
        </div>
        <h3 id="ind-<?= (int)$ind['id'] ?>">
          <a href="<?= url('indicateurs/' . esc($ind['slug'])) ?>" class="stretched-link"
             style="color:inherit;text-decoration:none">
            <?= esc($ind['libelle_fr']) ?>
          </a>
        </h3>
        <?php if ($ind['derniere_valeur'] !== null): ?>
        <div class="value">
          <?= format_number($ind['derniere_valeur']) ?>
          <span><?= esc($ind['unite_symbole'] ?? '') ?> <?= $ind['derniere_annee'] ? '('.$ind['derniere_annee'].')' : '' ?></span>
        </div>
        <?php endif; ?>
        <div class="sparkline-container" aria-hidden="true">
          <canvas id="spark-<?= (int)$ind['id'] ?>" height="50" role="img"
                  aria-label="Évolution <?= esc($ind['libelle_fr']) ?>"></canvas>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2.5rem">
      <a href="<?= url('indicateurs') ?>" class="btn btn-primary">
        <i class="fa-solid fa-table-list" aria-hidden="true"></i>
        Voir tous les indicateurs
      </a>
    </div>
  </div>
</section>

<!-- ── THÉMATIQUES ────────────────────────────────────────────── -->
<section class="section section-alt" aria-labelledby="thematiques-title">
  <div class="container">
    <div class="section-header">
      <h2 id="thematiques-title">Thématiques</h2>
      <p>Explorez les données par grande famille thématique</p>
    </div>
    <div class="cards-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
      <?php foreach ($thematiques as $them): ?>
      <?php
        $hexColor = $them['couleur'] ?? '#f7a13e';
        $r = hexdec(substr($hexColor,1,2));
        $g = hexdec(substr($hexColor,3,2));
        $b = hexdec(substr($hexColor,5,2));
        $lightBg = "rgba({$r},{$g},{$b},.12)";
      ?>
      <a href="<?= url('indicateurs?them=' . esc($them['slug'])) ?>"
         class="card card-thematique"
         style="--them-color:<?= esc($hexColor) ?>;--them-bg:<?= $lightBg ?>"
         aria-label="<?= esc($them['libelle_fr']) ?> — <?= (int)$them['nb_indicateurs'] ?> indicateurs">
        <div class="them-icon" aria-hidden="true">
          <i class="fa-solid fa-<?= esc($them['icone'] ?? 'chart-bar') ?>"></i>
        </div>
        <div class="count"><?= (int)$them['nb_indicateurs'] ?></div>
        <h3><?= esc($them['libelle_fr']) ?></h3>
        <p><?= esc($them['description_fr'] ?? '') ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── SECTION AGADEZ ─────────────────────────────────────────── -->
<section class="section" style="background:linear-gradient(135deg,#1a3a2a 0%,#2a5a3e 100%);color:#fff"
         aria-labelledby="agadez-section-title">
  <div class="container" style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center">
    <div>
      <span class="hero-badge" style="background:rgba(244,161,29,.2);border-color:var(--pndm-orange);color:var(--pndm-orange);margin-bottom:1rem">
        <i class="fa-solid fa-map-pin" aria-hidden="true"></i>
        Dossier spécial
      </span>
      <h2 id="agadez-section-title" style="font-size:2.25rem;font-weight:800;margin-bottom:1rem;color:#fff">
        Agadez, carrefour<br>migratoire
      </h2>
      <p style="opacity:.85;line-height:1.7;margin-bottom:2rem">
        Données et analyses complètes sur les flux migratoires de la région d'Agadez
        et du corridor Tamanrasset–Assamaka–Agadez. Intégration Power BI,
        cartes interactives et documents officiels.
      </p>
      <a href="<?= url('dossiers/agadez') ?>" class="btn btn-accent btn-lg">
        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        Accéder au dossier
      </a>
    </div>
    <div aria-hidden="true">
      <img src="<?= url('assets/images/carousel/agadez/carte_agadez.jpg') ?>"
           alt="Carte de la région d'Agadez"
           style="border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.3);width:100%;max-height:380px;object-fit:cover">
    </div>
  </div>
</section>

<!-- ── DERNIÈRES MISES À JOUR ─────────────────────────────────── -->
<?php if (!empty($derniers_updates)): ?>
<section class="section section-alt" aria-labelledby="updates-title">
  <div class="container">
    <div class="section-header left">
      <h2 id="updates-title" style="text-align:left">Dernières mises à jour</h2>
    </div>
    <div class="table-container">
      <table class="data-table" aria-label="Dernières publications de données">
        <thead>
          <tr>
            <th scope="col">Indicateur</th>
            <th scope="col">Thématique</th>
            <th scope="col">Période</th>
            <th scope="col">Valeur</th>
            <th scope="col">Publié</th>
            <th scope="col" class="sr-only">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($derniers_updates as $upd): ?>
          <tr>
            <td>
              <a href="<?= url('indicateurs/'.esc($upd['indicateur_slug'])) ?>">
                <?= esc($upd['indicateur']) ?>
              </a>
            </td>
            <td><span class="badge badge-primary"><?= esc($upd['thematique']) ?></span></td>
            <td><?= date('Y', strtotime($upd['periode_debut'])) ?></td>
            <td><?= format_number($upd['total']) ?></td>
            <td><time datetime="<?= esc($upd['publie_le'] ?? $upd['updated_at']) ?>"><?= ago($upd['publie_le'] ?? $upd['updated_at']) ?></time></td>
            <td><a href="<?= url('indicateurs/'.esc($upd['indicateur_slug'])) ?>" class="btn btn-ghost btn-sm" aria-label="Voir <?= esc($upd['indicateur']) ?>">Voir</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── À PROPOS ───────────────────────────────────────────────── -->
<section class="section" aria-labelledby="apropos-title">
  <div class="container" style="display:grid;grid-template-columns:1fr 2fr;gap:4rem;align-items:start">
    <div style="display:flex;flex-direction:column;gap:2rem">
      <img src="<?= url('assets/images/ins-logo.png') ?>" alt="Logo INS Niger" style="width:120px">
      <img src="<?= url('assets/images/iom-logo.png') ?>"  alt="OIM"          style="width:100px">
      <img src="<?= url('assets/images/maeci-logo.png') ?>" alt="Italie MAECI" style="width:120px">
    </div>
    <div>
      <h2 id="apropos-title">À propos du PNDM</h2>
      <p style="margin:1rem 0;line-height:1.8;color:var(--gray-700)">
        La Plateforme Nationale des Données sur la Migration (PNDM) est développée par
        l'<strong>Institut National de la Statistique (INS)</strong> du Niger avec l'appui
        de l'<strong>Organisation Internationale pour les Migrations (OIM)</strong>, dans le
        cadre du projet POMM financé par le Ministère des Affaires Étrangères et
        de la Coopération Internationale de l'Italie.
      </p>
      <p style="margin-bottom:1.5rem;line-height:1.8;color:var(--gray-700)">
        Elle offre un accès libre et ouvert aux données officielles sur les migrations
        internationales et internes au Niger : flux, stocks, personnes déplacées,
        vulnérabilités, transferts de fonds et diaspora.
      </p>
      <div style="display:flex;gap:1rem;flex-wrap:wrap">
        <a href="<?= url('a-propos') ?>"  class="btn btn-primary">En savoir plus</a>
        <a href="/api/v1/"               class="btn btn-ghost" target="_blank" rel="noopener">
          <i class="fa-solid fa-code" aria-hidden="true"></i> API ouverte
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ── SPARKLINES DATA (JSON in-page) ─────────────────────────── -->
<script id="sparklines-data" type="application/json">
<?= json_safe($sparklines_data ?? []) ?>
</script>

<!-- ── CAROUSEL SCRIPT ───────────────────────────────────────── -->
<script>
(function() {
  let current = 0;
  const slides = document.querySelectorAll('#mainCarousel .carousel-slide');
  const dots   = document.querySelectorAll('.carousel-dot');
  let timer    = setInterval(() => carouselMove(1), 5500);

  function show(idx) {
    slides[current].classList.remove('active');
    dots[current].classList.remove('active');
    dots[current].setAttribute('aria-selected','false');
    current = (idx + slides.length) % slides.length;
    slides[current].classList.add('active');
    dots[current].classList.add('active');
    dots[current].setAttribute('aria-selected','true');
  }

  window.carouselMove = function(dir) { clearInterval(timer); show(current + dir); timer = setInterval(() => carouselMove(1), 5500); };
  window.carouselGoto = function(idx) { clearInterval(timer); show(idx); timer = setInterval(() => carouselMove(1), 5500); };

  // Pause on hover
  const track = document.getElementById('mainCarousel');
  if (track) {
    track.addEventListener('mouseenter', () => clearInterval(timer));
    track.addEventListener('mouseleave', () => { timer = setInterval(() => carouselMove(1), 5500); });
  }

  // Touch swipe support
  let touchStartX = 0;
  document.querySelector('.home-carousel')?.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
  document.querySelector('.home-carousel')?.addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 50) carouselMove(dx < 0 ? 1 : -1);
  }, {passive:true});
})();
</script>
