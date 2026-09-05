<?php
/**
 * WORDORA — Luxury Google SMTP Mailer Engine
 * Powered by Google SMTP (smtp.gmail.com) with App Password & Pure PHP Socket TLS
 */
class Mailer {
    /**
     * Get SMTP Configuration Settings
     */
    public static function getConfig(): array {
        $cfg = [];
        if (file_exists(ROOT_PATH . '/config/config.php')) {
            $fileCfg = require ROOT_PATH . '/config/config.php';
            if (is_array($fileCfg)) {
                $cfg = $fileCfg;
            }
        }

        return [
            'host'       => setting('smtp_host', $cfg['mail_host'] ?? 'smtp.gmail.com'),
            'port'       => (int)setting('smtp_port', (string)($cfg['mail_port'] ?? 587)),
            'user'       => setting('smtp_user', $cfg['mail_user'] ?? ''),
            'pass'       => setting('smtp_pass', $cfg['mail_pass'] ?? ''),
            'from_email' => setting('contact_email', $cfg['mail_user'] ?? 'info@wordora.in'),
            'from_name'  => setting('site_name', 'WORDORA Editorial'),
            'admin_email'=> setting('contact_email', 'info@wordora.in')
        ];
    }

    /**
     * Send email via Direct Google SMTP Socket (TLS 587 / SSL 465) with mail() fallback
     */
    public static function send(string $to, string $subject, string $htmlBody, array $attachments = []): bool {
        $config = self::getConfig();
        
        $host = $config['host'];
        $port = $config['port'];
        $user = $config['user'];
        $pass = str_replace(' ', '', $config['pass']); // Remove spaces from app password
        $fromEmail = $config['from_email'];
        $fromName = $config['from_name'];

        // Attempt Socket SMTP TLS Dispatch with tight 3-second timeout
        $socketSuccess = false;
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 3);
            if ($socket) {
                stream_set_timeout($socket, 3);
                $greeting = self::readSocket($socket);

                if (str_starts_with($greeting, '220')) {
                    self::writeSocket($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                    self::readSocket($socket);

                    if ($port == 587) {
                        self::writeSocket($socket, "STARTTLS");
                        $tlsRes = self::readSocket($socket);
                        if (str_starts_with($tlsRes, '220')) {
                            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                            self::writeSocket($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                            self::readSocket($socket);
                        }
                    }

                    // Authenticate
                    self::writeSocket($socket, "AUTH LOGIN");
                    $authPrompt = self::readSocket($socket);
                    
                    if (str_starts_with($authPrompt, '334')) {
                        self::writeSocket($socket, base64_encode($user));
                        self::readSocket($socket);
                        self::writeSocket($socket, base64_encode($pass));
                        $authRes = self::readSocket($socket);

                        if (str_starts_with($authRes, '235')) {
                            // MAIL FROM & RCPT TO
                            self::writeSocket($socket, "MAIL FROM: <{$fromEmail}>");
                            self::readSocket($socket);
                            self::writeSocket($socket, "RCPT TO: <{$to}>");
                            self::readSocket($socket);
                            self::writeSocket($socket, "DATA");
                            self::readSocket($socket);

                            // Construct RFC 2822 Headers & Body
                            $headers  = "MIME-Version: 1.0\r\n";
                            $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n";
                            $headers .= "To: <{$to}>\r\n";
                            $headers .= "Reply-To: <{$fromEmail}>\r\n";
                            $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
                            $headers .= "Date: " . date('r') . "\r\n";
                            $headers .= "X-Mailer: WORDORA Engine/2.0\r\n";
                            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                            $headers .= "Content-Transfer-Encoding: base64\r\n\r\n";

                            $payload = $headers . chunk_split(base64_encode($htmlBody)) . "\r\n.\r\n";
                            self::writeSocket($socket, $payload);
                            $dataRes = self::readSocket($socket);

                            if (str_starts_with($dataRes, '250')) {
                                $socketSuccess = true;
                            }
                        }
                    }

                    self::writeSocket($socket, "QUIT");
                    fclose($socket);
                }
            }
        } catch (\Throwable $t) {
            $socketSuccess = false;
        }

        // Archive email locally in uploads/mail_logs for local visibility
        try {
            $logDir = ROOT_PATH . '/uploads/mail_logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0755, true);
            }
            $logEntry = "[" . date('Y-m-d H:i:s') . "] TO: {$to} | SUB: {$subject} | SMTP Status: " . ($socketSuccess ? 'DISPATCHED_SMTP' : 'FALLBACK') . "\n";
            @file_put_contents($logDir . '/dispatches.log', $logEntry, FILE_APPEND);
            @file_put_contents($logDir . '/latest_' . preg_replace('/[^a-zA-Z0-9]/', '_', $subject) . '.html', $htmlBody);
        } catch (\Throwable $e) {}

