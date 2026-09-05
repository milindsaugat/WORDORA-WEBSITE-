<?php
/**
 * WORDORA — Live Blog Search API Endpoint
 */
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 2) {
    echo json_encode(['success' => true, 'posts' => []]);
    exit;
}

try {
    $posts = Post::search($query, 5, 0);
    $results = [];

    foreach ($posts as $p) {
        $results[] = [
            'id'       => $p['id'],
            'title'    => $p['title'],
            'slug'     => $p['slug'],
            'url'      => url('blog/' . $p['slug']),
            'excerpt'  => truncate(strip_tags($p['excerpt'] ?: $p['content']), 90),
            'category' => $p['category_name'] ?? 'General',
            'image'    => media_url($p['featured_img'], '/img/blog.png'),
        ];
    }

    echo json_encode(['success' => true, 'posts' => $results]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Search query failed']);
}
