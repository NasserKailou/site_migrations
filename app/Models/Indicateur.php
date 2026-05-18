<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database as DB;

/** Modèle Indicateur */
class Indicateur
{
    public static function all(array $filters = []): array
    {
        $where  = ["i.statut = 'actif'"];
        $params = [];

        if (!empty($filters['thematique_id'])) {
            $where[]        = 'i.thematique_id = :tid';
            $params[':tid'] = (int) $filters['thematique_id'];
        }
        if (!empty($filters['entite_id'])) {
            $where[]        = 'i.entite_id = :eid';
            $params[':eid'] = (int) $filters['entite_id'];
        }
        if (!empty($filters['q'])) {
            $where[]        = '(i.libelle_fr LIKE :q OR i.definition_fr LIKE :q2)';
            $params[':q']   = '%' . $filters['q'] . '%';
            $params[':q2']  = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['frequence_id'])) {
            $where[]        = 'i.frequence_id = :fid';
            $params[':fid'] = (int) $filters['frequence_id'];
        }

        $whereStr = implode(' AND ', $where);
        $limit    = min((int)($filters['limit'] ?? 20), 100);
        $offset   = (int)($filters['offset'] ?? 0);
        $order    = in_array($filters['sort'] ?? '', ['libelle_fr','updated_at','ordre'])
                  ? $filters['sort'] : 'i.ordre, i.libelle_fr';