        // Fallback to PHP native mail()
        if (!$socketSuccess) {
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "Reply-To: {$fromEmail}\r\n";
            $headers .= "X-Mailer: WORDORA Native/2.0\r\n";
            return @mail($to, $subject, $htmlBody, $headers);
        }

        return true;
    }

    private static function writeSocket($socket, string $cmd): void {
        fputs($socket, $cmd . "\r\n");
    }

    private static function readSocket($socket): string {
        $res = '';
        while ($line = fgets($socket, 515)) {
            $res .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $res;
    }

    /**
     * Base Luxury HTML Email Template Wrapping Content
     */
    public static function wrapTemplate(string $eyebrow, string $headline, string $contentHtml, string $footerNote = ''): string {
        $siteName = setting('site_name', 'WORDORA');
        $siteUrl  = base_url('/');
        $year     = date('Y');

        return "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>{$headline}</title>
          <style>
            @media only screen and (max-width: 600px) {
              .email-shell { width: 100% !important; padding: 16px !important; }
              .email-body { padding: 24px 18px !important; }
              .metric-pill { font-size: 28px !important; padding: 12px 18px !important; }
            }
          </style>
        </head>
        <body style='margin: 0; padding: 0; background-color: #0A1322; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; color: #1E293B;'>
          
          <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background: linear-gradient(180deg, #0A1322 0%, #0F1E36 100%); min-height: 100vh; padding: 36px 12px;'>
            <tr>
              <td align='center'>
                
                <!-- Main Container -->
                <table border='0' cellpadding='0' cellspacing='0' width='600' class='email-shell' style='max-width: 600px; width: 100%;'>
                  
                  <!-- Brand Header -->
                  <tr>
                    <td align='center' style='padding-bottom: 24px;'>
                      <div style='display: inline-block; background: #ffffff; padding: 10px 26px; border-radius: 50px; border: 1.5px solid rgba(74, 139, 140, 0.45); box-shadow: 0 8px 24px rgba(0,0,0,0.4);'>
                        <span style='font-family: \"DM Sans\", -apple-system, sans-serif; font-size: 20px; font-weight: 900; letter-spacing: 0.16em; color: #0F1E36; text-transform: uppercase;'>
                          WORD<span style='color: #4A8B8C;'>ORA</span>
                        </span>
                      </div>
                      <div style='color: rgba(255, 255, 255, 0.6); font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin-top: 10px;'>
                        Words That Work. Stories That Sell.
                      </div>
                    </td>
                  </tr>

                  <!-- Card Box -->
                  <tr>
                    <td class='email-body' style='background: #FFFFFF; border-radius: 20px; padding: 36px 32px; box-shadow: 0 20px 50px rgba(0,0,0,0.35); border: 1.5px solid rgba(74, 139, 140, 0.3);'>
                      
                      <!-- Eyebrow -->
                      <div style='font-size: 11.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #4A8B8C; margin-bottom: 8px;'>
                        {$eyebrow}
                      </div>

                      <!-- Main Heading -->
                      <h1 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 24px; font-weight: 700; color: #0F1E36; line-height: 1.25; margin: 0 0 20px 0;'>
                        {$headline}
                      </h1>

                      <!-- Body Content -->
                      <div style='font-size: 14.5px; line-height: 1.65; color: #334155;'>
                        {$contentHtml}
                      </div>

                    </td>
                  </tr>

                  <!-- Footer -->
                  <tr>
                    <td align='center' style='padding-top: 24px;'>
                      <p style='font-size: 12px; color: rgba(255, 255, 255, 0.5); line-height: 1.5; margin: 0;'>
                        " . ($footerNote ?: "This is an automated notification from the WORDORA Studio Platform.") . "<br>
                        &copy; {$year} {$siteName}. All rights reserved. • Jaipur, Rajasthan, India
                      </p>
                    </td>
                  </tr>

                </table>

              </td>
            </tr>
          </table>

        </body>
        </html>";
    }

    /**
     * 1. Send 4-Digit Security OTP (Password Reset & Account Security)
     */
    public static function sendOTP(string $toEmail, string $otp, string $purpose = 'reset'): bool {
        $isReset = ($purpose === 'reset');
        $eyebrow = $isReset ? 'Control Panel • Password Reset' : 'Control Panel • Account Security';
        $headline = $isReset ? 'Your 4-Digit Password Reset Code' : 'Verify Administrator Account Credentials';

        $body = "
        <p style='margin-bottom: 18px;'>
          A request was initiated to verify your identity for the <strong>WORDORA Administrator Account</strong>.
        </p>

        <div style='text-align: center; margin: 28px 0;'>
          <div class='metric-pill' style='display: inline-block; background: #0F1E36; color: #E0F2FE; font-family: \"Courier New\", monospace; font-size: 38px; font-weight: 900; letter-spacing: 14px; padding: 14px 28px; border-radius: 14px; border: 2px solid #4A8B8C; box-shadow: 0 10px 25px rgba(15, 30, 54, 0.25);'>
            {$otp}
          </div>
          <div style='font-size: 12px; color: #64748B; font-weight: 600; margin-top: 10px;'>
            ⏱️ Valid for 15 minutes • Do not share this code with anyone.
          </div>
        </div>

        <p style='font-size: 13px; color: #64748B; background: #FAF8F5; border-left: 3px solid #4A8B8C; padding: 12px 14px; border-radius: 0 8px 8px 0; margin-top: 24px; line-height: 1.5;'>
          <strong>Security Notice:</strong> If you did not make this request, please review your administrator password or contact support immediately.
        </p>";

        $html = self::wrapTemplate($eyebrow, $headline, $body);
        $subject = "[WORDORA Security] Your 4-Digit Code: {$otp}";

        return self::send($toEmail, $subject, $html);
    }

    /**
     * 2. Send New Contact Lead Notification to Admin (info@wordora.in)
     */
    public static function sendContactNotification(array $data): bool {
        $config = self::getConfig();
        $adminEmail = $config['admin_email'];

        $name    = e($data['name'] ?? 'Prospective Client');
        $email   = e($data['email'] ?? '');
        $phone   = e($data['phone'] ?? 'Not provided');
        $company = e($data['company'] ?? 'Not provided');
        $service = e($data['service'] ?? 'General Consultation');
        $message = nl2br(e($data['message'] ?? ''));

        $eyebrow  = '🔥 New Client Inquiry Received';
        $headline = "New Scope Request from {$name}";

        $body = "
        <p style='margin-bottom: 20px;'>
          A new prospective client has submitted an inquiry through the <strong>WORDORA Contact / Scope Consultation form</strong>:
        </p>

        <table border='0' cellpadding='8' cellspacing='0' width='100%' style='background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 12px; margin-bottom: 20px; font-size: 13.5px;'>
          <tr>
            <td width='32%' style='font-weight: 700; color: #0F1E36; border-bottom: 1px solid #E2E8F0;'>Client Name:</td>
            <td style='color: #334155; border-bottom: 1px solid #E2E8F0;'><strong>{$name}</strong></td>
          </tr>
          <tr>
            <td style='font-weight: 700; color: #0F1E36; border-bottom: 1px solid #E2E8F0;'>Email Address:</td>
            <td style='border-bottom: 1px solid #E2E8F0;'><a href='mailto:{$email}' style='color: #4A8B8C; font-weight: 600; text-decoration: none;'>{$email}</a></td>
          </tr>
          <tr>
            <td style='font-weight: 700; color: #0F1E36; border-bottom: 1px solid #E2E8F0;'>Phone Number:</td>
            <td style='border-bottom: 1px solid #E2E8F0;'><a href='tel:{$phone}' style='color: #334155; text-decoration: none;'>{$phone}</a></td>
          </tr>
          <tr>
            <td style='font-weight: 700; color: #0F1E36; border-bottom: 1px solid #E2E8F0;'>Company / Brand:</td>
            <td style='color: #334155; border-bottom: 1px solid #E2E8F0;'>{$company}</td>
          </tr>
          <tr>
            <td style='font-weight: 700; color: #0F1E36;'>Service Requested:</td>
            <td style='color: #0F1E36; font-weight: 700;'><span style='background: #E0F2FE; color: #0369A1; padding: 3px 8px; border-radius: 6px;'>{$service}</span></td>
          </tr>
        </table>

        <div style='font-weight: 700; color: #0F1E36; margin-bottom: 8px; font-size: 14px;'>Project Scope / Message:</div>
        <div style='background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; padding: 14px; color: #1E293B; line-height: 1.6; font-size: 14px;'>
          {$message}
        </div>

        <div style='text-align: center; margin-top: 24px;'>
          <a href='mailto:{$email}?subject=Re:%20WORDORA%20Scope%20Consultation' style='display: inline-block; background: #0F1E36; color: #FFFFFF; padding: 12px 26px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13.5px;'>
            Reply Directly to {$name}
          </a>
        </div>";

        $html = self::wrapTemplate($eyebrow, $headline, $body);
        $subject = "🔥 [New Client Lead] {$name} — {$service}";

        return self::send($adminEmail, $subject, $html);
    }

    /**
     * 3. Send Auto-Reply Acknowledgment to Client
     */
    public static function sendContactAutoReply(array $data): bool {
        $name    = e($data['name'] ?? 'there');
        $toEmail = $data['email'] ?? '';
        if (empty($toEmail)) return false;

        $eyebrow  = 'Scope Request Confirmed';
        $headline = 'We have received your scope inquiry.';

        $body = "
        <p>Dear {$name},</p>
        <p>
          Thank you for reaching out to <strong>WORDORA</strong>. Our team has received your project scope request.
        </p>
        <p>
          We review every inquiry to ensure we match your brand with senior domain writers who understand the vocabulary, tone, and conversion mechanics of your vertical.
        </p>

        <div style='background: #FAF8F5; border-left: 3px solid #4A8B8C; padding: 16px; border-radius: 0 10px 10px 0; margin: 24px 0;'>
          <div style='font-weight: 700; color: #0F1E36; margin-bottom: 6px;'>What Happens Next?</div>
          <ul style='margin: 0; padding-left: 18px; color: #475569; font-size: 13.5px; line-height: 1.6;'>
            <li>Our team will review your timeline and deliverable specifications.</li>
            <li>You will receive a tailored scope breakdown and proposal within <strong>24 hours</strong>.</li>
            <li>If you require an immediate NDA signed, reply directly to this email.</li>
          </ul>
        </div>

        <p style='font-size: 14px; color: #334155;'>
          Warm regards,<br>
          <strong>The WORDORA Team</strong><br>
          WORDORA • Words That Work. Stories That Sell.
        </p>";

        $html = self::wrapTemplate($eyebrow, $headline, $body);
        $subject = "Thank you for contacting WORDORA — Scope Request Confirmed";

        return self::send($toEmail, $subject, $html);
    }

    /**
     * 4. Send Job Application Notification to Admin (info@wordora.in)
     * Premium Editorial Guild Theme — Matches Website Brand Identity
     */
    public static function sendJobApplicationNotification(array $data): bool {
        $config = self::getConfig();
        $adminEmail = $config['admin_email'];

        $name       = e($data['full_name'] ?? 'Candidate');
        $email      = e($data['email'] ?? '');
        $phone      = e($data['phone'] ?? '');
        $address    = e($data['address'] ?? 'Not specified');
        $jobTitle   = e($data['job_title'] ?? 'Editorial Position');
        $experience = e($data['experience_years'] ?? 'Not specified');
        $salary     = e($data['expected_salary'] ?? 'Not specified');
        $linkedin   = e($data['linkedin_url'] ?? '');
        $samples    = e($data['writing_samples'] ?? '');
        $resumeUrl  = !empty($data['resume_path']) ? base_url($data['resume_path']) : '';
        $coverNote  = nl2br(e($data['cover_note'] ?? ''));
        $siteUrl    = base_url('/');
        $year       = date('Y');

        $linkedinRow = '';
        if (!empty($linkedin)) {
            $linkedinRow = "
            <tr>
              <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>LinkedIn</td>
              <td style='padding: 12px 16px; border-bottom: 1px solid #E2E8EE;'><a href='{$linkedin}' target='_blank' style='color: #4A8B8C; font-weight: 600; text-decoration: none; font-size: 13.5px;'>{$linkedin}</a></td>
            </tr>";
        }

        $samplesRow = '';
        if (!empty($samples)) {
            $samplesRow = "
            <tr>
              <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Writing Samples</td>
              <td style='padding: 12px 16px; border-bottom: 1px solid #E2E8EE;'><a href='{$samples}' target='_blank' style='color: #4A8B8C; font-weight: 600; text-decoration: none; font-size: 13.5px;'>{$samples}</a></td>
            </tr>";
        }

        $resumeRow = '';
        if (!empty($resumeUrl)) {
            $resumeRow = "
            <tr>
              <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; width: 38%;'>Resume</td>
              <td style='padding: 12px 16px;'><a href='{$resumeUrl}' target='_blank' style='display: inline-block; background: #0F1E36; color: #FFFFFF; padding: 6px 16px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 700; letter-spacing: 0.02em;'>📄 Download Resume</a></td>
            </tr>";
        }

        $coverSection = '';
        if (!empty($coverNote)) {
            $coverSection = "
            <div style='margin-top: 24px;'>
              <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 16px; font-weight: 700; color: #0F1E36; margin-bottom: 10px;'>Cover Note</div>
              <div style='background: #FFFFFF; border: 1.5px solid #E2E8EE; border-left: 3px solid #4A8B8C; border-radius: 0 10px 10px 0; padding: 16px 18px; color: #334155; line-height: 1.7; font-size: 14px;'>{$coverNote}</div>
            </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>New Application: {$name}</title>
          <style>
            @media only screen and (max-width: 600px) {
              .email-container { width: 100% !important; padding: 12px !important; }
              .email-card { padding: 24px 16px !important; border-radius: 16px !important; }
              .hero-banner { padding: 28px 16px !important; }
            }
          </style>
        </head>
        <body style='margin: 0; padding: 0; background-color: #FAF8F5; font-family: Inter, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>

          <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #FAF8F5; padding: 32px 12px;'>
            <tr>
              <td align='center'>
                <table border='0' cellpadding='0' cellspacing='0' width='620' class='email-container' style='max-width: 620px; width: 100%;'>

                  <!-- Brand Header -->
                  <tr>
                    <td align='center' style='padding-bottom: 20px;'>
                      <div style='display: inline-block; padding: 8px 24px;'>
                        <span style='font-family: \"DM Sans\", Inter, sans-serif; font-size: 22px; font-weight: 900; letter-spacing: 0.18em; color: #0F1E36; text-transform: uppercase;'>WORD<span style='color: #4A8B8C;'>ORA</span></span>
                      </div>
                      <div style='color: #4A627A; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px;'>Editorial Talent Pipeline</div>
                    </td>
                  </tr>

                  <!-- Hero Banner -->
                  <tr>
                    <td class='hero-banner' style='background: linear-gradient(135deg, #0F1E36 0%, #1B2A4A 100%); border-radius: 20px 20px 0 0; padding: 36px 32px; text-align: center;'>
                      <div style='display: inline-block; background: rgba(74, 139, 140, 0.2); border: 1px solid rgba(74, 139, 140, 0.4); padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #6BA8A9; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px;'>
                        💼 New Application Received
                      </div>
                      <h1 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 24px; font-weight: 700; color: #FFFFFF; line-height: 1.3; margin: 12px 0 8px 0;'>{$name}</h1>
                      <div style='font-size: 14px; color: rgba(255,255,255,0.7); font-weight: 500;'>Applied for <span style='color: #6BA8A9; font-weight: 700;'>{$jobTitle}</span></div>
                    </td>
                  </tr>

                  <!-- Main Card Body -->
                  <tr>
                    <td class='email-card' style='background: #FFFFFF; border-radius: 0 0 20px 20px; padding: 32px; border: 1px solid #E2E8EE; border-top: none; box-shadow: 0 8px 30px rgba(15, 30, 54, 0.06);'>

                      <!-- Candidate Details Table -->
                      <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 16px; font-weight: 700; color: #0F1E36; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;'>Candidate Profile</div>

                      <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 12px; overflow: hidden; margin-bottom: 8px;'>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Full Name</td>
                          <td style='padding: 12px 16px; color: #0F1E36; font-weight: 700; font-size: 14px; border-bottom: 1px solid #E2E8EE;'>{$name}</td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Position</td>
                          <td style='padding: 12px 16px; border-bottom: 1px solid #E2E8EE;'><span style='display: inline-block; background: #D4EAEA; color: #1B2A4A; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 13px;'>{$jobTitle}</span></td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Email</td>
                          <td style='padding: 12px 16px; border-bottom: 1px solid #E2E8EE;'><a href='mailto:{$email}' style='color: #4A8B8C; font-weight: 600; text-decoration: none; font-size: 13.5px;'>{$email}</a></td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Phone</td>
                          <td style='padding: 12px 16px; color: #334155; font-size: 13.5px; border-bottom: 1px solid #E2E8EE;'>{$phone}</td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Location</td>
                          <td style='padding: 12px 16px; color: #334155; font-size: 13.5px; border-bottom: 1px solid #E2E8EE;'>{$address}</td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Experience</td>
                          <td style='padding: 12px 16px; color: #334155; font-size: 13.5px; border-bottom: 1px solid #E2E8EE;'>{$experience}</td>
                        </tr>
                        <tr>
                          <td style='padding: 12px 16px; font-weight: 600; color: #4A627A; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E2E8EE; width: 38%;'>Expected CTC</td>
                          <td style='padding: 12px 16px; color: #0F1E36; font-weight: 700; font-size: 13.5px; border-bottom: 1px solid #E2E8EE;'>{$salary}</td>
                        </tr>
                        {$linkedinRow}
                        {$samplesRow}
                        {$resumeRow}
                      </table>

                      {$coverSection}

                      <!-- Action Buttons -->
                      <div style='text-align: center; margin-top: 28px; padding-top: 24px; border-top: 1.5px solid #E2E8EE;'>
                        <a href='mailto:{$email}?subject=Your%20Application%20at%20WORDORA%20for%20" . urlencode($jobTitle) . "' style='display: inline-block; background: linear-gradient(135deg, #0F1E36 0%, #1B2A4A 100%); color: #FFFFFF; padding: 13px 28px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13.5px; letter-spacing: 0.02em; box-shadow: 0 4px 14px rgba(15, 30, 54, 0.2);'>
                          Reply to {$name} →
                        </a>
                      </div>

                    </td>
                  </tr>

                  <!-- Footer -->
                  <tr>
                    <td align='center' style='padding-top: 24px;'>
                      <p style='font-size: 12px; color: #8DA4B8; line-height: 1.5; margin: 0;'>
                        Automated notification from the WORDORA Editorial Talent Pipeline.<br>
                        &copy; {$year} WORDORA • Words That Work. Stories That Sell.
                      </p>
                    </td>
                  </tr>

                </table>
              </td>
            </tr>
          </table>

        </body>
        </html>";

        $subject = "💼 [New Application] {$name} — {$jobTitle}";
        return self::send($adminEmail, $subject, $html);
    }

    /**
     * 5. Send Job Application Confirmation to Candidate
     * Premium Editorial Guild Theme — Matches Website Brand Identity
     */
    public static function sendJobApplicationAutoReply(array $data): bool {
        $name     = e($data['full_name'] ?? 'Candidate');
        $toEmail  = $data['email'] ?? '';
        $jobTitle = e($data['job_title'] ?? 'Editorial Position');
        if (empty($toEmail)) return false;

        $siteUrl = base_url('/');
        $year    = date('Y');

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
          <title>Application Received — WORDORA</title>
          <style>
            @media only screen and (max-width: 600px) {
              .email-container { width: 100% !important; padding: 12px !important; }
              .email-card { padding: 24px 16px !important; }
              .hero-banner { padding: 28px 16px !important; }
              .step-grid td { display: block !important; width: 100% !important; padding: 0 0 16px 0 !important; }
            }
          </style>
        </head>
        <body style='margin: 0; padding: 0; background-color: #FAF8F5; font-family: Inter, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>

          <table border='0' cellpadding='0' cellspacing='0' width='100%' style='background-color: #FAF8F5; padding: 32px 12px;'>
            <tr>
              <td align='center'>
                <table border='0' cellpadding='0' cellspacing='0' width='620' class='email-container' style='max-width: 620px; width: 100%;'>

                  <!-- Brand Header -->
                  <tr>
                    <td align='center' style='padding-bottom: 20px;'>
                      <div style='display: inline-block; padding: 8px 24px;'>
                        <span style='font-family: \"DM Sans\", Inter, sans-serif; font-size: 22px; font-weight: 900; letter-spacing: 0.18em; color: #0F1E36; text-transform: uppercase;'>WORD<span style='color: #4A8B8C;'>ORA</span></span>
                      </div>
                      <div style='color: #4A627A; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px;'>Words That Work. Stories That Sell.</div>
                    </td>
                  </tr>

                  <!-- Hero Banner -->
                  <tr>
                    <td class='hero-banner' style='background: linear-gradient(135deg, #0F1E36 0%, #1B2A4A 100%); border-radius: 20px 20px 0 0; padding: 40px 32px; text-align: center;'>
                      <div style='display: inline-block; background: rgba(74, 139, 140, 0.2); border: 1px solid rgba(74, 139, 140, 0.4); padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #6BA8A9; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px;'>
                        Application Confirmed
                      </div>
                      <h1 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 26px; font-weight: 700; color: #FFFFFF; line-height: 1.3; margin: 12px 0 8px 0;'>Welcome to the WORDORA<br>Editorial Guild Pipeline</h1>
                      <div style='font-size: 14px; color: rgba(255,255,255,0.65); font-weight: 500; margin-top: 8px;'>Your application for <span style='color: #6BA8A9; font-weight: 700;'>{$jobTitle}</span> has been received.</div>
                    </td>
                  </tr>

                  <!-- Main Card Body -->
                  <tr>
                    <td class='email-card' style='background: #FFFFFF; border-radius: 0 0 20px 20px; padding: 36px 32px; border: 1px solid #E2E8EE; border-top: none; box-shadow: 0 8px 30px rgba(15, 30, 54, 0.06);'>

                      <p style='font-size: 15px; color: #334155; line-height: 1.7; margin: 0 0 20px 0;'>
                        Dear <strong style='color: #0F1E36;'>{$name}</strong>,
                      </p>
                      <p style='font-size: 14.5px; color: #334155; line-height: 1.7; margin: 0 0 16px 0;'>
                        Thank you for your interest in joining <strong style='color: #0F1E36;'>WORDORA</strong> as a <strong style='color: #4A8B8C;'>{$jobTitle}</strong>. We have received your complete application — including your resume, portfolio samples, and professional background.
                      </p>
                      <p style='font-size: 14.5px; color: #334155; line-height: 1.7; margin: 0 0 28px 0;'>
                        At WORDORA, we hold written language to an institutional standard. Every application is personally reviewed by our Senior Managing Editors — not automated screening bots.
                      </p>

                      <!-- What Happens Next — 3-Step Cards -->
                      <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 17px; font-weight: 700; color: #0F1E36; margin-bottom: 16px;'>What Happens Next</div>

                      <!-- Step 1 -->
                      <div style='background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 12px; padding: 18px 20px; margin-bottom: 12px; display: flex; gap: 14px;'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                          <tr>
                            <td width='44' valign='top'>
                              <div style='width: 36px; height: 36px; background: linear-gradient(135deg, #4A8B8C, #6BA8A9); border-radius: 10px; text-align: center; line-height: 36px; color: #FFFFFF; font-weight: 800; font-size: 15px;'>1</div>
                            </td>
                            <td style='padding-left: 12px;'>
                              <div style='font-weight: 700; color: #0F1E36; font-size: 14px; margin-bottom: 4px;'>Portfolio &amp; Technical Review</div>
                              <div style='font-size: 13px; color: #4A627A; line-height: 1.5;'>Domain editors evaluate your writing depth, editorial voice, and subject-matter expertise (3–5 business days).</div>
                            </td>
                          </tr>
                        </table>
                      </div>

                      <!-- Step 2 -->
                      <div style='background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 12px; padding: 18px 20px; margin-bottom: 12px;'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                          <tr>
                            <td width='44' valign='top'>
                              <div style='width: 36px; height: 36px; background: linear-gradient(135deg, #4A8B8C, #6BA8A9); border-radius: 10px; text-align: center; line-height: 36px; color: #FFFFFF; font-weight: 800; font-size: 15px;'>2</div>
                            </td>
                            <td style='padding-left: 12px;'>
                              <div style='font-weight: 700; color: #0F1E36; font-size: 14px; margin-bottom: 4px;'>Paid Editorial Assessment Sprint</div>
                              <div style='font-size: 13px; color: #4A627A; line-height: 1.5;'>Shortlisted candidates receive a compensated test assignment matching your expertise vertical.</div>
                            </td>
                          </tr>
                        </table>
                      </div>

                      <!-- Step 3 -->
                      <div style='background: #FAF8F5; border: 1px solid #E2E8EE; border-radius: 12px; padding: 18px 20px; margin-bottom: 28px;'>
                        <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                          <tr>
                            <td width='44' valign='top'>
                              <div style='width: 36px; height: 36px; background: linear-gradient(135deg, #4A8B8C, #6BA8A9); border-radius: 10px; text-align: center; line-height: 36px; color: #FFFFFF; font-weight: 800; font-size: 15px;'>3</div>
                            </td>
                            <td style='padding-left: 12px;'>
                              <div style='font-weight: 700; color: #0F1E36; font-size: 14px; margin-bottom: 4px;'>Founding Team Conversation</div>
                              <div style='font-size: 13px; color: #4A627A; line-height: 1.5;'>Final alignment call with the editorial leadership to discuss culture, workflow, and long-term collaboration.</div>
                            </td>
                          </tr>
                        </table>
                      </div>

                      <!-- Closing -->
                      <div style='border-top: 1.5px solid #E2E8EE; padding-top: 24px;'>
                        <p style='font-size: 14.5px; color: #334155; line-height: 1.7; margin: 0 0 6px 0;'>
                          Thank you for your time and craft,
                        </p>
                        <p style='font-size: 14.5px; color: #0F1E36; line-height: 1.5; margin: 0;'>
                          <strong>The Editorial Hiring Board</strong><br>
                          <span style='color: #4A8B8C; font-weight: 600;'>WORDORA</span> • Words That Work. Stories That Sell.
                        </p>
                      </div>

                    </td>
                  </tr>

                  <!-- Footer -->
                  <tr>
                    <td align='center' style='padding-top: 24px;'>
                      <p style='font-size: 12px; color: #8DA4B8; line-height: 1.5; margin: 0;'>
                        This is an automated acknowledgment from WORDORA.<br>
                        &copy; {$year} WORDORA • <a href='{$siteUrl}' style='color: #4A8B8C; text-decoration: none; font-weight: 600;'>wordora.in</a>
                      </p>
                    </td>
                  </tr>

                </table>
              </td>
            </tr>
          </table>

        </body>
        </html>";

        $subject = "Application Received for {$jobTitle} — WORDORA";
        return self::send($toEmail, $subject, $html);
    }
}

