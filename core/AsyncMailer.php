<?php
/**
 * WORDORA — Instant Non-Blocking Asynchronous Mail Dispatcher
 */
class AsyncMailer {
    public static function dispatchContact(array $contactData): void {
        self::runAsync('contact', $contactData);
    }

    public static function dispatchJob(array $jobData): void {
        self::runAsync('job', $jobData);
    }

    private static function runAsync(string $type, array $payload): void {
        $queueDir = ROOT_PATH . '/uploads/mail_queue';
        if (!is_dir($queueDir)) {
            @mkdir($queueDir, 0755, true);
        }

        $taskId = time() . '_' . bin2hex(random_bytes(6));
        $taskFile = $queueDir . '/' . $taskId . '.json';
        @file_put_contents($taskFile, json_encode([
            'type' => $type,
            'payload' => $payload,
            'created_at' => time()
        ]));

        $runnerScript = ROOT_PATH . '/core/mail_worker.php';
        $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

        // Locate PHP Binary
        $phpBin = PHP_BINARY;
        if (empty($phpBin) || !file_exists($phpBin)) {
            if ($isWindows) {
                if (file_exists('C:\\xampp\\php\\php.exe')) {
                    $phpBin = 'C:\\xampp\\php\\php.exe';
                } else {
                    $phpBin = 'php';
                }
            } else {
                $phpBin = 'php';
            }
        }

        try {
            if ($isWindows) {
                $cmd = "start /B \"\" " . escapeshellarg($phpBin) . " " . escapeshellarg($runnerScript) . " " . escapeshellarg($taskId) . " > NUL 2>&1";
                pclose(popen($cmd, "r"));
            } else {
                $cmd = escapeshellarg($phpBin) . " " . escapeshellarg($runnerScript) . " " . escapeshellarg($taskId) . " > /dev/null 2>&1 &";
                exec($cmd);
            }
        } catch (\Throwable $t) {
            // Fallback direct dispatch if CLI process spawn is restricted
            if (class_exists('Mailer')) {
                if ($type === 'contact') {
                    Mailer::sendContactNotification($payload);
                    Mailer::sendContactAutoReply($payload);
                } elseif ($type === 'job') {
                    Mailer::sendJobApplicationNotification($payload);
                    Mailer::sendJobApplicationAutoReply($payload);
                }
            }
        }
    }
}
