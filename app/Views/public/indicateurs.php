<?php
/**
 * Vue : Explorateur d'indicateurs
 * Variables : $indicateurs, $total, $page, $totalPages, $thematiques, $entites, $frequences, $filters
 */
\App\Core\View::renderWithLayout('public/indicateurs_body', get_defined_vars());
