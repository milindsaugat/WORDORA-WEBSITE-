<?php
/**
 * WORDORA — User Model
 */
class User {
    public static function getById(int $id): ?array {
        $stmt = DB::getInstance()->prepare("SELECT id, name, email, role, avatar FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }
}
