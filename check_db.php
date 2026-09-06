<?php
/**
 * WORDORA — Quick Database Diagnostic Tool
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', __DIR__);
$configFile = __DIR__ . '/config/config.php';

if (!file_exists($configFile)) {
    die("<h2 style='color:red;'>Error: config/config.php file not found!</h2>");
}

$cfg = require $configFile;

echo "<!DOCTYPE html><html><head><title>WORDORA DB Check</title><style>body{font-family:sans-serif;padding:30px;background:#0F1E36;color:#fff;line-height:1.6;} .box{background:#1E293B;padding:24px;border-radius:12px;max-width:650px;margin:0 auto;} h2{margin-top:0;} pre{background:#000;padding:12px;border-radius:8px;overflow:auto;}</style></head><body><div class='box'>";

echo "<h2>WORDORA Database Connection Diagnostic</h2>";
echo "<p><strong>Host:</strong> " . htmlspecialchars($cfg['db_host'] ?? '') . "</p>";
echo "<p><strong>Database:</strong> " . htmlspecialchars($cfg['db_name'] ?? '') . "</p>";
echo "<p><strong>User:</strong> " . htmlspecialchars($cfg['db_user'] ?? '') . "</p>";
echo "<p><strong>Password Set?</strong> " . (!empty($cfg['db_pass']) ? "<span style='color:#34D399;'>YES (" . strlen($cfg['db_pass']) . " characters)</span>" : "<span style='color:#EF4444;'>NO (Password is empty!)</span>") . "</p>";
echo "<hr style='border:none;border-top:1px solid rgba(255,255,255,0.1);margin:20px 0;'>";

try {
    $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<h3 style='color:#34D399;'>✓ Database Connected Successfully!</h3>";
    
    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Total Tables in Database:</strong> " . count($tables) . "</p>";
    
    if (in_array('services', $tables)) {
        $svcCount = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
        echo "<p><strong>Services in Table:</strong> <span style='font-size:1.2em;color:#38BDF8;font-weight:bold;'>$svcCount</span> (Expected: 14)</p>";
        
        $svcs = $pdo->query("SELECT id, title FROM services ORDER BY id ASC")->fetchAll();
        echo "<ul>";
        foreach ($svcs as $s) {
            echo "<li>ID {$s['id']}: " . htmlspecialchars($s['title']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:#EF4444;'><strong>Table 'services' is MISSING!</strong> Please import wordora_latest_clean.sql in phpMyAdmin.</p>";
    }
    
    if (in_array('settings', $tables)) {
        $sec3c = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'home_sec3c_enabled'")->fetchColumn();
        echo "<p><strong>home_sec3c_enabled:</strong> " . ($sec3c === '1' ? "<span style='color:#34D399;'>1 (ON)</span>" : "<span style='color:#F59E0B;'>$sec3c (OFF/Not set)</span>") . "</p>";
    } else {
        echo "<p style='color:#EF4444;'><strong>Table 'settings' is MISSING!</strong> Please import wordora_latest_clean.sql in phpMyAdmin.</p>";
    }

} catch (Exception $e) {
    echo "<h3 style='color:#EF4444;'>✗ Database Connection Failed:</h3>";
    echo "<p style='color:#FCA5A5; font-weight:bold; font-size:1.1em;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check that the password in <code>config/config.php</code> matches your Hostinger database user password.</p>";
}

echo "</div></body></html>";
