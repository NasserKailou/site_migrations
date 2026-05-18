#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * PNDM — Script de migration des données
 * Migre les 679 lignes de l'ancienne table `data` (pndm_old)
 * vers la nouvelle table `observations` (pndm).
 *
 * Usage :
 *   php database/migrate_old_data.php           # migration réelle
 *   php database/migrate_old_data.php --dry-run  # prévisualisation seule
 */

$isDryRun = in_array('--dry-run', $argv, true);

$OLD_DB = [
    'host' => 'localhost', 'port' => 3306,
    'dbname' => 'pndm_old', 'user' => 'pndm_user', 'pass' => 'pndm_dev_2026',
];
$NEW_DB = [
    'host' => 'localhost', 'port' => 3306,
    'dbname' => 'pndm',    'user' => 'pndm_user', 'pass' => 'pndm_dev_2026',
];

function pdo(array $cfg, string $label): PDO {
    try {
        $pdo = new PDO(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4",
            $cfg['user'], $cfg['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]
        );
        echo "✅  $label ({$cfg['dbname']})\n";
        return $pdo;
    } catch (PDOException $e) {
        die("❌  Connexion $label impossible : " . $e->getMessage() . "\n");
    }
}

/**
 * Normalise une chaîne pour la comparaison floue :
 * minuscules, sans accents, sans ponctuation, espaces collés.
 */
function normalise(string $s): string {
    $s = mb_strtolower($s);
    $map = [
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'à'=>'a','â'=>'a','ä'=>'a',
        'î'=>'i','ï'=>'i',
        'ô'=>'o','ö'=>'o',
        'ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c', 'ñ'=>'n',
    ];
    $s = strtr($s, $map);
    // Remove non-alphanumeric except spaces
    $s = preg_replace('/[^a-z0-9 ]/', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

echo str_repeat('═', 60) . "\n";
echo "  PNDM — Migration de données  " . ($isDryRun ? '[DRY-RUN]' : '[LIVE]') . "\n";
echo str_repeat('═', 60) . "\n\n";

$old = pdo($OLD_DB, 'Ancienne base');
$new = pdo($NEW_DB, 'Nouvelle base');

// ─────────────────────────────────────────────────────────
// [1/4] Mapping des indicateurs : ancien_id → nouvel_id
// Stratégie : comparaison de libellé normalisé
// ─────────────────────────────────────────────────────────
echo "\n[1/4] Mapping des indicateurs…\n";

$oldInds = $old->query("SELECT id, libelle FROM indicateurs ORDER BY id")->fetchAll();
$newInds = $new->query("SELECT id, libelle_fr FROM indicateurs ORDER BY id")->fetchAll();

// Build lookup: normalised_libelle → new_id
$newByLabel = [];
foreach ($newInds as $ni) {
    $key = normalise($ni['libelle_fr']);
    $newByLabel[$key] = (int)$ni['id'];
}

// Manual overrides for cases where name differs significantly
// Key = old indicator ID, Value = new indicator ID
$manualMap = [
    3  => 1,   // Proportion entreprises MO étrangère
    7  => 2,   // Bureaux HCNE
    8  => 3,   // Nigériens immatriculés représentations diplomatiques
    9  => 4,   // Élèves déplacés internes
    10 => 5,   // Élèves étrangers réfugiés
    11 => 6,   // Élèves retournés
    12 => 7,   // Migrants objets de trafic
    13 => 8,   // Auteurs déférés trafic illicite
    14 => 9,   // Auteurs jugés trafic illicite
    15 => 10,  // Taux condamnation trafic
    17 => 11,  // Taux poursuite pénale trafic
    18 => 12,  // Demandes visas travailleurs étrangers
    19 => 13,  // Flux entrant
    20 => 14,  // Flux sortant
    21 => 15,  // Migrants postes contrôle DSP
    22 => 16,  // Migrants enregistrés interne
    23 => 17,  // Sensibilisation migration irrégulière
    24 => 18,  // Demandeurs d'asile
    25 => 19,  // Population étrangère stock
    26 => 20,  // Migrants âge travailler
    27 => 21,  // Flux migratoire total
    28 => 22,  // Indice de sortie (INDICE DE SORTIE)
    29 => 23,  // Indice d'entrée (INDICE D'ENTREE)
    30 => 24,  // Solde migratoire
    31 => 25,  // Ratio emploi migrants internationaux
    32 => 26,  // Stock travailleurs migrants
    33 => 27,  // Taux migration internationale
    34 => 28,  // Abandons EFPT migrations
    35 => 29,  // Visas travail accordés
    36 => 30,  // Transferts reçus
    37 => 31,  // Transferts émis
    38 => 32,  // PDI nombre
    40 => 33,  // Enfants migrants rapatriés
    43 => 34,  // Personnes formées migration
    44 => 35,  // Indice rétention
    45 => 36,  // Indice efficacité
    46 => 37,  // Ménages déplacés internes
    47 => 38,  // Auteurs poursuivis trafic illicite
    48 => 39,  // Auteurs jugés traite personnes
    49 => 40,  // Auteurs poursuivis traite
    50 => 41,  // Auteurs déférés traite
    54 => 42,  // Victimes traite personnes
    55 => 43,  // Victimes traite centre Zinder
];

$indMap     = [];
$unmapped   = [];

foreach ($oldInds as $oi) {
    $oid = (int)$oi['id'];

    // 1. Manual map takes priority
    if (isset($manualMap[$oid])) {
        $indMap[$oid] = $manualMap[$oid];
        continue;
    }

    // 2. Fuzzy label match
    $norm = normalise($oi['libelle']);
    if (isset($newByLabel[$norm])) {
        $indMap[$oid] = $newByLabel[$norm];
        continue;
    }

    // 3. Partial match (longest common substring approach — simple contains)
    $matched = null;
    foreach ($newByLabel as $newLabel => $newId) {
        $words = explode(' ', $norm);
        $significantWords = array_filter($words, fn($w) => mb_strlen($w) > 4);
        $matchCount = 0;
        foreach ($significantWords as $w) {
            if (str_contains($newLabel, $w)) $matchCount++;
        }
        if ($matchCount >= 3) {
            $matched = $newId;
            break;
        }
    }

    if ($matched) {
        $indMap[$oid] = $matched;
        echo "  🔄 Indicateur ancien id=$oid '{$oi['libelle']}' → new_id=$matched (fuzzy)\n";
    } else {
        $unmapped[] = $oid;
        echo "  ⚠️  Indicateur ancien id=$oid '{$oi['libelle']}' → non mappé (ignoré)\n";
    }
}

$mappedCount = count($indMap);
$totalOld    = count($oldInds);
echo "  → $mappedCount/$totalOld indicateurs mappés";
if (!empty($unmapped)) {
    echo " (" . count($unmapped) . " non mappés, leurs données seront ignorées)";
}
echo "\n";

// ─────────────────────────────────────────────────────────
// [2/4] Références : geo Niger + fréquence annuelle
// ─────────────────────────────────────────────────────────
$nigerGeoId = $new->query(
    "SELECT id FROM geo_entites WHERE type='national' OR libelle='Niger' LIMIT 1"
)->fetchColumn() ?: null;

$annuelleId = $new->query(
    "SELECT id FROM frequences WHERE code='annual' OR libelle LIKE '%Annuel%' LIMIT 1"
)->fetchColumn() ?: null;

echo "\n[2/4] Références : geo_niger_id=$nigerGeoId, frequence_annuelle_id=$annuelleId\n";

if (!$nigerGeoId) {
    echo "⚠️  Aucune entité géographique 'national/Niger' trouvée — geo_entite_id sera NULL\n";
}
if (!$annuelleId) {
    echo "⚠️  Aucune fréquence annuelle trouvée — periodicite utilisera la valeur par défaut\n";
}

// ─────────────────────────────────────────────────────────
// [3/4] Lire l'ancienne table data
// Colonnes réelles : id, masculin, feminin, trans, total,
//   date, entite_id, indicateur_id,
//   niveau_desagregation_id, niveau_desagregation2_id, niveau_desagregation3_id,
//   niveau_desagregation_valeur, niveau_desagregation_valeur2, niveau_desagregation_valeur3,
//   created_at, updated_at, user_id
// ─────────────────────────────────────────────────────────
echo "\n[3/4] Lecture de l'ancienne table data…\n";
$rows = $old->query("
    SELECT d.*,
           e.acronyme          AS entite_acr,
           e.libelle           AS entite_libelle,
           i.libelle           AS indicateur_nom
    FROM `data` d
    LEFT JOIN entites      e ON e.id = d.entite_id
    LEFT JOIN indicateurs  i ON i.id = d.indicateur_id
    ORDER BY d.indicateur_id, d.date
")->fetchAll();

$total    = count($rows);
$inserted = 0;
$skipped  = 0;
$errCount = 0;
echo "  → $total lignes à traiter\n";

// ─────────────────────────────────────────────────────────
// Préparer l'INSERT — utilise les colonnes réelles de observations
// ─────────────────────────────────────────────────────────
$insertSQL = "
    INSERT INTO observations
        (indicateur_id, geo_entite_id,
         niveau_desagregation_id,  niveau_desagregation2_id, niveau_desagregation3_id,
         niveau_desag_valeur,      niveau_desag_valeur2,     niveau_desag_valeur3,
         periode_debut, periode_fin, periodicite,
         masculin, feminin, trans_autre, total,
         statut, commentaire_interne,
         created_by, created_at, updated_at)
    VALUES
        (:iid, :geo,
         :nd1, :nd2, :nd3,
         :ndv1, :ndv2, :ndv3,
         :pd, :pf, 'annuelle',
         :vm, :vf, :va, :vt,
         'publie', :com,
         1, :cat, :uat)
";
$stmt = $new->prepare($insertSQL);

// Contrôle doublon (même indicateur + même début de période + même zone géo)
$dupCheck = $new->prepare("
    SELECT COUNT(*) FROM observations
    WHERE indicateur_id  = :iid
      AND periode_debut  = :pd
      AND (
            (geo_entite_id = :geo AND :geo IS NOT NULL)
         OR (geo_entite_id IS NULL AND :geo IS NULL)
      )
      AND statut = 'publie'
");

echo "\n[4/4] Migration…\n";

if (!$isDryRun) {
    $new->beginTransaction();
}

foreach ($rows as $i => $row) {
    $oldIndId = (int)$row['indicateur_id'];
    $newIndId = $indMap[$oldIndId] ?? null;

    if (!$newIndId) {
        // Indicateur non mappé — ignorer silencieusement
        $skipped++;
        continue;
    }

    // Extraire l'année depuis le champ date (format: YYYY-MM-DD ou YYYY)
    $dateStr = $row['date'] ?? '';
    if (!$dateStr || !preg_match('/^(\d{4})/', $dateStr, $m)) {
        $skipped++;
        continue;
    }
    $year         = $m[1];
    $periodeDebut = "$year-01-01";
    $periodeFin   = "$year-12-31";

    // Doublon ?
    $dupCheck->execute([
        ':iid' => $newIndId,
        ':pd'  => $periodeDebut,
        ':geo' => $nigerGeoId,
    ]);
    if ($dupCheck->fetchColumn() > 0) {
        $skipped++;
        continue;
    }

    $vm = isset($row['masculin']) && $row['masculin'] !== '' ? (float)$row['masculin'] : null;
    $vf = isset($row['feminin'])  && $row['feminin']  !== '' ? (float)$row['feminin']  : null;
    $va = isset($row['trans'])    && $row['trans']    !== '' ? (float)$row['trans']    : null;
    $vt = isset($row['total'])    && $row['total']    !== '' ? (float)$row['total']    : null;

    $comment = 'Migré depuis ancienne base — old_id=' . $row['id'];
    if (!empty($row['entite_acr'])) {
        $comment .= ' src=' . $row['entite_acr'];
    } elseif (!empty($row['entite_libelle'])) {
        $comment .= ' src=' . mb_substr($row['entite_libelle'], 0, 50);
    }

    if ($isDryRun) {
        echo sprintf(
            "  [DRY] new_ind=%-3d %-40s %s  H=%s F=%s total=%s\n",
            $newIndId,
            mb_substr($row['indicateur_nom'] ?? '?', 0, 40),
            $periodeDebut,
            $vm ?? '-',
            $vf ?? '-',
            $vt ?? 'NULL'
        );
        $inserted++;
        continue;
    }

    try {
        // Les niveaux_desagregation_id de l'ancienne base ne correspondent pas
        // aux IDs de la nouvelle — on les nullifie (les valeurs texte sont conservées).
        $stmt->execute([
            ':iid'  => $newIndId,
            ':geo'  => $nigerGeoId,
            ':nd1'  => null,
            ':nd2'  => null,
            ':nd3'  => null,
            ':ndv1' => $row['niveau_desagregation_valeur']  ?? null,
            ':ndv2' => $row['niveau_desagregation_valeur2'] ?? null,
            ':ndv3' => $row['niveau_desagregation_valeur3'] ?? null,
            ':pd'   => $periodeDebut,
            ':pf'   => $periodeFin,
            ':vm'   => $vm,
            ':vf'   => $vf,
            ':va'   => $va,
            ':vt'   => $vt,
            ':com'  => $comment,
            ':cat'  => $row['created_at'] ?? date('Y-m-d H:i:s'),
            ':uat'  => $row['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);
        $inserted++;
    } catch (PDOException $e) {
        $errCount++;
        echo "  ❌ Ligne old_id={$row['id']} (ind={$oldIndId}→{$newIndId}): " . $e->getMessage() . "\n";
        if ($errCount > 20) {
            echo "  Trop d'erreurs — abandon de la transaction.\n";
            $new->rollBack();
            exit(1);
        }
    }

    if ($i % 100 === 0 && $i > 0) {
        echo "  … $i/$total traités ($inserted insérés, $skipped ignorés)\n";
    }
}

// ─────────────────────────────────────────────────────────
// Résumé
// ─────────────────────────────────────────────────────────
if (!$isDryRun) {
    if ($errCount === 0) {
        $new->commit();
        echo "\n✅  Transaction validée.\n";
    } else {
        $new->commit(); // on commite quand même les lignes OK
        echo "\n⚠️  Transaction validée avec $errCount erreur(s).\n";
    }
    $newTotal = (int)$new->query("SELECT COUNT(*) FROM observations")->fetchColumn();
    echo "   $inserted insérées, $skipped ignorées, $errCount erreur(s)\n";
    echo "   Total observations en base : $newTotal\n";
} else {
    echo "\nℹ️  Dry-run : $inserted lignes seraient insérées, $skipped ignorées\n";
    echo "   Relancez sans --dry-run pour appliquer.\n";
}
echo str_repeat('═', 60) . "\n";
