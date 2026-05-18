/**
 * PNDM — JavaScript principal
 * Institut National de la Statistique — Niger
 */
'use strict';

/* ── Sparklines sur la home ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  // Sparklines
  const sparkData = JSON.parse(document.getElementById('sparklines-data')?.textContent || '{}');
  Object.entries(sparkData).forEach(([id, data]) => {
    const canvas = document.getElementById('spark-' + id);
    if (!canvas || !data.length) return;
    new Chart(canvas, {
      type: 'line',
      data: {
        labels: data.map(d => d.annee),
        datasets: [{
          data: data.map(d => d.total || d.valeur),
          borderColor: canvas.closest('[style]')?.style.getPropertyValue('--card-color') || '#005B9A',
          borderWidth: 2, pointRadius: 0, fill: true,
          backgroundColor: 'rgba(0,91,154,.08)',
          tension: 0.4,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        animation: { duration: 800 }
      }
    });
  });

  // Compteurs animés
  document.querySelectorAll('.counter-value[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10);
    if (!target) return;
    let start = 0;
    const duration = 1500;
    const step = target / (duration / 16);
    const observer = new IntersectionObserver(entries => {
      if (!entries[0].isIntersecting) return;
      observer.disconnect();
      const timer = setInterval(() => {
        start = Math.min(start + step, target);
        el.textContent = Math.round(start).toLocaleString('fr-FR');
        if (start >= target) clearInterval(timer);
      }, 16);
    });
    observer.observe(el);
  });

  // Tabs
  document.querySelectorAll('.tabs').forEach(tabsEl => {
    tabsEl.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.dataset.tab;
        // désactiver tous
        tabsEl.querySelectorAll('.tab-btn').forEach(b => {
          b.classList.remove('active');
          b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        // panels dans le parent
        const container = tabsEl.closest('[data-tabs-container]') || tabsEl.parentElement;
        container.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        const panel = container.querySelector('#panel-' + target);
        if (panel) panel.classList.add('active');
      });
    });
  });

  // Toast auto-dismiss
  document.querySelectorAll('.toast[data-auto-dismiss]').forEach(toast => {
    setTimeout(() => toast.remove(), parseInt(toast.dataset.autoDismiss, 10) || 4000);
  });
});

/* ── Toast helper ─────────────────────────────────────────────── */
function showToast(msg, type = 'info', duration = 4000) {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icons = { success: 'circle-check', error: 'circle-xmark', info: 'circle-info' };
  toast.innerHTML = `<i class="fa-solid fa-${icons[type] || 'info'}" aria-hidden="true"></i><span>${escHtml(msg)}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, duration);
}

function escHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML;
}

/* ── Filtres indicateurs (page /indicateurs) ─────────────────── */
function initIndicatorsFilters() {
  const form   = document.getElementById('filters-form');
  const results= document.getElementById('indicators-results');
  if (!form || !results) return;

  let debounce = null;
  function applyFilters() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
      const params = new URLSearchParams(new FormData(form));
      // Mettre à jour l'URL sans rechargement
      history.replaceState(null, '', '/indicateurs?' + params.toString());
      fetchIndicators(params);
    }, 300);
  }

  form.querySelectorAll('input, select').forEach(el => {
    el.addEventListener('change', applyFilters);
    if (el.type === 'text' || el.type === 'search') {
      el.addEventListener('input', applyFilters);
    }
  });

  // Initialiser depuis l'URL
  const urlParams = new URLSearchParams(location.search);
  urlParams.forEach((val, key) => {
    const el = form.querySelector(`[name="${key}"]`);
    if (el) el.value = val;
  });
}

async function fetchIndicators(params) {
  const results = document.getElementById('indicators-results');
  if (!results) return;
  results.innerHTML = '<div class="skeleton" style="height:200px;margin:1rem 0"></div>';
  try {
    const res  = await fetch('/api/v1/indicateurs?' + params.toString());
    const data = await res.json();
    renderIndicators(data);
  } catch (e) {
    results.innerHTML = '<div class="alert alert-error">Erreur lors du chargement des données.</div>';
  }
}

function renderIndicators(data) {
  const results = document.getElementById('indicators-results');
  if (!data.data || !data.data.length) {
    results.innerHTML = '<div class="text-center" style="padding:3rem;color:var(--gray-500)"><i class="fa-solid fa-circle-info fa-2x" style="margin-bottom:1rem"></i><br>Aucun indicateur trouvé.</div>';
    return;
  }
  const total = document.getElementById('indicators-count');
  if (total) total.textContent = data.meta?.total || data.data.length;

  let html = '<div class="table-container"><table class="data-table" role="grid" aria-label="Liste des indicateurs"><thead><tr>'
    + '<th scope="col">Indicateur</th><th scope="col">Thème</th><th scope="col">Source</th>'
    + '<th scope="col">Dernière donnée</th><th scope="col" class="sr-only">Actions</th>'
    + '</tr></thead><tbody>';

  data.data.forEach(ind => {
    html += `<tr>
      <td><a href="/indicateurs/${escHtml(ind.slug)}" style="font-weight:600">${escHtml(ind.libelle_fr)}</a></td>
      <td><span class="badge badge-primary" style="background:rgba(${hexToRgb(ind.thematique_couleur)},.12);color:${escHtml(ind.thematique_couleur)}">${escHtml(ind.thematique)}</span></td>
      <td>${escHtml(ind.source)}</td>
      <td>${ind.derniere_date ? new Date(ind.derniere_date).getFullYear() : '—'}</td>
      <td><a href="/indicateurs/${escHtml(ind.slug)}" class="btn btn-ghost btn-sm" aria-label="Voir ${escHtml(ind.libelle_fr)}">Voir <i class="fa-solid fa-arrow-right fa-xs"></i></a></td>
    </tr>`;
  });
  html += '</tbody></table></div>';

  // Pagination
  if (data.meta && data.meta.total_pages > 1) {
    html += renderPagination(data.meta);
  }
  results.innerHTML = html;
}

function hexToRgb(hex) {
  if (!hex || hex.length < 7) return '0,91,154';
  const r = parseInt(hex.slice(1,3), 16);
  const g = parseInt(hex.slice(3,5), 16);
  const b = parseInt(hex.slice(5,7), 16);
  return `${r},${g},${b}`;
}

function renderPagination(meta) {
  const { page, total_pages } = meta;
  let html = '<nav class="pagination" style="margin-top:1.5rem" aria-label="Pagination">';
  const prev = page > 1 ? `<a href="#" data-page="${page-1}" aria-label="Page précédente">‹</a>` : '<span class="disabled">‹</span>';
  const next = page < total_pages ? `<a href="#" data-page="${page+1}" aria-label="Page suivante">›</a>` : '<span class="disabled">›</span>';
  html += prev;
  for (let i = Math.max(1, page-2); i <= Math.min(total_pages, page+2); i++) {
    html += i === page ? `<span class="active">${i}</span>` : `<a href="#" data-page="${i}">${i}</a>`;
  }
  html += next + '</nav>';
  return html;
}

/* ── Graphiques fiche indicateur ─────────────────────────────── */
let mainChart = null;

function initIndicatorCharts(indicateurSlug) {
  const canvas = document.getElementById('main-chart');
  if (!canvas) return;

  loadChartData(indicateurSlug, {}).then(data => {
    renderMainChart(canvas, data);
  });

  // Changement de type de graphique
  document.querySelectorAll('[data-chart-type]').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-chart-type]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      if (mainChart) {
        mainChart.config.type = btn.dataset.chartType;
        mainChart.update();
      }
    });
  });
}

async function loadChartData(slug, filters) {
  const params = new URLSearchParams(filters);
  const res = await fetch(`/api/v1/indicateurs/${slug}/donnees?${params}`);
  return res.json();
}

function renderMainChart(canvas, data) {
  if (mainChart) mainChart.destroy();
  if (!data.data || !data.data.length) {
    canvas.parentElement.innerHTML = '<div class="text-center" style="padding:3rem;color:var(--gray-500)">Aucune donnée disponible</div>';
    return;
  }

  const labels  = [...new Set(data.data.map(d => d.annee))].sort();
  const groups  = [...new Set(data.data.map(d => d.niveau_desag_valeur || 'Total'))];
  const colors  = ['#005B9A','#F4A11D','#1DA462','#E74C3C','#8E44AD','#0082C8','#27AE60','#D4870A'];

  const datasets = groups.map((g, i) => {
    const pts = labels.map(yr => {
      const row = data.data.find(d => d.annee == yr && (d.niveau_desag_valeur || 'Total') === g);
      return row ? (parseFloat(row.total) || parseFloat(row.masculin) || null) : null;
    });
    return {
      label: g, data: pts,
      borderColor: colors[i % colors.length],
      backgroundColor: colors[i % colors.length] + '22',
      borderWidth: 2.5, pointRadius: 4, tension: 0.3, fill: groups.length === 1,
    };
  });

  mainChart = new Chart(canvas, {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true, maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend:  { position: 'top', labels: { usePointStyle: true, padding: 20 } },
        tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y?.toLocaleString('fr-FR') ?? '—'}` } }
      },
      scales: {
        x: { grid: { color: '#f1f3f5' }, ticks: { font: { size: 11 } } },
        y: { grid: { color: '#f1f3f5' }, ticks: { font: { size: 11 }, callback: v => v.toLocaleString('fr-FR') } }
      }
    }
  });
}

