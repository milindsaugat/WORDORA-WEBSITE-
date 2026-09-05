<?php
define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
Auth::requireAuth();

$adminTitle = 'Site Settings & Security';
$error = '';

$db = DB::getInstance();
$adminUserId = (int)(Auth::user('id') ?? 1);
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$adminUserId]);
$adminUser = $stmt->fetch() ?: ['id' => 1, 'name' => 'Admin', 'email' => 'info@wordora.in'];

// ═══════════════════════════════════════════════════════════════════
// 1. ACTION: SEND 4-DIGIT SECURITY OTP FOR ACCOUNT CHANGES
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_account_otp') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        flash_set('error', 'Security token expired. Please try again.');
    } else {
        $res = PasswordReset::createOTP($adminUser['email'], 'account_change');
        $_SESSION['last_account_otp_dev'] = $res['otp'];
        flash_set('success', 'A 4-digit security OTP has been sent to ' . e($adminUser['email']) . ' to authorize credential changes.');
    }
    redirect('admin/settings/index.php#account-security');
}

// ═══════════════════════════════════════════════════════════════════
// 2. ACTION: UPDATE ADMIN EMAIL (OTP-PROTECTED)
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_admin_email') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        flash_set('error', 'Security token expired. Please try again.');
    } else {
        $newEmail = trim($_POST['new_email'] ?? '');
        $otp = trim($_POST['email_otp'] ?? '');

        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Please provide a valid new email address.');
        } elseif (empty($otp) || strlen($otp) !== 4) {
            flash_set('error', 'Please enter the 4-digit OTP sent to ' . e($adminUser['email']) . '.');
        } elseif (!PasswordReset::verifyOTP($adminUser['email'], $otp, 'account_change')) {
            flash_set('error', 'Invalid or expired 4-digit OTP. Please request a new security code.');
        } else {
            $chk = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
            $chk->execute([$newEmail, $adminUserId]);
            if ($chk->fetch()) {
                flash_set('error', 'This email address is already registered to another user.');
            } else {
                $upd = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
                $upd->execute([$newEmail, $adminUserId]);
                PasswordReset::consumeOTP($adminUser['email'], $otp, 'account_change');
                unset($_SESSION['last_account_otp_dev']);
                flash_set('success', 'Admin email address updated successfully to ' . e($newEmail) . '!');
            }
        }
    }
    redirect('admin/settings/index.php#account-security');
}

// ═══════════════════════════════════════════════════════════════════
// 3. ACTION: UPDATE ADMIN PASSWORD (OTP-PROTECTED + EYE TOGGLE)
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_admin_password') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        flash_set('error', 'Security token expired. Please try again.');
    } else {
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';
        $otp = trim($_POST['password_otp'] ?? '');

        if (empty($newPass) || strlen($newPass) < 6) {
            flash_set('error', 'New password must be at least 6 characters long.');
        } elseif ($newPass !== $confirmPass) {
            flash_set('error', 'New password and confirmation do not match.');
        } elseif (empty($otp) || strlen($otp) !== 4) {
            flash_set('error', 'Please enter the 4-digit OTP sent to ' . e($adminUser['email']) . '.');
        } elseif (!PasswordReset::verifyOTP($adminUser['email'], $otp, 'account_change')) {
            flash_set('error', 'Invalid or expired 4-digit OTP. Please request a new security code.');
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$hash, $adminUserId]);
            Setting::set('admin_display_pass', $newPass);
            PasswordReset::consumeOTP($adminUser['email'], $otp, 'account_change');
            unset($_SESSION['last_account_otp_dev']);
            flash_set('success', 'Admin password updated successfully! Please use your new password next time you sign in.');
        }
    }
    redirect('admin/settings/index.php#account-security');
}

