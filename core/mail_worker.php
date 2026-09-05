<?php
/**
 * WORDORA — Background Mail Worker (CLI Process)
 * Runs detached in background to deliver Google SMTP emails with zero browser wait time
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('CLI execution only.');
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/Mailer.php';

$taskId = $argv[1] ?? '';
if (empty($taskId)) {
    exit(0);
}

$taskFile = ROOT_PATH . '/uploads/mail_queue/' . basename($taskId) . '.json';
if (!file_exists($taskFile)) {
    exit(0);
}

$jsonRaw = file_get_contents($taskFile);
$data = json_decode($jsonRaw, true);
@unlink($taskFile); // Clean up task file immediately

if (!$data || empty($data['type'])) {
    exit(0);
}

$type = $data['type'];
$payload = $data['payload'] ?? [];

if ($type === 'contact') {
    Mailer::sendContactNotification($payload);
    Mailer::sendContactAutoReply($payload);
} elseif ($type === 'job') {
    Mailer::sendJobApplicationNotification($payload);
    Mailer::sendJobApplicationAutoReply($payload);
}
