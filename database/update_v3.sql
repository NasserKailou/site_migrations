-- ============================================================
-- PNDM — Mise à jour données v3
-- Session 3 — 2026-05-18
-- ============================================================
-- Ce script met à jour :
--   1. Power BI URLs réelles pour le dossier Agadez
--   2. Utilisateurs par rôle (super_admin, admin, validateur, point_focal, lecteur)
--   3. Paramètres système
-- ============================================================

-- ── 1. POWER BI URLS AGADEZ ──────────────────────────────────
-- Format : URLs séparées par | pour multi-onglets
-- Remplacez ces URLs par vos liens réels Power BI Service
-- (depuis Power BI Service → Partager → Incorporer un rapport)
-- Exemple format: https://app.powerbi.com/reportEmbed?reportId=XXXXX&autoAuth=true&ctid=YYYYY

UPDATE `dossiers`
SET `powerbi_url` = CONCAT(
  -- Onglet 1 : Flux migratoires
  'https://app.powerbi.com/reportEmbed?reportId=FLUX_MIGRATOIRES_ID&autoAuth=true&ctid=INS_TENANT_ID',
  '|',
  -- Onglet 2 : Refoulements & Retours volontaires
  'https://app.powerbi.com/reportEmbed?reportId=REFOULEMENTS_ID&autoAuth=true&ctid=INS_TENANT_ID',
  '|',
  -- Onglet 3 : Données socio-démographiques
  'https://app.powerbi.com/reportEmbed?reportId=SOCIODEM_ID&autoAuth=true&ctid=INS_TENANT_ID',
  '|',
  -- Onglet 4 : Tendances annuelles
  'https://app.powerbi.com/reportEmbed?reportId=TENDANCES_ID&autoAuth=true&ctid=INS_TENANT_ID'
)
WHERE `slug` = 'agadez';

-- Mise à jour du paramètre PowerBI (pour compatibilité ancien système)
UPDATE `parametres`
SET `valeur` = 'https://app.powerbi.com/reportEmbed?reportId=FLUX_MIGRATOIRES_ID&autoAuth=true&ctid=INS_TENANT_ID'
WHERE `cle` = 'powerbi_agadez_url';

-- ── INSTRUCTIONS POUR CONFIGURER LES VRAIES URLS ─────────────
-- 1. Connectez-vous sur https://app.powerbi.com
-- 2. Ouvrez votre rapport → Fichier → Incorporer le rapport → Site web ou portail
-- 3. Copiez le src de l'iframe (commence par https://app.powerbi.com/reportEmbed?...)
-- 4. Remplacez les valeurs FLUX_MIGRATOIRES_ID, INS_TENANT_ID ci-dessus
-- 5. Ou utilisez l'interface admin : /admin/dossiers → Agadez → modifier URL Power BI

-- Alternative via interface admin (sans SQL) :
-- Allez dans Admin → Dossiers → Agadez → Modifier
-- Dans le champ "URL Power BI", collez vos URLs séparées par |
-- Exemple : https://app.powerbi.com/reportEmbed?reportId=AAA|https://app.powerbi.com/reportEmbed?reportId=BBB

-- ── 2. UTILISATEURS PAR RÔLE ─────────────────────────────────
-- Rôles : 1=super_admin, 2=admin, 3=validateur, 4=point_focal, 5=lecteur
-- Mots de passe : voir ci-dessous

-- super_admin : admin@pndm.ne / SuperAdmin2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'Kailou Assoumane', 'Abdoul-Nasser',
  'akailou@ins.ne',
  '$2y$12$nC1W4mNcA6pfteDC/SM3pugEkRNAkPot1M.WPY1Wmwhkd/k4uaE76',
  1, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$nC1W4mNcA6pfteDC/SM3pugEkRNAkPot1M.WPY1Wmwhkd/k4uaE76',
  `role_id` = 1,
  `actif` = 1;

-- admin : ibabdoulaye@ins.ne / Admin2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'IDRISSA BOUKARY', 'Abdoulaye',
  'ibabdoulaye@ins.ne',
  '$2y$12$/X/6EsvrmkI40NRNDNQ9FuJ14b0EAsLsVj0VhzKfx6FvwoCD1NQle',
  2, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$/X/6EsvrmkI40NRNDNQ9FuJ14b0EAsLsVj0VhzKfx6FvwoCD1NQle',
  `role_id` = 2,
  `actif` = 1;

