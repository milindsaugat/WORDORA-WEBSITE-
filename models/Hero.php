<?php
/**
 * WORDORA — Universal Multi-Mode Hero Banner Model
 * Supports Single Image, Multi-Image Slider, and Video Hero modes across all pages.
 */
class Hero {
    public static function getActiveSlides(string $page = 'home'): array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM hero_slides WHERE page = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$page]);
        $slides = $stmt->fetchAll();
        
        // If page has no specific slides, fallback to home slides or empty
        if (empty($slides) && $page !== 'home') {
            $stmt = $db->prepare("SELECT * FROM hero_slides WHERE page = 'home' AND is_active = 1 ORDER BY sort_order ASC, id ASC");
            $stmt->execute();
            $slides = $stmt->fetchAll();
        }
        return $slides;
    }

    public static function getAll(string $page = 'home'): array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM hero_slides WHERE page = ? ORDER BY sort_order ASC, id DESC");
        $stmt->execute([$page]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM hero_slides WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function save(array $data, ?int $id = null): bool {
        $db = DB::getInstance();
        $page = $data['page'] ?? 'home';
        $fields = [
            $page,
            $data['banner_type'] ?? 'slider',
            $data['eyebrow'] ?? '',
            $data['title'],
            $data['subtitle'] ?? '',
            $data['media_url'] ?? '',
            $data['video_url'] ?? '',
            $data['button_primary_text'] ?? 'Get a Proposal',
            $data['button_primary_url'] ?? '/contact',
            $data['button_secondary_text'] ?? 'Our Services',
            $data['button_secondary_url'] ?? '/services',
            (int)($data['sort_order'] ?? 0),
            isset($data['is_active']) ? 1 : 0,
        ];

        if ($id) {
            $fields[] = $id;
            $stmt = $db->prepare("UPDATE hero_slides SET page=?, banner_type=?, eyebrow=?, title=?, subtitle=?, media_url=?, video_url=?, button_primary_text=?, button_primary_url=?, button_secondary_text=?, button_secondary_url=?, sort_order=?, is_active=? WHERE id=?");
        } else {
            $stmt = $db->prepare("INSERT INTO hero_slides (page, banner_type, eyebrow, title, subtitle, media_url, video_url, button_primary_text, button_primary_url, button_secondary_text, button_secondary_url, sort_order, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        }
        return $stmt->execute($fields);
    }

    public static function delete(int $id): bool {
        $slide = self::getById($id);
        if ($slide) {
            if (!empty($slide['media_url'])) {
                delete_uploaded_file($slide['media_url']);
            }
            if (!empty($slide['video_url'])) {
                delete_uploaded_file($slide['video_url']);
            }
        }
        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM hero_slides WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getHeroMode(string $page = 'home'): string {
        $pageKey = 'hero_mode_' . $page;
        $mode = setting($pageKey, '');
        if (!empty($mode)) {
            return $mode;
        }
        return setting('hero_mode', 'slider');
    }

    public static function setHeroMode(string $page, string $mode): bool {
        return Setting::set('hero_mode_' . $page, $mode);
    }
}
