<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Rate Limiting basé sur MySQL
 * Protège /login, /admin, /api
 */
class RateLimit
{
    /**
     * Vérifie et incrémente le compteur.
     * @return bool true = OK, false = limite dépassée
     */
    public static function check(string $key, int $maxRequests, int $windowSeconds): bool
    {
        try {
            // Nettoyer les fenêtres expirées
            Database::execute('DELETE FROM rate_limits WHERE fenetre_fin < NOW()');

            $row = Database::queryOne(
                'SELECT id, compteur, fenetre_fin FROM rate_limits WHERE cle = ?',
                [$key]
            );

            if (!$row) {
                // Nouvelle fenêtre
                Database::execute(
                    'INSERT INTO rate_limits (cle, compteur, fenetre_fin) VALUES (?, 1, DATE_ADD(NOW(), INTERVAL ? SECOND))',
                    [$key, $windowSeconds]
                );
                return true;
            }

            if ($row['compteur'] >= $maxRequests) {
                return false;
            }

            Database::execute(
                'UPDATE rate_limits SET compteur = compteur + 1 WHERE id = ?',
                [$row['id']]
            );
            return true;
        } catch (\Throwable) {
            return true; // En cas d'erreur BDD, on laisse passer
        }
    }

    public static function limitLogin(string $ip): bool
    {
        return self::check("login:{$ip}", 5, 900); // 5 essais / 15 min
    }

    public static function limitApi(string $key, bool $hasApiKey): bool
    {
        $max = $hasApiKey ? 600 : 60;
        return self::check("api:{$key}", $max, 60);
    }
}
