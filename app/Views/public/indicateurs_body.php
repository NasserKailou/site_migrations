<!-- ═══ Explorateur d'indicateurs PNDM ═══════════════════════ -->

<!-- Hero compact -->
<div style="background:linear-gradient(135deg,var(--pndm-blue-dark),var(--pndm-blue));color:#fff;padding:3rem 0 2rem">
  <div class="container">
    <nav aria-label="Fil d'Ariane" style="font-size:.8rem;margin-bottom:.75rem;opacity:.75">
      <a href="<?= url() ?>" style="color:inherit">Accueil</a> › Indicateurs
    </nav>
    <h1 style="font-size:2.25rem;font-weight:800;margin-bottom:.5rem">Indicateurs de migration</h1>
    <p style="opacity:.85;max-width:600px">
      <span id="indicators-count"><?= (int)$total ?></span> indicateur<?= $total > 1 ? 's' : '' ?> publiés
      par <?= count($entites) ?> organismes sources
    </p>
  </div>
</div>

<div class="container" style="padding-top:2rem;padding-bottom:4rem">
  <div style="display:grid;grid-template-columns:280px 1fr;gap:2rem;align-items:start">

    <!-- ── FILTRES ─────────────────────────────────────────── -->
    <aside aria-label="Filtres" class="filters-sidebar" id="filtersSidebar">
      <form id="filters-form" aria-label="Filtrer les indicateurs">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
          <h2 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin:0">
            <i class="fa-solid fa-filter" aria-hidden="true"></i> Filtres
          </h2>
          <a href="<?= url('indicateurs') ?>" class="btn btn-ghost btn-sm">Réinitialiser</a>
        </div>

        <!-- Recherche -->
        <div class="filter-group">
          <label for="filter-q">Recherche</label>
          <div style="position:relative">
            <input type="search" name="q" id="filter-q" class="form-control"
                   placeholder="Nom, définition…" value="<?= esc($filters['q'] ?? '') ?>"
                   aria-label="Rechercher un indicateur">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"
               style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);color:var(--gray-400)"></i>
          </div>
        </div>

        <!-- Thématique -->
        <div class="filter-group">
          <label for="filter-them">Thématique</label>
          <select name="them_id" id="filter-them" class="form-control" aria-label="Filtrer par thématique">
            <option value="">Toutes les thématiques</option>
            <?php foreach ($thematiques as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= ($filters['thematique_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
              <?= esc($t['libelle_fr']) ?> (<?= (int)$t['nb'] ?>)
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Source -->
        <div class="filter-group">
          <label for="filter-entite">Source</label>
          <select name="entite_id" id="filter-entite" class="form-control" aria-label="Filtrer par source">
            <option value="">Toutes les sources</option>
            <?php foreach ($entites as $e): ?>
            <option value="<?= (int)$e['id'] ?>" <?= ($filters['entite_id'] ?? '') == $e['id'] ? 'selected' : '' ?>>
              <?= esc($e['acronyme']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Fréquence -->
        <div class="filter-group">
          <label for="filter-freq">Fréquence</label>
          <select name="freq_id" id="filter-freq" class="form-control" aria-label="Filtrer par fréquence">
            <option value="">Toutes les fréquences</option>
            <?php foreach ($frequences as $f): ?>
            <option value="<?= (int)$f['id'] ?>" <?= ($filters['frequence_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
              <?= esc($f['libelle']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Liens rapides par thématique -->
        <div class="filter-group" style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gray-200)">
          <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500);margin-bottom:.75rem">
            Thématiques rapides
          </div>
          <div style="display:flex;flex-direction:column;gap:.25rem">
            <?php foreach ($thematiques as $t): ?>
            <a href="<?= url('indicateurs?them='.esc($t['slug'])) ?>"
               style="display:flex;align-items:center;justify-content:space-between;padding:.35rem .5rem;border-radius:6px;font-size:.8rem;color:var(--gray-700);text-decoration:none;transition:background .2s"
               onmouseover="this.style.background='var(--gray-100)'" onmouseout="this.style.background=''"
               aria-label="<?= esc($t['libelle_fr']) ?>">
              <span style="display:flex;align-items:center;gap:.5rem">
                <span style="width:8px;height:8px;border-radius:50%;background:<?= esc($t['couleur']) ?>;flex-shrink:0"></span>
                <?= esc($t['libelle_fr']) ?>
              </span>
              <span class="badge badge-gray"><?= (int)$t['nb'] ?></span>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </form>
    </aside>

    <!-- ── RÉSULTATS ───────────────────────────────────────── -->
    <main aria-label="Résultats">
      <!-- Barre actions -->
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem">
        <div style="font-size:.9rem;color:var(--gray-600)">
          <strong id="indicators-count"><?= (int)$total ?></strong> indicateur<?= $total > 1 ? 's' : '' ?>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center">
          <!-- Vue grille / tableau -->
          <div role="group" aria-label="Mode d'affichage">
            <button id="view-table" class="btn btn-ghost btn-sm active" aria-pressed="true" title="Vue tableau">
              <i class="fa-solid fa-table" aria-hidden="true"></i>
            </button>
            <button id="view-cards" class="btn btn-ghost btn-sm" aria-pressed="false" title="Vue cartes">
              <i class="fa-solid fa-grip" aria-hidden="true"></i>
            </button>
          </div>
          <!-- Export sélection -->
          <button class="btn btn-primary btn-sm" id="export-all-btn"
                  onclick="window.location='/api/v1/indicateurs/export?format=csv'">
            <i class="fa-solid fa-download" aria-hidden="true"></i> Export CSV
          </button>
        </div>
      </div>

      <!-- Zone de résultats (remplie par JS) -->
      <div id="indicators-results">
        <!-- Affichage initial côté serveur -->
        <div class="table-container">
          <table class="data-table" role="grid" aria-label="Liste des indicateurs de migration">
            <thead>
              <tr>
                <th scope="col">Indicateur</th>
                <th scope="col">Thématique</th>
                <th scope="col">Source</th>
                <th scope="col">Dernière donnée</th>
                <th scope="col" class="sr-only">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($indicateurs)): ?>
              <tr><td colspan="5" class="text-center" style="padding:3rem;color:var(--gray-500)">
                <i class="fa-solid fa-circle-info fa-2x" style="display:block;margin-bottom:1rem" aria-hidden="true"></i>
                Aucun indicateur trouvé. <a href="<?= url('indicateurs') ?>">Réinitialiser les filtres</a>
              </td></tr>
              <?php else: foreach ($indicateurs as $ind): ?>
              <?php
                $hexC  = $ind['thematique_couleur'] ?? '#005B9A';
                $r = hexdec(substr($hexC,1,2)); $g = hexdec(substr($hexC,3,2)); $b = hexdec(substr($hexC,5,2));
                $bg = "rgba({$r},{$g},{$b},.12)";
              ?>
              <tr>
                <td>
                  <a href="<?= url('indicateurs/'.esc($ind['slug'])) ?>" style="font-weight:600">
                    <?= esc($ind['libelle_fr']) ?>
                  </a>
                  <?php if ($ind['definition_fr']): ?>
                  <div style="font-size:.75rem;color:var(--gray-500);margin-top:2px;max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <?= esc(mb_substr(strip_tags($ind['definition_fr']), 0, 100)) ?>
                  </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge" style="background:<?= $bg ?>;color:<?= esc($hexC) ?>">
                    <i class="fa-solid fa-<?= esc($ind['thematique_icone'] ?? 'chart-bar') ?> fa-xs" aria-hidden="true"></i>
                    <?= esc($ind['thematique']) ?>
                  </span>
                </td>
                <td>
                  <span style="font-size:.8rem;font-weight:600;color:var(--gray-600)"><?= esc($ind['source']) ?></span>
                </td>
                <td>
                  <?php if ($ind['derniere_date']): ?>
                  <time datetime="<?= esc($ind['derniere_date']) ?>" style="font-size:.85rem">
                    <?= date('Y', strtotime($ind['derniere_date'])) ?>
                  </time>
                  <?php if ($ind['derniere_valeur'] !== null): ?>
                  <div style="font-size:.75rem;color:var(--gray-500)">
                    <?= format_number($ind['derniere_valeur']) ?> <?= esc($ind['unite_symbole'] ?? '') ?>
                  </div>
                  <?php endif; ?>
                  <?php else: ?>
                  <span style="color:var(--gray-400);font-size:.8rem">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="<?= url('indicateurs/'.esc($ind['slug'])) ?>"
                     class="btn btn-ghost btn-sm"
                     aria-label="Voir l'indicateur <?= esc($ind['libelle_fr']) ?>">
                    Voir <i class="fa-solid fa-arrow-right fa-xs" aria-hidden="true"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination des indicateurs" style="margin-top:1.5rem">
          <?php if ($page > 1): ?>
            <a href="<?= url('indicateurs?page='.($page-1).'&'.http_build_query(array_filter($filters))) ?>"
               aria-label="Page précédente">‹</a>
          <?php else: ?><span class="disabled" aria-hidden="true">‹</span><?php endif; ?>

          <?php for ($i = max(1, $page-3); $i <= min($totalPages, $page+3); $i++): ?>
            <?php if ($i === $page): ?>
              <span class="active" aria-current="page"><?= $i ?></span>
            <?php else: ?>
              <a href="<?= url('indicateurs?page='.$i.'&'.http_build_query(array_filter($filters))) ?>"
                 aria-label="Page <?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="<?= url('indicateurs?page='.($page+1).'&'.http_build_query(array_filter($filters))) ?>"
               aria-label="Page suivante">›</a>
          <?php else: ?><span class="disabled" aria-hidden="true">›</span><?php endif; ?>
        </nav>
        <?php endif; ?>
      </div><!-- /indicators-results -->
    </main>
  </div>
</div>

<script>
// Initialisation des filtres interactifs
document.addEventListener('DOMContentLoaded', () => initIndicatorsFilters());
</script>
