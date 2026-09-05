<?php
/**
 * WORDORA — Admin Forgot & Reset Password with 4-Digit OTP
 */
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

if (Auth::check()) {
    redirect('admin/index.php');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$resetEmail = $_SESSION['reset_email'] ?? '';

// If step 2 requested but no email in session, go back to step 1
if ($step === 2 && empty($resetEmail)) {
    $step = 1;
}

// ═══════════════════════════════════════════════════════════════════
// STEP 1: SEND 4-DIGIT OTP
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_otp') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!str_contains($email, '@')) {
            $email .= '@wordora.in';
        } elseif (str_ends_with($email, '@wordora')) {
            $email .= '.in';
        }

        if (empty($email)) {
            $error = 'Please enter your registered admin email address.';
        } else {
            $db = DB::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'No administrator account found with email: ' . htmlspecialchars($email);
            } else {
                $res = PasswordReset::createOTP($user['email'], 'reset');
                $_SESSION['reset_email'] = $user['email'];
                $_SESSION['last_otp_dev'] = $res['otp']; // Available in local dev mode
                
                flash_set('success', 'A 4-digit security OTP has been dispatched to ' . e($user['email']) . '.');
                redirect('admin/forgot-password.php?step=2');
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════
// STEP 2: VERIFY OTP & RESET PASSWORD
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_password') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $otp = trim($_POST['otp'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($otp) || strlen($otp) !== 4) {
            $error = 'Please enter the valid 4-digit OTP code received on your email.';
        } elseif (empty($newPassword) || strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation password do not match.';
        } elseif (!PasswordReset::verifyOTP($resetEmail, $otp, 'reset')) {
            $error = 'Invalid or expired 4-digit OTP. Please check the code or request a new one.';
        } else {
            // Valid OTP -> Update Password Hash
            $db = DB::getInstance();
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
            $upd->execute([$hash, $resetEmail]);
            Setting::set('admin_display_pass', $newPassword);

            // Invalidate OTP
            PasswordReset::consumeOTP($resetEmail, $otp, 'reset');
            unset($_SESSION['reset_email'], $_SESSION['last_otp_dev']);

            flash_set('success', 'Your password has been successfully reset! You can now log in.');
            redirect('admin/login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Admin Password — WORDORA Control Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
  <style>
    .password-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .password-wrapper .field-input {
      padding-right: 44px;
    }
    .toggle-password-btn {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: var(--admin-muted);
      cursor: pointer;
      padding: 6px;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
      transition: color 0.2s, background-color 0.2s;
    }
    .toggle-password-btn:hover {
      color: var(--admin-teal);
      background-color: var(--admin-teal-pale);
    }
    .otp-input-box {
      font-family: var(--font-mono, monospace);
      font-size: 24px;
      font-weight: 700;
      letter-spacing: 12px;
      text-align: center;
      padding: 12px 16px;
      border: 2px solid var(--admin-teal);
      border-radius: 8px;
      background: #FAF8F5;
      color: var(--admin-navy);
    }
  </style>
</head>
<body class="login-page">

  <div class="login-box">
    <div class="login-brand">
      <img src="<?= img('wordorga logo.png') ?>" alt="WORDORA">
      <h2><?= $step === 1 ? 'Forgot Password' : 'Enter 4-Digit Security OTP' ?></h2>
    </div>

    <?php 
    $flash = flash_get();
    if ($flash): ?>
      <div style="margin-bottom: 20px; padding: 12px; border-radius: 6px; font-size: 13px; <?= $flash['type'] === 'success' ? 'background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0;' : 'background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;' ?> display: flex; align-items: center; gap: 8px;">
        <i class="ri-<?= $flash['type'] === 'success' ? 'checkbox-circle-line' : 'error-warning-line' ?>"></i> <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div style="margin-bottom: 20px; padding: 12px; border-radius: 6px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; display: flex; align-items: center; gap: 8px;">
        <i class="ri-error-warning-line"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
      <!-- ═══════════════════════════════════════════════════════════════
           STEP 1 FORM: ENTER ADMIN EMAIL
           ═══════════════════════════════════════════════════════════════ -->
      <p style="font-size: 13px; color: var(--admin-muted); line-height: 1.5; margin-bottom: 20px; text-align: center;">
        Enter your administrator email address. We will send a secure <strong>4-digit OTP</strong> to verify your identity.
      </p>

      <form method="POST" action="<?= url('admin/forgot-password.php?step=1') ?>">
        <?= CSRF::field() ?>
        <input type="hidden" name="action" value="send_otp">

        <div class="form-field" style="margin-bottom: 24px;">
          <label class="field-label" for="resetEmailInput">Registered Admin Email</label>
          <input type="email" id="resetEmailInput" name="email" class="field-input" required placeholder="" value="<?= e($_POST['email'] ?? '') ?>" autofocus autocomplete="email">
        </div>

        <button type="submit" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; padding: 11px;">
          <i class="ri-mail-send-line"></i> Send 4-Digit Security OTP
        </button>
      </form>

    <?php else: ?>
      <!-- ═══════════════════════════════════════════════════════════════
           STEP 2 FORM: ENTER OTP & NEW PASSWORD
           ═══════════════════════════════════════════════════════════════ -->
      <div style="background: #FAF8F5; border: 1px dashed rgba(74, 139, 140, 0.4); border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 12.5px; color: var(--admin-navy);">
        <span>OTP sent to: <strong><?= e($resetEmail) ?></strong></span>
        <a href="<?= url('admin/forgot-password.php?step=1') ?>" style="float: right; color: var(--admin-teal); text-decoration: none; font-weight: 600;">Change Email</a>
      </div>

      <?php if (!empty($_SESSION['last_otp_dev'])): ?>
        <div style="background: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; padding: 8px 12px; border-radius: 6px; font-size: 12px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
          <span><i class="ri-information-line"></i> Local Dev OTP: <strong><?= e($_SESSION['last_otp_dev']) ?></strong></span>
          <span style="font-size: 10px; color: #B45309;">(Auto-filled for local test)</span>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('admin/forgot-password.php?step=2') ?>">
        <?= CSRF::field() ?>
        <input type="hidden" name="action" value="reset_password">

        <div class="form-field" style="margin-bottom: 18px;">
          <label class="field-label" for="otpInput" style="text-align: center; display: block; font-weight: 700;">
            Enter 4-Digit OTP Code *
          </label>
          <input type="text" id="otpInput" name="otp" class="field-input otp-input-box" required maxlength="4" pattern="\d{4}" placeholder="••••" value="<?= e($_POST['otp'] ?? $_SESSION['last_otp_dev'] ?? '') ?>" autofocus autocomplete="one-time-code">
        </div>

        <div class="form-field" style="margin-bottom: 16px;">
          <label class="field-label" for="newPassword">New Password *</label>
          <div class="password-wrapper">
            <input type="password" id="newPassword" name="new_password" class="field-input" required minlength="6" placeholder="At least 6 characters">
            <button type="button" class="toggle-password-btn" onclick="togglePass('newPassword', 'eyeIcon1')" title="Show / Hide Password">
              <i class="ri-eye-line" id="eyeIcon1"></i>
            </button>
          </div>
        </div>

        <div class="form-field" style="margin-bottom: 24px;">
          <label class="field-label" for="confirmPassword">Confirm New Password *</label>
          <div class="password-wrapper">
            <input type="password" id="confirmPassword" name="confirm_password" class="field-input" required minlength="6" placeholder="Re-enter new password">
            <button type="button" class="toggle-password-btn" onclick="togglePass('confirmPassword', 'eyeIcon2')" title="Show / Hide Password">
              <i class="ri-eye-line" id="eyeIcon2"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; padding: 11px;">
          <i class="ri-shield-keyhole-line"></i> Verify OTP &amp; Update Password
        </button>
      </form>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--admin-border);">
      <a href="<?= url('admin/login.php') ?>" style="font-size: 13px; color: var(--admin-teal); text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
        <i class="ri-arrow-left-line"></i> Back to Sign In
      </a>
    </div>
  </div>

  <script>
    function togglePass(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);
      if (input && icon) {
        const isPass = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPass ? 'text' : 'password');
        icon.className = isPass ? 'ri-eye-off-line' : 'ri-eye-line';
      }
    }
  </script>
</body>
</html>
