<?php
/**
 * WORDORA — Contact Form API Endpoint
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// CSRF Check
if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'CSRF verification failed']);
    exit;
}

// Validate
$errors = [];
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');
$service = trim($_POST['service'] ?? '');
$budget  = trim($_POST['budget'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($name) || mb_strlen($name) < 2) {
    $errors['name'] = 'Please enter your full name';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address';
}
if (empty($message) || mb_strlen($message) < 10) {
    $errors['message'] = 'Please enter a message (at least 10 characters)';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Save to database
try {
    $contactData = [
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'company' => $company,
        'service' => $service,
        'budget'  => $budget,
        'message' => $message,
    ];

    Contact::save($contactData);

    // Dispatch emails in background process (zero browser wait)
    require_once ROOT_PATH . '/core/AsyncMailer.php';
    AsyncMailer::dispatchContact($contactData);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! We\'ll respond within 24 hours.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