        return DB::query(
            "SELECT i.id, i.slug, i.libelle_fr, i.libelle_en, i.definition_fr, i.type_graphes,
                    i.prochaine_maj, i.updated_at,
                    t.libelle_fr AS thematique, t.slug AS thematique_slug,
                    t.couleur AS thematique_couleur, t.icone AS thematique_icone,
                    e.acronyme AS source, u.symbole AS unite_symbole,
                    (SELECT MAX(o.periode_debut) FROM observations o
                     WHERE o.indicateur_id = i.id AND o.statut='publie') AS derniere_date,
                    (SELECT o.total FROM observations o
                     WHERE o.indicateur_id = i.id AND o.statut='publie'
                     ORDER BY o.periode_debut DESC LIMIT 1) AS derniere_valeur
             FROM indicateurs i
             JOIN thematiques t ON t.id = i.thematique_id
             JOIN entites e ON e.id = i.entite_id
             LEFT JOIN unites u ON u.id = i.unite_id
             WHERE {$whereStr}
             ORDER BY {$order}
             LIMIT :lim OFFSET :off",
            array_merge($params, [':lim' => $limit, ':off' => $offset])
        );
    }

    public static function count(array $filters = []): int
    {
        $where  = ["i.statut = 'actif'"];
        $params = [];
        if (!empty($filters['thematique_id'])) {
            $where[]        = 'i.thematique_id = :tid';
            $params[':tid'] = (int) $filters['thematique_id'];
        }
        if (!empty($filters['entite_id'])) {
            $where[]        = 'i.entite_id = :eid';
            $params[':eid'] = (int) $filters['entite_id'];
        }
        if (!empty($filters['q'])) {
            $where[]        = '(i.libelle_fr LIKE :q OR i.definition_fr LIKE :q2)';
            $params[':q']   = '%' . $filters['q'] . '%';
            $params[':q2']  = '%' . $filters['q'] . '%';
        }
        $whereStr = implode(' AND ', $where);
        return DB::count("SELECT COUNT(*) FROM indicateurs i WHERE {$whereStr}", $params);
    }

    public static function findBySlug(string $slug): array|false
    {
        return DB::queryOne(
            "SELECT i.*, t.libelle_fr AS thematique, t.slug AS thematique_slug, t.couleur AS thematique_couleur,
                    t.icone AS thematique_icone, t.description_fr AS thematique_desc,
                    e.libelle AS source_libelle, e.acronyme AS source_acronyme, e.email AS source_email,
                    u.libelle AS unite_libelle, u.symbole AS unite_symbole,
                    f.libelle AS frequence_libelle
             FROM indicateurs i
             JOIN thematiques t ON t.id = i.thematique_id
             JOIN entites e ON e.id = i.entite_id
             LEFT JOIN unites u ON u.id = i.unite_id
             LEFT JOIN frequences f ON f.id = i.frequence_id
             WHERE i.slug = :slug AND i.statut = 'actif'",
            [':slug' => $slug]
        );
    }

    public static function findById(int $id): array|false
    {
        return DB::queryOne(
            "SELECT i.*, t.libelle_fr AS thematique, e.acronyme AS source_acronyme,
                    u.symbole AS unite_symbole, f.libelle AS frequence_libelle
             FROM indicateurs i
             JOIN thematiques t ON t.id = i.thematique_id
             JOIN entites e ON e.id = i.entite_id
             LEFT JOIN unites u ON u.id = i.unite_id
             LEFT JOIN frequences f ON f.id = i.frequence_id
             WHERE i.id = :id",
            [':id' => $id]
        );
    }

    /** Données pour sparkline (5 dernières valeurs publiées) */
    public static function sparkline(int $id): array
    {
        return DB::query(
            "SELECT YEAR(periode_debut) AS annee, total, masculin, feminin
             FROM observations
             WHERE indicateur_id = :id AND statut = 'publie' AND total IS NOT NULL
             AND niveau_desagregation_id IS NULL
             ORDER BY periode_debut DESC
             LIMIT 7",
            [':id' => $id]
        );
    }

    /** Données complètes pour graphique */
    public static function donnees(int $id, array $filters = []): array
    {
        $where  = ['o.indicateur_id = :id', "o.statut = 'publie'"];
        $params = [':id' => $id];

        if (!empty($filters['niveau_id'])) {
            $where[]            = 'o.niveau_desagregation_id = :niv';
            $params[':niv']     = (int) $filters['niveau_id'];
        }
        if (!empty($filters['valeur'])) {
            $where[]            = 'o.niveau_desag_valeur = :val';
            $params[':val']     = $filters['valeur'];
        }
        if (!empty($filters['year_start'])) {
            $where[]            = 'YEAR(o.periode_debut) >= :ys';
            $params[':ys']      = (int) $filters['year_start'];
        }
        if (!empty($filters['year_end'])) {
            $where[]            = 'YEAR(o.periode_debut) <= :ye';
            $params[':ye']      = (int) $filters['year_end'];
        }
        if (!empty($filters['geo_id'])) {
            $where[]            = 'o.geo_entite_id = :geo';
            $params[':geo']     = (int) $filters['geo_id'];
        }

        $whereStr = implode(' AND ', $where);

        return DB::query(
            "SELECT o.periode_debut, YEAR(o.periode_debut) AS annee,
                    o.masculin, o.feminin, o.trans_autre, o.total,
                    o.niveau_desag_valeur, o.niveau_desag_valeur2,
                    g.libelle AS geo_region, n.libelle AS niveau_libelle
             FROM observations o
             LEFT JOIN geo_entites g ON g.id = o.geo_entite_id
             LEFT JOIN niveaux_desagregation n ON n.id = o.niveau_desagregation_id
             WHERE {$whereStr}
             ORDER BY o.periode_debut ASC, o.niveau_desag_valeur ASC",
            $params
        );
    }

    public static function niveauxDisponibles(int $id): array
    {
        return DB::query(
            "SELECT DISTINCT n.id, n.libelle
             FROM indicateur_niveaux ind
             JOIN niveaux_desagregation n ON n.id = ind.niveau_desagregation_id
             WHERE ind.indicateur_id = :id",
            [':id' => $id]
        );
    }

    /** Indicateurs phares pour la home (6 premiers avec données récentes) */
    public static function phares(): array
    {
        return DB::query(
            "SELECT i.id, i.slug, i.libelle_fr,
                    t.libelle_fr AS thematique, t.couleur AS thematique_couleur, t.icone AS thematique_icone,
                    u.symbole AS unite_symbole,
                    (SELECT o.total FROM observations o
                     WHERE o.indicateur_id = i.id AND o.statut='publie' AND o.total IS NOT NULL
                     ORDER BY o.periode_debut DESC LIMIT 1) AS derniere_valeur,
                    (SELECT MAX(YEAR(o.periode_debut)) FROM observations o
                     WHERE o.indicateur_id = i.id AND o.statut='publie') AS derniere_annee
             FROM indicateurs i
             JOIN thematiques t ON t.id = i.thematique_id
             LEFT JOIN unites u ON u.id = i.unite_id
             WHERE i.statut = 'actif'
             ORDER BY i.ordre ASC
             LIMIT 6"
        );
    }

    /** Stats globales pour le bandeau hero */
    public static function stats(): array
    {
        $total_indicateurs = DB::queryOne("SELECT COUNT(*) AS n FROM indicateurs WHERE statut='actif'");
        $total_obs = DB::queryOne("SELECT COUNT(*) AS n FROM observations WHERE statut='publie'");
        $derniere_maj = DB::queryOne("SELECT MAX(updated_at) AS d FROM observations WHERE statut='publie'");
        $total_dl = DB::queryOne("SELECT COUNT(*) AS n FROM telechargements");
        return [
            'indicateurs'  => (int)($total_indicateurs['n'] ?? 0),
            'observations' => (int)($total_obs['n'] ?? 0),
            'derniere_maj' => $derniere_maj['d'] ?? null,
            'telechargements' => (int)($total_dl['n'] ?? 0),
            'regions'      => 8,
        ];
    }

    public static function allAdmin(int $page = 1, int $perPage = 25): array
    {
        $offset = ($page - 1) * $perPage;
        return DB::query(
            "SELECT i.id, i.slug, i.libelle_fr, i.statut, i.updated_at,
                    t.libelle_fr AS thematique, e.acronyme AS source
             FROM indicateurs i
             JOIN thematiques t ON t.id = i.thematique_id
             JOIN entites e ON e.id = i.entite_id
             ORDER BY i.ordre, i.libelle_fr
             LIMIT {$perPage} OFFSET {$offset}"
        );
    }
}
