<?php
/**
 * WORDORA — PDO Database Singleton (Resilient Local & Hostinger Connection)
 */
class DB {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $cfg = require ROOT_PATH . '/config/config.php';
            
            try {
                $dsn = "mysql:host={$cfg['db_host']};dbname={$cfg['db_name']};charset=utf8mb4";
                self::$instance = new PDO($dsn, $cfg['db_user'], $cfg['db_pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Secondary fallback attempt
                try {
                    if ($cfg['app_env'] === 'development') {
                        $prodDsn = "mysql:host=localhost;dbname=u105592622_wordora;charset=utf8mb4";
                        self::$instance = new PDO($prodDsn, 'u105592622_wordora', $cfg['db_pass'] ?? '', [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ]);
                    } else {
                        $localDsn = "mysql:host=localhost;dbname=wordora_db;charset=utf8mb4";
                        self::$instance = new PDO($localDsn, 'root', '', [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ]);
                    }
                } catch (PDOException $e2) {
                    throw new Exception("Database Connection Failed: " . $e->getMessage());
                }
            }
        }
        return self::$instance;
    }
}
