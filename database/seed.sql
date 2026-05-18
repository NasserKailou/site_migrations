-- ============================================================
-- SEED DATA — PNDM
-- Données de référence + migration depuis l'ancienne base
-- ============================================================

USE `pndm`;

-- ============================================================
-- RÔLES
-- ============================================================
INSERT IGNORE INTO `roles` (`id`, `libelle`, `permissions`) VALUES
(1, 'super_admin',  '{"all": true}'),
(2, 'admin',        '["manage_users","manage_indicators","manage_data","validate","publish","import","manage_dossiers"]'),
(3, 'validateur',   '["validate","publish","view_drafts"]'),
(4, 'point_focal',  '["create_data","edit_own_data","submit_data"]'),
(5, 'lecteur',      '["read"]');

-- ============================================================
-- FRÉQUENCES
-- ============================================================
INSERT IGNORE INTO `frequences` (`id`, `libelle`, `libelle_en`, `code`) VALUES
(1, 'Annuelle',       'Annual',      'annual'),
(2, 'Mensuelle',      'Monthly',     'monthly'),
(3, 'Trimestrielle',  'Quarterly',   'quarterly'),
(4, 'Semestrielle',   'Semi-annual', 'semi-annual'),
(5, 'Journalière',    'Daily',       'daily');

-- ============================================================
-- UNITÉS
-- ============================================================
INSERT IGNORE INTO `unites` (`id`, `libelle`, `libelle_en`, `symbole`) VALUES
(1, 'Personnes',          'Persons',        'pers.'),
(2, 'Pourcentage',        'Percentage',     '%'),
(3, 'Millions FCFA',      'Millions FCFA',  'M FCFA'),
(4, 'Nombre',             'Number',         'nb'),
(5, 'Ménages',            'Households',     'mén.'),
(6, 'Élèves',             'Students',       'élèves'),
(7, 'Bureaux',            'Offices',        'bur.');

-- ============================================================
-- THÉMATIQUES
-- ============================================================
INSERT IGNORE INTO `thematiques` (`id`,`slug`,`libelle_fr`,`libelle_en`,`description_fr`,`icone`,`couleur`,`ordre`) VALUES
(1, 'main-oeuvre',    'Main-d\'œuvre',              'Labour Migration',        'Migration des travailleurs et emploi des migrants',    'briefcase',     '#005B9A', 1),
(2, 'stock',          'Stock de migrations',        'Migration Stock',         'Effectifs de population migrante à un instant donné',  'users',         '#0082C8', 2),
(3, 'flux',           'Flux migratoires',           'Migration Flows',         'Mouvements d\'entrée et de sortie des migrants',       'arrows-alt-h',  '#00A896', 3),
(4, 'pdis',           'Personnes Déplacées (PDIs)', 'Internally Displaced',    'Déplacements internes au Niger',                       'home',          '#F4A11D', 4),
(5, 'vulnerabilite',  'Vulnérabilité',              'Vulnerability',           'Trafic, traite, migrants vulnérables',                 'shield-alt',    '#E74C3C', 5),
(6, 'transferts',     'Transferts de fonds',        'Remittances',             'Envois et réceptions de fonds par les migrants',       'exchange-alt',  '#27AE60', 6),
(7, 'diaspora',       'Diaspora',                   'Diaspora',                'Nigériens à l\'étranger et contribution au développement', 'globe-africa', '#8E44AD', 7);

