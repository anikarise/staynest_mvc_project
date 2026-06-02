<?php
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
| Centralizes session state, role checks, CSRF helpers, and flash messages
| used across the MVC controllers.
|
*/

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']) && is_array($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        return isset($_SESSION['user']['user_id']) ? (int) $_SESSION['user']['user_id'] : null;
    }

    public static function name(): ?string
    {
        return $_SESSION['user']['name'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function roleLabel(?string $role = null): string
    {
        $labels = [
            'customer' => 'Customer',
            'host' => 'Host',
            'staff' => 'Staff',
            'main_admin' => 'Main Admin',
            'booking_property_admin' => 'Booking & Property Admin',
            'host_location_admin' => 'Host & Location Admin',
        ];

        $role = $role ?? self::role();
        return $labels[$role] ?? 'Guest';
    }

    public static function dashboardPath(?string $role = null): string
    {
        return 'dashboard';
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            self::flash('error', 'Please login first to access that page.');
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: ' . URL_ROOT . '/' . self::dashboardPath());
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        // Enforce role-based access before protected controllers execute.
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            require APP_ROOT . '/app/views/errors/403.php';
            exit;
        }
    }

    public static function login(array $user): void
    {
        // Regenerate the session ID on login to reduce session fixation risk.
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => (int) $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? '',
            'role' => $user['role'],
            'status' => $user['status'] ?? 'active',
            'account_status' => $user['account_status'] ?? 'active',
        ];
    }

    public static function updateSessionUser(array $user): void
    {
        if (!self::check()) {
            return;
        }

        $_SESSION['user']['name'] = $user['name'] ?? $_SESSION['user']['name'];
        $_SESSION['user']['email'] = $user['email'] ?? $_SESSION['user']['email'];
        $_SESSION['user']['phone'] = $user['phone'] ?? ($_SESSION['user']['phone'] ?? '');
        $_SESSION['user']['account_status'] = $user['account_status'] ?? ($_SESSION['user']['account_status'] ?? 'active');
    }

    public static function logout(): void
    {
        // Clear server and browser session state during logout.
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    public static function csrfToken(): string
    {
        // Create one token per session for POST form verification.
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verifyCsrf(?string $token): bool
    {
        // Constant-time comparison protects CSRF checks from timing leaks.
        return isset($_SESSION['_csrf_token']) && is_string($token) && hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function getFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
}
