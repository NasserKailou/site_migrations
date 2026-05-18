<?php /** @var array $stats */ use App\Core\View; ?>
<!-- HERO -->
<section class="page-hero page-hero--blue">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-light">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Accueil</a></li>
                <li class="breadcrumb-item active">À propos</li>
            </ol>
        </nav>
        <h1 class="page-hero__title">À propos de la PNDM</h1>
        <p class="page-hero__subtitle">
            Plateforme Nationale des Données sur la Migration du Niger
        </p>
    </div>
</section>

<!-- INTRO -->
<section class="section-py bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <h2 class="section-title">Qu'est-ce que la PNDM ?</h2>
                <p class="lead">
                    La <strong>Plateforme Nationale des Données sur la Migration (PNDM)</strong> est un
                    système d'information centralisé développé par l'<strong>Institut National de la
                    Statistique (INS) du Niger</strong> en partenariat avec l'Organisation Internationale
                    pour les Migrations (<strong>OIM/IOM</strong>), avec le soutien financier de l'Italie
                    (MAECI — Ministère des Affaires Étrangères et de la Coopération Internationale).
                </p>
                <p>
                    Conçue pour rassembler, organiser et diffuser les données relatives aux flux
                    migratoires au Niger, la plateforme vise à soutenir les décideurs, chercheurs
                    et organisations humanitaires dans leur compréhension des dynamiques migratoires
                    complexes qui traversent ce pays carrefour de l'Afrique de l'Ouest.
                </p>
                <p>
                    Le Niger constitue un point de passage stratégique sur les routes migratoires
                    entre l'Afrique subsaharienne, le Maghreb et l'Europe. La ville d'<strong>Agadez</strong>,
                    au cœur du pays, est au croisement de plusieurs corridors migratoires majeurs.
                </p>
            </div>
            <div class="col-lg-5">
                <div class="about-stats-card">
                    <h3 class="about-stats-title">La PNDM en chiffres</h3>
                    <ul class="about-stats-list">
                        <li>
                            <span class="about-stat-value"><?= number_format($stats['nb_indicateurs'] ?? 0) ?></span>
                            <span class="about-stat-label">Indicateurs thématiques</span>
                        </li>
                        <li>
                            <span class="about-stat-value"><?= number_format($stats['nb_observations'] ?? 0) ?></span>
                            <span class="about-stat-label">Observations publiées</span>
                        </li>
                        <li>
                            <span class="about-stat-value"><?= number_format($stats['nb_thematiques'] ?? 0) ?></span>
                            <span class="about-stat-label">Thématiques couvertes</span>
                        </li>
                        <li>
                            <span class="about-stat-value"><?= number_format($stats['nb_entites'] ?? 0) ?></span>
                            <span class="about-stat-label">Sources de données</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- OBJECTIFS -->
<section class="section-py bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nos objectifs</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="objective-card">
                    <div class="objective-icon objective-icon--orange">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                    <h3>Centraliser</h3>
                    <p>Rassembler en un seul lieu toutes les données disponibles sur la migration au Niger, issues de sources gouvernementales, onusiennes et de la société civile.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="objective-card">
                    <div class="objective-icon objective-icon--green">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <h3>Analyser</h3>
                    <p>Produire des analyses thématiques et temporelles permettant de mieux comprendre les tendances et les dynamiques des flux migratoires.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="objective-card">
                    <div class="objective-icon objective-icon--blue">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3>Partager</h3>
                    <p>Mettre à disposition des données ouvertes et accessibles à tous — décideurs, chercheurs, journalistes et citoyens — dans le respect des normes de confidentialité.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- THÉMATIQUES COUVERTES -->
