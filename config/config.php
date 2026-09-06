<?php
/**
 * WORDORA — Smart Dual-Environment Configuration
 * Automatically detects Local XAMPP vs Hostinger Production (wordora.in)
 */

$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (
    php_sapi_name() === 'cli' ||
    in_array($host, ['localhost', '127.0.0.1', '::1']) ||
    str_starts_with($host, 'localhost:') ||
    str_starts_with($host, '127.0.0.1:') ||
    str_contains(strtolower($_SERVER['DOCUMENT_ROOT'] ?? ''), 'xampp')
);

if ($isLocal) {
    // ═══════════════════════════════════════════
    // 1. LOCAL XAMPP DEVELOPMENT CONFIG
    // ═══════════════════════════════════════════
    return [
        'db_host'         => 'localhost',
        'db_name'         => 'wordora_db',
        'db_user'         => 'root',
        'db_pass'         => '',
        'app_env'         => 'development',
        'base_url'        => 'http://localhost/WORDORA',
        'site_name'       => 'WORDORA',
        'mail_host'       => 'smtp.gmail.com',
        'mail_port'       => 587,
        'mail_user'       => '',
        'mail_pass'       => '',
        'mail_from_name'  => 'WORDORA',
        'upload_max_size' => 52428800, // 50MB
    ];
} else {
    // ═══════════════════════════════════════════
    // 2. HOSTINGER PRODUCTION CONFIG (wordora.in)
    // ═══════════════════════════════════════════
    return [
        'db_host'         => 'localhost',
        'db_name'         => 'u105592622_wordora',
        'db_user'         => 'u105592622_wordora',
        'db_pass'         => 'Wordora@2026#Db',
        'app_env'         => 'production',
        'base_url'        => 'https://wordora.in',
        'site_name'       => 'WORDORA',
        'mail_host'       => 'smtp.gmail.com',
        'mail_port'       => 587,
        'mail_user'       => 'info@wordora.in',
        'mail_pass'       => 'bunibbmacdkejtij',
        'mail_from_name'  => 'WORDORA',
        'upload_max_size' => 52428800, // 50MB
    ];
}
