<?php
class Contact {
    public static function ensureTable(): void {
        $db = DB::getInstance();
        try {
            $db->exec("ALTER TABLE contacts ADD COLUMN phone VARCHAR(100) NULL AFTER email;");
        } catch (\Throwable $t) {}
    }

    public static function save(array $data): bool {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, company, service, budget, message, ip_address) VALUES (?,?,?,?,?,?,?,?)");
        return $stmt->execute([
            $data['name'], 
            $data['email'], 
            $data['phone'] ?? '',
            $data['company'] ?? '',
            $data['service'] ?? '', 
            $data['budget'] ?? '', 
            $data['message'],
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
    }

    public static function getAll(string $status = ''): array {
        self::ensureTable();
        $db = DB::getInstance();
        if ($status && $status !== 'all') {
            $stmt = $db->prepare("SELECT * FROM contacts WHERE status = ? ORDER BY submitted_at DESC");
            $stmt->execute([$status]);
            return $stmt->fetchAll();
        }
        return $db->query("SELECT * FROM contacts ORDER BY submitted_at DESC")->fetchAll();
    }

    public static function updateStatus(int $id, string $status): bool {
        $db = DB::getInstance();
        return $db->prepare("UPDATE contacts SET status = ? WHERE id = ?")->execute([$status, $id]);
    }

    public static function markRead(int $id): bool {
        return self::updateStatus($id, 'read');
    }

    public static function delete(int $id): bool {
        $db = DB::getInstance();
        return $db->prepare("DELETE FROM contacts WHERE id = ?")->execute([$id]);
    }

    public static function countByStatus(string $status = 'unread'): int {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM contacts WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    public static function countAll(): int {
        return (int)DB::getInstance()->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    }
}