/* ── Carte choroplèthe Leaflet ─────────────────────────────────── */
let map = null;

async function initNigerMap(containerId, indicateurSlug) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (map) { map.remove(); map = null; }

  map = L.map(container, { zoomControl: true, attributionControl: true });
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  // Centre Niger
  map.setView([17.6, 8.1], 5);

  // Données choroplèthe
  try {
    const [geoRes, dataRes] = await Promise.all([
      fetch('/api/v1/geo'),
      fetch(`/api/v1/indicateurs/${indicateurSlug}/donnees?format=geo`)
    ]);
    const geoJson   = await geoRes.json();
    const dataJson  = await dataRes.json();
    const dataMap   = {};
    (dataJson.data || []).forEach(d => { if (d.geo_region) dataMap[d.geo_region] = parseFloat(d.total); });

    const values = Object.values(dataMap).filter(v => !isNaN(v));
    const minV   = Math.min(...values);
    const maxV   = Math.max(...values);

    L.geoJSON(geoJson.data || [], {
      style: feature => {
        const name = feature.properties?.name || feature.properties?.libelle;
        const val  = dataMap[name];
        const t    = val !== undefined ? (val - minV) / (maxV - minV || 1) : 0;
        const fill = `rgba(0,91,154,${0.15 + t * 0.7})`;
        return { fillColor: fill, weight: 1.5, color: '#fff', fillOpacity: 1 };
      },
      onEachFeature: (feature, layer) => {
        const name = feature.properties?.name || feature.properties?.libelle;
        const val  = dataMap[name];
        layer.bindTooltip(`<strong>${name}</strong><br>${val !== undefined ? val.toLocaleString('fr-FR') : 'N/D'}`, { sticky: true });
      }
    }).addTo(map);
  } catch (e) {
    console.warn('Carte non disponible:', e);
  }
}

/* ── Export CSV ───────────────────────────────────────────────── */
async function exportData(slug, format = 'csv') {
  const btn = document.querySelector('[data-export]');
  if (btn) { btn.disabled = true; btn.textContent = 'Export en cours…'; }
  try {
    window.location.href = `/api/v1/indicateurs/${slug}/export?format=${format}`;
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Exporter'; }
  }
}

/* ── Autosave formulaire admin ────────────────────────────────── */
function initAutosave(formId, url) {
  const form = document.getElementById(formId);
  if (!form) return;
  const indicator = document.getElementById('autosave-indicator');

  setInterval(async () => {
    const data = new FormData(form);
    data.append('autosave', '1');
    try {
      const res = await fetch(url, { method: 'POST', body: data });
      const j   = await res.json();
      if (indicator) {
        indicator.textContent = '💾 Sauvegardé ' + new Date().toLocaleTimeString('fr-FR');
        indicator.style.opacity = '1';
        setTimeout(() => { indicator.style.opacity = '.5'; }, 2000);
      }
      if (j.id && !form.querySelector('[name="observation_id"]')) {
        const hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'observation_id'; hidden.value = j.id;
        form.appendChild(hidden);
      }
    } catch (e) { /* silencieux */ }
  }, 30000);
}
