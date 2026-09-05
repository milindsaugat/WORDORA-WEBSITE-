<?php
/**
 * WORDORA — Admin Login
 */
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

$error = '';
try {
    if (Auth::check()) {
        redirect('admin/index.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
            $error = 'Invalid security token. Please refresh and try again.';
        } else {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please enter both username/email and password.';
            } elseif (Auth::login($email, $password)) {
                redirect('admin/index.php');
            } else {
                $error = 'Invalid credentials. Please check your username/email and password.';
            }
        }
    }
} catch (Throwable $e) {
    $error = 'Login Connection Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp, notranslate, noimageindex">
  <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
  <meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet">

  <title>Admin Login — WORDORA Control Panel</title>

  <!-- Favicon -->
  <?php $siteFavicon = setting('site_favicon', '/img/logo.png'); ?>
  <link rel="icon" type="image/png" href="<?= media_url($siteFavicon) ?>">
  <link rel="apple-touch-icon" href="<?= media_url($siteFavicon) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/admin.css') ?>">
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
    .toggle-password-btn:focus {
      outline: 2px solid var(--admin-teal);
    }
  </style>
</head>
<body class="login-page">

  <div class="login-box">
    <div class="login-brand">
      <img src="<?= img('wordorga logo.png') ?>" alt="WORDORA">
      <h2>Control Panel Login</h2>
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

    <form method="POST" action="<?= url('admin/login.php') ?>">
      <?= CSRF::field() ?>
      <div class="form-field">
        <label class="field-label" for="loginEmail">Email Address</label>
        <input type="email" id="loginEmail" name="email" class="field-input" required placeholder="" value="<?= e($_POST['email'] ?? '') ?>" autofocus autocomplete="email">
      </div>

      <div class="form-field" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label class="field-label" for="loginPassword" style="margin-bottom: 0;">Password</label>
          <a href="<?= url('admin/forgot-password.php') ?>" style="font-size: 12px; color: var(--admin-teal); text-decoration: none; font-weight: 600;">
            Forgot Password?
          </a>
        </div>
        <div class="password-wrapper">
          <input type="password" id="loginPassword" name="password" class="field-input" required placeholder="" autocomplete="current-password">
          <button type="button" class="toggle-password-btn" id="togglePasswordBtn" title="Show / Hide Password" aria-label="Show password">
            <i class="ri-eye-line" id="togglePasswordIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; padding: 11px;">
        <i class="ri-lock-line"></i> Sign In to Dashboard
      </button>
    </form>

    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: var(--admin-muted); display: flex; justify-content: center; gap: 12px; align-items: center;">
      <span>Need help?</span>
      <a href="<?= url('admin/forgot-password.php') ?>" style="color: var(--admin-teal); text-decoration: underline; font-weight: 600;">
        Reset with 4-Digit OTP
      </a>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtn = document.getElementById('togglePasswordBtn');
      const passwordInput = document.getElementById('loginPassword');
      const icon = document.getElementById('togglePasswordIcon');

      if (toggleBtn && passwordInput && icon) {
        toggleBtn.addEventListener('click', () => {
          const isPassword = passwordInput.getAttribute('type') === 'password';
          if (isPassword) {
            passwordInput.setAttribute('type', 'text');
            icon.className = 'ri-eye-off-line';
            toggleBtn.setAttribute('aria-label', 'Hide password');
            toggleBtn.setAttribute('title', 'Hide Password');
          } else {
            passwordInput.setAttribute('type', 'password');
            icon.className = 'ri-eye-line';
            toggleBtn.setAttribute('aria-label', 'Show password');
            toggleBtn.setAttribute('title', 'Show Password');
          }
        });
      }
    });
  </script>

</body>
</html>
