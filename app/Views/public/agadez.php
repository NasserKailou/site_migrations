<?php
/**
 * Page Agadez — wrapper
 * @var array  $dossier
 * @var array  $observations
 * @var array  $documents
 */
use App\Core\View;

View::renderWithLayout('public/agadez_body', [
    'dossier'      => $dossier      ?? [],
    'observations' => $observations ?? [],
    'documents'    => $documents    ?? [],
    'pageTitle'    => 'Agadez — Carrefour des migrations | PNDM Niger',
    'pageDesc'     => 'Découvrez les données sur la migration à Agadez, ville-carrefour des routes migratoires sahariennes. Flux, acteurs, corridor et tableau de bord Power BI.',
    'canonicalUrl' => url('agadez'),
]);
