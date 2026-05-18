<?php
declare(strict_types=1);
/**
 * Page d'accueil PNDM
 * Variables: $stats, $indicateurs_phares, $thematiques, $derniers_updates
 */
$metaTitle = 'PNDM — Plateforme Nationale des Données sur la Migration du Niger';
$metaDesc  = 'Données officielles sur la migration au Niger : flux, stocks, PDIs, transferts de fonds, vulnérabilité. Institut National de la Statistique.';
\App\Core\View::renderWithLayout('public/home_body', get_defined_vars());