// ═══════════════════════════════════════════════════════════════════
// 4. ACTION: SAVE GLOBAL WEBSITE SETTINGS
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] === 'save_global_settings')) {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $uploader = new Upload('branding');

        // Handle Header Logo Image (Priority: New Upload > Remove > Existing)
        $siteLogo = setting('site_logo', '/img/wordorga logo.png');
        if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
            $upRes = $uploader->handle($_FILES['site_logo_file']);
            if ($upRes['success']) {
                $siteLogo = $upRes['path'];
            } else {
                $error = 'Header Logo Upload error: ' . $upRes['msg'];
            }
        } elseif (!empty($_POST['remove_site_logo']) && $_POST['remove_site_logo'] === '1') {
            $siteLogo = '/img/wordorga logo.png';
        }

        // Handle Footer Logo Image (Priority: New Upload > Remove > Existing)
        $footerLogo = setting('footer_logo', '/img/footer logo.png');
        if (isset($_FILES['footer_logo_file']) && $_FILES['footer_logo_file']['error'] === UPLOAD_ERR_OK) {
            $upRes = $uploader->handle($_FILES['footer_logo_file']);
            if ($upRes['success']) {
                $footerLogo = $upRes['path'];
            } else {
                $error = 'Footer Logo Upload error: ' . $upRes['msg'];
            }
        } elseif (!empty($_POST['remove_footer_logo']) && $_POST['remove_footer_logo'] === '1') {
            $footerLogo = '/img/footer logo.png';
        }

        // Handle Favicon Image (Priority: New Upload > Remove > Existing)
        $siteFavicon = setting('site_favicon', '/img/logo.png');
        if (isset($_FILES['site_favicon_file']) && $_FILES['site_favicon_file']['error'] === UPLOAD_ERR_OK) {
            $upRes = $uploader->handle($_FILES['site_favicon_file']);
            if ($upRes['success']) {
                $siteFavicon = $upRes['path'];
            } else {
                $error = 'Favicon Upload error: ' . $upRes['msg'];
            }
        } elseif (!empty($_POST['remove_site_favicon']) && $_POST['remove_site_favicon'] === '1') {
            $siteFavicon = '/img/logo.png';
        }

        // Handle Marquee Background Image (Priority: New Upload > Remove > Existing)
        $marqueeBg = setting('marquee_bg_image', '/img/papaer banner.png');
        if (isset($_FILES['marquee_bg_file']) && $_FILES['marquee_bg_file']['error'] === UPLOAD_ERR_OK) {
            $upRes = $uploader->handle($_FILES['marquee_bg_file']);
            if ($upRes['success']) {
                $marqueeBg = $upRes['path'];
            } else {
                $error = 'Marquee BG Upload error: ' . $upRes['msg'];
            }
        } elseif (!empty($_POST['remove_marquee_bg']) && $_POST['remove_marquee_bg'] === '1') {
            $marqueeBg = '';
        }

        // Handle Hero Video Poster Image (Priority: New Upload > Remove > Existing)
        $heroPoster = setting('hero_video_poster', '/img/home section 2.png');
        if (isset($_FILES['hero_video_poster_file']) && $_FILES['hero_video_poster_file']['error'] === UPLOAD_ERR_OK) {
            $upRes = $uploader->handle($_FILES['hero_video_poster_file']);
            if ($upRes['success']) {
                $heroPoster = $upRes['path'];
            } else {
                $error = 'Hero Poster Upload error: ' . $upRes['msg'];
            }
        } elseif (!empty($_POST['remove_hero_poster']) && $_POST['remove_hero_poster'] === '1') {
            $heroPoster = '';
        }

        if (!$error) {
            $settingsToSave = [
                'site_name'            => trim($_POST['site_name'] ?? 'WORDORA'),
                'tagline'              => trim($_POST['tagline'] ?? ''),
                'site_logo'            => $siteLogo,
                'footer_logo'          => $footerLogo,
                'site_favicon'         => $siteFavicon,
                'enable_case_studies'  => isset($_POST['enable_case_studies']) && $_POST['enable_case_studies'] === '1' ? '1' : '0',
                'contact_email'        => trim($_POST['contact_email'] ?? ''),
                'contact_phone'        => trim($_POST['contact_phone'] ?? ''),
                'address'              => trim($_POST['address'] ?? ''),
                'hero_mode'            => $_POST['hero_mode'] ?? 'slider',
                'hero_overlay_opacity'  => (string)max(0, min(100, (int)($_POST['hero_overlay_opacity'] ?? 85))),
                'hero_gradient_coverage' => (string)max(25, min(90, (int)($_POST['hero_gradient_coverage'] ?? 55))),
                'hero_video_url'        => trim($_POST['hero_video_url'] ?? ''),
                'hero_video_poster'     => $heroPoster,
                'marquee_bg_image'      => $marqueeBg,
                'linkedin'              => trim($_POST['linkedin'] ?? ''),
                'twitter'               => trim($_POST['twitter'] ?? ''),
                'instagram'             => trim($_POST['instagram'] ?? ''),
                'facebook'              => trim($_POST['facebook'] ?? ''),
                'youtube'               => trim($_POST['youtube'] ?? ''),
            ];

            foreach ($settingsToSave as $k => $v) {
                Setting::set($k, $v);
            }

            flash_set('success', 'Site settings and global hero gradient overlay updated successfully!');
            redirect('admin/settings/index.php');
        }
    }
}