<section class="section-py bg-white">
    <div class="container">
        <h2 class="section-title text-center mb-2">Thématiques couvertes</h2>
        <p class="text-center text-muted mb-5">La PNDM organise ses données autour de sept grandes thématiques</p>
        <div class="row g-3">
            <?php
            $themes = [
                ['icon' => '🔄', 'name' => 'Flux migratoires', 'desc' => 'Entrées, sorties, transits et flux de retour'],
                ['icon' => '🏕️', 'name' => 'Déplacements forcés', 'desc' => 'Réfugiés, demandeurs d\'asile, déplacés internes'],
                ['icon' => '🔁', 'name' => 'Retours & Réintégration', 'desc' => 'Retours volontaires et assistés (AVR), réinsertion'],
                ['icon' => '💼', 'name' => 'Migration économique', 'desc' => 'Transferts de fonds (remittances), emploi migrant'],
                ['icon' => '⚕️', 'name' => 'Santé & Migration', 'desc' => 'Santé des migrants, vulnérabilités sanitaires'],
                ['icon' => '🛡️', 'name' => 'Protection', 'desc' => 'Traite des personnes, trafic de migrants, MNA'],
                ['icon' => '🗺️', 'name' => 'Géographie', 'desc' => 'Cartographie des routes, points de passage, Agadez'],
            ];
            foreach ($themes as $theme): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="theme-pill">
                    <span class="theme-pill-icon"><?= $theme['icon'] ?></span>
                    <div>
                        <div class="theme-pill-name"><?= esc($theme['name']) ?></div>
                        <div class="theme-pill-desc"><?= esc($theme['desc']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PARTENAIRES -->
<section class="section-py bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Nos partenaires</h2>
        <div class="partners-grid">
            <div class="partner-card">
                <img src="<?= View::asset('assets/images/logo-ins.png') ?>"
                     alt="INS Niger — Institut National de la Statistique"
                     loading="lazy"
                     onerror="this.parentElement.querySelector('.partner-fallback').style.display='flex'; this.style.display='none'">
                <div class="partner-fallback" style="display:none; width:120px; height:60px; background:#f3f4f6; border-radius:8px; align-items:center; justify-content:center; font-weight:700; color:#005B9A; font-size:.9rem; text-align:center; padding:.5rem;">INS Niger</div>
                <div class="partner-info">
                    <strong>Institut National de la Statistique</strong>
                    <span>Niger — Entité productrice principale</span>
                </div>
            </div>
            <div class="partner-card">
                <img src="<?= View::asset('assets/images/logo-iom.png') ?>"
                     alt="OIM — Organisation Internationale pour les Migrations"
                     loading="lazy"
                     onerror="this.parentElement.querySelector('.partner-fallback').style.display='flex'; this.style.display='none'">
                <div class="partner-fallback" style="display:none; width:120px; height:60px; background:#f3f4f6; border-radius:8px; align-items:center; justify-content:center; font-weight:700; color:#005B9A; font-size:.9rem; text-align:center; padding:.5rem;">OIM/IOM</div>
                <div class="partner-info">
                    <strong>Organisation Internationale pour les Migrations</strong>
                    <span>Support technique & financement</span>
                </div>
            </div>
            <div class="partner-card">
                <img src="<?= View::asset('assets/images/logo-maeci.png') ?>"
                     alt="MAECI Italie — Ministère des Affaires Étrangères"
                     loading="lazy"
                     onerror="this.parentElement.querySelector('.partner-fallback').style.display='flex'; this.style.display='none'">
                <div class="partner-fallback" style="display:none; width:120px; height:60px; background:#f3f4f6; border-radius:8px; align-items:center; justify-content:center; font-weight:700; color:#009246; font-size:.9rem; text-align:center; padding:.5rem;">Italie MAECI</div>
                <div class="partner-info">
                    <strong>MAECI Italie</strong>
                    <span>Bailleur de fonds principal</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- API & DONNÉES OUVERTES -->
<section class="section-py bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title">Données ouvertes & API</h2>
                <p>
                    Toutes les données publiées sur la PNDM sont accessibles librement sous licence
                    <strong>Creative Commons Attribution 4.0 (CC BY 4.0)</strong>.
                    Vous pouvez les télécharger, les utiliser et les redistribuer, à condition de citer la source.
                </p>
                <p>
                    La PNDM dispose également d'une <strong>API REST v1</strong> permettant
                    un accès programmatique aux données. Parfaite pour les développeurs, chercheurs
                    et journalistes de données.
                </p>
                <div class="d-flex gap-3 mt-4">
                    <a href="<?= url('indicateurs') ?>" class="btn btn-primary">
                        Explorer les données
                    </a>
                    <a href="<?= url('api/v1/meta') ?>" target="_blank" class="btn btn-outline-primary">
                        Documentation API
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="code-block">
                    <div class="code-block-header">
                        <span>Exemple d'appel API</span>
                        <button class="btn-copy" onclick="copyCode(this)" data-target="codeExample">Copier</button>
                    </div>
                    <pre id="codeExample"><code>GET <?= url('api/v1/indicateurs') ?>

# Réponse JSON
{
  "data": [
    {
      "id": 1,
      "slug": "flux-retours-volontaires",
      "libelle": "Flux de retours volontaires",
      "valeur_derniere": 2847,
      "periode": "2023"
    }
    ...
  ],
  "meta": { "total": 43, "page": 1 }
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT CTA -->
<section class="section-py" style="background:linear-gradient(135deg, var(--primary) 0%, #003d6b 100%); color:#fff;">
    <div class="container text-center">
        <h2 style="color:#fff;" class="mb-3">Une question ? Un partenariat ?</h2>
        <p class="mb-4" style="opacity:.85; max-width:500px; margin-inline:auto;">
            Notre équipe est disponible pour répondre à vos questions sur les données,
            les méthodologies ou les possibilités de collaboration.
        </p>
        <a href="<?= url('contact') ?>" class="btn btn-light btn-lg">
            Nous contacter
        </a>
    </div>
</section>

<style>
.about-stats-card {
    background:var(--primary); color:#fff;
    border-radius:12px; padding:2rem;
}
.about-stats-title { font-size:1.1rem; font-weight:700; margin-bottom:1.5rem; opacity:.9; }
.about-stats-list  { list-style:none; padding:0; margin:0; }
.about-stats-list li {
    display:flex; align-items:center; justify-content:space-between;
    padding:.75rem 0; border-bottom:1px solid rgba(255,255,255,.15);
}
.about-stats-list li:last-child { border-bottom:none; }
.about-stat-value { font-size:2rem; font-weight:800; line-height:1; }
.about-stat-label { font-size:.85rem; opacity:.8; text-align:right; max-width:150px; }

.objective-card {
    background:#fff; border-radius:12px; padding:2rem;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    height:100%;
}
.objective-icon {
    width:56px; height:56px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom:1.25rem;
}
.objective-icon--orange { background:rgba(244,161,29,.12); color:#F4A11D; }
.objective-icon--green  { background:rgba(29,164,98,.12);  color:#1DA462; }
.objective-icon--blue   { background:rgba(0,91,154,.12);   color:#005B9A; }
.objective-card h3 { font-size:1.1rem; margin-bottom:.5rem; }

.theme-pill {
    background:#fff; border-radius:10px; padding:1rem;
    display:flex; align-items:flex-start; gap:.75rem;
    box-shadow:0 1px 6px rgba(0,0,0,.06); height:100%;
}
.theme-pill-icon { font-size:1.5rem; flex-shrink:0; }
.theme-pill-name { font-weight:600; font-size:.9rem; }
.theme-pill-desc { font-size:.78rem; color:#6b7280; margin-top:.1rem; }

.partners-grid {
    display:flex; flex-wrap:wrap; gap:2rem;
    justify-content:center; align-items:center;
}
.partner-card {
    display:flex; flex-direction:column; align-items:center; gap:.75rem;
    background:#fff; border-radius:12px; padding:1.5rem 2rem;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
    text-align:center; min-width:200px;
}
.partner-card img { max-height:60px; max-width:140px; object-fit:contain; }
.partner-info strong { display:block; font-size:.9rem; }
.partner-info span   { font-size:.8rem; color:#6b7280; }

.code-block { background:#1e293b; border-radius:10px; overflow:hidden; }
.code-block-header {
    display:flex; justify-content:space-between; align-items:center;
    padding:.6rem 1rem; background:#0f172a; font-size:.8rem; color:#94a3b8;
}
.code-block pre { margin:0; padding:1.25rem; overflow-x:auto; }
.code-block code { color:#e2e8f0; font-size:.82rem; line-height:1.6; }
.btn-copy {
    background:none; border:1px solid #334155; color:#94a3b8;
    border-radius:4px; padding:.2rem .6rem; font-size:.75rem; cursor:pointer;
}
.btn-copy:hover { background:#334155; }
</style>

<script>
function copyCode(btn) {
    const id = btn.dataset.target;
    const text = document.getElementById(id)?.textContent ?? '';
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'Copié !';
        setTimeout(() => { btn.textContent = 'Copier'; }, 2000);
    });
}
</script>
