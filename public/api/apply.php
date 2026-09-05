<?php
/**
 * WORDORA — Job Application API Handler with Resume File Upload & Address
 */
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/CSRF.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$jobTitle     = trim($_POST['job_title'] ?? 'General Application');
$fullName     = trim($_POST['full_name'] ?? '');
$email        = trim($_POST['email'] ?? '');
$phone        = trim($_POST['phone'] ?? '');
$address      = trim($_POST['address'] ?? '');
$linkedinUrl  = trim($_POST['linkedin_url'] ?? '');
$samples      = trim($_POST['writing_samples'] ?? '');
$expYears     = trim($_POST['experience_years'] ?? '');
$salaryExp    = trim($_POST['expected_salary'] ?? '');
$coverNote    = trim($_POST['cover_note'] ?? '');

$errors = [];

if (empty($fullName)) {
    $errors['full_name'] = 'Please enter your full name.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email address.';
}
if (empty($phone)) {
    $errors['phone'] = 'Please provide your contact number.';
}
if (empty($address)) {
    $errors['address'] = 'Please enter your current city or address.';
}

$resumePath = null;

// Handle Resume File Upload
if (isset($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['resume'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowedExts = ['pdf', 'doc', 'docx'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExt, $allowedExts)) {
            $errors['resume'] = 'Invalid file type. Please upload a PDF, DOC, or DOCX document.';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $errors['resume'] = 'File size exceeds 10MB limit.';
        } else {
            $uploadDir = ROOT_PATH . '/uploads/resumes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
            $targetPath = $uploadDir . $safeName;
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $resumePath = 'uploads/resumes/' . $safeName;
            } else {
                $errors['resume'] = 'Failed to save uploaded file. Please try again.';
            }
        }
    } else {
        $errors['resume'] = 'Error uploading resume file. Error code: ' . $file['error'];
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors, 'message' => 'Please fix the errors below and try again.']);
    exit;
}

// Attempt to store in database if table exists or create it
try {
    $db = DB::getInstance();
    $db->exec("CREATE TABLE IF NOT EXISTS job_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        job_title VARCHAR(255) NOT NULL,
        full_name VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        address VARCHAR(255) NULL,
        linkedin_url VARCHAR(255) NULL,
        writing_samples TEXT NULL,
        resume_path VARCHAR(255) NULL,
        experience_years VARCHAR(50) NULL,
        expected_salary VARCHAR(100) NULL,
        cover_note TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Add address column if it doesn't exist
    try {
        $db->exec("ALTER TABLE job_applications ADD COLUMN address VARCHAR(255) NULL AFTER phone;");
    } catch (\Throwable $ignored) {}

    // Add resume_path column if it doesn't exist
    try {
        $db->exec("ALTER TABLE job_applications ADD COLUMN resume_path VARCHAR(255) NULL AFTER writing_samples;");
    } catch (\Throwable $ignored) {}

    $jobData = [
        'job_title'        => $jobTitle,
        'full_name'        => $fullName,
        'email'            => $email,
        'phone'            => $phone,
        'address'          => $address,
        'linkedin_url'     => $linkedinUrl,
        'writing_samples'  => $samples,
        'resume_path'      => $resumePath,
        'experience_years' => $expYears,
        'expected_salary'  => $salaryExp,
        'cover_note'       => $coverNote
    ];

    $stmt = $db->prepare("INSERT INTO job_applications (job_title, full_name, email, phone, address, linkedin_url, writing_samples, resume_path, experience_years, expected_salary, cover_note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $jobTitle,
        $fullName,
        $email,
        $phone,
        $address,
        $linkedinUrl,
        $samples,
        $resumePath,
        $expYears,
        $salaryExp,
        $coverNote
    ]);
    // Dispatch emails in background process (zero browser wait)
    require_once ROOT_PATH . '/core/AsyncMailer.php';
    AsyncMailer::dispatchJob($jobData);

    echo json_encode([
        'success' => true,
        'message' => 'Application received successfully! Our editorial talent team will review your profile & resume and contact you within 48 hours.'
    ]);
    exit;
} catch (\Throwable $e) {
    // If DB fails, log error and continue gracefully
    error_log('Job Application DB Error: ' . $e->getMessage());
    echo json_encode([
        'success' => true,
        'message' => 'Application received successfully! Our editorial talent team will review your profile & resume and contact you within 48 hours.'
    ]);
    exit;
}
