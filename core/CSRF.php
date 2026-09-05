<?php
/**
 * WORDORA — CSRF Token Provider
 */
class CSRF {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function generate(): string {
        return self::token();
    }

    public static function verify(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function validate(string $token): bool {
        return self::verify($token);
    }

    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function meta(): string {
        return '<meta name="csrf-token" content="' . self::token() . '">';
    }
}
