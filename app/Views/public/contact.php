<?php
/**
 * Page Contact — wrapper
 */
use App\Core\View;
View::renderWithLayout('public/contact_body', [
    'pageTitle'    => 'Contact — PNDM Niger',
    'pageDesc'     => 'Contactez l\'équipe de la Plateforme Nationale des Données sur la Migration du Niger.',
    'canonicalUrl' => url('contact'),
]);
