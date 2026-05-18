<?php
declare(strict_types=1);
namespace App\Core;

use PDO;
use PDOException;

/**
 * Singleton PDO — accès sécurisé à la base de données MySQL 8
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = Config::get('db.host',    'localhost');
            $port    = Config::get('db.port',    '3306');
            $dbname  = Config::get('db.name',    'pndm');
            $user    = Config::get('db.user',    'root');
            $pass    = Config::get('db.password','');
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Ne pas exposer les identifiants
                error_log('[PNDM DB] Connection failed: ' . $e->getMessage());
                throw new \RuntimeException('Connexion base de données impossible.');
            }
        }
        return self::$instance;
    }

    /** Exécute une requête préparée et retourne un tableau de résultats */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Retourne une seule ligne */
    public static function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /** INSERT / UPDATE / DELETE — retourne le nombre de lignes affectées */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Retourne l'ID du dernier INSERT */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }

    /** Transactions */
    public static function beginTransaction(): void  { self::getInstance()->beginTransaction(); }
    public static function commit(): void             { self::getInstance()->commit(); }
    public static function rollback(): void           { self::getInstance()->rollBack(); }

    /** Compte rapide — accepte soit (sql_complet, params) soit (table, where, params) */
    public static function count(string $sqlOrTable, array|string $paramsOrWhere = [], array $params = []): int
    {
        // Detect if first arg is a full SELECT COUNT(*) query
        if (str_starts_with(strtoupper(ltrim($sqlOrTable)), 'SELECT')) {
            $binds = is_array($paramsOrWhere) ? $paramsOrWhere : [];
            $row   = self::queryOne($sqlOrTable, $binds);
        } else {
            // Legacy table+where usage
            $where = is_string($paramsOrWhere) ? $paramsOrWhere : '1';
            $row   = self::queryOne("SELECT COUNT(*) AS n FROM `{$sqlOrTable}` WHERE {$where}", $params);
        }
        // Handle both COUNT(*) returning the first column or 'n'
        if ($row === false) return 0;
        $val = $row['n'] ?? $row['COUNT(*)'] ?? current($row);
        return (int) $val;
    }

}