-- ============================================================
-- ENTITÉS PRODUCTRICES DE DONNÉES
-- ============================================================
INSERT IGNORE INTO `entites` (`id`,`libelle`,`acronyme`,`created_at`) VALUES
(1,  'Observatoire National de l\'Emploi et de la Formation Professionnelle',                                                 'ONEF',       NOW()),
(2,  'Institut National de la Statistique',                                                                                   'INS',        NOW()),
(3,  'Direction des Statistiques et de la Promotion de l\'Informatique du Ministère de l\'Education Nationale',               'DSPI/MEN',   NOW()),
(4,  'Direction des Statistiques/Ministère de la Justice',                                                                    'DS/MJ',      NOW()),
(5,  'Direction de la Sécurité Publique',                                                                                     'DSP',        NOW()),
(6,  'Haut Conseil des Nigériens à l\'Extérieur',                                                                             'HCNE',       NOW()),
(7,  'Direction des Nigériens à l\'Extérieur/Ministère des Affaires Étrangères et de la Coopération',                         'DNE/MAEC',   NOW()),
(8,  'Agence Nationale pour la Promotion de l\'Emploi',                                                                       'ANPE',       NOW()),
(9,  'Direction de la Surveillance du Territoire',                                                                            'DST',        NOW()),
(10, 'Direction Générale de l\'État Civil, des Migrations et des Réfugiés',                                                   'DGEC/M/R',   NOW()),
(11, 'Institut National de la Statistique (Données RGPH)',                                                                    'INS/RGPH',   NOW()),
(12, 'Direction des Statistiques et de la Digitalisation/Ministère des Enseignements Technique et de la Formation Professionnelle', 'DSD/METFP', NOW()),
(13, 'Direction des Statistiques et de l\'Informatique/Ministère de l\'emploi, du Travail et de la Sécurité Sociale',         'DSI/ME/T/SS',NOW()),
(14, 'Banque Centrale des États de l\'Afrique de l\'Ouest',                                                                   'BCEAO',      NOW()),
(15, 'Direction des Statistiques et de l\'Informatique/Ministère de l\'Action Humanitaire et de la Gestion des Catastrophes', 'DS/MAH/GC',  NOW()),
(16, 'Direction des Etudes, de la Programmation et des Statistiques/Ministère de Promotion de la Femme et de la Protection de l\'Enfant', 'DEPS/MPF/PE', NOW());

-- ============================================================
-- NIVEAUX DE DÉSAGRÉGATION
-- ============================================================
INSERT IGNORE INTO `niveaux_desagregation` (`id`,`libelle`,`libelle_en`,`type`) VALUES
(1,  'National',                'National',                   'geo'),
(2,  'Région',                  'Region',                     'geo'),
(3,  'Département',             'Department',                 'geo'),
(4,  'Commune',                 'Municipality',               'geo'),
(5,  'Nationalité',             'Nationality',                'demo'),
(6,  'Groupe d\'âge',           'Age group',                  'demo'),
(7,  'Sexe',                    'Sex',                        'demo'),
(8,  'Milieu de résidence',     'Area of residence',          'geo'),
(9,  'Branche d\'activité',     'Activity branch',            'eco'),
(10, 'Taille de l\'entreprise', 'Company size',               'eco'),
(11, 'Catégorie professionnelle','Professional category',     'eco'),
(12, 'Secteur d\'activités',    'Activity sector',            'eco'),
(13, 'Juridiction',             'Jurisdiction',               'autre'),
(14, 'Ordre d\'enseignement',   'Education level',            'autre'),
(15, 'Pays',                    'Country',                    'geo'),
(16, 'Décisions rendues',       'Court decisions',            'autre'),
(17, 'Postes de contrôle',      'Checkpoints',                'geo'),
(18, 'Type de traite',          'Type of trafficking',        'autre'),
(19, 'TGI',                     'Court',                      'autre'),
(20, 'Tranche d\'âges',         'Age range',                  'demo');

-- ============================================================
-- ENTITÉS GÉOGRAPHIQUES DU NIGER
-- ============================================================
INSERT IGNORE INTO `geo_entites` (`id`,`libelle`,`code`,`type`,`parent_id`,`lat`,`lng`) VALUES
(1, 'Niger',      'NE',  'national', NULL,   17.6078, 8.0817),
(2, 'Agadez',     'AG',  'region',   1,      20.1674, 7.9913),
(3, 'Diffa',      'DI',  'region',   1,      13.3154, 12.6113),
(4, 'Dosso',      'DO',  'region',   1,      13.0349, 3.1979),
(5, 'Maradi',     'MA',  'region',   1,      13.5000, 7.1000),
(6, 'Niamey',     'NI',  'capital',  1,      13.5137, 2.1098),
(7, 'Tahoua',     'TA',  'region',   1,      14.8888, 5.2648),
(8, 'Tillabéry',  'TI',  'region',   1,      14.2110, 1.4500),
(9, 'Zinder',     'ZI',  'region',   1,      13.8076, 8.9881);

