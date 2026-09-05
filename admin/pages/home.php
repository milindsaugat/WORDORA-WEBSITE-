<?php
ob_start();
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
Auth::requireAuth();

// AJAX Instant Toggle for Section 03C Master Switch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'toggle_sec3c') {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Security token expired. Please refresh the page.']);
            exit;
        }
        $newState = (!empty($_POST['state']) && $_POST['state'] !== '0' && $_POST['state'] !== 'false') ? '1' : '0';
        Setting::set('home_sec3c_enabled', $newState);
        echo json_encode([
            'success' => true,
            'enabled' => ($newState === '1'),
            'message' => ($newState === '1') 
                ? 'Master Switch turned ON! 7 Dev Services are now active.' 
                : 'Master Switch turned OFF! 7 Dev Services are now hidden.'
        ]);
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

$adminTitle = 'Homepage Visual Section Studio';

include ROOT_PATH . '/admin/includes/header.php';
include ROOT_PATH . '/admin/includes/homepage-sections-editor.php';
include ROOT_PATH . '/admin/includes/footer.php';

ob_end_flush();
