<?php
/**
 * WORDORA — Helper Functions
 */

// Define ROOT_PATH if not set
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Start output buffering and session if not started
if (ob_get_level() === 0) {
    ob_start();
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload core classes
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/core/' . $class . '.php',
        ROOT_PATH . '/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

/**
 * Sanitize output for HTML context
 */
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

/**
 * Automatically detect base path (e.g. '/wordora' or '')
 */
function app_base(): string {
    static $base = null;
    if ($base !== null) return $base;

    // Method 1: Compare ROOT_PATH with DOCUMENT_ROOT
    if (!empty($_SERVER['DOCUMENT_ROOT']) && defined('ROOT_PATH')) {
        $realRoot = str_replace('\\', '/', realpath(ROOT_PATH) ?: ROOT_PATH);
        $realDoc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']);
        if (str_starts_with(strtolower($realRoot), strtolower($realDoc))) {
            $rel = substr($realRoot, strlen($realDoc));
            $rel = trim(str_replace('\\', '/', $rel), '/');
            $base = $rel === '' ? '' : '/' . $rel;
            return $base;
        }
    }

    // Method 2: SCRIPT_NAME prefix before /public, /admin, or /views
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (preg_match('~^(.*?)/(?:public|admin|views)(?:/.*)?$~i', $script, $m)) {
        $base = rtrim($m[1], '/');
        return $base;
    }

    // Method 3: Fallback directory calculation
    $dir = str_replace('\\', '/', dirname($script));
    $dir = preg_replace('~/public(?:/.*)?$~i', '', $dir);
    $dir = rtrim($dir, '/.');
    $base = ($dir === '' || $dir === '/') ? '' : $dir;
    return $base;
}

/**
 * Generate site URL relative to base path
 */
function url(string $path = ''): string {
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'mailto:') || str_starts_with($path, 'tel:')) {
        return $path;
    }

    // Normalize legacy service-detail.php?slug=xxx to service/xxx
    if (preg_match('~^/?service-detail(?:\.php)?\?slug=([a-zA-Z0-9\-]+)$~i', $path, $sm)) {
        $path = 'service/' . $sm[1];
    }
    // Normalize legacy case-study-detail.php?slug=xxx to case-study/xxx
    if (preg_match('~^/?case-study-detail(?:\.php)?\?slug=([a-zA-Z0-9\-]+)$~i', $path, $csm)) {
        $path = 'case-study/' . $csm[1];
    }

    $base = app_base();
    $path = '/' . ltrim($path, '/');
    
    // Avoid double prefixing if path already includes the base
    if ($base !== '' && (str_starts_with($path, $base . '/') || $path === $base)) {
        return $path;
    }

    if ($path === '/') {
        return $base ? $base . '/' : '/';
    }
    return $base . $path;
}

/**
 * Generate the full absolute base URL
 */
function base_url(string $path = ''): string {
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . $host . url($path);
}

/**
 * Get current page full URL
 */
function current_url(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return $protocol . $host . $uri;
}

/**
 * Asset URL helper (for /assets/...)
 */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Image URL helper (for /img/...)
 */
function img(string $filename): string {
    return url('img/' . ltrim($filename, '/'));
}

/**
 * Upload URL helper (for /uploads/...)
 */
function upload(string $path): string {
    return url('uploads/' . ltrim($path, '/'));
}

/**
 * Resolve any media path (handles uploads, imgs, and external URLs)
 */
