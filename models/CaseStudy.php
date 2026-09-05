<?php
/**
 * WORDORA — CaseStudy Model
 */
class CaseStudy {
    public static function ensureTable(): void {
        $db = DB::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS `case_studies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `slug` varchar(255) NOT NULL,
            `client` varchar(200) DEFAULT '',
            `industry` varchar(150) DEFAULT 'SaaS & DevOps',
            `industry_slug` varchar(150) DEFAULT 'saas-devops',
            `badge` varchar(100) DEFAULT 'Commercial Proof',
            `headline_metric` varchar(100) DEFAULT '',
            `headline_label` varchar(150) DEFAULT '',
            `secondary_metric` varchar(100) DEFAULT '',
            `secondary_label` varchar(150) DEFAULT '',
            `tertiary_metric` varchar(100) DEFAULT '',
            `tertiary_label` varchar(150) DEFAULT '',
            `timeline` varchar(100) DEFAULT '6 Month Retainer',
            `location` varchar(150) DEFAULT 'Remote / Global',
            `excerpt` text DEFAULT NULL,
            `challenge` text DEFAULT NULL,
            `solution` text DEFAULT NULL,
            `deliverables` text DEFAULT NULL,
            `results_summary` text DEFAULT NULL,
            `testimonial_quote` text DEFAULT NULL,
            `testimonial_author` varchar(150) DEFAULT NULL,
            `testimonial_role` varchar(200) DEFAULT NULL,
            `content` longtext DEFAULT NULL,
            `image` varchar(255) DEFAULT 'service treasure.png',
            `read_time` varchar(50) DEFAULT '6 min read',
            `sort_order` int(11) DEFAULT 0,
            `is_featured` tinyint(1) DEFAULT 0,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `idx_slug` (`slug`),
            KEY `idx_active_sort` (`is_active`,`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    public static function getIndustries(): array {
        $raw = setting('case_study_categories', '');
        if (!empty($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return [
            ['name' => 'SaaS & DevOps', 'slug' => 'saas-devops'],
            ['name' => 'FinTech & Banking', 'slug' => 'fintech-banking'],
            ['name' => 'D2C & eCommerce', 'slug' => 'd2c-ecommerce'],
            ['name' => 'Startups & Venture', 'slug' => 'startups-venture'],
            ['name' => 'Healthcare & AI', 'slug' => 'healthcare-ai'],
            ['name' => 'Education & EdTech', 'slug' => 'education-edtech'],
            ['name' => 'B2B & Consulting', 'slug' => 'b2b-consulting'],
            ['name' => 'Academic & STEM', 'slug' => 'academic-stem'],
            ['name' => 'Enterprise Technology', 'slug' => 'enterprise-technology']
        ];
    }

    public static function saveIndustry(string $name, string $slug = ''): void {
        $slug = !empty($slug) ? $slug : slugify($name);
        $list = self::getIndustries();
        foreach ($list as $k => $item) {
            if ($item['slug'] === $slug) {
                $list[$k]['name'] = $name;
                Setting::set('case_study_categories', json_encode(array_values($list)));
                return;
            }
        }
        $list[] = ['name' => $name, 'slug' => $slug];
        Setting::set('case_study_categories', json_encode(array_values($list)));
    }

    public static function deleteIndustry(string $slug): void {
        $list = self::getIndustries();
        $filtered = array_filter($list, function($item) use ($slug) {
            return $item['slug'] !== $slug;
        });
        Setting::set('case_study_categories', json_encode(array_values($filtered)));
    }

    public static function getLatest(int $limit = 4): array {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM case_studies WHERE is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAll(string $industry = '', bool $onlyActive = false): array {
        self::ensureTable();
        $db = DB::getInstance();
        $sql = "SELECT * FROM case_studies WHERE 1=1";
        $params = [];

        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }
        if (!empty($industry) && $industry !== 'all') {
            $sql .= " AND (industry_slug = ? OR industry = ?)";
            $params[] = $industry;
            $params[] = $industry;
        }

        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getBySlug(string $slug): ?array {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM case_studies WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public static function getById(int $id): ?array {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM case_studies WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public static function save(array $data, ?int $id = null): int {
        self::ensureTable();
        $db = DB::getInstance();

        if (empty($data['slug'])) {
            $data['slug'] = slugify($data['title']);
        } else {
            $data['slug'] = slugify($data['slug']);
        }

        if ($id) {
            $stmt = $db->prepare("UPDATE case_studies SET
                title = ?, slug = ?, client = ?, industry = ?, industry_slug = ?, badge = ?,
                headline_metric = ?, headline_label = ?, secondary_metric = ?, secondary_label = ?,
                tertiary_metric = ?, tertiary_label = ?, timeline = ?, location = ?,
                excerpt = ?, challenge = ?, solution = ?, deliverables = ?, results_summary = ?,
                testimonial_quote = ?, testimonial_author = ?, testimonial_role = ?, content = ?,
                image = ?, read_time = ?, sort_order = ?, is_featured = ?, is_active = ?,
                meta_title = ?, meta_desc = ?, meta_keywords = ?
                WHERE id = ?");
            $stmt->execute([
                $data['title'], $data['slug'], $data['client'] ?? '', $data['industry'] ?? '', $data['industry_slug'] ?? '', $data['badge'] ?? '',
                $data['headline_metric'] ?? '', $data['headline_label'] ?? '', $data['secondary_metric'] ?? '', $data['secondary_label'] ?? '',
                $data['tertiary_metric'] ?? '', $data['tertiary_label'] ?? '', $data['timeline'] ?? '', $data['location'] ?? '',
                $data['excerpt'] ?? '', $data['challenge'] ?? '', $data['solution'] ?? '', $data['deliverables'] ?? '', $data['results_summary'] ?? '',
                $data['testimonial_quote'] ?? '', $data['testimonial_author'] ?? '', $data['testimonial_role'] ?? '', $data['content'] ?? '',
                $data['image'] ?? 'service treasure.png', $data['read_time'] ?? '6 min read', (int)($data['sort_order'] ?? 0),
                (int)($data['is_featured'] ?? 0), (int)($data['is_active'] ?? 1),
                $data['meta_title'] ?? '', $data['meta_desc'] ?? '', $data['meta_keywords'] ?? '',
                $id
            ]);
            return $id;
        } else {
            $stmt = $db->prepare("INSERT INTO case_studies (
                title, slug, client, industry, industry_slug, badge,
                headline_metric, headline_label, secondary_metric, secondary_label,
                tertiary_metric, tertiary_label, timeline, location,
                excerpt, challenge, solution, deliverables, results_summary,
                testimonial_quote, testimonial_author, testimonial_role, content,
                image, read_time, sort_order, is_featured, is_active,
                meta_title, meta_desc, meta_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['title'], $data['slug'], $data['client'] ?? '', $data['industry'] ?? '', $data['industry_slug'] ?? '', $data['badge'] ?? '',
                $data['headline_metric'] ?? '', $data['headline_label'] ?? '', $data['secondary_metric'] ?? '', $data['secondary_label'] ?? '',
                $data['tertiary_metric'] ?? '', $data['tertiary_label'] ?? '', $data['timeline'] ?? '', $data['location'] ?? '',
                $data['excerpt'] ?? '', $data['challenge'] ?? '', $data['solution'] ?? '', $data['deliverables'] ?? '', $data['results_summary'] ?? '',
                $data['testimonial_quote'] ?? '', $data['testimonial_author'] ?? '', $data['testimonial_role'] ?? '', $data['content'] ?? '',
                $data['image'] ?? 'service treasure.png', $data['read_time'] ?? '6 min read', (int)($data['sort_order'] ?? 0),
                (int)($data['is_featured'] ?? 0), (int)($data['is_active'] ?? 1),
                $data['meta_title'] ?? '', $data['meta_desc'] ?? '', $data['meta_keywords'] ?? ''
            ]);
            return (int)$db->lastInsertId();
        }
    }

    public static function delete(int $id): bool {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM case_studies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function countAll(string $industry = ''): int {
        self::ensureTable();
        $db = DB::getInstance();
        if ($industry && $industry !== 'all') {
            $stmt = $db->prepare("SELECT COUNT(*) FROM case_studies WHERE industry_slug = ? OR industry = ?");
            $stmt->execute([$industry, $industry]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$db->query("SELECT COUNT(*) FROM case_studies")->fetchColumn();
    }
}
