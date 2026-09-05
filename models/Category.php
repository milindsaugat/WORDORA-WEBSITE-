<?php
class Category {
    public static function getAll(): array {
        $db = DB::getInstance();
        return $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    }
    public static function getById(int $id): ?array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }
    public static function getBySlug(string $slug): ?array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch();
        return $r ?: null;
    }
}
