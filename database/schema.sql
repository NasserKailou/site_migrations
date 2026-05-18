-- ============================================================
-- PLATEFORME NATIONALE DES DONNÉES SUR LA MIGRATION (PNDM)
-- Institut National de la Statistique - République du Niger
-- Schema MySQL 8 — Version 1.0 — 2026-05-18
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- Base de données
CREATE DATABASE IF NOT EXISTS `pndm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pndm`;

-- ============================================================
-- TABLES DE RÉFÉRENCE / DIMENSIONS
-- ============================================================

-- Thématiques (7 grandes familles)
CREATE TABLE `thematiques` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(100) NOT NULL,
  `libelle_fr`  VARCHAR(200) NOT NULL,
  `libelle_en`  VARCHAR(200) DEFAULT NULL,
  `description_fr` TEXT DEFAULT NULL,
  `description_en` TEXT DEFAULT NULL,
  `icone`       VARCHAR(100) DEFAULT NULL COMMENT 'Nom icône Font Awesome ou SVG',
  `couleur`     VARCHAR(20) DEFAULT '#005B9A',
  `ordre`       TINYINT UNSIGNED DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_thematiques_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Entités productrices de données (sources)
CREATE TABLE `entites` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`     VARCHAR(300) NOT NULL,
  `acronyme`    VARCHAR(80) NOT NULL,
  `ministere`   VARCHAR(300) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `site_web`    VARCHAR(255) DEFAULT NULL,
  `email`       VARCHAR(150) DEFAULT NULL,
  `telephone`   VARCHAR(50) DEFAULT NULL,
  `actif`       TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_entites_acronyme` (`acronyme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Niveaux de désagrégation (Région, National, Nationalité, etc.)
CREATE TABLE `niveaux_desagregation` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`     VARCHAR(150) NOT NULL,
  `libelle_en`  VARCHAR(150) DEFAULT NULL,
  `type`        ENUM('geo','demo','eco','autre') DEFAULT 'autre',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Valeurs possibles par niveau (ex: Agadez, Diffa... pour Région)
CREATE TABLE `niveau_valeurs` (
  `id`                      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `niveau_desagregation_id` INT UNSIGNED NOT NULL,
  `valeur`                  VARCHAR(200) NOT NULL,
  `code`                    VARCHAR(20) DEFAULT NULL COMMENT 'Code ISO ou code interne',
  `ordre`                   SMALLINT UNSIGNED DEFAULT 0,
  `created_at`              TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_nv_niveau` (`niveau_desagregation_id`),
  CONSTRAINT `fk_nv_niveau` FOREIGN KEY (`niveau_desagregation_id`)
    REFERENCES `niveaux_desagregation`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fréquences de collecte
CREATE TABLE `frequences` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`    VARCHAR(50) NOT NULL,
  `libelle_en` VARCHAR(50) DEFAULT NULL,
  `code`       VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Unités de mesure
CREATE TABLE `unites` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`     VARCHAR(100) NOT NULL,
  `libelle_en`  VARCHAR(100) DEFAULT NULL,
  `symbole`     VARCHAR(20) DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INDICATEURS
-- ============================================================

CREATE TABLE `indicateurs` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(200) NOT NULL COMMENT 'URL-friendly, ex: transferts-recus-migrants',
  `libelle_fr`      VARCHAR(500) NOT NULL,
  `libelle_en`      VARCHAR(500) DEFAULT NULL,
  `definition_fr`   TEXT DEFAULT NULL,
  `definition_en`   TEXT DEFAULT NULL,
  `methode_calcul`  TEXT DEFAULT NULL,
  `donnees_requises` TEXT DEFAULT NULL,
  `source_details`  TEXT DEFAULT NULL COMMENT 'Détails textuels de la source',
  `entite_id`       INT UNSIGNED NOT NULL COMMENT 'Source principale (entité productrice)',
  `thematique_id`   INT UNSIGNED NOT NULL,
  `unite_id`        INT UNSIGNED DEFAULT NULL,
  `frequence_id`    INT UNSIGNED DEFAULT NULL,
  `type_graphes`    VARCHAR(100) DEFAULT 'line,bar' COMMENT 'Types de graphiques disponibles',
  `statut`          ENUM('actif','archive','brouillon') DEFAULT 'actif',
  `prochaine_maj`   DATE DEFAULT NULL,
  `contact_nom`     VARCHAR(200) DEFAULT NULL,
  `contact_email`   VARCHAR(150) DEFAULT NULL,
  `licence`         VARCHAR(100) DEFAULT 'Open Data Commons',
  `notes`           TEXT DEFAULT NULL,
  `ordre`           SMALLINT UNSIGNED DEFAULT 0,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_indicateurs_slug` (`slug`),
  KEY `idx_ind_entite` (`entite_id`),
  KEY `idx_ind_thematique` (`thematique_id`),
  KEY `idx_ind_statut` (`statut`),
  CONSTRAINT `fk_ind_entite` FOREIGN KEY (`entite_id`) REFERENCES `entites`(`id`),
  CONSTRAINT `fk_ind_thematique` FOREIGN KEY (`thematique_id`) REFERENCES `thematiques`(`id`),
  CONSTRAINT `fk_ind_unite` FOREIGN KEY (`unite_id`) REFERENCES `unites`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ind_frequence` FOREIGN KEY (`frequence_id`) REFERENCES `frequences`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Niveaux de désagrégation disponibles par indicateur
CREATE TABLE `indicateur_niveaux` (
  `id`                        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `indicateur_id`             INT UNSIGNED NOT NULL,
  `niveau_desagregation_id`   INT UNSIGNED NOT NULL,
  `niveau_desagregation2_id`  INT UNSIGNED DEFAULT NULL,
  `niveau_desagregation3_id`  INT UNSIGNED DEFAULT NULL,
  `avec_ventilation_sexe`     TINYINT(1) DEFAULT 0,
  `frequence_id`              INT UNSIGNED DEFAULT NULL,
  `created_at`                TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_in_indicateur` (`indicateur_id`),
  KEY `idx_in_niveau` (`niveau_desagregation_id`),
  CONSTRAINT `fk_in_indicateur` FOREIGN KEY (`indicateur_id`) REFERENCES `indicateurs`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_in_niveau` FOREIGN KEY (`niveau_desagregation_id`) REFERENCES `niveaux_desagregation`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ENTITÉS GÉOGRAPHIQUES / ADMINISTRATIVES
-- ============================================================

CREATE TABLE `geo_entites` (
  `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`   VARCHAR(200) NOT NULL,
  `code`      VARCHAR(20) DEFAULT NULL,
  `type`      ENUM('national','region','departement','commune') NOT NULL DEFAULT 'region',
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `lat`       DECIMAL(10,6) DEFAULT NULL,
  `lng`       DECIMAL(10,6) DEFAULT NULL,
  `geojson`   MEDIUMTEXT DEFAULT NULL COMMENT 'Contour GeoJSON de la région',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_geo_parent` (`parent_id`),
  KEY `idx_geo_type` (`type`),
  CONSTRAINT `fk_geo_parent` FOREIGN KEY (`parent_id`) REFERENCES `geo_entites`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- UTILISATEURS & RÔLES (ADMIN)
-- ============================================================

CREATE TABLE `roles` (
  `id`          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `libelle`     VARCHAR(50) NOT NULL,
  `permissions` JSON DEFAULT NULL COMMENT 'Liste des permissions JSON',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nom`                 VARCHAR(100) NOT NULL,
  `prenom`              VARCHAR(100) NOT NULL,
  `email`               VARCHAR(200) NOT NULL,
  `password_hash`       VARCHAR(255) NOT NULL,
  `role_id`             TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1=super_admin,2=admin,3=validateur,4=point_focal,5=lecteur',
  `entite_id`           INT UNSIGNED DEFAULT NULL COMMENT 'Entité rattachée (pour point focal)',
  `totp_secret`         VARCHAR(100) DEFAULT NULL COMMENT 'Secret TOTP chiffré (2FA)',
  `totp_enabled`        TINYINT(1) DEFAULT 0,
  `actif`               TINYINT(1) DEFAULT 1,
  `tentatives_connexion` TINYINT DEFAULT 0,
  `bloque_jusqu_a`      TIMESTAMP NULL DEFAULT NULL,
  `dernier_login`       TIMESTAMP NULL DEFAULT NULL,
  `token_reset`         VARCHAR(100) DEFAULT NULL,
  `token_reset_expire`  TIMESTAMP NULL DEFAULT NULL,
  `api_key`             VARCHAR(100) DEFAULT NULL,
  `api_key_expire`      DATE DEFAULT NULL,
  `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  UNIQUE KEY `uk_users_api_key` (`api_key`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_entite` (`entite_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
  CONSTRAINT `fk_users_entite` FOREIGN KEY (`entite_id`) REFERENCES `entites`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES PRINCIPALES
-- ============================================================

CREATE TABLE `observations` (
  `id`                          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Clés de dimension
  `indicateur_id`               INT UNSIGNED NOT NULL,
  `geo_entite_id`               INT UNSIGNED DEFAULT NULL COMMENT 'Entité géographique si applicable',
  `niveau_desagregation_id`     INT UNSIGNED DEFAULT NULL,
  `niveau_desagregation2_id`    INT UNSIGNED DEFAULT NULL,
  `niveau_desagregation3_id`    INT UNSIGNED DEFAULT NULL,
  `niveau_desag_valeur`         VARCHAR(250) DEFAULT NULL,
  `niveau_desag_valeur2`        VARCHAR(250) DEFAULT NULL,
  `niveau_desag_valeur3`        VARCHAR(250) DEFAULT NULL,
  -- Période
  `periode_debut`               DATE NOT NULL,
  `periode_fin`                 DATE DEFAULT NULL,
  `periodicite`                 VARCHAR(20) DEFAULT 'annuelle',
  -- Valeurs
  `masculin`                    DECIMAL(18,4) DEFAULT NULL,
  `feminin`                     DECIMAL(18,4) DEFAULT NULL,
  `trans_autre`                 DECIMAL(18,4) DEFAULT NULL,
  `total`                       DECIMAL(18,4) DEFAULT NULL,
  `valeur_categorie`            VARCHAR(255) DEFAULT NULL COMMENT 'Pour valeurs non numériques',
  -- Métadonnées éditoriales
  `statut`                      ENUM('brouillon','soumis','valide','publie','rejete') DEFAULT 'brouillon',
  `commentaire_interne`         TEXT DEFAULT NULL,
  `commentaire_rejet`           TEXT DEFAULT NULL,
  `document_source_path`        VARCHAR(500) DEFAULT NULL,
  `document_source_nom`         VARCHAR(255) DEFAULT NULL,
  -- Traçabilité
  `created_by`                  INT UNSIGNED DEFAULT NULL,
  `updated_by`                  INT UNSIGNED DEFAULT NULL,
  `valide_par`                  INT UNSIGNED DEFAULT NULL,
  `valide_le`                   TIMESTAMP NULL DEFAULT NULL,
  `publie_par`                  INT UNSIGNED DEFAULT NULL,
  `publie_le`                   TIMESTAMP NULL DEFAULT NULL,
  `created_at`                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- Contrainte d'unicité pour éviter les doublons
  UNIQUE KEY `uk_obs_unique` (
    `indicateur_id`, `geo_entite_id`, `niveau_desagregation_id`,
    `niveau_desag_valeur`, `periode_debut`
  ),
  KEY `idx_obs_indicateur` (`indicateur_id`),
  KEY `idx_obs_geo` (`geo_entite_id`),
  KEY `idx_obs_statut` (`statut`),
  KEY `idx_obs_periode` (`periode_debut`),
  KEY `idx_obs_created_by` (`created_by`),
  CONSTRAINT `fk_obs_indicateur` FOREIGN KEY (`indicateur_id`) REFERENCES `indicateurs`(`id`),
  CONSTRAINT `fk_obs_geo` FOREIGN KEY (`geo_entite_id`) REFERENCES `geo_entites`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_obs_niveau` FOREIGN KEY (`niveau_desagregation_id`) REFERENCES `niveaux_desagregation`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_obs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_obs_valide_par` FOREIGN KEY (`valide_par`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_obs_publie_par` FOREIGN KEY (`publie_par`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DOSSIERS THÉMATIQUES (ex: Page Agadez)
-- ============================================================

CREATE TABLE `dossiers` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`            VARCHAR(100) NOT NULL,
  `titre_fr`        VARCHAR(300) NOT NULL,
  `titre_en`        VARCHAR(300) DEFAULT NULL,
  `sous_titre_fr`   VARCHAR(500) DEFAULT NULL,
  `sous_titre_en`   VARCHAR(500) DEFAULT NULL,
  `contenu_fr`      LONGTEXT DEFAULT NULL,
  `contenu_en`      LONGTEXT DEFAULT NULL,
  `image_hero`      VARCHAR(500) DEFAULT NULL,
  `powerbi_url`     VARCHAR(1000) DEFAULT NULL COMMENT 'URL iframe Power BI éditable',
  `powerbi_titre`   VARCHAR(200) DEFAULT NULL,
  `statut`          ENUM('brouillon','publie') DEFAULT 'brouillon',
  `ordre`           TINYINT UNSIGNED DEFAULT 0,
  `meta_title_fr`   VARCHAR(60) DEFAULT NULL,
  `meta_desc_fr`    VARCHAR(160) DEFAULT NULL,
  `meta_title_en`   VARCHAR(60) DEFAULT NULL,
  `meta_desc_en`    VARCHAR(160) DEFAULT NULL,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dossiers_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Documents liés aux dossiers (PDFs téléchargeables)
CREATE TABLE `dossier_documents` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `dossier_id`  INT UNSIGNED NOT NULL,
  `titre`       VARCHAR(300) NOT NULL,
  `fichier`     VARCHAR(500) NOT NULL,
  `taille`      INT UNSIGNED DEFAULT NULL,
  `type_mime`   VARCHAR(100) DEFAULT NULL,
  `ordre`       TINYINT UNSIGNED DEFAULT 0,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dd_dossier` (`dossier_id`),
  CONSTRAINT `fk_dd_dossier` FOREIGN KEY (`dossier_id`) REFERENCES `dossiers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOG
-- ============================================================

CREATE TABLE `audit_log` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(100) NOT NULL COMMENT 'login, logout, create_obs, validate_obs, etc.',
  `table_cible` VARCHAR(50) DEFAULT NULL,
  `record_id`   BIGINT UNSIGNED DEFAULT NULL,
  `details`     JSON DEFAULT NULL COMMENT 'Snapshot avant/après',
  `ip`          VARCHAR(45) DEFAULT NULL,
  `user_agent`  VARCHAR(500) DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_user` (`user_id`),
  KEY `idx_al_action` (`action`),
  KEY `idx_al_created` (`created_at`),
  CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- RATE LIMITING
-- ============================================================

CREATE TABLE `rate_limits` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cle`         VARCHAR(150) NOT NULL COMMENT 'IP ou api_key:endpoint',
  `compteur`    INT UNSIGNED DEFAULT 1,
  `fenetre_fin` TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rl_cle` (`cle`),
  KEY `idx_rl_fenetre` (`fenetre_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEWSLETTER
-- ============================================================

CREATE TABLE `newsletter_abonnes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(200) NOT NULL,
  `langue`      CHAR(2) DEFAULT 'fr',
  `token_unsub` VARCHAR(64) DEFAULT NULL,
  `actif`       TINYINT(1) DEFAULT 1,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STATISTIQUES DE TÉLÉCHARGEMENT
-- ============================================================

CREATE TABLE `telechargements` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `indicateur_id` INT UNSIGNED DEFAULT NULL,
  `format`        VARCHAR(10) DEFAULT NULL COMMENT 'csv, xlsx, json',
  `ip`            VARCHAR(45) DEFAULT NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dl_indicateur` (`indicateur_id`),
  KEY `idx_dl_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PARAMÈTRES DU SITE
-- ============================================================

CREATE TABLE `parametres` (
  `cle`     VARCHAR(100) NOT NULL,
  `valeur`  TEXT DEFAULT NULL,
  `type`    VARCHAR(20) DEFAULT 'string',
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