$currentMarqueeBg = setting('marquee_bg_image', '/img/papaer banner.png');
$currentHeroPoster = setting('hero_video_poster', '/img/home section 2.png');
$curHeroOpacity = (int)setting('hero_overlay_opacity', '85');
$curHeroCoverage = (int)setting('hero_gradient_coverage', '55');

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card">
  <div class="card-header">
    <h2 class="card-title"><i class="ri-settings-4-line"></i> Global Website Configuration</h2>
  </div>

  <div class="card-body">
    <?php if ($error): ?>
      <div style="margin-bottom: 20px; padding: 12px; border-radius: 6px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
        <i class="ri-error-warning-line"></i> <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/settings/index.php') ?>" enctype="multipart/form-data">
      <?= CSRF::field() ?>

      <!-- General Info -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--admin-navy); margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid var(--admin-border);">
        <i class="ri-information-line"></i> Brand & Contact Information
      </h3>

      <div class="form-grid">
        <div class="form-field">
          <label class="field-label" for="siteName">Agency / Site Name</label>
          <input type="text" id="siteName" name="site_name" class="field-input" value="<?= e(setting('site_name', 'WORDORA')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="siteTagline">Brand Tagline</label>
          <input type="text" id="siteTagline" name="tagline" class="field-input" value="<?= e(setting('tagline', 'Words That Work. Stories That Sell.')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="contactEmail">Contact Email Address</label>
          <input type="email" id="contactEmail" name="contact_email" class="field-input" value="<?= e(setting('contact_email', 'info@wordora.in')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="contactPhone">Contact Phone Number</label>
          <input type="text" id="contactPhone" name="contact_phone" class="field-input" value="<?= e(setting('contact_phone', '+91-XXXXXXXXXX')) ?>">
        </div>

        <div class="form-field full">
          <label class="field-label" for="contactAddress">Physical Address / City</label>
          <input type="text" id="contactAddress" name="address" class="field-input" value="<?= e(setting('address', 'Jaipur, Rajasthan, India')) ?>">
        </div>

        <!-- Header / Navbar Logo -->
        <div class="form-field" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-image-line" style="color: var(--admin-teal);"></i> Header / Navbar Logo
          </label>
          
          <?php $curSiteLogo = setting('site_logo', '/img/wordorga logo.png'); ?>
          <?php if (!empty($curSiteLogo)): ?>
            <div id="preview_site_logo" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 14px; background: #0F1E36; padding: 10px 14px; border-radius: 6px;">
              <img src="<?= media_url($curSiteLogo) ?>" alt="Navbar Logo" style="max-height: 36px;">
              <div style="flex: 1;">
                <div style="font-size: 12px; font-weight: 600; color: #FFF;">Navbar Logo</div>
                <div style="font-size: 10px; color: rgba(255,255,255,0.6); word-break: break-all;"><?= e($curSiteLogo) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_site_logo', 'preview_site_logo')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                Reset
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_site_logo" id="remove_site_logo" value="0">
          <input type="file" name="site_logo_file" class="field-input" accept="image/*">
          <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">
            Recommended: Transparent PNG / SVG (width ~140px).
          </small>
        </div>

        <!-- Footer Logo -->
        <div class="form-field" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-image-line" style="color: var(--admin-teal);"></i> Footer Brand Logo
          </label>
          
          <?php $curFooterLogo = setting('footer_logo', '/img/footer logo.png'); ?>
          <?php if (!empty($curFooterLogo)): ?>
            <div id="preview_footer_logo" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 14px; background: #0F1E36; padding: 10px 14px; border-radius: 6px;">
              <img src="<?= media_url($curFooterLogo) ?>" alt="Footer Logo" style="max-height: 36px;">
              <div style="flex: 1;">
                <div style="font-size: 12px; font-weight: 600; color: #FFF;">Footer Logo</div>
                <div style="font-size: 10px; color: rgba(255,255,255,0.6); word-break: break-all;"><?= e($curFooterLogo) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_footer_logo', 'preview_footer_logo')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                Reset
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_footer_logo" id="remove_footer_logo" value="0">
          <input type="file" name="footer_logo_file" class="field-input" accept="image/*">
          <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">
            Recommended: Light/White Transparent PNG for dark footer.
          </small>
        </div>

        <!-- Website Favicon -->
        <div class="form-field" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-window-line" style="color: var(--admin-teal);"></i> Browser Tab Favicon
          </label>
          
          <?php $curFavicon = setting('site_favicon', '/img/logo.png'); ?>
          <?php if (!empty($curFavicon)): ?>
            <div id="preview_site_favicon" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 14px; background: #FFFFFF; border: 1px solid var(--admin-border); padding: 10px 14px; border-radius: 6px;">
              <img src="<?= media_url($curFavicon) ?>" alt="Favicon" style="width: 32px; height: 32px; object-fit: contain;">
              <div style="flex: 1;">
                <div style="font-size: 12px; font-weight: 600; color: var(--admin-navy);">Active Favicon</div>
                <div style="font-size: 10px; color: var(--admin-muted); word-break: break-all;"><?= e($curFavicon) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_site_favicon', 'preview_site_favicon')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;">
                Reset
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_site_favicon" id="remove_site_favicon" value="0">
          <input type="file" name="site_favicon_file" class="field-input" accept="image/*,.ico">
          <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">
            Recommended: 32x32px or 64x64px PNG / ICO / SVG. Updates in browser tabs everywhere.
          </small>
        </div>
      </div>

      <!-- Universal Hero Section Visual Overlay Controls (Applies to All Pages) -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--admin-navy); margin: 28px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--admin-border); display: flex; align-items: center; gap: 8px;">
        <i class="ri-paint-brush-line" style="color: var(--admin-teal);"></i> Universal Hero Overlay &amp; Area Coverage Controls (Entire Website)
      </h3>

      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 22px; margin-bottom: 24px;">
        <p style="font-size: 13px; color: var(--admin-navy); margin-top: 0; margin-bottom: 16px;">
          Adjust the global dark contrast for text readability (Left side) and horizontal shading coverage across <strong>ALL hero sections</strong> (Home, Who We Are, Services, Service Detail, Blog, Contact). The right-side artwork area will remain 100% crystal-clear with zero dimming.
        </p>

        <div class="form-grid">
          <!-- Opacity Slider -->
          <div class="form-field">
            <label class="field-label" for="heroOpacitySlider" style="display: flex; justify-content: space-between;">
              <span>1. Text-Side Dark Opacity</span>
              <span id="heroOpacityVal" style="color: var(--admin-teal); font-weight: 800;"><?= $curHeroOpacity ?>%</span>
            </label>
            <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
              <input type="range" id="heroOpacitySlider" min="20" max="100" step="5" value="<?= $curHeroOpacity ?>" class="field-range" style="flex: 1; accent-color: var(--admin-teal); cursor: pointer;" oninput="updateHeroGradientPreview()">
              <input type="number" id="heroOpacityInput" name="hero_overlay_opacity" min="0" max="100" value="<?= $curHeroOpacity ?>" class="field-input" style="width: 75px; text-align: center; font-weight: 700;" oninput="document.getElementById('heroOpacitySlider').value = this.value; updateHeroGradientPreview()">
            </div>
            <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">Darkness level behind white headlines (default 85%).</small>
          </div>

          <!-- Coverage Slider -->
          <div class="form-field">
            <label class="field-label" for="heroCoverageSlider" style="display: flex; justify-content: space-between;">
              <span>2. Horizontal Shading Coverage</span>
              <span id="heroCoverageVal" style="color: var(--admin-teal); font-weight: 800;"><?= $curHeroCoverage ?>% Width</span>
            </label>
            <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
              <input type="range" id="heroCoverageSlider" min="30" max="85" step="5" value="<?= $curHeroCoverage ?>" class="field-range" style="flex: 1; accent-color: var(--admin-teal); cursor: pointer;" oninput="updateHeroGradientPreview()">
              <input type="number" id="heroCoverageInput" name="hero_gradient_coverage" min="25" max="90" value="<?= $curHeroCoverage ?>" class="field-input" style="width: 75px; text-align: center; font-weight: 700;" oninput="document.getElementById('heroCoverageSlider').value = this.value; updateHeroGradientPreview()">
            </div>
            <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">How far the dark shade extends before right-side artwork becomes completely clear (default 55%).</small>
          </div>
        </div>

        <!-- Real-Time Interactive Visual Gradient Preview Bar -->
        <div style="margin-top: 18px;">
          <label style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--admin-teal); font-family: monospace; display: block; margin-bottom: 6px;">
            <i class="ri-eye-line"></i> Real-Time Visual Coverage &amp; Contrast Preview:
          </label>
          <div id="heroPreviewBox" style="height: 110px; border-radius: 10px; border: 1.5px solid var(--admin-border); position: relative; overflow: hidden; background-image: url('<?= img('home section 2.png') ?>'); background-size: cover; background-position: right center; display: flex; align-items: center; padding: 0 24px;">
            <div style="position: relative; z-index: 2; max-width: 320px;">
              <div style="font-size: 11px; font-weight: 800; color: #6BA8A9; text-transform: uppercase; font-family: monospace;">Editorial Preview</div>
              <div style="font-size: 17px; font-weight: 800; color: #FFFFFF; font-family: serif; line-height: 1.2;">High-Contrast White Text</div>
              <div style="font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 2px;">Crystal-clear readable copy on left</div>
            </div>
            <div style="margin-left: auto; z-index: 2; background: rgba(0,0,0,0.4); padding: 4px 10px; border-radius: 6px; font-size: 11px; color: #FFF; border: 1px solid rgba(255,255,255,0.2);">
              Right Artwork 100% Visible &amp; Clear &rarr;
            </div>
            <div id="heroPreviewOverlay" style="position: absolute; inset: 0; z-index: 1;"></div>
          </div>
        </div>

        <script>
        function updateHeroGradientPreview() {
          var op = parseInt(document.getElementById('heroOpacitySlider').value) || 85;
          var cov = parseInt(document.getElementById('heroCoverageSlider').value) || 55;
          
          document.getElementById('heroOpacityInput').value = op;
          document.getElementById('heroOpacityVal').textContent = op + '%';
          
          document.getElementById('heroCoverageInput').value = cov;
          document.getElementById('heroCoverageVal').textContent = cov + '% Width';
          
          var aLeft = (0.85 + ((op / 100) * 0.14)).toFixed(2);
          var aMid = (0.40 + ((op / 100) * 0.40)).toFixed(2);
          var midStop = Math.round(cov * 0.60);
          var fadeStop = Math.round(cov * 0.92);
          var clearStop = cov;
          
          var grad = "linear-gradient(90deg, rgba(15, 30, 54, " + aLeft + ") 0%, rgba(15, 30, 54, " + aMid + ") " + midStop + "%, rgba(15, 30, 54, 0.16) " + fadeStop + "%, rgba(15, 30, 54, 0.0) " + clearStop + "%, rgba(15, 30, 54, 0.0) 100%)";
          document.getElementById('heroPreviewOverlay').style.background = grad;
        }
        document.addEventListener('DOMContentLoaded', updateHeroGradientPreview);
        </script>
      </div>

      <!-- Home Hero Mode & Media -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--admin-navy); margin: 28px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--admin-border);">
        <i class="ri-tv-2-line"></i> Homepage Default Hero Mode &amp; Fallback Media
      </h3>

      <div class="form-grid">
        <div class="form-field">
          <label class="field-label">Active Hero Display Mode (Homepage)</label>
          <select name="hero_mode" class="field-select">
            <option value="slider" <?= setting('hero_mode', 'slider') === 'slider' ? 'selected' : '' ?>>Multi-Image Slider (Swiper)</option>
            <option value="single_image" <?= setting('hero_mode', 'slider') === 'single_image' ? 'selected' : '' ?>>Single Hero Image</option>
            <option value="video" <?= setting('hero_mode', 'slider') === 'video' ? 'selected' : '' ?>>Background Video</option>
          </select>
        </div>

        <div class="form-field">
          <label class="field-label" for="heroVideoUrl">Homepage Background Video URL (For Video Mode)</label>
          <input type="text" id="heroVideoUrl" name="hero_video_url" class="field-input" placeholder="e.g. /uploads/hero/hero-video.mp4" value="<?= e(setting('hero_video_url', '')) ?>">
        </div>

        <!-- Hero Video Poster File Upload Box (Instant Remove) -->
        <div class="form-field full" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-image-line" style="color: var(--admin-teal);"></i> Video Fallback Poster Image
          </label>
          
          <?php if (!empty($currentHeroPoster)): ?>
            <div id="preview_hero_poster" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
              <img src="<?= media_url($currentHeroPoster) ?>" alt="Current Poster" style="max-height: 70px; border-radius: 4px; border: 1px solid var(--admin-border);">
              <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; color: var(--admin-navy);">Active Poster Image</div>
                <div style="font-size: 11px; color: var(--admin-muted); word-break: break-all;"><?= e($currentHeroPoster) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_hero_poster', 'preview_hero_poster')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_hero_poster" id="remove_hero_poster" value="0">

          <input type="file" name="hero_video_poster_file" class="field-input" accept="image/*">
          <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">
            Upload PNG, JPG, or WebP image to display before hero video loads.
          </small>
        </div>

        <!-- Marquee Background Upload Box (Instant Remove) -->
        <div class="form-field full" style="background: #F8FAFC; padding: 18px; border-radius: 8px; border: 1.5px solid var(--admin-border);">
          <label class="field-label" style="display: flex; align-items: center; gap: 6px; font-weight: 700; color: var(--admin-navy);">
            <i class="ri-landscape-line" style="color: var(--admin-teal);"></i> Capabilities Marquee Banner Background Image
          </label>

          <?php if (!empty($currentMarqueeBg)): ?>
            <div id="preview_marquee_bg" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 10px 14px; border-radius: 6px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
              <img src="<?= media_url($currentMarqueeBg) ?>" alt="Current Marquee Background" style="max-height: 70px; border-radius: 4px; border: 1px solid var(--admin-border);">
              <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; color: var(--admin-navy);">Active Banner Artwork</div>
                <div style="font-size: 11px; color: var(--admin-muted); word-break: break-all;"><?= e($currentMarqueeBg) ?></div>
              </div>
              <button type="button" onclick="instantRemoveMedia('remove_marquee_bg', 'preview_marquee_bg')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            </div>
          <?php endif; ?>
          <input type="hidden" name="remove_marquee_bg" id="remove_marquee_bg" value="0">

          <input type="file" name="marquee_bg_file" class="field-input" accept="image/*">
          <small style="color: var(--admin-muted); font-size: 11px; margin-top: 4px; display: block;">
            Upload background image for the 3-row capability glass pill marquee across Home and Who We Are pages (default: <code>papaer banner.png</code>).
          </small>
        </div>
      </div>

      <!-- Modules & Public Page Visibility -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--admin-navy); margin: 28px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--admin-border); display: flex; align-items: center; gap: 8px;">
        <i class="ri-toggle-line" style="color: var(--admin-teal);"></i> Modules &amp; Public Page Visibility Controls
      </h3>

      <div style="background: #F8FAFC; border: 1.5px solid var(--admin-border); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <?php $curCsLive = (setting('enable_case_studies', '1') !== '0'); ?>
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $curCsLive ? '#DCFCE7' : '#FEF3C7' ?>; color: <?= $curCsLive ? '#15803D' : '#B45309' ?>; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
              <i class="ri-folder-shield-2-line"></i>
            </div>
            <div>
              <div style="font-weight: 700; font-size: 14.5px; color: var(--admin-navy);">
                Case Studies Module (Public Visibility)
              </div>
              <div style="font-size: 12.5px; color: var(--admin-muted); margin-top: 2px;">
                Toggle whether the Case Studies archive page, navbar dropdown proofs, and footer navigation links are visible to website visitors.
              </div>
            </div>
          </div>
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
            <input type="checkbox" name="enable_case_studies" value="1" <?= $curCsLive ? 'checked' : '' ?> style="width: 20px; height: 20px; accent-color: var(--admin-teal); cursor: pointer;">
            <span style="font-size: 13px; font-weight: 700; color: <?= $curCsLive ? '#15803D' : '#64748B' ?>;">
              <?= $curCsLive ? 'Visible / Active' : 'Hidden / Disabled' ?>
            </span>
          </label>
        </div>
      </div>

      <!-- Social Media -->
      <h3 style="font-size: 15px; font-weight: 700; color: var(--admin-navy); margin: 28px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--admin-border);">
        <i class="ri-share-line"></i> Social Media Profiles
      </h3>

      <div class="form-grid">
        <div class="form-field">
          <label class="field-label" for="socialLinkedin">LinkedIn Profile URL</label>
          <input type="text" id="socialLinkedin" name="linkedin" class="field-input" placeholder="https://linkedin.com/company/wordora" value="<?= e(setting('linkedin', '')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="socialTwitter">Twitter / X Profile URL</label>
          <input type="text" id="socialTwitter" name="twitter" class="field-input" placeholder="https://x.com/wordora" value="<?= e(setting('twitter', '')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="socialInstagram">Instagram Profile URL</label>
          <input type="text" id="socialInstagram" name="instagram" class="field-input" placeholder="https://instagram.com/wordora" value="<?= e(setting('instagram', '')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="socialFacebook">Facebook Page URL</label>
          <input type="text" id="socialFacebook" name="facebook" class="field-input" placeholder="https://facebook.com/wordora" value="<?= e(setting('facebook', '')) ?>">
        </div>

        <div class="form-field">
          <label class="field-label" for="socialYoutube">YouTube Channel URL</label>
          <input type="text" id="socialYoutube" name="youtube" class="field-input" placeholder="https://youtube.com/@wordora" value="<?= e(setting('youtube', '')) ?>">
        </div>
      </div>

      <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <button type="submit" class="btn-adm btn-adm-primary">
          <i class="ri-save-line"></i> Save All Settings
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     ADMINISTRATOR ACCOUNT & SECURITY (ACTIVE CREDENTIALS & OTP CHANGE)
     ═══════════════════════════════════════════════════════════════════ -->
