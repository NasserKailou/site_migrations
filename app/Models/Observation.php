<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database as DB;

/** Modèle Observation (données) */
class Observation
{
    /** Liste paginée pour l'admin avec filtres */
    public static function allAdmin(array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['indicateur_id'])) {
            $where[]        = 'o.indicateur_id = :iid';
            $params[':iid'] = (int) $filters['indicateur_id'];
        }
        if (!empty($filters['statut'])) {
            $where[]        = 'o.statut = :st';
            $params[':st']  = $filters['statut'];
        }
        if (!empty($filters['user_id'])) {
            $where[]        = 'o.created_by = :uid';
            $params[':uid'] = (int) $filters['user_id'];
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        return DB::query(
            "SELECT o.id, o.indicateur_id, o.periode_debut, o.periode_fin,
                    o.masculin, o.feminin, o.trans_autre, o.total, o.statut,
                    o.niveau_desag_valeur, o.created_at, o.updated_at,
                    i.libelle_fr AS indicateur_libelle, i.slug AS indicateur_slug,
                    CONCAT(u.prenom, ' ', u.nom) AS saisi_nom,
                    g.libelle AS geo_libelle,
                    n.libelle AS niveau_libelle,
                    un.symbole AS unite_symbole
             FROM observations o
             JOIN indicateurs i ON i.id = o.indicateur_id
             LEFT JOIN users u ON u.id = o.created_by
             LEFT JOIN geo_entites g ON g.id = o.geo_entite_id
             LEFT JOIN niveaux_desagregation n ON n.id = o.niveau_desagregation_id
             LEFT JOIN unites un ON un.id = i.unite_id
             WHERE {$whereStr}
             ORDER BY o.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
    }

    public static function countAdmin(array $filters = []): int
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['statut'])) {
            $where[]        = 'statut = :st';
            $params[':st']  = $filters['statut'];
        }
        if (!empty($filters['indicateur_id'])) {
            $where[]        = 'indicateur_id = :iid';
            $params[':iid'] = (int) $filters['indicateur_id'];
        }
        if (!empty($filters['user_id'])) {
            $where[]        = 'created_by = :uid';
            $params[':uid'] = (int) $filters['user_id'];
        }
        $whereStr = implode(' AND ', $where);
        return DB::count("SELECT COUNT(*) FROM observations WHERE {$whereStr}", $params);
    }

    public static function findById(int $id): array|false
    {
        return DB::queryOne(
            "SELECT o.*,
                    i.libelle_fr AS indicateur_libelle, i.slug AS indicateur_slug, i.entite_id,
                    CONCAT(u.prenom, ' ', u.nom) AS saisi_nom,
                    CONCAT(uv.prenom, ' ', uv.nom) AS valide_nom,
                    n.libelle AS niveau_libelle
             FROM observations o
             JOIN indicateurs i ON i.id = o.indicateur_id
             LEFT JOIN users u  ON u.id = o.created_by
             LEFT JOIN users uv ON uv.id = o.valide_par
             LEFT JOIN niveaux_desagregation n ON n.id = o.niveau_desagregation_id
             WHERE o.id = :id",
            [':id' => $id]
        );
    }

    public static function create(array $data): int
    {
        DB::execute(
            "INSERT INTO observations
             (indicateur_id, geo_entite_id, niveau_desagregation_id, niveau_desag_valeur,
              niveau_desag_valeur2, niveau_desag_valeur3,
              periode_debut, periode_fin, periodicite,
              masculin, feminin, trans_autre, total,
              statut, commentaire_interne, document_source_path, document_source_nom,
              created_by, updated_by, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
            [
                $data['indicateur_id'],
                $data['geo_entite_id']           ?? null,
                $data['niveau_desagregation_id'] ?? null,
                $data['niveau_desag_valeur']     ?? null,
                $data['niveau_desag_valeur2']    ?? null,
                $data['niveau_desag_valeur3']    ?? null,
                $data['periode_debut'],
                $data['periode_fin']             ?? null,
                $data['periodicite']             ?? 'annuelle',
                $data['masculin']                ?? null,
                $data['feminin']                 ?? null,
                $data['trans_autre']             ?? null,
                $data['total']                   ?? null,
                $data['statut']                  ?? 'brouillon',
                $data['commentaire_interne']     ?? null,
                $data['document_source_path']    ?? null,
                $data['document_source_nom']     ?? null,
                $data['created_by']              ?? null,
                $data['created_by']              ?? null,
            ]
        );
        return (int)DB::lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['masculin','feminin','trans_autre','total','nivel_desag_valeur',
                    'periode_debut','periode_fin','commentaire_interne','statut',
                    'document_source_path','document_source_nom','updated_by'];
        $sets   = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]          = "{$col} = :{$col}";
                $params[":{$col}"] = $data[$col];
            }
        }
        if (empty($sets)) return;
        $params[':id'] = $id;
        DB::execute("UPDATE observations SET " . implode(', ', $sets) . ", updated_at=NOW() WHERE id = :id", $params);
    }

    public static function changeStatut(int $id, string $statut, int $userId, string $commentaire = ''): void
    {
        $extra  = '';
        $params = [':st' => $statut, ':id' => $id];
        if ($statut === 'valide') {
            $extra            = ', valide_par=:vpar, valide_le=NOW()';
            $params[':vpar']  = $userId;
        } elseif ($statut === 'publie') {
            $extra            = ', publie_par=:ppar, publie_le=NOW()';
            $params[':ppar']  = $userId;
        } elseif ($statut === 'rejete' && $commentaire !== '') {
            $extra            = ', commentaire_rejet=:rej';
            $params[':rej']   = $commentaire;
        }
        DB::execute("UPDATE observations SET statut=:st {$extra}, updated_at=NOW() WHERE id=:id", $params);
    }

    /** Observations en attente de validation */
    public static function aValider(): array
    {
        return DB::query(
            "SELECT o.id, o.periode_debut, o.total, o.statut, o.created_at,
                    i.libelle_fr AS indicateur,
                    CONCAT(u.prenom, ' ', u.nom) AS saisi_nom
             FROM observations o
             JOIN indicateurs i ON i.id = o.indicateur_id
             LEFT JOIN users u ON u.id = o.created_by
             WHERE o.statut = 'soumis'
             ORDER BY o.created_at ASC
             LIMIT 10"
        );
    }

    /** KPIs pour le dashboard admin */
    public static function kpis(): array
    {
        return [
            'publie'   => DB::count("SELECT COUNT(*) FROM observations WHERE statut='publie'"),
            'brouillon'=> DB::count("SELECT COUNT(*) FROM observations WHERE statut='brouillon'"),
            'soumis'   => DB::count("SELECT COUNT(*) FROM observations WHERE statut='soumis'"),
            'rejete'   => DB::count("SELECT COUNT(*) FROM observations WHERE statut='rejete'"),
        ];
    }

    /** Saisies par mois (12 derniers mois) */
    public static function parMois(): array
    {
        return DB::query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mois, COUNT(*) AS n
             FROM observations
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY mois ASC"
        );
    }
}
