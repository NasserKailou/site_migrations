<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Authentification — gestion des sessions admin
 */
class Auth
{
    private static ?array $currentUser = null;

    public static function attempt(string $email, string $password): array|false
    {
        $user = Database::queryOne(
            'SELECT u.*, r.libelle AS role_libelle, r.permissions
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.actif = 1',
            [':email' => strtolower(trim($email))]
        );

        if (!$user) return false;

        // Vérifier si bloqué
        if ($user['bloque_jusqu_a'] && strtotime($user['bloque_jusqu_a']) > time()) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            // Incrémenter les tentatives
            $attempts = (int)$user['tentatives_connexion'] + 1;
            $blockedUntil = null;
            if ($attempts >= 5) {
                $blockedUntil = date('Y-m-d H:i:s', time() + 900); // 15 min
                $attempts = 0;
            }
            Database::execute(
                'UPDATE users SET tentatives_connexion=?, bloque_jusqu_a=? WHERE id=?',
                [$attempts, $blockedUntil, $user['id']]
            );
            return false;
        }

        // Reset tentatives, maj dernier login
        Database::execute(
            'UPDATE users SET tentatives_connexion=0, bloque_jusqu_a=NULL, dernier_login=NOW() WHERE id=?',
            [$user['id']]
        );

        unset($user['password_hash'], $user['totp_secret'], $user['token_reset']);
        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('auth_user_id', $user['id']);
        Session::set('auth_user',    $user);
        // Si 2FA requis, marquer comme non terminé
        if (in_array($user['role_libelle'], ['super_admin','admin','validateur']) && $user['totp_enabled']) {
            Session::set('auth_2fa_pending', true);
        } else {
            Session::set('auth_2fa_done', true);
        }
        self::$currentUser = $user;
    }

    public static function complete2FA(): void
    {
        Session::remove('auth_2fa_pending');
        Session::set('auth_2fa_done', true);
    }

    public static function logout(): void
    {
        self::$currentUser = null;
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('auth_user_id') && Session::get('auth_2fa_done', false);
    }

    public static function user(): ?array
    {
        if (self::$currentUser) return self::$currentUser;
        if (Session::has('auth_user')) {
            self::$currentUser = Session::get('auth_user');
            return self::$currentUser;
        }
        return null;
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function hasRole(string ...$roles): bool
    {
        $u = self::user();
        if (!$u) return false;
        return in_array($u['role_libelle'], $roles);
    }

    public static function can(string $permission): bool
    {
        $u = self::user();
        if (!$u) return false;
        $perms = json_decode($u['permissions'] ?? '[]', true);
        if (isset($perms['all'])) return true;
        return in_array($permission, $perms ?? []);
    }

    /** Middleware — redirige si non connecté */
    public static function require(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Veuillez vous connecter.');
            header('Location: ' . View::url('admin/login'));
            exit;
        }
    }

    /** Middleware — redirige si pas la permission */
    public static function requirePermission(string $permission): void
    {
        self::require();
        if (!self::can($permission)) {
            throw new \App\Core\ForbiddenException('Accès non autorisé.');
        }
    }

    /** Middleware — redirige si pas le rôle */
    public static function requireRole(string ...$roles): void
    {
        self::require();
        if (!self::hasRole(...$roles)) {
            throw new \App\Core\ForbiddenException('Accès réservé.');
        }
    }

    /** Génère un token de réinitialisation */
    public static function generateResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', time() + 1800); // 30 min
        Database::execute(
            'UPDATE users SET token_reset=?, token_reset_expire=? WHERE id=?',
            [$token, $expire, $userId]
        );
        return $token;
    }
}
