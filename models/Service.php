<?php
/**
 * WORDORA — Service Model
 */
class Service {
    public static function getActive(): array {
        $db = DB::getInstance();
        return $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
    }

    public static function getAll(): array {
        return DB::getInstance()->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll();
    }

    public static function getById(int $id): ?array {
        $stmt = DB::getInstance()->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public static function getBySlug(string $slug): ?array {
        $stmt = DB::getInstance()->prepare("SELECT * FROM services WHERE slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public static function save(array $data, ?int $id = null): int {
        $db = DB::getInstance();
        if ($id) {
            $stmt = $db->prepare("UPDATE services SET 
                title = ?, slug = ?, icon = ?, description = ?, bullets = ?, 
                image_path = ?, deliverables = ?, metrics_val = ?, metrics_lbl = ?, 
                tag = ?, sort_order = ?, is_active = ?,
                hero_headline = ?, hero_intro = ?, hero_image = ?, what_we_do_lead = ?,
                why_matters = ?, who_for = ?, process_steps = ?, faqs = ?,
                cta_title = ?, cta_desc = ?, cta_btn_text = ?, cta_btn_url = ?
                WHERE id = ?");
            $stmt->execute([
                $data['title'],
                $data['slug'] ?: slugify($data['title']),
                $data['icon'] ?? 'ri-quill-pen-line',
                $data['description'] ?? '',
                $data['bullets'] ?? '',
                $data['image_path'] ?? null,
                $data['deliverables'] ?? null,
                $data['metrics_val'] ?? '+400%',
                $data['metrics_lbl'] ?? 'Growth Lift',
                $data['tag'] ?? 'Core Capability',
                (int)($data['sort_order'] ?? 0),
                isset($data['is_active']) ? (int)$data['is_active'] : 1,
                $data['hero_headline'] ?? null,
                $data['hero_intro'] ?? null,
                $data['hero_image'] ?? null,
                $data['what_we_do_lead'] ?? null,
                $data['why_matters'] ?? null,
                $data['who_for'] ?? null,
                $data['process_steps'] ?? null,
                $data['faqs'] ?? null,
                $data['cta_title'] ?? null,
                $data['cta_desc'] ?? null,
                $data['cta_btn_text'] ?? null,
                $data['cta_btn_url'] ?? null,
                $id
            ]);
            return $id;
        } else {
            $stmt = $db->prepare("INSERT INTO services 
                (title, slug, icon, description, bullets, image_path, deliverables, metrics_val, metrics_lbl, tag, sort_order, is_active,
                 hero_headline, hero_intro, hero_image, what_we_do_lead, why_matters, who_for, process_steps, faqs, cta_title, cta_desc, cta_btn_text, cta_btn_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'],
                $data['slug'] ?: slugify($data['title']),
                $data['icon'] ?? 'ri-quill-pen-line',
                $data['description'] ?? '',
                $data['bullets'] ?? '',
                $data['image_path'] ?? null,
                $data['deliverables'] ?? null,
                $data['metrics_val'] ?? '+400%',
                $data['metrics_lbl'] ?? 'Growth Lift',
                $data['tag'] ?? 'Core Capability',
                (int)($data['sort_order'] ?? 0),
                isset($data['is_active']) ? (int)$data['is_active'] : 1,
                $data['hero_headline'] ?? null,
                $data['hero_intro'] ?? null,
                $data['hero_image'] ?? null,
                $data['what_we_do_lead'] ?? null,
                $data['why_matters'] ?? null,
                $data['who_for'] ?? null,
                $data['process_steps'] ?? null,
                $data['faqs'] ?? null,
                $data['cta_title'] ?? null,
                $data['cta_desc'] ?? null,
                $data['cta_btn_text'] ?? null,
                $data['cta_btn_url'] ?? null,
            ]);
            return (int)$db->lastInsertId();
        }
    }

    public static function delete(int $id): bool {
        $svc = self::getById($id);
        if ($svc) {
            if (!empty($svc['image_path'])) {
                delete_uploaded_file($svc['image_path']);
            }
            if (!empty($svc['hero_image'])) {
                delete_uploaded_file($svc['hero_image']);
            }
        }
        $stmt = DB::getInstance()->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