<div class="admin-card" id="account-security" style="margin-top: 32px; border: 1.5px solid rgba(74, 139, 140, 0.35); box-shadow: 0 10px 30px rgba(15, 30, 54, 0.06);">
  <div class="card-header" style="background: linear-gradient(135deg, #FAF8F5 0%, #F0F7F7 100%); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-bottom: 1.5px solid rgba(74, 139, 140, 0.2);">
    <div>
      <h2 class="card-title" style="color: var(--admin-navy); margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="ri-shield-keyhole-line" style="color: var(--admin-teal);"></i> Administrator Account &amp; Security Credentials
      </h2>
      <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">
        Manage your active login credentials and update email or password securely using 4-digit OTP verification.
      </div>
    </div>
    
    <!-- OTP Generator Button -->
    <form method="POST" action="<?= url('admin/settings/index.php') ?>" style="margin: 0;">
      <?= CSRF::field() ?>
      <input type="hidden" name="action" value="send_account_otp">
      <button type="submit" class="btn-adm btn-adm-secondary" style="font-size: 12.5px; padding: 8px 16px; background: #0F1E36; color: #D4EAEA; border: 1.5px solid var(--admin-teal); border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
        <i class="ri-mail-send-line" style="color: var(--admin-teal-light);"></i> Send 4-Digit Security OTP
      </button>
    </form>
  </div>

  <div class="card-body" style="padding: 24px;">
    
    <?php if (!empty($_SESSION['last_account_otp_dev'])): ?>
      <div style="background: #FEF3C7; border: 1.5px solid #FDE68A; color: #92400E; padding: 12px 18px; border-radius: 8px; font-size: 13px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <i class="ri-shield-check-line" style="font-size: 18px; color: #D97706;"></i>
          <span>Active 4-Digit Security OTP: <strong style="font-size: 16px; font-family: monospace; letter-spacing: 3px; background: #FFF; padding: 2px 8px; border-radius: 4px; border: 1px solid #FDE68A;"><?= e($_SESSION['last_account_otp_dev']) ?></strong> (Valid for 15 min)</span>
        </div>
        <span style="font-size: 11.5px; color: #B45309;">Dispatched to <?= e($adminUser['email']) ?></span>
      </div>
    <?php endif; ?>

    <!-- Active Credentials Overview Card (Current Email & Current Password with Eye Toggle) -->
    <div style="background: #FAF8F5; border: 1.5px solid rgba(74, 139, 140, 0.25); border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; box-shadow: 0 4px 16px rgba(15, 30, 54, 0.03);">
      <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.2px; color: var(--admin-teal); margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
        <i class="ri-lock-2-line"></i> Active Administrator Credentials
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        
        <!-- Current Email Block -->
        <div style="background: #FFFFFF; border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px 18px;">
          <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-muted); margin-bottom: 6px;">
            Current Active Email
          </div>
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            <span style="font-size: 15px; font-weight: 700; color: var(--admin-navy); word-break: break-all;">
              <?= e($adminUser['email']) ?>
            </span>
            <span style="background: #D4EAEA; color: #0F1E36; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
              Primary
            </span>
          </div>
        </div>

        <!-- Current Password Block (With Eye Toggle & Copy) -->
        <div style="background: #FFFFFF; border: 1px solid var(--admin-border); border-radius: 10px; padding: 14px 18px;">
          <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--admin-muted); margin-bottom: 6px;">
            Current Active Password
          </div>
          <div style="display: flex; align-items: center; gap: 8px;">
            <div style="position: relative; flex: 1;">
              <input type="password" id="currentAdminPassDisplay" readonly value="<?= e(setting('admin_display_pass', 'admin123')) ?>" style="width: 100%; padding: 8px 38px 8px 12px; font-size: 14.5px; font-family: monospace; font-weight: 700; color: var(--admin-navy); background: #FAF8F5; border: 1.5px solid var(--admin-border); border-radius: 6px;" title="Current Admin Password">
              <button type="button" onclick="togglePass('currentAdminPassDisplay', 'eyeCurrentAdminPass')" title="View / Hide Password" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--admin-muted); cursor: pointer; padding: 4px; font-size: 17px; display: flex; align-items: center; justify-content: center;">
                <i class="ri-eye-line" id="eyeCurrentAdminPass"></i>
              </button>
            </div>
            <button type="button" onclick="copyCurrentPass()" class="btn-adm btn-adm-outline" style="padding: 7px 12px; font-size: 12px; white-space: nowrap; height: 38px;" title="Copy Password">
              <i class="ri-file-copy-line"></i> Copy
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Update Credential Subcards (Email & Password with 4-Digit OTP) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
      
      <!-- Subcard 1: Change Admin Email -->
      <div style="background: #FAF8F5; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <h3 style="font-size: 14.5px; font-weight: 700; color: var(--admin-navy); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-mail-settings-line" style="color: var(--admin-teal);"></i> Change Administrator Email
          </h3>
          <p style="font-size: 12px; color: var(--admin-muted); margin-bottom: 18px; line-height: 1.5;">
            Enter new email and verify with the 4-digit OTP dispatched to <strong><?= e($adminUser['email']) ?></strong>.
          </p>

          <form method="POST" action="<?= url('admin/settings/index.php') ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="action" value="update_admin_email">
            
            <div class="form-field">
              <label class="field-label" for="newAdminEmail">New Admin Email Address *</label>
              <input type="email" id="newAdminEmail" name="new_email" class="field-input" required placeholder="info@wordora.in">
            </div>

            <div class="form-field">
              <label class="field-label" for="emailOtp">4-Digit Security OTP *</label>
              <input type="text" id="emailOtp" name="email_otp" class="field-input" required maxlength="4" pattern="\d{4}" placeholder="••••" style="font-family: monospace; font-size: 16px; letter-spacing: 6px; font-weight: 700;" value="<?= e($_SESSION['last_account_otp_dev'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
              <i class="ri-check-line"></i> Verify OTP &amp; Update Email
            </button>
          </form>
        </div>
      </div>

      <!-- Subcard 2: Change Admin Password (With Eye Buttons) -->
      <div style="background: #FAF8F5; padding: 22px; border-radius: 12px; border: 1px solid var(--admin-border); display: flex; flex-direction: column; justify-content: space-between;">
        <div>
          <h3 style="font-size: 14.5px; font-weight: 700; color: var(--admin-navy); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
            <i class="ri-lock-password-line" style="color: var(--admin-teal);"></i> Change Administrator Password
          </h3>
          <p style="font-size: 12px; color: var(--admin-muted); margin-bottom: 18px; line-height: 1.5;">
            Enter new password and confirm with the 4-digit OTP dispatched to <strong><?= e($adminUser['email']) ?></strong>.
          </p>

          <form method="POST" action="<?= url('admin/settings/index.php') ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="action" value="update_admin_password">

            <div class="form-field">
              <label class="field-label" for="newAdminPass">New Password *</label>
              <div class="password-wrapper" style="position: relative; display: flex; align-items: center;">
                <input type="password" id="newAdminPass" name="new_password" class="field-input" required minlength="6" placeholder="At least 6 characters" style="padding-right: 44px; width: 100%;">
                <button type="button" class="toggle-password-btn" onclick="togglePass('newAdminPass', 'eyePass1')" title="Show / Hide Password" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--admin-muted); cursor: pointer; padding: 6px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                  <i class="ri-eye-line" id="eyePass1"></i>
                </button>
              </div>
            </div>

            <div class="form-field">
              <label class="field-label" for="confirmAdminPass">Confirm New Password *</label>
              <div class="password-wrapper" style="position: relative; display: flex; align-items: center;">
                <input type="password" id="confirmAdminPass" name="confirm_password" class="field-input" required minlength="6" placeholder="Re-enter new password" style="padding-right: 44px; width: 100%;">
                <button type="button" class="toggle-password-btn" onclick="togglePass('confirmAdminPass', 'eyePass2')" title="Show / Hide Password" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: var(--admin-muted); cursor: pointer; padding: 6px; font-size: 18px; display: flex; align-items: center; justify-content: center;">
                  <i class="ri-eye-line" id="eyePass2"></i>
                </button>
              </div>
            </div>

            <div class="form-field">
              <label class="field-label" for="passOtp">4-Digit Security OTP *</label>
              <input type="text" id="passOtp" name="password_otp" class="field-input" required maxlength="4" pattern="\d{4}" placeholder="••••" style="font-family: monospace; font-size: 16px; letter-spacing: 6px; font-weight: 700;" value="<?= e($_SESSION['last_account_otp_dev'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
              <i class="ri-shield-keyhole-line"></i> Verify OTP &amp; Update Password
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function instantRemoveMedia(inputId, previewId) {
  const input = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  if (input) input.value = '1';
  if (preview) {
    preview.style.opacity = '0';
    preview.style.transform = 'translateY(-6px)';
    setTimeout(() => { preview.remove(); }, 250);
  }
}

function togglePass(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input && icon) {
    const isPass = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPass ? 'text' : 'password');
    icon.className = isPass ? 'ri-eye-off-line' : 'ri-eye-line';
  }
}

function copyCurrentPass() {
  const input = document.getElementById('currentAdminPassDisplay');
  if (input) {
    navigator.clipboard.writeText(input.value).then(() => {
      alert('Password copied to clipboard: ' + input.value);
    }).catch(() => {
      input.select();
      document.execCommand('copy');
      alert('Password copied to clipboard!');
    });
  }
}
</script>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>

