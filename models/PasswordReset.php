<?php
/**
 * WORDORA — PasswordReset & OTP Service Model
 */
class PasswordReset {
    public static function ensureTable(): void {
        $db = DB::getInstance();
        $db->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(200) NOT NULL,
            `otp` varchar(10) NOT NULL,
            `token` varchar(100) DEFAULT NULL,
            `purpose` varchar(50) DEFAULT 'reset',
            `expires_at` timestamp NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `idx_email_purpose` (`email`, `purpose`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Generate a 4-digit OTP and send via email
     */
    public static function createOTP(string $email, string $purpose = 'reset'): array {
        self::ensureTable();
        $db = DB::getInstance();

        // Invalidate old OTPs for this email & purpose
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ? AND purpose = ?");
        $stmt->execute([$email, $purpose]);

        // Generate 4-digit numeric code
        $otp = (string)random_int(1000, 9999);
        $token = bin2hex(random_bytes(16));
        $expiresAt = date('Y-m-d H:i:s', time() + 900); // 15 minutes validity

        $ins = $db->prepare("INSERT INTO password_resets (email, otp, token, purpose, expires_at) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$email, $otp, $token, $purpose, $expiresAt]);

        // Dispatch Email
        $mailSent = self::sendOTPEmail($email, $otp, $purpose);

        return [
            'success'   => true,
            'otp'       => $otp,
            'token'     => $token,
            'email'     => $email,
            'mail_sent' => $mailSent
        ];
    }

    /**
     * Verify 4-digit OTP
     */
    public static function verifyOTP(string $email, string $otp, string $purpose = 'reset'): bool {
        self::ensureTable();
        $db = DB::getInstance();

        $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND purpose = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute([$email, trim($otp), $purpose]);
        return (bool)$stmt->fetch();
    }

    /**
     * Consume / delete OTP after successful verification
     */
    public static function consumeOTP(string $email, string $otp, string $purpose = 'reset'): void {
        self::ensureTable();
        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ? AND otp = ? AND purpose = ?");
        $stmt->execute([$email, trim($otp), $purpose]);
    }

    /**
     * Send HTML formatted email containing the 4-digit OTP via Mailer
     */
    public static function sendOTPEmail(string $email, string $otp, string $purpose = 'reset'): bool {
        if (!class_exists('Mailer')) {
            require_once ROOT_PATH . '/core/Mailer.php';
        }
        return Mailer::sendOTP($email, $otp, $purpose);
    }
}
