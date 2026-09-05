<?php
/**
 * WORDORA — What We Do / Services Visual Section Studio
 * 8-Tab Visual Management matching public/services.php
 */

$editorError = '';
$editorSuccess = '';
$activeTab = $_GET['tab'] ?? 'sec01';
$currentUrl = url('admin/pages/services.php');

// Handle Delete Hero Slide from Tab 01
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_slide_id'])) {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please try again.';
    } else {
        $delId = (int)$_POST['delete_slide_id'];
        if (Hero::delete($delId)) {
            $editorSuccess = 'Hero slide deleted successfully.';
        } else {
            $editorError = 'Failed to delete hero slide.';
        }
    }
}

// Handle POST Save for all sections
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['services_editor_submit'])) {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please try again.';
    } else {
        $uploader = new Upload('services');
        $tab = $_POST['tab'] ?? 'sec01';
        $activeTab = $tab;

        // Section 01: Hero Cover & Multi-Mode (Upload + URL)
        if ($tab === 'sec01') {
            if (isset($_POST['hero_mode'])) {
                Setting::set('hero_mode_services', $_POST['hero_mode']);
            }
            $servicesVideo = setting('services_hero_video_url', '');
            if (isset($_FILES['services_hero_video_file']) && $_FILES['services_hero_video_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['services_hero_video_file'], true);
                if ($up['success']) { 
                    if (!empty($servicesVideo) && $servicesVideo !== $up['path']) {
                        delete_uploaded_file($servicesVideo);
                    }
                    $servicesVideo = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (isset($_POST['hero_video_url'])) {
                $newVideoUrl = trim($_POST['hero_video_url']);
                if ($newVideoUrl !== $servicesVideo && !empty($servicesVideo)) {
                    delete_uploaded_file($servicesVideo);
                }
                $servicesVideo = $newVideoUrl;
            } elseif (!empty($_POST['remove_services_hero_video']) && $_POST['remove_services_hero_video'] === '1') {
                delete_uploaded_file($servicesVideo);
                $servicesVideo = '';
            }

            if (!$editorError) {
                Setting::set('services_hero_video_url', $servicesVideo);
            }
        }

        // Section 02: Quick Jump & Matrix Header
        if ($tab === 'sec02') {
            Setting::set('services_sec2_badge', trim($_POST['services_sec2_badge'] ?? ''));
            Setting::set('services_sec2_title', trim($_POST['services_sec2_title'] ?? ''));
            Setting::set('services_sec2_desc', trim($_POST['services_sec2_desc'] ?? ''));
        }

        // Section 04: The 4-Stage Editorial Framework (Methodology)
        if ($tab === 'sec04') {
            $sec4Art = setting('services_sec4_artwork', '/img/process.png');
            if (isset($_FILES['services_sec4_artwork_file']) && $_FILES['services_sec4_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['services_sec4_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec4Art) && $sec4Art !== $up['path']) {
                        delete_uploaded_file($sec4Art);
                    }
                    $sec4Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec4_artwork']) && $_POST['remove_sec4_artwork'] === '1') {
                delete_uploaded_file($sec4Art);
                $sec4Art = '/img/process.png';
            }

            $steps = [];
            if (!empty($_POST['steps']) && is_array($_POST['steps'])) {
                foreach ($_POST['steps'] as $st) {
                    if (!empty($st['title'])) {
                        $steps[] = [
                            'num'   => trim($st['num'] ?? '01'),
                            'title' => trim($st['title'] ?? ''),
                            'desc'  => trim($st['desc'] ?? '')
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('services_sec4_badge', trim($_POST['services_sec4_badge'] ?? ''));
                Setting::set('services_sec4_title', trim($_POST['services_sec4_title'] ?? ''));
                Setting::set('services_sec4_desc', trim($_POST['services_sec4_desc'] ?? ''));
                Setting::set('services_sec4_artwork', $sec4Art);
                if (!empty($steps)) {
                    Setting::set('services_sec4_steps', json_encode($steps, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 05: Commodity vs. Wordora Editorial (Comparison Table)
        if ($tab === 'sec05') {
            $tableRows = [];
            if (!empty($_POST['table_rows']) && is_array($_POST['table_rows'])) {
                foreach ($_POST['table_rows'] as $r) {
                    if (!empty($r['pillar'])) {
                        $tableRows[] = [
                            'pillar'    => trim($r['pillar'] ?? ''),
                            'commodity' => trim($r['commodity'] ?? ''),
                            'wordora'   => trim($r['wordora'] ?? ''),
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('services_sec5_badge', trim($_POST['services_sec5_badge'] ?? ''));
                Setting::set('services_sec5_title', trim($_POST['services_sec5_title'] ?? ''));
                Setting::set('services_sec5_desc', trim($_POST['services_sec5_desc'] ?? ''));
                if (!empty($tableRows)) {
                    Setting::set('services_sec5_table', json_encode($tableRows, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 06: Engagement Models & Scope Tiers
        if ($tab === 'sec06') {
            $tiers = [];
            if (!empty($_POST['tiers']) && is_array($_POST['tiers'])) {
                foreach ($_POST['tiers'] as $t) {
                    if (!empty($t['title'])) {
                        $tiers[] = [
                            'badge'       => trim($t['badge'] ?? ''),
                            'title'       => trim($t['title'] ?? ''),
                            'desc'        => trim($t['desc'] ?? ''),
                            'bullets'     => trim($t['bullets'] ?? ''),
                            'btn_text'    => trim($t['btn_text'] ?? 'Request Scope'),
                            'btn_url'     => trim($t['btn_url'] ?? 'contact.php'),
                            'is_featured' => !empty($t['is_featured']) ? 1 : 0,
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('services_sec6_badge', trim($_POST['services_sec6_badge'] ?? ''));
                Setting::set('services_sec6_title', trim($_POST['services_sec6_title'] ?? ''));
                Setting::set('services_sec6_desc', trim($_POST['services_sec6_desc'] ?? ''));
                if (!empty($tiers)) {
                    Setting::set('services_sec6_tiers', json_encode($tiers, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 07: Frequently Asked Questions (FAQ)
        if ($tab === 'sec07') {
            $sec7Art = setting('services_sec7_artwork', '/img/FAQ 2.png');
            if (isset($_FILES['services_sec7_artwork_file']) && $_FILES['services_sec7_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['services_sec7_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec7Art) && $sec7Art !== $up['path']) {
                        delete_uploaded_file($sec7Art);
                    }
                    $sec7Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec7_artwork']) && $_POST['remove_sec7_artwork'] === '1') {
                delete_uploaded_file($sec7Art);
                $sec7Art = '/img/FAQ 2.png';
            }

            $faqs = [];
            if (!empty($_POST['faqs']) && is_array($_POST['faqs'])) {
                foreach ($_POST['faqs'] as $f) {
                    if (!empty($f['q'])) {
                        $faqs[] = [
                            'q' => trim($f['q'] ?? ''),
                            'a' => trim($f['a'] ?? ''),
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('services_sec7_badge', trim($_POST['services_sec7_badge'] ?? ''));
                Setting::set('services_sec7_title', trim($_POST['services_sec7_title'] ?? ''));
                Setting::set('services_sec7_desc', trim($_POST['services_sec7_desc'] ?? ''));
                Setting::set('services_sec7_artwork', $sec7Art);
                if (!empty($faqs)) {
                    Setting::set('services_sec7_faqs', json_encode($faqs, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 08: Start a Conversation CTA
        if ($tab === 'sec08') {
            $sec8Art = setting('services_sec8_artwork', '/img/cta 1.png');
            if (isset($_FILES['services_sec8_artwork_file']) && $_FILES['services_sec8_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['services_sec8_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec8Art) && $sec8Art !== $up['path']) {
                        delete_uploaded_file($sec8Art);
                    }
                    $sec8Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec8_artwork']) && $_POST['remove_sec8_artwork'] === '1') {
                delete_uploaded_file($sec8Art);
                $sec8Art = '/img/cta 1.png';
            }

            $pills = [];
            if (!empty($_POST['pills']) && is_array($_POST['pills'])) {
                foreach ($_POST['pills'] as $p) {
                    if (!empty($p['text'])) {
                        $pills[] = [
                            'icon' => trim($p['icon'] ?? 'ri-checkbox-circle-fill'),
                            'text' => trim($p['text'] ?? '')
                        ];
                    }
                }
            }

            if (!$editorError) {
                Setting::set('services_sec8_badge', trim($_POST['services_sec8_badge'] ?? ''));
                Setting::set('services_sec8_title', trim($_POST['services_sec8_title'] ?? ''));
                Setting::set('services_sec8_desc', trim($_POST['services_sec8_desc'] ?? ''));
                Setting::set('services_sec8_btn1_text', trim($_POST['services_sec8_btn1_text'] ?? ''));
                Setting::set('services_sec8_btn1_url', trim($_POST['services_sec8_btn1_url'] ?? ''));
                Setting::set('services_sec8_btn2_text', trim($_POST['services_sec8_btn2_text'] ?? ''));
                Setting::set('services_sec8_btn2_url', trim($_POST['services_sec8_btn2_url'] ?? ''));
                Setting::set('services_sec8_artwork', $sec8Art);
                if (!empty($pills)) {
                    Setting::set('services_sec8_pills', json_encode($pills, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        if (!$editorError) {
            $editorSuccess = 'Section saved and published successfully!';
        }
    }
}

// Fetch all Services Slides for Tab 01
$servicesHeroSlidesList = Hero::getAll('services');
$allServicesList = Service::getAll();

// Navigation Tabs Definition for Services Page
$tabs = [
    'sec01' => ['num' => '01', 'name' => 'The Editorial Cover (Hero)', 'icon' => 'ri-slideshow-line'],
    'sec02' => ['num' => '02', 'name' => 'Quick Jump & Header', 'icon' => 'ri-compass-3-line'],
    'sec03' => ['num' => '03', 'name' => 'Disciplines & Stacking Deck', 'icon' => 'ri-stack-line'],
    'sec04' => ['num' => '04', 'name' => '4-Stage Methodology', 'icon' => 'ri-node-tree'],
    'sec05' => ['num' => '05', 'name' => 'Commodity vs Wordora', 'icon' => 'ri-table-line'],
    'sec06' => ['num' => '06', 'name' => 'Engagement Models & Tiers', 'icon' => 'ri-price-tag-3-line'],
    'sec07' => ['num' => '07', 'name' => 'Frequently Asked Questions', 'icon' => 'ri-questionnaire-line'],
    'sec08' => ['num' => '08', 'name' => 'Start a Conversation CTA', 'icon' => 'ri-send-plane-fill'],
];
?>

<!-- Google Fonts for Real Visual Match -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,700;0,800;1,500;1,700&display=swap" rel="stylesheet">

<style>
:root {
  --wdr-navy: #1B2A4A;
  --wdr-deep-navy: #0F1E36;
  --wdr-teal: #4A8B8C;
  --wdr-teal-light: #6BA8A9;
  --wdr-teal-pale: #D4EAEA;
  --wdr-canvas: #FAF8F5;
  --wdr-white: #FFFFFF;
  --wdr-font-display: 'Playfair Display', Georgia, serif;
  --wdr-font-body: 'Inter', sans-serif;
  --wdr-font-mono: 'JetBrains Mono', monospace;
}

.visual-studio-card {
  background: var(--wdr-canvas);
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 28px;
  position: relative;
}

.visual-studio-card-dark {
  background: var(--wdr-deep-navy);
  border: 1.5px dashed rgba(74, 139, 140, 0.45);
  border-radius: 20px;
  padding: 32px;
  margin-bottom: 28px;
  position: relative;
  color: #FFFFFF;
}

.visual-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  border: 1px dashed var(--wdr-teal);
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.visual-badge-dark {
  background: rgba(74, 139, 140, 0.25);
  color: var(--wdr-teal-light);
  border: 1px dashed var(--wdr-teal-light);
}

.visual-label-upper {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--wdr-teal);
  display: block;
  margin-bottom: 8px;
  font-family: var(--wdr-font-mono);
}

.visual-display-heading {
  font-family: var(--wdr-font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--wdr-navy);
  line-height: 1.25;
  margin: 12px 0 16px;
}

.visual-media-frame {
  background: var(--wdr-white);
  padding: 24px;
  border: 1.5px dashed rgba(74, 139, 140, 0.45);
  border-radius: 24px;
  box-shadow: 0 8px 24px rgba(15, 30, 54, 0.06);
  position: relative;
  text-align: center;
}

.visual-input-styled {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
  border-radius: 8px;
  background: #FFFFFF;
  font-size: 13.5px;
  color: var(--wdr-deep-navy);
  font-family: inherit;
  transition: all 0.2s ease;
  box-sizing: border-box;
}
.visual-input-styled:focus {
  outline: none;
  border-color: var(--wdr-teal);
  border-style: solid;
  box-shadow: 0 0 0 3px rgba(74, 139, 140, 0.15);
}

.visual-input-dark {
  background: rgba(255,255,255,0.08);
  border: 1px dashed rgba(255,255,255,0.3);
  color: #FFFFFF;
}
.visual-input-dark:focus {
  border-color: var(--wdr-teal-light);
  background: rgba(255,255,255,0.14);
}

.hero-mode-option-card {
  border: 2px solid #E2E8EE;
  background: #FAF8F5;
  padding: 18px;
  border-radius: 12px;
  cursor: pointer;
  display: flex;
  gap: 12px;
  transition: all 0.25s ease;
}
.hero-mode-option-card:hover {
  border-color: rgba(74, 139, 140, 0.45);
  background: #FFFFFF;
}
.hero-mode-option-card.is-active,
.hero-mode-option-card:has(input[type="radio"]:checked) {
  border-color: var(--wdr-teal) !important;
  background: rgba(74, 139, 140, 0.10) !important;
  box-shadow: 0 4px 14px rgba(74, 139, 140, 0.15);
}

/* Action Buttons */
.table-actions {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.btn-adm-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  line-height: 1.2;
  box-sizing: border-box;
  font-family: var(--wdr-font-body);
}

.btn-adm-action.btn-adm-edit {
  background: #FAF8F5;
  color: var(--wdr-navy);
  border-color: #CBD5E1;
  box-shadow: 0 1px 3px rgba(15, 30, 54, 0.04);
}
.btn-adm-action.btn-adm-edit:hover {
  background: var(--wdr-teal-pale);
  border-color: var(--wdr-teal);
  color: var(--wdr-navy);
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(74, 139, 140, 0.15);
}

.btn-adm-action.btn-adm-delete {
  background: #FEF2F2;
  color: #DC2626;
  border-color: #FECACA;
  box-shadow: 0 1px 3px rgba(220, 38, 38, 0.04);
}
.btn-adm-action.btn-adm-delete:hover {
  background: #DC2626;
  color: #FFFFFF;
  border-color: #DC2626;
  transform: translateY(-1px);
  box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
}
</style>

<script>
function updateHeroModeCards(radio) {
  document.querySelectorAll('.hero-mode-option-card').forEach(function(card) {
    card.classList.remove('is-active');
  });
  if (radio && radio.checked) {
    var parent = radio.closest('.hero-mode-option-card');
    if (parent) {
      parent.classList.add('is-active');
    }
  }
}
</script>

<!-- Studio Header & Live Preview Link -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-family: var(--wdr-font-display); font-size: 24px; font-weight: 700; color: var(--admin-navy); margin: 0;">
      <i class="ri-briefcase-4-line" style="color: var(--admin-teal);"></i> Services Live Visual Studio
    </h2>
    <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
      What We Do / Services page ke sabhi 8 sections (01 to 08) ka complete editable visual content &amp; media editor.
    </p>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="<?= url('services.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
      <i class="ri-external-link-line"></i> View Live Services
    </a>
  </div>
</div>

<?php if (!empty($editorSuccess)): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; display: flex; align-items: center; gap: 10px;">
    <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #16A34A;"></i>
    <span><?= e($editorSuccess) ?></span>
    <a href="<?= url('services.php') ?>" target="_blank" style="margin-left: auto; font-size: 12px; text-decoration: underline; color: #166534; font-weight: 700;">View Live Page <i class="ri-external-link-line"></i></a>
  </div>
<?php endif; ?>

<?php if (!empty($editorError)): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
    <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
  </div>
<?php endif; ?>

<!-- Section Navigation Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
  <?php foreach ($tabs as $k => $t): 
      $isAct = ($activeTab === $k);
  ?>
  <a href="<?= $currentUrl ?>?tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1.5px solid var(--admin-border);' ?>">
    <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--admin-teal)' : 'var(--admin-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--admin-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
    <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
  </a>
  <?php endforeach; ?>
</div>


<!-- ═══════════════════════════════════════════
     TAB 01: HERO MULTI-MODE & SLIDE CAROUSEL
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec01'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px;">
    <span class="visual-badge"><i class="ri-slideshow-line"></i> SECTION 01 — THE EDITORIAL COVER</span>
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">What We Do / Services — Hero Presentation &amp; Multi-Mode Manager</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Multi-slide carousel slides, background videos, single artwork, and custom copy for the Services page.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <input type="hidden" name="services_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec01">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper">Active Hero Display Mode (Services Page)</label>
      <?php $curServicesMode = Hero::getHeroMode('services'); ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <label class="hero-mode-option-card <?= ($curServicesMode === 'slider') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="slider" <?= ($curServicesMode === 'slider') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">✨ Multi-Slide Carousel (Slider)</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Smooth Swiper.js fade slider where each slide has its own full background artwork and distinct text.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curServicesMode === 'single' || $curServicesMode === 'single_image') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="single" <?= ($curServicesMode === 'single' || $curServicesMode === 'single_image') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🖼️ Single Background Image</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Renders 1 static high-impact background image hero without slide transitions.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curServicesMode === 'video') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="video" <?= ($curServicesMode === 'video') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🎬 Background Video Hero</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Autoplay looping MP4/WebM video hero with dark overlay.</div>
          </div>
        </label>

      </div>

      <!-- Video Hero Configuration Box -->
      <?php $curServicesHeroVideo = setting('services_hero_video_url', ''); ?>
      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <label class="visual-label-upper"><i class="ri-video-line"></i> Background Video Configuration (Upload File or Enter Direct URL)</label>

        <?php if (!empty($curServicesHeroVideo)): ?>
          <div id="preview_services_hero_video" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
            <div style="width: 80px; height: 50px; background: #0F1E36; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal); font-size: 24px; overflow: hidden; flex-shrink: 0;">
              <video src="<?= media_url($curServicesHeroVideo) ?>" style="width: 100%; height: 100%; object-fit: cover;" muted autoplay loop playsinline></video>
            </div>
            <div style="flex: 1; min-width: 0;">
              <div style="font-size: 13px; font-weight: 700; color: var(--admin-navy);">Active Video Source</div>
              <div style="font-size: 11px; color: var(--admin-teal); word-break: break-all;"><?= e($curServicesHeroVideo) ?></div>
            </div>
            <button type="button" onclick="document.getElementById('remove_services_hero_video').value='1'; document.getElementById('preview_services_hero_video').style.display='none'; document.querySelector('input[name=hero_video_url]').value='';" class="btn-adm-action btn-adm-delete" style="font-size: 11px; padding: 6px 12px;">
              <i class="ri-delete-bin-line"></i> Remove Video
            </button>
          </div>
        <?php endif; ?>
        <input type="hidden" name="remove_services_hero_video" id="remove_services_hero_video" value="0">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-upload-cloud-2-line"></i> Option 1: Upload Video File (MP4, WebM)</label>
            <input type="file" name="services_hero_video_file" class="visual-input-styled" accept="video/mp4,video/webm">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Max 50MB MP4/WebM video.</small>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-link"></i> Option 2: Or Paste Direct Video URL</label>
            <input type="text" name="hero_video_url" class="visual-input-styled" value="<?= e($curServicesHeroVideo) ?>" placeholder="/uploads/services_hero.mp4 or https://...">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">External CDN or hosted video URL.</small>
          </div>
        </div>
      </div>

      <div style="margin-top: 16px;">
        <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Display Mode &amp; Video</button>
      </div>
    </div>
  </form>

  <!-- Full Hero Slides List for Services -->
  <div class="admin-card" style="margin-bottom: 0; background: #FFFFFF; border-radius: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
    <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--admin-border);">
      <div>
        <h2 class="card-title" style="font-size: 18px; font-weight: 700; color: var(--admin-navy); margin: 0;">
          <i class="ri-slideshow-line" style="color: var(--admin-teal);"></i> Manage Services Hero Slides (<?= count($servicesHeroSlidesList) ?>)
        </h2>
        <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;">
          Slides curated specifically for the What We Do / Services Page.
        </div>
      </div>
      <a href="<?= url('admin/hero/edit.php?page=services&return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm btn-adm-primary">
        <i class="ri-add-line"></i> Add New Slide
      </a>
    </div>

    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Artwork Preview</th>
            <th>Slide Title &amp; Eyebrow</th>
            <th>Buttons</th>
            <th>Sort</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($servicesHeroSlidesList)): ?>
            <?php foreach ($servicesHeroSlidesList as $slide): ?>
            <tr>
              <td style="width: 90px;">
                <?php if (!empty($slide['media_url'])): ?>
                  <img src="<?= media_url($slide['media_url']) ?>" alt="Slide" style="width: 76px; height: 46px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
                <?php else: ?>
                  <div style="width: 76px; height: 46px; background: var(--admin-teal-pale); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal); font-size: 18px;">
                    <i class="ri-image-line"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size: 11px; font-weight: 700; color: var(--admin-teal); font-family: var(--wdr-font-mono); text-transform: uppercase;">
                  <?= e($slide['eyebrow'] ?: 'Services Hero') ?>
                </div>
                <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-top: 2px;">
                  <?= e($slide['title']) ?>
                </div>
              </td>
              <td>
                <div style="font-size: 12px; font-weight: 600; color: var(--admin-navy);">1. <?= e($slide['button_primary_text'] ?: '—') ?></div>
                <div style="font-size: 11px; color: var(--admin-muted);">2. <?= e($slide['button_secondary_text'] ?: '—') ?></div>
              </td>
              <td><?= (int)$slide['sort_order'] ?></td>
              <td>
                <?php if ($slide['is_active']): ?>
                  <span class="badge badge-teal" style="font-size: 11px;">Active</span>
                <?php else: ?>
                  <span class="badge" style="background: #E2E8F0; color: #64748B; font-size: 11px;">Inactive</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div class="table-actions" style="justify-content: flex-end;">
                  <a href="<?= url('admin/hero/edit.php?id=' . $slide['id'] . '&page=services&return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm-action btn-adm-edit" title="Edit Slide">
                    <i class="ri-edit-line"></i> <span>Edit</span>
                  </a>
                  <form method="POST" action="<?= $currentUrl ?>?tab=sec01" style="display:inline; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this hero slide?');">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="delete_slide_id" value="<?= $slide['id'] ?>">
                    <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Slide">
                      <i class="ri-delete-bin-line"></i> <span>Delete</span>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 24px;">No hero slides created for Services yet. Click "Add New Slide" to create one.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 02: QUICK JUMP & MATRIX HEADER
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec02'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px;">
    <span class="visual-badge"><i class="ri-compass-3-line"></i> SECTION 02 — INTRO &amp; JUMP BAR</span>
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Services Matrix Header &amp; Quick Jump Bar</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Heading, badge, and intro copy introducing the core editorial disciplines.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec02">
    <?= CSRF::field() ?>
    <input type="hidden" name="services_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec02">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <div style="margin-bottom: 16px;">
        <label class="visual-label-upper">Section Eyebrow Badge</label>
        <input type="text" name="services_sec2_badge" class="visual-input-styled" value="<?= e(setting('services_sec2_badge', 'CORE EDITORIAL DISCIPLINES')) ?>">
      </div>

      <div style="margin-bottom: 16px;">
        <label class="visual-label-upper">Main Section Headline</label>
        <input type="text" name="services_sec2_title" class="visual-input-styled" style="font-size: 16px; font-weight: 700;" value="<?= e(setting('services_sec2_title', 'Engineered for Depth. Refined for Impact.')) ?>">
      </div>

      <div style="margin-bottom: 16px;">
        <label class="visual-label-upper">Supporting Description</label>
        <textarea name="services_sec2_desc" class="visual-input-styled" rows="3"><?= e(setting('services_sec2_desc', 'Each discipline is led by specialized domain writers and subject-matter editors who understand the nuances of your industry.')) ?></textarea>
      </div>

      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 16px; margin-top: 20px;">
        <label class="visual-label-upper"><i class="ri-compass-3-line"></i> Active Quick Jump Pills (Generated from Active Services)</label>
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
          <?php foreach ($allServicesList as $srv): if (!$srv['is_active']) continue; ?>
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #FFF; border: 1px solid #CBD5E1; border-radius: 20px; font-size: 12px; font-weight: 700; color: var(--wdr-navy);">
              <i class="<?= e($srv['icon'] ?: 'ri-quill-pen-line') ?>" style="color: var(--wdr-teal);"></i>
              <?= e($srv['title']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="margin-top: 24px;">
        <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Intro &amp; Jump Bar</button>
      </div>
    </div>
  </form>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 03: MASTER SERVICE SHOWCASE (STACKING CARDS)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec03'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
      <span class="visual-badge"><i class="ri-stack-line"></i> SECTION 03 — MASTER DISCIPLINES</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Master Service Showcase (Stacking Cards Deck)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Each service card renders its own full scope, verified tag, bullets, metric pill, artwork, and CTA button.</p>
    </div>
    <a href="<?= url('admin/services/edit.php') ?>" class="btn-adm btn-adm-primary">
      <i class="ri-add-line"></i> Add New Service Discipline
    </a>
  </div>

  <div class="admin-card" style="margin-bottom: 0; background: #FFFFFF; border-radius: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Artwork</th>
            <th>Discipline &amp; Tag</th>
            <th>Metrics Lift</th>
            <th>Deliverables Scope</th>
            <th>Sort</th>
            <th>Status</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($allServicesList)): ?>
            <?php foreach ($allServicesList as $srv): ?>
            <tr>
              <td style="width: 80px;">
                <?php if (!empty($srv['image_path'])): ?>
                  <img src="<?= media_url($srv['image_path']) ?>" alt="Service" style="width: 65px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
                <?php else: ?>
                  <div style="width: 65px; height: 42px; background: var(--admin-teal-pale); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal);">
                    <i class="<?= e($srv['icon'] ?: 'ri-quill-pen-line') ?>"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size: 11px; font-weight: 700; color: var(--admin-teal); font-family: var(--wdr-font-mono); text-transform: uppercase;">
                  <?= e($srv['tag'] ?: 'Editorial Discipline') ?>
                </div>
                <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-top: 2px;">
                  <?= e($srv['title']) ?>
                </div>
              </td>
              <td>
                <div style="font-size: 13px; font-weight: 700; color: var(--admin-navy);"><?= e($srv['metrics_val'] ?: '—') ?></div>
                <div style="font-size: 11px; color: var(--admin-muted);"><?= e($srv['metrics_lbl'] ?: 'Impact') ?></div>
              </td>
              <td>
                <div style="font-size: 12px; color: var(--admin-navy); max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                  <?php
                  $delivText = $srv['deliverables'] ?? '';
                  if (empty($delivText) && !empty($srv['bullets'])) {
                      $dec = json_decode($srv['bullets'], true);
                      if (is_array($dec)) {
                          $titles = array_filter(array_column($dec, 'title'));
                          $delivText = implode('; ', $titles);
                      } else {
                          $delivText = $srv['bullets'];
                      }
                  }
                  ?>
                  <?= e($delivText) ?>
                </div>
              </td>
              <td><?= (int)$srv['sort_order'] ?></td>
              <td>
                <?php if ($srv['is_active']): ?>
                  <span class="badge badge-teal" style="font-size: 11px;">Active</span>
                <?php else: ?>
                  <span class="badge" style="background: #E2E8F0; color: #64748B; font-size: 11px;">Inactive</span>
                <?php endif; ?>
              </td>
              <td style="text-align: right;">
                <div class="table-actions" style="justify-content: flex-end;">
                  <a href="<?= url('admin/services/edit.php?id=' . $srv['id']) ?>" class="btn-adm-action btn-adm-edit" title="Edit Service">
                    <i class="ri-edit-line"></i> <span>Edit Scope</span>
                  </a>
                  <a href="<?= url('service/' . urlencode($srv['slug'])) ?>" target="_blank" class="btn-adm-action" style="background: #FFF; border: 1.5px solid #CBD5E1; color: var(--wdr-navy);" title="View Detail Page">
                    <i class="ri-external-link-line"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align: center; color: var(--admin-muted); padding: 24px;">No services found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 04: OUR METHODOLOGY (4-STAGE FRAMEWORK)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec04'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec04" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="services_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec04">

  <div class="visual-studio-card-dark">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge visual-badge-dark"><i class="ri-node-tree"></i> SECTION 04 — 4-STAGE METHODOLOGY</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px; color: #FFFFFF;">The 4-Stage Editorial Framework</h2>
      <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0;">Dark luxury container with process artwork and 4 glowing stage cards.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start; margin-bottom: 24px;">
      
      <!-- Left Column: Settings and 4 Stage Cards -->
      <div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper" style="color: #FFF;">Section Eyebrow Badge</label>
            <input type="text" name="services_sec4_badge" class="visual-input-styled visual-input-dark" value="<?= e(setting('services_sec4_badge', 'OUR METHODOLOGY')) ?>">
          </div>
          <div>
            <label class="visual-label-upper" style="color: #FFF;">Main Headline</label>
            <input type="text" name="services_sec4_title" class="visual-input-styled visual-input-dark" value="<?= e(setting('services_sec4_title', 'The 4-Stage Editorial Framework')) ?>" style="font-weight: 700;">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper" style="color: #FFF;">Supporting Description</label>
          <textarea name="services_sec4_desc" class="visual-input-styled visual-input-dark" rows="2"><?= e(setting('services_sec4_desc', 'How we transform a rough brief into authoritative, search-dominant, and commercially potent content.')) ?></textarea>
        </div>

        <!-- 4 Framework Steps -->
        <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-teal-light); margin-bottom: 12px;"><i class="ri-node-tree"></i> 4 Production Framework Steps</h3>
        <?php 
        $stepsJson = setting('services_sec4_steps', '');
        $steps = !empty($stepsJson) ? json_decode($stepsJson, true) : [];
        if (empty($steps)) {
            $steps = [
                ['num' => '01', 'title' => 'Discovery & Intent Audit', 'desc' => 'We dissect your audience personas, buyer journey stages, competitor keyword gaps, and brand positioning requirements before writing a single word.'],
                ['num' => '02', 'title' => 'Architecture & Thesis', 'desc' => 'Structuring topic clusters, semantic keyword mappings, thesis outlines, and editorial frameworks that give every piece strategic direction.'],
                ['num' => '03', 'title' => 'Human Craftsmanship', 'desc' => 'Senior domain writers draft copy tailored to the exact rhythm, vocabulary, and technical expectations of your sector. Zero AI filler.'],
                ['num' => '04', 'title' => 'Fact-Checking & Polish', 'desc' => 'Multi-layer editorial review, citation verification, search-intent audits, and two comprehensive revision cycles before delivery.']
            ];
        }
        ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($steps as $idx => $st): ?>
            <div style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.15); border-radius: 10px; padding: 14px;">
              <div style="display: flex; gap: 8px; margin-bottom: 8px;">
                <input type="text" name="steps[<?= $idx ?>][num]" class="visual-input-styled visual-input-dark" value="<?= e($st['num'] ?? sprintf('%02d', $idx+1)) ?>" style="width: 60px; text-align: center; font-weight: 800; font-family: var(--wdr-font-mono);">
                <input type="text" name="steps[<?= $idx ?>][title]" class="visual-input-styled visual-input-dark" value="<?= e($st['title'] ?? '') ?>" placeholder="Step Title" style="flex: 1; font-weight: 700;">
              </div>
              <textarea name="steps[<?= $idx ?>][desc]" class="visual-input-styled visual-input-dark" rows="2" placeholder="Step description..."><?= e($st['desc'] ?? '') ?></textarea>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right Column: Process Artwork Upload Frame -->
      <div>
        <?php $sec4Art = setting('services_sec4_artwork', '/img/process.png'); ?>
        <div style="background: rgba(255,255,255,0.06); border: 1.5px dashed rgba(74, 139, 140, 0.5); border-radius: 20px; padding: 24px; text-align: center;">
          <label class="visual-label-upper" style="color: #FFF; margin-bottom: 12px; display: block;"><i class="ri-image-line"></i> Process Methodology Artwork</label>
          <div style="background: #FAF8F5; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.2);">
            <img id="process_art_img" src="<?= media_url($sec4Art) ?>" alt="Process graphic" style="max-height: 180px; width: auto; object-fit: contain; margin: 0 auto; display: block; border-radius: 8px;">
          </div>
          <div style="text-align: left; background: rgba(0,0,0,0.3); padding: 14px; border-radius: 10px;">
            <label style="font-size: 11px; font-weight: 700; color: #FFF; display: block; margin-bottom: 6px;">Upload Replacement Artwork</label>
            <input type="file" name="services_sec4_artwork_file" class="visual-input-styled visual-input-dark" accept="image/*">
            <?php if (!empty($sec4Art) && $sec4Art !== '/img/process.png'): ?>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #F87171; margin-top: 8px; cursor: pointer;">
                <input type="checkbox" name="remove_sec4_artwork" value="1"> Reset to Default Process Artwork
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save 4-Stage Methodology</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 05: COMMODITY vs. WORDORA EDITORIAL (TABLE)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec05'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px;">
    <span class="visual-badge"><i class="ri-table-line"></i> SECTION 05 — THE EDITORIAL ADVANTAGE</span>
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Commodity Content vs. Wordora Editorial Table</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">5-pillar evaluation table highlighting research depth, search intent, voice, governance, and commercial ROI.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec05">
    <?= CSRF::field() ?>
    <input type="hidden" name="services_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec05">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
        <div>
          <label class="visual-label-upper">Section Eyebrow Badge</label>
          <input type="text" name="services_sec5_badge" class="visual-input-styled" value="<?= e(setting('services_sec5_badge', 'THE EDITORIAL ADVANTAGE')) ?>">
        </div>
        <div>
          <label class="visual-label-upper">Main Headline</label>
          <input type="text" name="services_sec5_title" class="visual-input-styled" value="<?= e(setting('services_sec5_title', 'Commodity Content vs. Wordora Editorial')) ?>" style="font-weight: 700;">
        </div>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="visual-label-upper">Supporting Description</label>
        <textarea name="services_sec5_desc" class="visual-input-styled" rows="2"><?= e(setting('services_sec5_desc', 'Why discerning market leaders partner with Wordora instead of generic freelance platforms or automated AI tools.')) ?></textarea>
      </div>

      <h3 style="font-size: 16px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 12px;"><i class="ri-table-line"></i> 5 Comparison Evaluation Pillars</h3>
      <?php 
      $tableJson = setting('services_sec5_table', '');
      $tableRows = !empty($tableJson) ? json_decode($tableJson, true) : [];
      if (empty($tableRows)) {
          $tableRows = [
              ['pillar' => 'Research Depth', 'commodity' => 'Surface-level summaries regurgitated from search snippets.', 'wordora' => 'Primary data sourcing, expert quotes & academic synthesis.'],
              ['pillar' => 'Search Engine Intent', 'commodity' => 'Keyword stuffing that gets flagged or demoted by Google.', 'wordora' => 'Topic cluster architecture & high-intent conversion paths.'],
              ['pillar' => 'Voice & Nuance', 'commodity' => 'Repetitive, robotic cadence with zero brand personality.', 'wordora' => 'Bespoke tone governance matching your unique market stature.'],
              ['pillar' => 'Turnaround Governance', 'commodity' => 'Unpredictable deadlines, ghosting, and endless rework.', 'wordora' => 'Strict sprint schedules with dedicated managing editors.'],
              ['pillar' => 'Commercial ROI', 'commodity' => 'Zero reader trust, high bounce rates, wasted budget.', 'wordora' => 'High organic rankings, pipeline velocity & qualified inbound leads.']
          ];
      }
      ?>

      <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
        <?php foreach ($tableRows as $idx => $r): ?>
          <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.3); border-radius: 10px; padding: 16px; display: grid; grid-template-columns: 180px 1fr 1fr; gap: 12px; align-items: center;">
            <div>
              <label class="visual-label-upper" style="font-size: 10px;">Pillar</label>
              <input type="text" name="table_rows[<?= $idx ?>][pillar]" class="visual-input-styled" style="font-weight: 800;" value="<?= e($r['pillar']) ?>">
            </div>
            <div>
              <label class="visual-label-upper" style="font-size: 10px; color: #991B1B;"><i class="ri-close-circle-line"></i> Commodity / AI Content</label>
              <textarea name="table_rows[<?= $idx ?>][commodity]" class="visual-input-styled" rows="2"><?= e($r['commodity']) ?></textarea>
            </div>
            <div>
              <label class="visual-label-upper" style="font-size: 10px; color: var(--wdr-teal);"><i class="ri-checkbox-circle-line"></i> WORDORA Editorial</label>
              <textarea name="table_rows[<?= $idx ?>][wordora]" class="visual-input-styled" rows="2" style="background: #F0FDF4; border-color: #86EFAC; color: #166534; font-weight: 600;"><?= e($r['wordora']) ?></textarea>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Comparison Table</button>
    </div>
  </form>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 06: ENGAGEMENT MODELS & SCOPE TIERS
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec06'): ?>
<div class="visual-studio-card">
  <div style="margin-bottom: 20px;">
    <span class="visual-badge"><i class="ri-price-tag-3-line"></i> SECTION 06 — ENGAGEMENT MODELS</span>
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Flexible Editorial Scopes &amp; Engagement Models</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">3 distinct engagement tiers: Sprint Model, Most Popular Brand Launch, and Executive Retainer.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec06">
    <?= CSRF::field() ?>
    <input type="hidden" name="services_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec06">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 16px;">
        <div>
          <label class="visual-label-upper">Section Eyebrow Badge</label>
          <input type="text" name="services_sec6_badge" class="visual-input-styled" value="<?= e(setting('services_sec6_badge', 'ENGAGEMENT MODELS')) ?>">
        </div>
        <div>
          <label class="visual-label-upper">Main Headline</label>
          <input type="text" name="services_sec6_title" class="visual-input-styled" value="<?= e(setting('services_sec6_title', 'Flexible Editorial Scopes')) ?>" style="font-weight: 700;">
        </div>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="visual-label-upper">Supporting Description</label>
        <textarea name="services_sec6_desc" class="visual-input-styled" rows="2"><?= e(setting('services_sec6_desc', 'Whether you require a one-time brand manifesto or a high-velocity monthly content engine, we structure transparent, predictable engagements.')) ?></textarea>
      </div>

      <h3 style="font-size: 16px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 12px;"><i class="ri-price-tag-3-line"></i> 3 Scope Tier Cards</h3>
      <?php 
      $tiersJson = setting('services_sec6_tiers', '');
      $tiers = !empty($tiersJson) ? json_decode($tiersJson, true) : [];
      if (empty($tiers)) {
          $tiers = [
              [
                  'badge' => 'Sprint Model',
                  'title' => 'Topic Cluster Engine',
                  'desc' => 'For growth-stage SaaS and B2B firms scaling organic search footprint and outranking incumbents.',
                  'bullets' => "4 In-Depth Long-Form Pillars (2,000+ words)\nSemantic Keyword & Topic Map\nMeta Descriptions & Schema Schematics\n2 Full Rounds of Editorial Revisions",
                  'btn_text' => 'Request Cluster Scope',
                  'btn_url' => 'contact.php?plan=topic-cluster',
                  'is_featured' => 0
              ],
              [
                  'badge' => 'Most Popular',
                  'title' => 'Brand Voice & Launch',
                  'desc' => 'Complete messaging architecture, website copy decks, and brand manifesto for new product launches.',
                  'bullets' => "Full Homepage & Core Service Copy\nBrand Manifesto & Tagline Matrix\nExecutive Pitch Deck Narrative\nComprehensive Tone & Style Guide",
                  'btn_text' => 'Request Launch Scope',
                  'btn_url' => 'contact.php?plan=brand-voice',
                  'is_featured' => 1
              ],
              [
                  'badge' => 'Executive Retainer',
                  'title' => 'C-Suite Thought Leadership',
                  'desc' => 'Ghostwriting for founders, managing partners, and venture leaders to build undeniable market authority.',
                  'bullets' => "Weekly Strategic LinkedIn Essays\nMonthly Industry Whitepaper or Digest\nMulti-Slide Figma Carousel Graphics\nGhostwritten Op-Eds & Guest Posts",
                  'btn_text' => 'Request Retainer Scope',
                  'btn_url' => 'contact.php?plan=executive-thought-leadership',
                  'is_featured' => 0
              ]
          ];
      }
      ?>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <?php foreach ($tiers as $idx => $t): ?>
          <div style="background: <?= !empty($t['is_featured']) ? 'var(--wdr-deep-navy)' : '#FAF8F5' ?>; color: <?= !empty($t['is_featured']) ? '#FFF' : 'var(--wdr-navy)' ?>; border: 1.5px dashed <?= !empty($t['is_featured']) ? 'var(--wdr-teal-light)' : 'rgba(74, 139, 140, 0.4)' ?>; border-radius: 12px; padding: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
              <input type="text" name="tiers[<?= $idx ?>][badge]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" value="<?= e($t['badge']) ?>" placeholder="Badge Label" style="max-width: 140px; font-size: 11px; font-weight: 800;">
              <label style="font-size: 11px; display: inline-flex; align-items: center; gap: 4px; cursor: pointer; color: <?= !empty($t['is_featured']) ? 'var(--wdr-teal-light)' : 'var(--wdr-teal)' ?>;">
                <input type="checkbox" name="tiers[<?= $idx ?>][is_featured]" value="1" <?= !empty($t['is_featured']) ? 'checked' : '' ?> style="accent-color: var(--wdr-teal);">
                <strong>Featured Dark Card</strong>
              </label>
            </div>

            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper" style="color: inherit; font-size: 10px;">Plan Title</label>
              <input type="text" name="tiers[<?= $idx ?>][title]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" style="font-size: 15px; font-weight: 800;" value="<?= e($t['title']) ?>">
            </div>

            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper" style="color: inherit; font-size: 10px;">Description</label>
              <textarea name="tiers[<?= $idx ?>][desc]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" rows="2"><?= e($t['desc']) ?></textarea>
            </div>

            <div style="margin-bottom: 10px;">
              <label class="visual-label-upper" style="color: inherit; font-size: 10px;">Bullets (One per line)</label>
              <textarea name="tiers[<?= $idx ?>][bullets]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" rows="4"><?= e($t['bullets']) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
              <div>
                <label class="visual-label-upper" style="color: inherit; font-size: 10px;">Button Text</label>
                <input type="text" name="tiers[<?= $idx ?>][btn_text]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" value="<?= e($t['btn_text'] ?? 'Request Scope') ?>">
              </div>
              <div>
                <label class="visual-label-upper" style="color: inherit; font-size: 10px;">Button URL</label>
                <input type="text" name="tiers[<?= $idx ?>][btn_url]" class="visual-input-styled <?= !empty($t['is_featured']) ? 'visual-input-dark' : '' ?>" value="<?= e($t['btn_url'] ?? 'contact.php') ?>">
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Engagement Models</button>
    </div>
  </form>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 07: FREQUENTLY ASKED QUESTIONS (FAQ)
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec07'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec07" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="services_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec07">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-questionnaire-line"></i> SECTION 07 — FAQ &amp; OBJECTION HANDLING</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Frequently Asked Questions (FAQ)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Interactive accordions answering domain expertise, AI policy, turnaround, revisions, and NDAs.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      
      <!-- Left Column: FAQ Inputs -->
      <div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper">Section Eyebrow Badge</label>
            <input type="text" name="services_sec7_badge" class="visual-input-styled" value="<?= e(setting('services_sec7_badge', 'FREQUENTLY ASKED QUESTIONS')) ?>">
          </div>
          <div>
            <label class="visual-label-upper">Main Headline</label>
            <input type="text" name="services_sec7_title" class="visual-input-styled" value="<?= e(setting('services_sec7_title', 'Everything You Need to Know')) ?>" style="font-weight: 700;">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper">Supporting Description</label>
          <textarea name="services_sec7_desc" class="visual-input-styled" rows="2"><?= e(setting('services_sec7_desc', 'Clear answers on how we scope, draft, refine, and deliver high-impact content.')) ?></textarea>
        </div>

        <!-- Dynamic FAQ Items -->
        <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 12px;"><i class="ri-questionnaire-line"></i> 5 Expandable FAQ Questions &amp; Answers</h3>
        <?php 
        $faqsJson = setting('services_sec7_faqs', '');
        $faqs = !empty($faqsJson) ? json_decode($faqsJson, true) : [];
        if (empty($faqs)) {
            $faqs = [
                ['q' => 'How do you ensure writers understand our complex domain?', 'a' => 'Every project is assigned to a senior writer with relevant background in your domain (e.g. computer science, finance, biomedicine, B2B SaaS). We conduct a comprehensive discovery interview and review your technical docs before writing.'],
                ['q' => 'What is your policy on AI-generated content?', 'a' => '100% human-crafted and fact-checked. We use technology only for semantic keyword clustering and grammar audits. Every sentence, thesis, and argument is constructed by experienced human journalists and editors.'],
                ['q' => 'How many revisions are included in a project scope?', 'a' => 'All scopes include two complete rounds of revisions within 14 days of delivery. Because we align on detailed outlines beforehand, most deliverables are approved on the first review.'],
                ['q' => 'Do you sign Non-Disclosure Agreements (NDAs)?', 'a' => 'Yes, unconditionally. We protect all proprietary data, pre-release roadmaps, and ghostwriting arrangements under strict mutual NDAs.'],
                ['q' => 'What is the typical turnaround timeline?', 'a' => 'Standard blog articles are delivered in 5 to 7 business days. Deep-technical whitepapers and full brand messaging bibles typically require 10 to 14 business days.']
            ];
        }
        ?>

        <div id="faq_items_container" style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
          <?php foreach ($faqs as $idx => $f): ?>
            <div class="faq-item-row" style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 16px;">
              <div style="margin-bottom: 8px;">
                <label class="visual-label-upper" style="color: var(--wdr-teal);">Question #<?= $idx + 1 ?></label>
                <input type="text" name="faqs[<?= $idx ?>][q]" class="visual-input-styled" style="font-weight: 700;" value="<?= e($f['q']) ?>">
              </div>
              <div>
                <label class="visual-label-upper">Answer Narrative</label>
                <textarea name="faqs[<?= $idx ?>][a]" class="visual-input-styled" rows="3"><?= e($f['a']) ?></textarea>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right Column: Artwork Uploader -->
      <div>
        <div class="visual-media-frame">
          <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;">Right-Side FAQ Artwork (FAQ 2.png)</label>
          <?php $sec7Art = setting('services_sec7_artwork', '/img/FAQ 2.png'); ?>
          <img src="<?= media_url($sec7Art) ?>" alt="FAQ Artwork" style="max-height: 220px; width: auto; object-fit: contain; margin: 0 auto 16px; display: block; border-radius: 12px;">
          
          <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload New FAQ Artwork</label>
            <input type="file" name="services_sec7_artwork_file" class="visual-input-styled" accept="image/*">
            <?php if (!empty($sec7Art) && $sec7Art !== '/img/FAQ 2.png'): ?>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #991B1B; margin-top: 8px; cursor: pointer;">
                <input type="checkbox" name="remove_sec7_artwork" value="1"> Reset to Default FAQ Illustration
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save FAQ Section</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 08: START A CONVERSATION CTA
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec08'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec08" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="services_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec08">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-send-plane-fill"></i> SECTION 08 — BOTTOM CTA SIGNATURE</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Start a Conversation (Bottom CTA Signature)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Full-bleed luxury banner converting readers with dual buttons, trust pills, and artwork.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      
      <!-- Left Column: CTA Inputs -->
      <div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper">Badge Label</label>
            <input type="text" name="services_sec8_badge" class="visual-input-styled" value="<?= e(setting('services_sec8_badge', 'READY TO ELEVATE YOUR WORDS?')) ?>">
          </div>
          <div>
            <label class="visual-label-upper">Main Headline</label>
            <input type="text" name="services_sec8_title" class="visual-input-styled" style="font-weight: 700;" value="<?= e(setting('services_sec8_title', 'Let\'s build content worth reading.')) ?>">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper">Supporting Description</label>
          <textarea name="services_sec8_desc" class="visual-input-styled" rows="2"><?= e(setting('services_sec8_desc', 'Tell us about your brand, your goals, and what you need written. We\'ll deliver a tailored proposal within 24 hours.')) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
          <div style="background: #FFF; padding: 12px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--wdr-teal); font-size: 11px; margin-bottom: 4px;">PRIMARY BUTTON</div>
            <input type="text" name="services_sec8_btn1_text" class="visual-input-styled" value="<?= e(setting('services_sec8_btn1_text', 'Start a Conversation')) ?>" placeholder="Text" style="margin-bottom: 6px;">
            <input type="text" name="services_sec8_btn1_url" class="visual-input-styled" value="<?= e(setting('services_sec8_btn1_url', 'contact.php')) ?>" placeholder="URL">
          </div>
          <div style="background: #FFF; padding: 12px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--wdr-teal); font-size: 11px; margin-bottom: 4px;">SECONDARY BUTTON</div>
            <input type="text" name="services_sec8_btn2_text" class="visual-input-styled" value="<?= e(setting('services_sec8_btn2_text', 'Our Editorial Story')) ?>" placeholder="Text" style="margin-bottom: 6px;">
            <input type="text" name="services_sec8_btn2_url" class="visual-input-styled" value="<?= e(setting('services_sec8_btn2_url', 'who-we-are.php')) ?>" placeholder="URL">
          </div>
        </div>

        <!-- Trust Pills -->
        <h3 style="font-size: 14px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 10px;"><i class="ri-shield-check-line"></i> Trust Assurance Pills</h3>
        <?php 
        $pillsJson = setting('services_sec8_pills', '');
        $pills = !empty($pillsJson) ? json_decode($pillsJson, true) : [];
        if (empty($pills)) {
            $pills = [
                ['icon' => 'ri-time-line', 'text' => '24-Hour Response Guarantee'],
                ['icon' => 'ri-shield-check-line', 'text' => 'Strict Mutual NDA Protection'],
                ['icon' => 'ri-quill-pen-line', 'text' => 'Free Editorial Strategy Audit']
            ];
        }
        ?>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px;">
          <?php foreach ($pills as $idx => $p): ?>
            <div style="display: flex; gap: 8px; align-items: center; background: #FFF; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--admin-border);">
              <input type="text" name="pills[<?= $idx ?>][icon]" class="visual-input-styled" value="<?= e($p['icon'] ?? 'ri-checkbox-circle-fill') ?>" style="width: 140px; font-size: 11px;">
              <input type="text" name="pills[<?= $idx ?>][text]" class="visual-input-styled" value="<?= e($p['text'] ?? '') ?>" placeholder="Pill text" style="flex: 1; font-weight: 600;">
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right Column: Artwork Uploader -->
      <div>
        <div class="visual-media-frame">
          <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;">Right-Side CTA Artwork (cta 1.png)</label>
          <?php $sec8Art = setting('services_sec8_artwork', '/img/cta 1.png'); ?>
          <img src="<?= media_url($sec8Art) ?>" alt="CTA Art" style="max-height: 220px; width: auto; object-fit: contain; margin: 0 auto 16px; display: block; border-radius: 12px;">
          
          <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload New CTA Artwork</label>
            <input type="file" name="services_sec8_artwork_file" class="visual-input-styled" accept="image/*">
            <?php if (!empty($sec8Art) && $sec8Art !== '/img/cta 1.png'): ?>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #991B1B; margin-top: 8px; cursor: pointer;">
                <input type="checkbox" name="remove_sec8_artwork" value="1"> Reset to Default CTA Illustration
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save CTA Section</button>
  </div>
</form>
<?php endif; ?>
