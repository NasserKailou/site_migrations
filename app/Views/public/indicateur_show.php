<?php
/**
 * Fiche détaillée indicateur — onglets : Graphique / Carte / Désagrégation / Données / Métadonnées
 */
$extraJs = <<<JS
<script>
document.addEventListener('DOMContentLoaded', () => {
  initIndicatorCharts('{$slug}');
});
</script>
JS;
\App\Core\View::renderWithLayout('public/indicateur_show_body', get_defined_vars());
