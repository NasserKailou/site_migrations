<?php
/**
 * Page À propos — wrapper
 * @var array $stats
 */
use App\Core\View;

View::renderWithLayout('public/a_propos_body', [
    'stats'       => $stats ?? [],
    'pageTitle'   => 'À propos de la PNDM',
    'pageDesc'    => 'Découvrez la Plateforme Nationale des Données sur la Migration du Niger — son histoire, ses objectifs et ses partenaires.',
    'canonicalUrl'=> url('a-propos'),
]);