-- ============================================================
-- VALEURS DE NIVEAUX DE DÉSAGRÉGATION
-- ============================================================
-- Régions
INSERT IGNORE INTO `niveau_valeurs` (`niveau_desagregation_id`,`valeur`,`code`,`ordre`) VALUES
(2, 'Agadez',   'AG', 1), (2, 'Diffa',    'DI', 2), (2, 'Dosso',    'DO', 3),
(2, 'Maradi',   'MA', 4), (2, 'Niamey',   'NI', 5), (2, 'Tahoua',   'TA', 6),
(2, 'Tillabéry','TI', 7), (2, 'Zinder',   'ZI', 8);
-- Milieu
INSERT IGNORE INTO `niveau_valeurs` (`niveau_desagregation_id`,`valeur`,`ordre`) VALUES
(8, 'Urbain', 1), (8, 'Rural', 2);
-- Secteur
INSERT IGNORE INTO `niveau_valeurs` (`niveau_desagregation_id`,`valeur`,`ordre`) VALUES
(12, 'Agricole',    1), (12, 'Industriel', 2), (12, 'Tertiaire',  3);

-- ============================================================
-- INDICATEURS (depuis l'ancienne base enrichis)
-- ============================================================
INSERT IGNORE INTO `indicateurs` (`id`,`slug`,`libelle_fr`,`definition_fr`,`methode_calcul`,`donnees_requises`,`entite_id`,`thematique_id`,`unite_id`,`frequence_id`,`type_graphes`,`statut`) VALUES
(1,  'proportion-entreprises-mo-etrangere',
     'Proportion des entreprises faisant appel à la main-d\'œuvre étrangère',
     'Pourcentage d\'entreprises qui emploient au moins un travailleur étranger parmi l\'ensemble des entreprises enquêtées.',
     'Nombre d\'entreprises faisant appel à la MO étrangère / Nombre total d\'entreprises × 100',
     'Nombre d\'entreprises faisant appel à la MO étrangère et le nombre total d\'entreprises',
     1, 1, 2, 1, 'line,bar', 'actif'),
(2,  'bureaux-haut-conseil-nigeriens-exterieur',
     'Nombre de bureaux du Haut Conseil des Nigériens à l\'Extérieur',
     'Nombre cumulé de bureaux représentatifs du HCNE à travers le monde.',
     'Cumul des bureaux mis en place par mandat',
     'Bureaux mis en place par mandat',
     7, 1, 7, 1, 'line,bar,scatter', 'actif'),
(3,  'nigeriens-immatricules-representations-diplomatiques',
     'Nombre de Nigériens immatriculés dans les Représentations Diplomatiques et postes Consulaires Nigériens à l\'Extérieur',
     'Nombre total de Nigériens ayant effectué leur immatriculation auprès d\'une représentation diplomatique nigérienne à l\'étranger.',
     'Cumul des personnes immatriculées',
     'Personnes immatriculées',
     7, 2, 1, 1, 'line,bar,scatter', 'actif'),
(4,  'eleves-deplaces-internes',
     'Nombre des élèves déplacés internes',
     'Effectif total des élèves scolarisés qui ont été déplacés à l\'intérieur du Niger suite à des crises.',
     'Somme des élèves déplacés internes',
     'Élèves déplacés internes par région et ordre d\'enseignement',
     3, 4, 6, 1, 'line,bar,scatter', 'actif'),
(5,  'eleves-etrangers-refugies',
     'Nombre des élèves étrangers (réfugiés)',
     'Effectif des élèves non nigériens bénéficiant du statut de réfugié scolarisés au Niger.',
     'Somme des élèves non nigériens (réfugiés)',
     'Effectif élèves non nigériens (réfugiés)',
     3, 5, 6, 1, 'line,bar,scatter', 'actif'),
(6,  'eleves-retournes',
     'Nombre des élèves retournés',
     'Effectif des élèves précédemment déplacés qui ont pu regagner leur lieu d\'origine et reprendre leur scolarité.',
     'Somme des élèves retournés',
     'Effectif élèves retournés',
     3, 4, 6, 1, 'line,bar,scatter', 'actif'),
(7,  'migrants-objets-trafic',
     'Migrants objets de trafic',
     'Nombre de migrants identifiés comme victimes de trafic illicite de migrants au Niger.',
     'Cumul du nombre de migrants objets de trafic',
     'Nombre de migrants objets de trafic',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(8,  'auteurs-deferes-trafic-illicite',
     'Auteurs déférés pour trafic illicite de migrants',
     'Nombre de personnes déférées devant la justice pour trafic illicite de migrants.',
     'Cumul du nombre des auteurs déférés pour trafic illicite de migrants',
     'Nombre des auteurs déférés pour trafic illicite de migrants',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(9,  'auteurs-juges-trafic-illicite',
     'Auteurs jugés pour trafic illicite de migrants',
     'Nombre de personnes ayant été jugées pour trafic illicite de migrants.',
     'Cumul du nombre des auteurs jugés pour trafic illicite de migrants',
     'Nombre des auteurs jugés pour trafic illicite de migrants',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(10, 'taux-condamnation-trafic-illicite',
     'Taux de condamnation pour trafic illicite de migrants',
     'Ratio entre le nombre de personnes condamnées et le nombre total de personnes jugées pour trafic illicite.',
     'Personnes condamnées / Effectif total de personnes jugées × 100',
     'Personnes condamnées et effectif total de personnes jugées',
     4, 5, 2, 1, 'line,bar', 'actif'),
(11, 'taux-poursuite-penale-trafic',
     'Taux de poursuite pénale pour trafic illicite de migrants',
     'Ratio entre le nombre d\'auteurs poursuivis et le nombre d\'auteurs déférés pour trafic illicite.',
     'Auteurs poursuivis / Auteurs déférés × 100',
     'Auteurs poursuivis et auteurs déférés',
     4, 5, 2, 1, 'line,bar', 'actif'),
(12, 'demandes-visas-travailleurs-etrangers',
     'Nombre de demandes de visas des travailleurs étrangers',
     'Nombre total de demandes de visa de travail introduites auprès des services compétents du Niger.',
     'Somme des demandes de visas des travailleurs étrangers',
     'Demandes de visas des travailleurs étrangers',
     8, 1, 4, 1, 'line,bar,scatter', 'actif'),
(13, 'flux-entrant-migrants',
     'Flux entrant de migrants',
     'Nombre de migrants entrant sur le territoire nigérien enregistrés aux postes de contrôle.',
     'Somme de tous les migrants entrant',
     'Migrants entrant sur le territoire nigérien',
     9, 3, 1, 1, 'line,bar,scatter', 'actif'),
(14, 'flux-sortant-migrants',
     'Flux sortant de migrants',
     'Nombre de migrants quittant le territoire nigérien enregistrés aux postes de contrôle.',
     'Somme de tous les migrants sortant',
     'Migrants sortant du territoire nigérien',
     9, 3, 1, 1, 'line,bar,scatter', 'actif'),
(15, 'migrants-postes-controle-dsp',
     'Nombre de migrants au niveau des postes de contrôle gérés par la DSP',
     'Nombre total de migrants enregistrés aux postes de contrôle gérés par la Direction de la Sécurité Publique.',
     'Somme de tous les migrants enregistrés au niveau des trois postes de contrôle',
     'Nombre de migrants enregistrés au niveau des postes de contrôle',
     5, 3, 1, 1, 'line,bar,scatter', 'actif'),
(16, 'migrants-enregistres-interne',
     'Nombre des migrants enregistrés à l\'interne',
     'Nombre de migrants enregistrés à l\'intérieur du pays par les services compétents.',
     'Somme de tous les migrants enregistrés à l\'intérieur du pays',
     'Nombre de migrants enregistrés à l\'intérieur du pays',
     10, 3, 1, 1, 'line,bar,scatter', 'actif'),
(17, 'personnes-sensibilisation-migration',
     'Nombre de personnes touchées par les séances de sensibilisation sur la migration irrégulière',
     'Nombre cumulé de personnes ayant participé à des séances de sensibilisation sur les risques de la migration irrégulière.',
     'Somme de toutes les personnes touchées par les séances de sensibilisation',
     'Nombre de personnes touchées par les séances de sensibilisation',
     10, 3, 1, 1, 'line,bar,scatter', 'actif'),
(18, 'demandeurs-asile-enregistres',
     'Nombre de demandeurs d\'asile enregistrés',
     'Nombre total de personnes ayant déposé une demande de reconnaissance du statut de réfugié au Niger.',
     'Somme de tous les demandeurs d\'asile enregistrés',
     'Nombre de demandeurs d\'asile enregistrés dans le pays',
     10, 3, 1, 1, 'line,bar,scatter', 'actif'),
(19, 'population-etrangere-stock',
     'Effectif de la population étrangère',
     'Nombre total de personnes de nationalité étrangère résidant au Niger.',
     'Comptage des personnes se trouvant dans cette situation',
     'Comptage des personnes de nationalité étrangère',
     11, 2, 1, 1, 'line,bar,scatter', 'actif'),
(20, 'migrants-age-travailler',
     'Effectif des migrants internationaux en âge de travailler',
     'Nombre de migrants internationaux présents au Niger dont l\'âge est compris dans la tranche d\'activité (15 ans et plus).',
     'Comptage des personnes se trouvant dans cette situation',
     'Comptage des migrants internationaux en âge de travailler',
     11, 2, 1, 1, 'line,bar,scatter', 'actif'),
(21, 'flux-migratoire-total',
     'Flux migratoire total',
     'Somme des flux entrants et sortants de migrants sur le territoire nigérien.',
     'Somme des entrants et des sortants',
     'Entrants et sortants',
     11, 3, 1, 1, 'line,bar,scatter', 'actif'),
(22, 'indice-sortie',
     'Indice de sortie',
     'Rapport entre la population sortie d\'une région et la population née dans cette région.',
     'Population sortie de la région / Population née dans la région',
     'Population sortie de la région et population née dans la région',
     11, 3, 2, 1, 'line,bar', 'actif'),
(23, 'indice-entree',
     'Indice d\'entrée',
     'Rapport entre la population entrée dans une région et la population née dans cette région.',
     'Population entrée dans la région / Population née dans la région',
     'Population entrée et population née dans la région',
     11, 3, 2, 1, 'line,bar', 'actif'),
(24, 'solde-migratoire',
     'Nombre de migrants net / Solde migratoire',
     'Différence entre les entrées et les sorties de migrants dans une entité administrative.',
     'Entrées - Sorties dans la même entité administrative',
     'Nombre de personnes entrées et sorties par entité administrative',
     11, 2, 1, 1, 'line,bar', 'actif'),
(25, 'ratio-emploi-migrants-internationaux',
     'Ratio emploi-population des migrants internationaux',
     'Taux d\'emploi des travailleurs migrants internationaux au Niger.',
     'Nombre de travailleurs migrants disposant d\'un emploi / Population totale des travailleurs migrants en âge de travailler × 100',
     'Migrants employés et migrants en âge de travailler',
     11, 1, 2, 1, 'line,bar', 'actif'),
(26, 'stock-travailleurs-migrants-internationaux',
     'Stock de travailleurs migrants internationaux',
     'Nombre total de travailleurs migrants internationaux présents au Niger à un instant donné.',
     'Comptage ou estimation du nombre de travailleurs migrants internationaux présents',
     'Travailleurs migrants internationaux présents dans le pays',
     11, 2, 1, 1, 'line,bar,scatter', 'actif'),
(27, 'taux-migration-internationale',
     'Taux de migration internationale',
     'Proportion de migrants internationaux dans la population totale du Niger.',
     'Nombre de migrants internationaux / Population générale du pays × 100',
     'Migrants internationaux et population totale',
     11, 3, 2, 1, 'line,bar', 'actif'),
(28, 'abandons-efpt-migrations',
     'Nombre d\'abandons de l\'EFPT à la suite des migrations',
     'Nombre d\'élèves ayant abandonné l\'Enseignement et la Formation Professionnels et Techniques en raison de la migration.',
     'Somme des abandons de l\'EFPT à la suite des migrations',
     'Abandons EFPT liés aux migrations',
     12, 1, 6, 1, 'line,bar,scatter', 'actif'),
(29, 'visas-travail-accordes',
     'Nombre de visas de travail accordés aux travailleurs étrangers',
     'Nombre total de visas de travail délivrés à des ressortissants étrangers au Niger.',
     'Somme des visas de travail accordés',
     'Visas de travail accordés aux travailleurs étrangers',
     13, 1, 4, 1, 'line,bar,scatter', 'actif'),
(30, 'transferts-recus-travailleurs-migrants',
     'Transferts reçus des travailleurs migrants (en millions de Francs CFA)',
     'Montant total des fonds envoyés au Niger par les Nigériens résidant à l\'étranger.',
     'Cumul des transferts reçus',
     'Fonds envoyés au Niger par les Nigériens vivant à l\'Étranger',
     14, 6, 3, 1, 'line,bar', 'actif'),
(31, 'transferts-emis-travailleurs-migrants',
     'Transferts émis des travailleurs migrants (en millions de Francs CFA)',
     'Montant total des fonds envoyés à l\'étranger par les migrants résidant au Niger.',
     'Cumul des transferts émis',
     'Fonds envoyés à l\'Extérieur par les migrants résidants au Niger',
     14, 6, 3, 1, 'line,bar', 'actif'),
(32, 'pdi-nombre',
     'Nombre de Personnes Déplacées Internes (PDIs)',
     'Nombre total de personnes ayant été contraintes de quitter leur lieu de résidence habituel à l\'intérieur du Niger.',
     'Cumul des Personnes Déplacées Internes (PDIs)',
     'Effectif des Personnes Déplacées Internes (PDIs)',
     15, 4, 1, 1, 'line,bar,scatter', 'actif'),
(33, 'enfants-migrants-rapatries',
     'Enfants migrants rapatriés/refoulés',
     'Nombre d\'enfants migrants rapatriés ou refoulés au Niger.',
     'Cumul des enfants migrants rapatriés ou refoulés',
     NULL,
     16, 5, 1, 1, 'line,bar,scatter', 'actif'),
(34, 'personnes-formees-migration',
     'Nombre de personnes formées sur la thématique de migration',
     'Nombre de personnes ayant bénéficié d\'une formation sur les enjeux de la migration.',
     'Somme des personnes formées sur la thématique de migration',
     NULL,
     10, 3, 1, 1, 'line,bar,scatter', 'actif'),
(35, 'indice-retention',
     'Indice de rétention',
     'Rapport entre les personnes non migrantes et le total des personnes nées dans une entité administrative.',
     'Personnes non migrantes / Effectif total des personnes nées dans l\'unité administrative',
     'Personnes non migrantes et total des personnes nées dans l\'unité administrative',
     11, 3, 2, 1, 'line,bar', 'actif'),
(36, 'indice-efficacite',
     'Indice d\'efficacité',
     'Différence entre les entrées et les sorties rapportée à la somme des entrées et des sorties.',
     '(Entrées - Sorties) / (Entrées + Sorties)',
     'Entrées et sorties',
     11, 3, 2, 1, 'line,bar', 'actif'),
(37, 'menages-deplaces-internes',
     'Nombre de ménages déplacés internes (PDIs)',
     'Nombre total de ménages déplacés à l\'intérieur du Niger.',
     'Cumul des Ménages Déplacés Internes (PDIs)',
     'Nombre de ménages déplacés internes (PDIs)',
     15, 4, 5, 1, 'line,bar,scatter', 'actif'),
(38, 'auteurs-poursuivis-trafic',
     'Auteurs poursuivis pour trafic illicite de migrants',
     'Nombre de personnes faisant l\'objet de poursuites pénales pour trafic illicite de migrants.',
     'Cumul du nombre des auteurs poursuivis pour trafic illicite de migrants',
     'Nombre des auteurs poursuivis pour trafic illicite de migrants',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(39, 'auteurs-juges-traite-personnes',
     'Auteurs jugés pour traite des personnes',
     'Nombre de personnes jugées pour des faits de traite des personnes.',
     'Cumul des auteurs jugés pour traite des personnes',
     'Nombre des auteurs jugés pour traite des personnes',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(40, 'auteurs-poursuivis-traite',
     'Auteurs poursuivis pour traite des personnes',
     'Nombre de personnes faisant l\'objet de poursuites pénales pour traite des personnes.',
     'Cumul des auteurs poursuivis pour traite des personnes',
     'Nombre des auteurs poursuivis pour traite des personnes',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(41, 'auteurs-deferes-traite',
     'Auteurs déférés pour traite des personnes',
     'Nombre de personnes déférées devant la justice pour traite des personnes.',
     'Cumul des auteurs déférés pour traite des personnes',
     'Nombre d\'auteurs déférés pour traite des personnes',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(42, 'victimes-traite-personnes',
     'Victimes de traite des personnes',
     'Nombre total de victimes identifiées de traite des personnes au Niger.',
     'Cumul du nombre des victimes de traite des personnes',
     'Nombre des victimes de traite des personnes',
     4, 5, 1, 1, 'line,bar,scatter', 'actif'),
(43, 'victimes-traite-centre-zinder',
     'Victimes de traite enregistrées au centre d\'accueil de Zinder',
     'Nombre de victimes enregistrées au Centre d\'Accueil et de Protection des Victimes de Traite de Zinder.',
     'Cumul du nombre de victimes enregistrées au centre de Zinder',
     'Victimes de traite enregistrées au centre d\'accueil et de protection des victimes de Zinder',
     4, 5, 1, 1, 'line,bar,scatter', 'actif');

-- ============================================================
-- DOSSIER AGADEZ
-- ============================================================
INSERT IGNORE INTO `dossiers` (`slug`,`titre_fr`,`titre_en`,`sous_titre_fr`,`sous_titre_en`,`powerbi_titre`,`statut`,`ordre`) VALUES
('agadez',
 'Agadez, carrefour migratoire',
 'Agadez, Migration Crossroads',
 'Données et analyses sur les flux migratoires de la région d\'Agadez et du corridor Tamanrasset-Assamaka-Agadez',
 'Data and analysis on migration flows in the Agadez region and the Tamanrasset-Assamaka-Agadez corridor',
 'Tableau de bord Agadez — Power BI',
 'publie', 1);

-- ============================================================
-- PARAMÈTRES DU SITE
-- ============================================================
INSERT IGNORE INTO `parametres` (`cle`,`valeur`,`type`) VALUES
('site_nom',            'Plateforme Nationale des Données sur la Migration',   'string'),
('site_nom_court',      'PNDM',                                                'string'),
('site_url',            'https://stat-niger.org/migrations',                   'string'),
('site_email',          'pndm@ins.niger.ne',                                   'string'),
('site_langue_defaut',  'fr',                                                  'string'),
('admin_email',         'admin@ins.niger.ne',                                  'string'),
('powerbi_agadez_url',  '',                                                    'string'),
('google_analytics_id', '',                                                    'string'),
('newsletter_active',   '1',                                                   'boolean'),
('api_rate_limit_anon', '60',                                                  'integer'),
('api_rate_limit_key',  '600',                                                 'integer'),
('maintenance_mode',    '0',                                                   'boolean');

-- ============================================================
-- SUPER ADMIN PAR DÉFAUT (mot de passe: ChangeMe123!)
-- Hash bcrypt cost 12 — à changer immédiatement
-- ============================================================
INSERT IGNORE INTO `users` (`nom`,`prenom`,`email`,`password_hash`,`role_id`,`actif`) VALUES
('Administrateur','PNDM','admin@pndm.ne',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 1, 1);