-- validateur : sharo@ins.ne / Validateur2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'HARO', 'Souleymane',
  'sharo@ins.ne',
  '$2y$12$nPxGWp69bkUYIS6wcPZCXuzyE8VI13/arPFi5ChxdyiKHoRq.AvO2',
  3, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$nPxGWp69bkUYIS6wcPZCXuzyE8VI13/arPFi5ChxdyiKHoRq.AvO2',
  `role_id` = 3,
  `actif` = 1;

-- point_focal : youssoufa@ins.ne / PointFocal2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'Ousseini Youssoufa', 'Lamou',
  'youssoufa@ins.ne',
  '$2y$12$9OB2Lo62VdR/ToAye3w6pO4UTOtRzHG5lfDtFadDqXpuk3hIRlBcC',
  4, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$9OB2Lo62VdR/ToAye3w6pO4UTOtRzHG5lfDtFadDqXpuk3hIRlBcC',
  `role_id` = 4,
  `actif` = 1;

-- point_focal 2 : abachirou@ins.ne / PointFocal2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'Bachirou Seydou', 'Abdoul Karim',
  'abachirou@ins.ne',
  '$2y$12$9OB2Lo62VdR/ToAye3w6pO4UTOtRzHG5lfDtFadDqXpuk3hIRlBcC',
  4, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$9OB2Lo62VdR/ToAye3w6pO4UTOtRzHG5lfDtFadDqXpuk3hIRlBcC',
  `role_id` = 4,
  `actif` = 1;

-- lecteur : lecteur@pndm.ne / Lecteur2024!
INSERT INTO `users` (`nom`, `prenom`, `email`, `password_hash`, `role_id`, `actif`, `created_at`)
VALUES (
  'Lecteur', 'PNDM',
  'lecteur@pndm.ne',
  '$2y$12$0IxT4z1o6VcX5kgNO7m2KeakchAhXReYLOgHnyxFJteMpQqjfYrYe',
  5, 1, NOW()
)
ON DUPLICATE KEY UPDATE
  `password_hash` = '$2y$12$0IxT4z1o6VcX5kgNO7m2KeakchAhXReYLOgHnyxFJteMpQqjfYrYe',
  `role_id` = 5,
  `actif` = 1;

-- Mise à jour du compte admin générique (conserver pour compatibilité)
UPDATE `users`
SET
  `email`         = 'admin@pndm.ne',
  `password_hash` = '$2y$12$nC1W4mNcA6pfteDC/SM3pugEkRNAkPot1M.WPY1Wmwhkd/k4uaE76',
  `role_id`       = 1,
  `actif`         = 1
WHERE `email` IN ('admin@pndm.ne', 'Administrateur');

-- ── 3. PARAMÈTRES SYSTÈME ─────────────────────────────────────
INSERT INTO `parametres` (`cle`, `valeur`, `type`) VALUES
('site_version',  '3.0', 'string'),
('updated_at_v3', '2026-05-18', 'string')
ON DUPLICATE KEY UPDATE `valeur` = VALUES(`valeur`);

-- ── RÉCAPITULATIF IDENTIFIANTS ─────────────────────────────────
-- ┌─────────────────────────────────┬──────────────────────────────────┬─────────────────┐
-- │ Email                           │ Mot de passe                     │ Rôle            │
-- ├─────────────────────────────────┼──────────────────────────────────┼─────────────────┤
-- │ admin@pndm.ne                   │ SuperAdmin2024!                  │ super_admin     │
-- │ akailou@ins.ne                  │ SuperAdmin2024!                  │ super_admin     │
-- │ ibabdoulaye@ins.ne              │ Admin2024!                       │ admin           │
-- │ sharo@ins.ne                    │ Validateur2024!                  │ validateur      │
-- │ youssoufa@ins.ne                │ PointFocal2024!                  │ point_focal     │
-- │ abachirou@ins.ne                │ PointFocal2024!                  │ point_focal     │
-- │ lecteur@pndm.ne                 │ Lecteur2024!                     │ lecteur         │
-- └─────────────────────────────────┴──────────────────────────────────┴─────────────────┘
