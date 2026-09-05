<?php
/**
 * WORDORA — Blog Post Model
 */
class Post {
    public static function getPublished(int $limit = 6, int $offset = 0, ?int $categoryId = null): array {
        $db = DB::getInstance();
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published'";
        $params = [];
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countPublished(?int $categoryId = null): int {
        $db = DB::getInstance();
        $sql = "SELECT COUNT(*) FROM posts WHERE status = 'published'";
        $params = [];
        if ($categoryId) {
            $sql .= " AND category_id = ?";
            $params[] = $categoryId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getBySlug(string $slug): ?array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name
                              FROM posts p
                              LEFT JOIN categories c ON p.category_id = c.id
                              LEFT JOIN users u ON p.author_id = u.id
                              WHERE p.slug = ? AND p.status = 'published' LIMIT 1");
        $stmt->execute([$slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getFeatured(): ?array {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT p.*, c.name as category_name, c.slug as category_slug, c.color as category_color, u.name as author_name
                            FROM posts p
                            LEFT JOIN categories c ON p.category_id = c.id
                            LEFT JOIN users u ON p.author_id = u.id
                            WHERE p.status = 'published'
                            ORDER BY p.views DESC, p.created_at DESC
                            LIMIT 1");
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getRelated(int $postId, int $categoryId, int $limit = 3): array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM posts p
                              LEFT JOIN categories c ON p.category_id = c.id
                              WHERE p.id != ? AND p.category_id = ? AND p.status = 'published'
                              ORDER BY p.created_at DESC LIMIT ?");
        $stmt->execute([$postId, $categoryId, $limit]);
        return $stmt->fetchAll();
    }

    public static function getLatest(int $limit = 4, int $excludeId = 0): array {
        $db = DB::getInstance();
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'published'";
        $params = [];
        if ($excludeId > 0) {
            $sql .= " AND p.id != ?";
            $params[] = $excludeId;
        }
        $sql .= " ORDER BY p.created_at DESC LIMIT " . (int)$limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function incrementViews(int $id): void {
        $db = DB::getInstance();
        $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function search(string $query, int $limit = 6, int $offset = 0): array {
        $db = DB::getInstance();
        $like = '%' . $query . '%';
        $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM posts p
                              LEFT JOIN categories c ON p.category_id = c.id
                              WHERE p.status = 'published' AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)
                              ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$like, $like, $like, $limit, $offset]);
        return $stmt->fetchAll();
    }

    // Admin methods
    public static function getAll(): array {
        $db = DB::getInstance();
        return $db->query("SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function save(array $data, ?int $id = null): bool {
        $db = DB::getInstance();
        if ($id) {
            $stmt = $db->prepare("UPDATE posts SET title=?, slug=?, excerpt=?, content=?, featured_img=?, category_id=?, status=?, read_time=?, meta_title=?, meta_desc=?, meta_keywords=? WHERE id=?");
            return $stmt->execute([
                $data['title'], $data['slug'], $data['excerpt'] ?? '', $data['content'],
                $data['featured_img'] ?? '', $data['category_id'] ?: null, $data['status'] ?? 'draft',
                $data['read_time'] ?? 5, $data['meta_title'] ?? '', $data['meta_desc'] ?? '', $data['meta_keywords'] ?? '', $id
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, content, featured_img, category_id, author_id, status, read_time, meta_title, meta_desc, meta_keywords) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            return $stmt->execute([
                $data['title'], $data['slug'], $data['excerpt'] ?? '', $data['content'],
                $data['featured_img'] ?? '', $data['category_id'] ?: null, $data['author_id'] ?? 1,
                $data['status'] ?? 'draft', $data['read_time'] ?? 5, $data['meta_title'] ?? '', $data['meta_desc'] ?? '', $data['meta_keywords'] ?? ''
            ]);
        }
    }

    public static function delete(int $id): bool {
        $post = self::getById($id);
        if ($post && !empty($post['featured_img'])) {
            delete_uploaded_file($post['featured_img']);
        }
        $db = DB::getInstance();
        return $db->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    }
}