function media_url(string $path, string $default = ''): string {
    if (empty($path)) {
        return $default ? media_url($default) : '';
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    $clean = ltrim($path, '/');
    if (!str_starts_with($clean, 'uploads/') && !str_starts_with($clean, 'img/') && !str_starts_with($clean, 'assets/')) {
        if (file_exists(ROOT_PATH . '/img/' . $clean)) {
            return url('img/' . $clean);
        }
        if (file_exists(ROOT_PATH . '/uploads/' . $clean)) {
            return url('uploads/' . $clean);
        }
        return url('img/' . $clean);
    }
    return url($clean);
}

/**
 * Safely delete an uploaded file from disk (only within /uploads/ directory)
 */
function delete_uploaded_file(?string $path): bool {
    if (empty($path)) {
        return false;
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return false;
    }
    $clean = ltrim($path, '/');
    if (str_starts_with($clean, 'uploads/')) {
        $fullPath = ROOT_PATH . '/' . $clean;
        if (file_exists($fullPath) && is_file($fullPath)) {
            return @unlink($fullPath);
        }
    }
    return false;
}

/**
 * Get a site setting value by key (with real-time cache invalidation support)
 */
function setting(string $key, string $default = '', bool $forceRefresh = false): string {
    static $cache = [];
    if ($key === '__CLEAR_CACHE__') {
        $cache = [];
        return '';
    }
    if ($forceRefresh) {
        $cache[$key] = $default;
        return $default;
    }
    if (empty($cache)) {
        try {
            $db = DB::getInstance();
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            return $default;
        }
    }
    return $cache[$key] ?? $default;
}

/**
 * Flash message helpers
 */
function flash_set(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/**
 * Bulletproof Redirect helper (handles HTTP headers, output buffer clearing, and fallback JS)
 */
function redirect(string $url): void {
    $target = (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) ? $url : url($url);
    
    // Clear any buffered output so clean HTTP redirect headers can be sent
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    
    if (!headers_sent()) {
        header("Location: " . $target, true, 302);
    } else {
        echo '<script>window.location.replace(' . json_encode($target) . ');</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"></noscript>';
    }
    exit;
}

/**
 * Truncate text to a given length
 */
function truncate(string $text, int $length = 150, string $suffix = '...'): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Generate a URL-safe slug from text
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

/**
 * Smart resolver for default service artwork / illustration
 */
function resolve_service_illustration(string $slug): string {
    $slug = strtolower($slug);
    if (str_contains($slug, 'seo') || str_contains($slug, 'search')) {
        return 'Blog service.png';
    }
    if (str_contains($slug, 'social') || str_contains($slug, 'instagram') || str_contains($slug, 'media')) {
        if (str_contains($slug, 'thought') || str_contains($slug, 'leader')) {
            return 'servcie page.png';
        }
        return 'social media service.png';
    }
    if (str_contains($slug, 'tech') || str_contains($slug, 'doc') || str_contains($slug, 'code') || str_contains($slug, 'api')) {
        return 'service treasure.png';
    }
    if (str_contains($slug, 'brand') || str_contains($slug, 'copy')) {
        return 'brand content.png';
    }
    if (str_contains($slug, 'thought') || str_contains($slug, 'leader') || str_contains($slug, 'executive') || str_contains($slug, 'c-suite')) {
        return 'servcie page.png';
    }
    if (str_contains($slug, 'acad') || str_contains($slug, 'research') || str_contains($slug, 'paper') || str_contains($slug, 'thesis')) {
        return 'acedmic.png';
    }
    if (str_contains($slug, 'blog') || str_contains($slug, 'article')) {
        return 'blog.png';
    }
    return 'home section 2.png';
}

/**
 * Format date for display
 */
function format_date(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

/**
 * Calculate approximate read time for content
 */
function read_time(string $content): int {
    $words = str_word_count(strip_tags($content));
    return max(1, (int)ceil($words / 200));
}

/**
 * Check if current page matches path for nav active state
 */
function is_active(string $path): string {
    $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $current = rtrim($current, '/');
    $base = rtrim(app_base(), '/');
    $target = rtrim(url($path), '/');

    if ($path === '/' || $path === '') {
        return ($current === '' || $current === $base || $current === $base . '/public') ? 'active' : '';
    }
    if ($target !== '' && str_starts_with($current, $target)) return 'active';
    return '';
}

/**
 * Check if a date string is within recent N days (default 7 days)
 */
function is_recent_new(?string $datetime, int $days = 7): bool {
    if (empty($datetime)) return false;
    $time = strtotime($datetime);
    if ($time === false) return false;
    $diff = time() - $time;
    return ($diff >= 0 && $diff <= ($days * 86400));
}

/**
 * Universal Global Directional Gradient Generator for all Hero Sections
 * Reads hero_overlay_opacity (0-100%) and hero_gradient_coverage (30-90%)
 */
function get_hero_directional_gradient(): string {
    $opacity = (int)setting('hero_overlay_opacity', '85');
    $opacity = max(10, min(100, $opacity));
    $coverage = (int)setting('hero_gradient_coverage', '55');
    $coverage = max(25, min(90, $coverage));

    $alphaLeft = round(0.85 + (($opacity / 100) * 0.14), 2); // 0.85 to 0.99
    $alphaMid  = round(0.40 + (($opacity / 100) * 0.40), 2); // 0.40 to 0.80

    $midStop   = round($coverage * 0.60); // e.g. 33%
    $fadeStop  = round($coverage * 0.92); // e.g. 51%
    $clearStop = $coverage;               // e.g. 55%

    return "linear-gradient(90deg, rgba(15, 30, 54, {$alphaLeft}) 0%, rgba(15, 30, 54, {$alphaMid}) {$midStop}%, rgba(15, 30, 54, 0.16) {$fadeStop}%, rgba(15, 30, 54, 0.0) {$clearStop}%, rgba(15, 30, 54, 0.0) 100%)";
}



