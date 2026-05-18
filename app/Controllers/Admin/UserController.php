<?php
declare(strict_types=1);
namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\View;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database as DB;

/**
 * Contrôleur admin – gestion des utilisateurs
 * Colonnes réelles users : id, nom, prenom, email, password_hash,
 *   role_id, entite_id, totp_secret, totp_enabled, actif (TINYINT 0/1),
 *   tentatives_connexion, bloque_jusqu_a, dernier_login, token_reset,
 *   token_reset_expire, api_key, api_key_expire, created_at, updated_at
 * Colonnes réelles roles : id, libelle, permissions  (PAS de slug !)
 */
class UserController
{
    /* ------------------------------------------------------------------ */
    /*  LISTE                                                               */
    /* ------------------------------------------------------------------ */
    public function index(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $search  = Request::get('q', '');
        $role    = Request::get('role', '');
        $statut  = Request::get('statut', '');
        $page    = max(1, (int) Request::get('page', 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $where  = ['1=1'];
        $binds  = [];

        if ($search !== '') {
            $where[]      = '(u.nom LIKE :q OR u.prenom LIKE :q2 OR u.email LIKE :q3)';
            $binds[':q']  = "%$search%";
            $binds[':q2'] = "%$search%";
            $binds[':q3'] = "%$search%";
        }
        if ($role !== '') {
            // role = libelle de la table roles (super_admin, admin, etc.)
            $where[]        = 'r.libelle = :role';
            $binds[':role'] = $role;
        }
        if ($statut !== '') {
            // statut envoyé comme '0' ou '1' depuis le filtre
            $where[]      = 'u.actif = :st';
            $binds[':st'] = (int) $statut;
        }

        $whereStr = implode(' AND ', $where);

        $total = DB::count(
            "SELECT COUNT(*) FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE $whereStr",
            $binds
        );

        $users = DB::query(
            "SELECT u.id, CONCAT(u.prenom, ' ', u.nom) AS nom_complet,
                    u.nom, u.prenom, u.email,
                    u.actif, u.totp_enabled,
                    u.dernier_login, u.created_at,
                    r.libelle AS role_libelle
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE $whereStr
             ORDER BY u.nom, u.prenom
             LIMIT :lim OFFSET :off",
            array_merge($binds, [':lim' => $perPage, ':off' => $offset])
        );

        $roles      = DB::query("SELECT id, libelle FROM roles ORDER BY libelle");
        $totalPages = (int) ceil($total / $perPage);

        View::renderWithLayout('admin/utilisateurs/index', compact(
            'users', 'total', 'page', 'totalPages',
            'roles', 'search', 'role', 'statut'
        ), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE CRÉATION                                                 */
    /* ------------------------------------------------------------------ */
    public function create(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $roles = DB::query("SELECT id, libelle FROM roles ORDER BY libelle");
        $user  = [];

        View::renderWithLayout('admin/utilisateurs/form', compact('user', 'roles'), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  STORE                                                               */
    /* ------------------------------------------------------------------ */
    public function store(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url('admin/utilisateurs/nouveau'));
            return;
        }

        $errors = $this->validateUserForm();

        // Mot de passe requis à la création
        $password = Request::post('password', '');
        if ($password === '') {
            $errors[] = 'Le mot de passe est obligatoire pour la création.';
        } elseif (strlen($password) < 12) {
            $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
        }

        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            Session::flash('old', Request::post());
            Response::redirect(url('admin/utilisateurs/nouveau'));
            return;
        }

        $email  = strtolower(trim(Request::post('email', '')));
        $exists = DB::count("SELECT COUNT(*) FROM users WHERE email = :e", [':e' => $email]);
        if ($exists > 0) {
            Session::flash('error', 'Cet email est déjà utilisé.');
            Session::flash('old', Request::post());
            Response::redirect(url('admin/utilisateurs/nouveau'));
            return;
        }

        $roleId  = (int) Request::post('role_id', 0);
        // Seul un super_admin peut créer un autre super_admin
        $roleRow = DB::queryOne("SELECT libelle FROM roles WHERE id = :id", [':id' => $roleId]);
        if ($roleRow && $roleRow['libelle'] === 'super_admin' && !Auth::hasRole('super_admin')) {
            Session::flash('error', 'Vous ne pouvez pas créer un super administrateur.');
            Response::redirect(url('admin/utilisateurs/nouveau'));
            return;
        }

        DB::execute(
            "INSERT INTO users
                (nom, prenom, email, password_hash, role_id, actif, created_at, updated_at)
             VALUES (:nom, :prenom, :email, :pwd, :rid, 1, NOW(), NOW())",
            [
                ':nom'   => trim(Request::post('nom', '')),
                ':prenom'=> trim(Request::post('prenom', '')),
                ':email' => $email,
                ':pwd'   => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                ':rid'   => $roleId,
            ]
        );
        $id = DB::lastInsertId();

        $this->audit('CREATE_USER', "Création utilisateur id=$id email=$email");

        Session::flash('success', 'Utilisateur créé avec succès.');
        Response::redirect(url("admin/utilisateurs/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  FORMULAIRE ÉDITION                                                  */
    /* ------------------------------------------------------------------ */
    public function edit(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id   = (int) ($params['id'] ?? 0);
        $user = DB::queryOne(
            "SELECT u.*, r.libelle AS role_libelle
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id",
            [':id' => $id]
        );

        if (!$user) {
            Response::notFound();
            return;
        }

        // Admin ne peut pas éditer un super_admin (sauf lui-même)
        if ($user['role_libelle'] === 'super_admin' && !Auth::hasRole('super_admin')) {
            Session::flash('error', 'Accès refusé.');
            Response::redirect(url('admin/utilisateurs'));
            return;
        }

        $roles = DB::query("SELECT id, libelle FROM roles ORDER BY libelle");

        View::renderWithLayout('admin/utilisateurs/form', compact('user', 'roles'), 'layouts/admin');
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE                                                              */
    /* ------------------------------------------------------------------ */
    public function update(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url("admin/utilisateurs/$id/modifier"));
            return;
        }

        $user = DB::queryOne(
            "SELECT u.*, r.libelle AS role_libelle
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.id = :id",
            [':id' => $id]
        );
        if (!$user) {
            Response::notFound();
            return;
        }

        $errors = $this->validateUserForm($id);
        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            Response::redirect(url("admin/utilisateurs/$id/modifier"));
            return;
        }

        $password      = Request::post('password', '');
        $passwordSQL   = '';
        $passwordBinds = [];
        if ($password !== '') {
            if (strlen($password) < 12) {
                Session::flash('error', 'Le mot de passe doit contenir au moins 12 caractères.');
                Response::redirect(url("admin/utilisateurs/$id/modifier"));
                return;
            }
            $passwordSQL   = ', password_hash = :pwd';
            $passwordBinds = [':pwd' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])];
        }

        $roleId = (int) Request::post('role_id', 0);
        // actif = 1 (actif) ou 0 (suspendu) — TINYINT
        $actif  = Request::post('actif', '1') === '1' ? 1 : 0;

        // Ne pas désactiver son propre compte
        if ($id === Auth::id() && $actif === 0) {
            Session::flash('error', 'Vous ne pouvez pas désactiver votre propre compte.');
            Response::redirect(url("admin/utilisateurs/$id/modifier"));
            return;
        }

        DB::execute(
            "UPDATE users SET
                nom = :nom, prenom = :prenom, email = :email,
                role_id = :rid, actif = :actif
                $passwordSQL,
                updated_at = NOW()
             WHERE id = :id",
            array_merge([
                ':nom'   => trim(Request::post('nom', '')),
                ':prenom'=> trim(Request::post('prenom', '')),
                ':email' => strtolower(trim(Request::post('email', ''))),
                ':rid'   => $roleId,
                ':actif' => $actif,
                ':id'    => $id,
            ], $passwordBinds)
        );

        $this->audit('UPDATE_USER', "Mise à jour utilisateur id=$id");

        Session::flash('success', 'Utilisateur mis à jour.');
        Response::redirect(url("admin/utilisateurs/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  RESET 2FA                                                           */
    /* ------------------------------------------------------------------ */
    public function reset2fa(array $params = []): void
    {
        Auth::requireRole('super_admin');

        $id = (int) ($params['id'] ?? 0);

        if (!Session::verifyCsrf(Request::post('_csrf', ''))) {
            Session::flash('error', 'Token CSRF invalide.');
            Response::redirect(url("admin/utilisateurs/$id/modifier"));
            return;
        }

        DB::execute(
            "UPDATE users SET totp_secret = NULL, totp_enabled = 0, updated_at = NOW() WHERE id = :id",
            [':id' => $id]
        );

        $this->audit('RESET_2FA', "Réinitialisation 2FA utilisateur id=$id");

        Session::flash('success', '2FA réinitialisé pour cet utilisateur.');
        Response::redirect(url("admin/utilisateurs/$id/modifier"));
    }

    /* ------------------------------------------------------------------ */
    /*  TOGGLE STATUT (AJAX)                                                */
    /* ------------------------------------------------------------------ */
    public function toggleStatut(array $params = []): void
    {
        Auth::requireRole('admin', 'super_admin');

        $id = (int) ($params['id'] ?? 0);

        if ($id === Auth::id()) {
            Response::json(['error' => 'Impossible de modifier son propre statut.'], 400);
            return;
        }

        $user = DB::queryOne(
            "SELECT id, actif FROM users WHERE id = :id",
            [':id' => $id]
        );
        if (!$user) {
            Response::json(['error' => 'Utilisateur introuvable.'], 404);
            return;
        }

        // actif est TINYINT : 1 = actif, 0 = suspendu
        $nouveau = (int) $user['actif'] === 1 ? 0 : 1;
        DB::execute(
            "UPDATE users SET actif = :actif, updated_at = NOW() WHERE id = :id",
            [':actif' => $nouveau, ':id' => $id]
        );

        $label = $nouveau === 1 ? 'actif' : 'suspendu';
        $this->audit('TOGGLE_USER_STATUT', "Utilisateur id=$id actif→$nouveau");

        Response::json(['actif' => $nouveau, 'statut' => $label]);
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */
    private function validateUserForm(int $excludeId = 0): array
    {
        $errors = [];

        $nom   = trim(Request::post('nom', ''));
        $email = strtolower(trim(Request::post('email', '')));

        if ($nom === '') {
            $errors[] = 'Le nom est obligatoire.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide.';
        } else {
            $q = $excludeId > 0
                ? DB::count("SELECT COUNT(*) FROM users WHERE email = :e AND id != :id", [':e' => $email, ':id' => $excludeId])
                : DB::count("SELECT COUNT(*) FROM users WHERE email = :e", [':e' => $email]);
            if ($q > 0) {
                $errors[] = 'Cet email est déjà utilisé par un autre compte.';
            }
        }

        $roleId = (int) Request::post('role_id', 0);
        if ($roleId <= 0) {
            $errors[] = 'Le rôle est obligatoire.';
        }

        return $errors;
    }

    private function audit(string $action, string $detail): void
    {
        DB::execute(
            "INSERT INTO audit_log (user_id, action, details, ip, created_at)
             VALUES (:uid, :act, :det, :ip, NOW())",
            [
                ':uid' => Auth::id(),
                ':act' => $action,
                ':det' => $detail,
                ':ip'  => Request::ip(),
            ]
        );
    }
}
