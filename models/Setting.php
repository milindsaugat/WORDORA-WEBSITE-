<?php
/**
 * WORDORA — Site Setting Model
 */
class Setting {
    public static function get(string $key, string $default = ''): string {
        return setting($key, $default);
    }

    public static function set(string $key, string $value): bool {
        $db = DB::getInstance();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $ok = $stmt->execute([$key, $value, $value]);
        if ($ok) {
            setting($key, $value, true);
        }
        return $ok;
    }
}
