<?php
/**
 * WORDORA — 1-Click Database Auto-Installer & Hostinger Migration Engine
 * 
 * Features:
 * - Automatically connects to Local XAMPP or Hostinger MySQL (phpMyAdmin)
 * - Auto-creates all tables and populates full database from wordora_db.sql
 * - Option to enter custom Hostinger DB Credentials or use config/config.php
 * - Step-by-step validation & live deployment readiness report
 */

define('ROOT_PATH', __DIR__);

if (!function_exists('e')) {
    function e($str) {
        return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

$configFile = ROOT_PATH . '/config/config.php';
$sqlFile    = ROOT_PATH . '/wordora_db.sql';

// Read default config
$currentConfig = file_exists($configFile) ? (require $configFile) : [
    'db_host' => 'localhost',
    'db_name' => 'wordora_db',
    'db_user' => 'root',
    'db_pass' => '',
    'app_env' => 'development',
];

$step = $_GET['step'] ?? 'welcome';
$error = '';
$successLogs = [];
$tableStats = [];
$dbConnected = false;

// Determine environment
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (
    php_sapi_name() === 'cli' ||
    in_array($host, ['localhost', '127.0.0.1', '::1']) ||
    str_starts_with($host, 'localhost:') ||
    str_starts_with($host, '127.0.0.1:') ||
    str_contains(strtolower($_SERVER['DOCUMENT_ROOT'] ?? ''), 'xampp')
);

// Form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_install') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = trim($_POST['admin_pass'] ?? 'admin123');
    $adminEmail = trim($_POST['admin_email'] ?? 'info@wordora.in');

    if (empty($dbName) || empty($dbUser)) {
        $error = 'Database Name and Database User are required.';
    } else {
        try {
            // 1. Test Connection
            $pdo = null;
            try {
                // Try connecting directly to the database
                $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
                $pdo = new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $ex) {
                // If DB doesn't exist, try connecting to MySQL server to create it (Local only)
                if ($isLocal) {
                    $serverDsn = "mysql:host={$dbHost};charset=utf8mb4";
                    $serverPdo = new PDO($serverDsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                } else {
                    throw $ex;
                }
            }

            if ($pdo) {
                $dbConnected = true;
                $successLogs[] = "✓ Successfully connected to MySQL database: <strong>{$dbName}</strong>";

                // 2. Read wordora_db.sql
                if (!file_exists($sqlFile)) {
                    throw new Exception("SQL dump file 'wordora_db.sql' was not found in project root.");
                }

                $sqlContent = file_get_contents($sqlFile);
                if (empty($sqlContent)) {
                    throw new Exception("SQL dump file 'wordora_db.sql' is empty.");
                }

                // Automatically detect and convert UTF-16LE / UTF-16BE to UTF-8
                if (str_starts_with($sqlContent, "\xFF\xFE")) {
                    $sqlContent = mb_convert_encoding(substr($sqlContent, 2), 'UTF-8', 'UTF-16LE');
                } elseif (str_starts_with($sqlContent, "\xFE\xFF")) {
                    $sqlContent = mb_convert_encoding(substr($sqlContent, 2), 'UTF-8', 'UTF-16BE');
                } elseif (str_starts_with($sqlContent, "\xEF\xBB\xBF")) {
                    // Strip UTF-8 BOM
                    $sqlContent = substr($sqlContent, 3);
                }

                // Normalize line endings
                $sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);

                $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");

                // 3. Clean comments & Execute SQL statements
                $lines = explode("\n", $sqlContent);
                $cleanLines = [];
                foreach ($lines as $line) {
                    $trimmed = trim($line);
                    // Ignore pure comment lines, empty lines, or hash comments
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    // Ignore mysqldump conditional comment directives
                    if (preg_match('/^\/\*!.*?\*\/;?$/', $trimmed)) {
                        continue;
                    }
                    $cleanLines[] = $line;
                }
                $cleanSql = implode("\n", $cleanLines);

                $statements = array_filter(
                    array_map('trim', preg_split('/;\s*[\r\n]+/', $cleanSql)),
                    fn($stmt) => !empty($stmt)
                );

                $executedCount = 0;
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                        $executedCount++;
                    }
                }
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

                $successLogs[] = "✓ Successfully executed {$executedCount} SQL statements from <code>wordora_db.sql</code>";

                // 4. Ensure Admin User
                $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
                $stmtAdmin = $pdo->prepare("INSERT INTO users (id, name, email, password, role, created_at) VALUES (1, ?, ?, ?, 'admin', NOW()) ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), password = VALUES(password)");
                $stmtAdmin->execute([$adminUser, $adminEmail, $hashedPass]);

                $stmtSetPass = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_display_pass', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmtSetPass->execute([$adminPass]);

                $stmtSetEmail = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('admin_email', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                $stmtSetEmail->execute([$adminEmail]);

                $successLogs[] = "✓ Admin account verified: <strong>{$adminEmail}</strong> (Username: {$adminUser})";

                // 5. Update config/config.php if on Hostinger / Production
                if (!$isLocal) {
                    $configContent = "<?php\n/**\n * WORDORA — Smart Dual-Environment Configuration\n */\n" .
                        "\$host = \$_SERVER['HTTP_HOST'] ?? '';\n" .
                        "\$isLocal = (php_sapi_name() === 'cli' || in_array(\$host, ['localhost', '127.0.0.1', '::1']) || str_starts_with(\$host, 'localhost:') || str_starts_with(\$host, '127.0.0.1:') || str_contains(strtolower(\$_SERVER['DOCUMENT_ROOT'] ?? ''), 'xampp'));\n\n" .
                        "if (\$isLocal) {\n" .
                        "    return [\n" .
                        "        'db_host'         => 'localhost',\n" .
                        "        'db_name'         => 'wordora_db',\n" .
                        "        'db_user'         => 'root',\n" .
                        "        'db_pass'         => '',\n" .
                        "        'app_env'         => 'development',\n" .
                        "        'base_url'        => 'http://localhost/word',\n" .
                        "        'site_name'       => 'WORDORA',\n" .
                        "        'upload_max_size' => 52428800,\n" .
                        "    ];\n" .
                        "} else {\n" .
                        "    return [\n" .
                        "        'db_host'         => " . var_export($dbHost, true) . ",\n" .
                        "        'db_name'         => " . var_export($dbName, true) . ",\n" .
                        "        'db_user'         => " . var_export($dbUser, true) . ",\n" .
                        "        'db_pass'         => " . var_export($dbPass, true) . ",\n" .
                        "        'app_env'         => 'production',\n" .
                        "        'base_url'        => (isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . (\$_SERVER['HTTP_HOST'] ?? 'wordora.in'),\n" .
                        "        'site_name'       => 'WORDORA',\n" .
                        "        'upload_max_size' => 52428800,\n" .
                        "    ];\n" .
                        "}\n";
                    @file_put_contents($configFile, $configContent);
                    $successLogs[] = "✓ Updated <code>config/config.php</code> with live credentials.";
                }

                // 6. Gather Table Stats
                $tablesQuery = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tablesQuery as $tbl) {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM `{$tbl}`")->fetchColumn();
                    $tableStats[$tbl] = $cnt;
                }

                $step = 'complete';
            }
        } catch (Exception $e) {
            $error = 'Database Setup Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WORDORA — 1-Click Database Auto-Installer & Hostinger Migration</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=JetBrains+Mono:wght@500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  
  <style>
    :root {
      --wdr-navy: #0F1E36;
      --wdr-navy-deep: #081120;
      --wdr-teal: #4A8B8C;
      --wdr-teal-light: #6BA8A9;
      --wdr-teal-pale: #D4EAEA;
      --wdr-canvas: #FAF8F5;
      --wdr-white: #FFFFFF;
      --wdr-border: rgba(74, 139, 140, 0.35);
      --font-display: 'Playfair Display', serif;
      --font-body: 'DM Sans', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-body);
      background: var(--wdr-navy-deep);
      color: #E2E8F0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
      position: relative;
      overflow-x: hidden;
    }

    /* Ambient background artwork */
    body::before {
      content: '';
      position: absolute;
      top: -10%;
      left: -10%;
      width: 50vw;
      height: 50vw;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(74, 139, 140, 0.15) 0%, rgba(15, 30, 54, 0) 70%);
      pointer-events: none;
    }

    .installer-box {
      width: 100%;
      max-width: 780px;
      background: #0F1E36;
      border: 1.5px dashed var(--wdr-border);
      border-radius: 24px;
      padding: 40px 48px;
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
      position: relative;
      z-index: 2;
    }

    .installer-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .installer-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 16px;
      border-radius: 9999px;
      background: rgba(74, 139, 140, 0.15);
      border: 1px dashed var(--wdr-teal);
      color: var(--wdr-teal-light);
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      font-family: var(--font-mono);
      margin-bottom: 12px;
    }

    .installer-title {
      font-family: var(--font-display);
      font-size: 2rem;
      color: #FFFFFF;
      margin-bottom: 8px;
    }

    .installer-subtitle {
      font-size: 0.95rem;
      color: #94A3B8;
      max-width: 560px;
      margin: 0 auto;
      line-height: 1.5;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      display: block;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--wdr-teal-light);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 6px;
    }

    .form-input {
      width: 100%;
      padding: 12px 16px;
      border-radius: 12px;
      border: 1.5px dashed var(--wdr-border);
      background: rgba(255, 255, 255, 0.04);
      color: #FFFFFF;
      font-size: 0.95rem;
      font-family: inherit;
      transition: all 0.2s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--wdr-teal);
      border-style: solid;
      background: rgba(255, 255, 255, 0.08);
      box-shadow: 0 0 0 3px rgba(74, 139, 140, 0.2);
    }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    @media (max-width: 640px) {
      .grid-2 { grid-template-columns: 1fr; }
      .installer-box { padding: 28px 20px; }
    }

    .btn-submit {
      width: 100%;
      padding: 16px 24px;
      border-radius: 14px;
      background: var(--wdr-teal);
      color: #FFFFFF;
      border: none;
      font-size: 1rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: all 0.25s ease;
      box-shadow: 0 8px 24px rgba(74, 139, 140, 0.35);
      margin-top: 12px;
    }

    .btn-submit:hover {
      background: var(--wdr-teal-light);
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(74, 139, 140, 0.45);
    }

    .alert {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 24px;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-error {
      background: rgba(239, 68, 68, 0.15);
      border: 1px solid rgba(239, 68, 68, 0.4);
      color: #FCA5A5;
    }

    .alert-success {
      background: rgba(34, 197, 94, 0.15);
      border: 1px solid rgba(34, 197, 94, 0.4);
      color: #86EFAC;
    }

    .log-list {
      background: rgba(0, 0, 0, 0.35);
      border-radius: 14px;
      padding: 18px 22px;
      margin-bottom: 24px;
      font-family: var(--font-mono);
      font-size: 0.85rem;
      line-height: 1.8;
      border: 1px solid rgba(255, 255, 255, 0.08);
    }

    .table-stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 10px;
      margin-bottom: 28px;
    }

    .table-stat-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(74, 139, 140, 0.25);
      padding: 10px 14px;
      border-radius: 10px;
      text-align: left;
    }

    .table-stat-name {
      font-family: var(--font-mono);
      font-size: 0.72rem;
      color: #94A3B8;
      display: block;
      margin-bottom: 2px;
    }

    .table-stat-count {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--wdr-teal-light);
    }

    .btn-action-group {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
  </style>
</head>
<body>

  <div class="installer-box">
    
    <div class="installer-header">
      <span class="installer-badge"><i class="ri-rocket-line"></i> HOSTINGER &amp; LOCAL DEPLOY ENGINE</span>
      <h1 class="installer-title">WORDORA Database Auto-Installer</h1>
      <p class="installer-subtitle">
        Yeh tool automatically <code>wordora_db.sql</code> se sabhi tables, curated content, hero banners aur admin login create &amp; sync kar dega.
      </p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error">
        <i class="ri-error-warning-line" style="font-size: 1.2rem;"></i>
        <span><?= e($error) ?></span>
      </div>
    <?php endif; ?>

    <?php if ($step === 'welcome'): ?>
      <form method="POST" action="install.php?step=run">
        <input type="hidden" name="action" value="run_install">

        <div style="background: rgba(74, 139, 140, 0.08); border: 1px dashed var(--wdr-border); border-radius: 16px; padding: 20px; margin-bottom: 20px;">
          <div style="font-size: 0.85rem; font-weight: 700; color: var(--wdr-teal-light); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-server-line"></i> 1. DATABASE CREDENTIALS (HOSTINGER / LOCAL)
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Database Host</label>
              <input type="text" name="db_host" class="form-input" value="<?= e($currentConfig['db_host'] ?? 'localhost') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Database Name</label>
              <input type="text" name="db_name" class="form-input" value="<?= e($currentConfig['db_name'] ?? 'wordora_db') ?>" placeholder="e.g. u105592622_wordora" required>
            </div>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Database Username</label>
              <input type="text" name="db_user" class="form-input" value="<?= e($currentConfig['db_user'] ?? 'root') ?>" placeholder="e.g. u105592622_wordora" required>
            </div>
            <div class="form-group">
              <label class="form-label">Database Password</label>
              <input type="password" name="db_pass" class="form-input" value="<?= e($currentConfig['db_pass'] ?? '') ?>" placeholder="Hostinger Database Password">
            </div>
          </div>
        </div>

        <div style="background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--wdr-border); border-radius: 16px; padding: 20px; margin-bottom: 24px;">
          <div style="font-size: 0.85rem; font-weight: 700; color: var(--wdr-teal-light); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-shield-user-line"></i> 2. ADMIN DASHBOARD CREDENTIALS
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Admin Username</label>
              <input type="text" name="admin_user" class="form-input" value="admin" required>
            </div>
            <div class="form-group">
              <label class="form-label">Admin Password</label>
              <input type="text" name="admin_pass" class="form-input" value="admin123" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Admin Email</label>
            <input type="email" name="admin_email" class="form-input" value="info@wordora.in" required>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          <i class="ri-flashlight-fill"></i> Setup &amp; Import Database Now
        </button>
      </form>

    <?php elseif ($step === 'complete'): ?>
      
      <div class="alert alert-success">
        <i class="ri-checkbox-circle-fill" style="font-size: 1.3rem;"></i>
        <div>
          <strong>Deployment Successful!</strong> All tables and curated content have been populated.
        </div>
      </div>

      <div class="log-list">
        <?php foreach ($successLogs as $log): ?>
          <div><?= $log ?></div>
        <?php endforeach; ?>
      </div>

      <div style="margin-bottom: 12px;">
        <span class="form-label">Verified Database Tables (<?= count($tableStats) ?> Tables Ready):</span>
      </div>

      <div class="table-stats-grid">
        <?php foreach ($tableStats as $tbl => $cnt): ?>
          <div class="table-stat-card">
            <span class="table-stat-name"><?= e($tbl) ?></span>
            <span class="table-stat-count"><?= (int)$cnt ?> rows</span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="btn-action-group">
        <a href="index.php" class="btn-submit" style="text-decoration: none; background: #1B2A4A;">
          <i class="ri-home-4-line"></i> View Live Website
        </a>
        <a href="admin/login.php" class="btn-submit" style="text-decoration: none;">
          <i class="ri-dashboard-line"></i> Go to Admin Studio
        </a>
      </div>

    <?php endif; ?>

  </div>

</body>
</html>
