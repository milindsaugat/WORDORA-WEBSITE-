<?php
/**
 * WORDORA — Session-Based Authentication
 */
class Auth {
    public static function login(string $identifier, string $password): bool {
        try {
            $db = DB::getInstance();
        } catch (Exception $e) {
            return false;
        }

        $cleanId = trim($identifier);
        $emailVariant1 = str_ireplace('.com', '.in', $cleanId);
        $emailVariant2 = str_ireplace('.in', '.com', $cleanId);
        $emailVariant3 = !str_contains($cleanId, '@') ? $cleanId . '@wordora.in' : $cleanId;

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR name = ? OR email = ? OR email = ? OR email = ? LIMIT 1");
        $stmt->execute([$cleanId, $cleanId, $emailVariant1, $emailVariant2, $emailVariant3]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                @session_regenerate_id(true);
            }
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['name'];
            $_SESSION['user_role']     = $user['role'];
            $_SESSION['last_activity'] = time();
            // Update last_login timestamp
            try {
                $upd = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $upd->execute([$user['id']]);
            } catch (Exception $e) {}
            return true;
        }
        return false;
    }

    public static function check(): bool {
        if (!isset($_SESSION['user_id'])) return false;
        // Session timeout: 30 minutes idle
        if (time() - ($_SESSION['last_activity'] ?? 0) > 1800) {
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            redirect('admin/login.php');
        }
    }

    public static function user(string $key = ''): mixed {
        if ($key === 'id')   return $_SESSION['user_id']   ?? null;
        if ($key === 'name') return $_SESSION['user_name'] ?? null;
        if ($key === 'role') return $_SESSION['user_role'] ?? null;
        return $_SESSION['user_id'] ?? null;
    }
}
