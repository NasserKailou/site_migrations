<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\View;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\RateLimit;
use App\Core\Database as DB;

/** Contrôleur d'authentification admin */
class AuthController
{
    public function loginForm(array $params = []): void
    {
        if (Auth::check()) {
            Response::redirect(url('admin/dashboard'));
        }
        View::renderWithLayout('admin/login', [], 'layouts/admin_auth');
    }

    public function login(array $params = []): void
    {
        if (Auth::check()) Response::redirect(url('admin/dashboard'));

        // CSRF
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token invalide. Veuillez réessayer.');
            Response::redirect(url('admin/login'));
        }

        $ip = Request::ip();
        if (!RateLimit::limitLogin($ip)) {
            Session::flash('error', 'Trop de tentatives. Réessayez dans 15 minutes.');
            Response::redirect(url('admin/login'));
        }

        $email    = Request::post('email', '');
        $password = Request::post('password', '');

        if (!$email || !$password) {
            Session::flash('error', 'Email et mot de passe requis.');
            Response::redirect(url('admin/login'));
        }

        $user = Auth::attempt($email, $password);
        if (!$user) {
            // Log tentative
            DB::execute("INSERT INTO audit_log (action, details, ip, created_at) VALUES ('login_failed', ?, ?, NOW())",
                [json_encode(['email' => $email]), $ip]);
            Session::flash('error', 'Identifiants incorrects ou compte bloqué.');
            Response::redirect(url('admin/login'));
        }

        Auth::login($user);

        // Log connexion réussie
        DB::execute("INSERT INTO audit_log (user_id, action, ip, created_at) VALUES (?, 'login', ?, NOW())",
            [$user['id'], $ip]);

        // 2FA requis ?
        if (Session::get('auth_2fa_pending')) {
            Response::redirect(url('admin/2fa'));
        }

        Response::redirect(url('admin/dashboard'));
    }

    public function twoFactorForm(array $params = []): void
    {
        if (!Session::has('auth_user_id')) Response::redirect(url('admin/login'));
        if (Auth::check()) Response::redirect(url('admin/dashboard'));
        View::renderWithLayout('admin/2fa', [], 'layouts/admin_auth');
    }

    public function twoFactor(array $params = []): void
    {
        if (!Session::has('auth_user_id')) Response::redirect(url('admin/login'));
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token invalide.');
            Response::redirect(url('admin/2fa'));
        }

        $code = preg_replace('/\D/', '', Request::post('code', ''));
        $userId = (int)Session::get('auth_user_id');
        $user = DB::queryOne('SELECT totp_secret FROM users WHERE id=?', [$userId]);

        if (!$user || !$this->verifyTotp($user['totp_secret'], $code)) {
            Session::flash('error', 'Code 2FA incorrect.');
            Response::redirect(url('admin/2fa'));
        }

        Auth::complete2FA();
        Response::redirect(url('admin/dashboard'));
    }

    public function logout(array $params = []): void
    {
        if (Auth::id()) {
            DB::execute("INSERT INTO audit_log (user_id, action, ip, created_at) VALUES (?, 'logout', ?, NOW())",
                [Auth::id(), Request::ip()]);
        }
        Auth::logout();
        Session::flash('success', 'Déconnexion réussie.');
        Response::redirect(url('admin/login'));
    }

    public function resetForm(array $params = []): void
    {
        View::renderWithLayout('admin/reset_request', [], 'layouts/admin_auth');
    }

    public function resetRequest(array $params = []): void
    {
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Response::redirect(url('admin/reset-password'));
        }
        $email = strtolower(trim(Request::post('email', '')));
        $user  = DB::queryOne("SELECT id FROM users WHERE email=? AND actif=1", [$email]);
        // Toujours afficher le même message (sécurité)
        if ($user) {
            $token = Auth::generateResetToken((int)$user['id']);
            // TODO: Envoyer l'email via PHPMailer
        }
        Session::flash('success', 'Si cet email existe, un lien de réinitialisation vous a été envoyé.');
        Response::redirect(url('admin/reset-password'));
    }

    public function resetConfirmForm(array $params): void
    {
        $token = $params['token'] ?? '';
        $user  = DB::queryOne(
            "SELECT id FROM users WHERE token_reset=? AND token_reset_expire > NOW()",
            [$token]
        );
        if (!$user) {
            Session::flash('error', 'Lien invalide ou expiré.');
            Response::redirect(url('admin/login'));
        }
        View::renderWithLayout('admin/reset_confirm', ['token' => $token], 'layouts/admin_auth');
    }

    public function resetConfirm(array $params): void
    {
        $token = $params['token'] ?? '';
        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Response::redirect(url('admin/reset/'.$token));
        }
        $user = DB::queryOne("SELECT id FROM users WHERE token_reset=? AND token_reset_expire > NOW()", [$token]);
        if (!$user) {
            Session::flash('error', 'Lien invalide ou expiré.');
            Response::redirect(url('admin/login'));
        }
        $pw  = Request::post('password', '');
        $pw2 = Request::post('password_confirm', '');
        if (strlen($pw) < 8 || $pw !== $pw2) {
            Session::flash('error', 'Mot de passe invalide ou non correspondant (min. 8 caractères).');
            Response::redirect(url('admin/reset/'.$token));
        }
        $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
        DB::execute("UPDATE users SET password_hash=?, token_reset=NULL, token_reset_expire=NULL WHERE id=?",
            [$hash, $user['id']]);
        Session::flash('success', 'Mot de passe mis à jour. Connectez-vous.');
        Response::redirect(url('admin/login'));
    }

    /** Vérification TOTP simple (RFC 6238) */
    private function verifyTotp(string $secret, string $code): bool
    {
        // Implementation simplifiée — en production utiliser une lib TOTP
        $time = floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            $t = $time + $i;
            $key  = base64_decode($secret);
            $msg  = pack('N*', 0) . pack('N*', $t);
            $hash = hash_hmac('sha1', $msg, $key, true);
            $offset = ord($hash[19]) & 0x0F;
            $otp = ((ord($hash[$offset]) & 0x7F) << 24)
                 | ((ord($hash[$offset+1]) & 0xFF) << 16)
                 | ((ord($hash[$offset+2]) & 0xFF) << 8)
                 | (ord($hash[$offset+3]) & 0xFF);
            $otp = str_pad((string)($otp % 1000000), 6, '0', STR_PAD_LEFT);
            if ($otp === $code) return true;
        }
        return false;
    }
}
