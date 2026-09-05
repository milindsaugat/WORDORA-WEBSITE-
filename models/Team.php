<?php
/**
 * WORDORA — Team Model
 */
class Team {
    public static function getActive(): array {
        return DB::getInstance()->query("SELECT * FROM team WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    }
    public static function getAll(): array {
        return DB::getInstance()->query("SELECT * FROM team ORDER BY sort_order ASC")->fetchAll();
    }
}
