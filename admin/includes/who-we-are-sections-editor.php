<?php
/**
 * WORDORA — Reusable Who We Are Live Visual Section Editor Component
 * Included in admin/pages/who-we-are.php
 */

$editorError = '';
$activeTab = $_GET['tab'] ?? 'sec01';
$currentUrl = strtok($_SERVER['REQUEST_URI'], '?');

// Handle Delete Hero Slide Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_slide_id'])) {
    if (CSRF::verify($_POST['csrf_token'] ?? '')) {
        $delId = (int)$_POST['delete_slide_id'];
        Hero::delete($delId);
        flash_set('success', 'Hero slide deleted successfully.');
        redirect($currentUrl . '?tab=sec01');
    }
}

// Handle POST Save for all sections
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['who_we_are_editor_submit'])) {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please try again.';
    } else {
        $uploader = new Upload('about');
        $tab = $_POST['tab'] ?? 'sec01';
        $activeTab = $tab;

        // Section 01: Hero Cover & Multi-Mode (Upload + URL)
        if ($tab === 'sec01') {
            if (isset($_POST['hero_mode'])) {
                Setting::set('hero_mode_who_we_are', $_POST['hero_mode']);
            }
            $existingWhoVideo = setting('who_hero_video_url', '');
            $whoVideo = $existingWhoVideo;

            if (!empty($_POST['remove_who_hero_video']) && $_POST['remove_who_hero_video'] === '1') {
                delete_uploaded_file($existingWhoVideo);
                $whoVideo = '';
            } elseif (isset($_FILES['who_hero_video_file']) && $_FILES['who_hero_video_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_hero_video_file'], true);
                if ($up['success']) { 
                    if (!empty($existingWhoVideo) && $existingWhoVideo !== $up['path']) {
                        delete_uploaded_file($existingWhoVideo);
                    }
                    $whoVideo = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (isset($_POST['hero_video_url'])) {
                $newVideoUrl = trim($_POST['hero_video_url']);
                if ($newVideoUrl !== $existingWhoVideo && !empty($existingWhoVideo)) {
                    delete_uploaded_file($existingWhoVideo);
                }
                $whoVideo = $newVideoUrl;
            }

            if (!$editorError) {
                Setting::set('who_hero_video_url', $whoVideo);
            }
        }

        // Section 02: Our Mission
        if ($tab === 'sec02') {
            $sec2Art = setting('who_sec2_artwork', '/img/why choose us.png');
            if (isset($_FILES['who_sec2_artwork_file']) && $_FILES['who_sec2_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec2_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec2Art) && $sec2Art !== $up['path']) {
                        delete_uploaded_file($sec2Art);
                    }
                    $sec2Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec2_artwork']) && $_POST['remove_sec2_artwork'] === '1') {
                delete_uploaded_file($sec2Art);
                $sec2Art = '/img/why choose us.png';
            }

            if (!$editorError) {
                Setting::set('who_sec2_badge', trim($_POST['who_sec2_badge'] ?? ''));
                Setting::set('who_sec2_title', trim($_POST['who_sec2_title'] ?? ''));
                Setting::set('who_sec2_p1', trim($_POST['who_sec2_p1'] ?? ''));
                Setting::set('who_sec2_quote', trim($_POST['who_sec2_quote'] ?? ''));
                Setting::set('who_sec2_p2', trim($_POST['who_sec2_p2'] ?? ''));
                Setting::set('who_sec2_btn1_text', trim($_POST['who_sec2_btn1_text'] ?? ''));
                Setting::set('who_sec2_btn1_url', trim($_POST['who_sec2_btn1_url'] ?? ''));
                Setting::set('who_sec2_btn2_text', trim($_POST['who_sec2_btn2_text'] ?? ''));
                Setting::set('who_sec2_btn2_url', trim($_POST['who_sec2_btn2_url'] ?? ''));
                Setting::set('who_sec2_artwork', $sec2Art);
            }
        }

        // Section 03: Editorial Capabilities Marquee
        if ($tab === 'sec03') {
            $sec3MarqueeBg = setting('who_sec3_marquee_bg', '/img/papaer banner.png');
            if (isset($_FILES['who_sec3_marquee_bg_file']) && $_FILES['who_sec3_marquee_bg_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec3_marquee_bg_file']);
                if ($up['success']) { 
                    if (!empty($sec3MarqueeBg) && $sec3MarqueeBg !== $up['path']) {
                        delete_uploaded_file($sec3MarqueeBg);
                    }
                    $sec3MarqueeBg = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec3_marquee_bg']) && $_POST['remove_sec3_marquee_bg'] === '1') {
                delete_uploaded_file($sec3MarqueeBg);
                $sec3MarqueeBg = '/img/papaer banner.png';
            }

            $marqueeRows = [
                'row1' => trim($_POST['who_marquee_row1'] ?? ''),
                'row2' => trim($_POST['who_marquee_row2'] ?? ''),
                'row3' => trim($_POST['who_marquee_row3'] ?? ''),
            ];

            if (!$editorError) {
                Setting::set('who_sec3_badge', trim($_POST['who_sec3_badge'] ?? ''));
                Setting::set('who_sec3_title', trim($_POST['who_sec3_title'] ?? ''));
                Setting::set('who_sec3_marquee_bg', $sec3MarqueeBg);
                Setting::set('who_sec3_rows', json_encode($marqueeRows, JSON_UNESCAPED_UNICODE));
            }
        }

        // Section 04: Our Journey (5 Milestones)
        if ($tab === 'sec04') {
            $sec4Art = setting('who_sec4_artwork', '/img/journey.png');
            if (isset($_FILES['who_sec4_artwork_file']) && $_FILES['who_sec4_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec4_artwork_file']);
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
                $sec4Art = '/img/journey.png';
            }

            $milestones = [];
            if (!empty($_POST['milestones']) && is_array($_POST['milestones'])) {
                foreach ($_POST['milestones'] as $idx => $m) {
                    $milestones[] = [
                        'year'      => trim($m['year'] ?? ''),
                        'tag'       => trim($m['tag'] ?? ''),
                        'title'     => trim($m['title'] ?? ''),
                        'desc'      => trim($m['desc'] ?? ''),
                        'is_active' => isset($m['is_active']) ? true : false,
                    ];
                }
            }

            if (!$editorError) {
                Setting::set('who_sec4_badge', trim($_POST['who_sec4_badge'] ?? ''));
                Setting::set('who_sec4_title', trim($_POST['who_sec4_title'] ?? ''));
                Setting::set('who_sec4_desc', trim($_POST['who_sec4_desc'] ?? ''));
                Setting::set('who_sec4_artwork', $sec4Art);
                if (!empty($milestones)) {
                    Setting::set('who_sec4_milestones', json_encode($milestones, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 05: Core Values
        if ($tab === 'sec05') {
            $sec5Art = setting('who_sec5_artwork', '/img/value.png');
            if (isset($_FILES['who_sec5_artwork_file']) && $_FILES['who_sec5_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec5_artwork_file']);
                if ($up['success']) { 
                    if (!empty($sec5Art) && $sec5Art !== $up['path']) {
                        delete_uploaded_file($sec5Art);
                    }
                    $sec5Art = $up['path']; 
                } else { 
                    $editorError = $up['msg']; 
                }
            } elseif (!empty($_POST['remove_sec5_artwork']) && $_POST['remove_sec5_artwork'] === '1') {
                delete_uploaded_file($sec5Art);
                $sec5Art = '/img/value.png';
            }

            $valCards = [];
            if (!empty($_POST['val_cards']) && is_array($_POST['val_cards'])) {
                foreach ($_POST['val_cards'] as $v) {
                    $valCards[] = [
                        'num'   => trim($v['num'] ?? ''),
                        'icon'  => trim($v['icon'] ?? 'ri-quill-pen-line'),
                        'title' => trim($v['title'] ?? ''),
                        'desc'  => trim($v['desc'] ?? ''),
                    ];
                }
            }

            if (!$editorError) {
                Setting::set('who_sec5_badge', trim($_POST['who_sec5_badge'] ?? ''));
                Setting::set('who_sec5_title', trim($_POST['who_sec5_title'] ?? ''));
                Setting::set('who_sec5_desc', trim($_POST['who_sec5_desc'] ?? ''));
                Setting::set('who_sec5_artwork', $sec5Art);
                if (!empty($valCards)) {
                    Setting::set('who_sec5_cards', json_encode($valCards, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 06: Meet The Team
        if ($tab === 'sec06') {
            $teamMembers = [];
            if (!empty($_POST['team']) && is_array($_POST['team'])) {
                foreach ($_POST['team'] as $idx => $t) {
                    $avatarImg = trim($t['avatar_img'] ?? ($t['image'] ?? ''));
                    if (!empty($t['remove_avatar']) && $t['remove_avatar'] === '1') {
                        $avatarImg = '';
                    }
                    if (!empty($_FILES['team']['name'][$idx]['avatar_file'])) {
                        try {
                            $fileArray = [
                                'name'     => $_FILES['team']['name'][$idx]['avatar_file'],
                                'type'     => $_FILES['team']['type'][$idx]['avatar_file'],
                                'tmp_name' => $_FILES['team']['tmp_name'][$idx]['avatar_file'],
                                'error'    => $_FILES['team']['error'][$idx]['avatar_file'],
                                'size'     => $_FILES['team']['size'][$idx]['avatar_file'],
                            ];
                            if ($fileArray['error'] === UPLOAD_ERR_OK) {
                                $avatarImg = Upload::image($fileArray, 'team');
                            }
                        } catch (\Exception $e) {
                            // keep existing
                        }
                    }

                    $teamMembers[] = [
                        'name'            => trim($t['name'] ?? ''),
                        'role'            => trim($t['role'] ?? ''),
                        'spec'            => trim($t['spec'] ?? ''),
                        'avatar_initials' => trim($t['avatar_initials'] ?? 'ED'),
                        'avatar_color'    => trim($t['avatar_color'] ?? '#4A8B8C'),
                        'avatar_bg'       => trim($t['avatar_bg'] ?? '#E8F4F4'),
                        'avatar_img'      => $avatarImg,
                        'image'           => $avatarImg,
                        'linkedin'        => trim($t['linkedin'] ?? 'https://linkedin.com'),
                    ];
                }
            }

            if (!$editorError) {
                Setting::set('who_sec6_badge', trim($_POST['who_sec6_badge'] ?? ''));
                Setting::set('who_sec6_title', trim($_POST['who_sec6_title'] ?? ''));
                Setting::set('who_sec6_desc', trim($_POST['who_sec6_desc'] ?? ''));
                if (!empty($teamMembers)) {
                    Setting::set('who_sec6_team', json_encode($teamMembers, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 07: Why Brands Choose WORDORA
        if ($tab === 'sec07') {
            $sec7Art = setting('who_sec7_artwork', '/img/culture notes.png');
            if (isset($_FILES['who_sec7_artwork_file']) && $_FILES['who_sec7_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec7_artwork_file']);
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
                $sec7Art = '/img/culture notes.png';
            }

            $pillars = [];
            if (!empty($_POST['pillars']) && is_array($_POST['pillars'])) {
                foreach ($_POST['pillars'] as $p) {
                    $pillars[] = [
                        'icon'  => trim($p['icon'] ?? 'ri-quill-pen-line'),
                        'title' => trim($p['title'] ?? ''),
                        'desc'  => trim($p['desc'] ?? ''),
                    ];
                }
            }

            $stats = [];
            if (!empty($_POST['stats']) && is_array($_POST['stats'])) {
                foreach ($_POST['stats'] as $s) {
                    $stats[] = [
                        'count'  => trim($s['count'] ?? '0'),
                        'suffix' => trim($s['suffix'] ?? '+'),
                        'label'  => trim($s['label'] ?? ''),
                    ];
                }
            }

            if (!$editorError) {
                Setting::set('who_sec7_badge', trim($_POST['who_sec7_badge'] ?? ''));
                Setting::set('who_sec7_title', trim($_POST['who_sec7_title'] ?? ''));
                Setting::set('who_sec7_desc', trim($_POST['who_sec7_desc'] ?? ''));
                Setting::set('who_sec7_btn_text', trim($_POST['who_sec7_btn_text'] ?? ''));
                Setting::set('who_sec7_btn_url', trim($_POST['who_sec7_btn_url'] ?? ''));
                Setting::set('who_sec7_artwork', $sec7Art);
                if (!empty($pillars)) {
                    Setting::set('who_sec7_pillars', json_encode($pillars, JSON_UNESCAPED_UNICODE));
                }
                if (!empty($stats)) {
                    Setting::set('who_sec7_stats', json_encode($stats, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // Section 08: Signature CTA
        if ($tab === 'sec08') {
            $sec8Art = setting('who_sec8_artwork', '/img/cta 1.png');
            if (isset($_FILES['who_sec8_artwork_file']) && $_FILES['who_sec8_artwork_file']['error'] === UPLOAD_ERR_OK) {
                $up = $uploader->handle($_FILES['who_sec8_artwork_file']);
                if ($up['success']) { $sec8Art = $up['path']; } else { $editorError = $up['msg']; }
            } elseif (!empty($_POST['remove_sec8_artwork']) && $_POST['remove_sec8_artwork'] === '1') {
                $sec8Art = '/img/cta 1.png';
            }

            if (!$editorError) {
                Setting::set('who_sec8_badge', trim($_POST['who_sec8_badge'] ?? ''));
                Setting::set('who_sec8_title', trim($_POST['who_sec8_title'] ?? ''));
                Setting::set('who_sec8_desc', trim($_POST['who_sec8_desc'] ?? ''));
                Setting::set('who_sec8_btn1_text', trim($_POST['who_sec8_btn1_text'] ?? ''));
                Setting::set('who_sec8_btn1_url', trim($_POST['who_sec8_btn1_url'] ?? ''));
                Setting::set('who_sec8_btn2_text', trim($_POST['who_sec8_btn2_text'] ?? ''));
                Setting::set('who_sec8_btn2_url', trim($_POST['who_sec8_btn2_url'] ?? ''));
                Setting::set('who_sec8_trust_pills', trim($_POST['who_sec8_trust_pills'] ?? ''));
                Setting::set('who_sec8_artwork', $sec8Art);
            }
        }

        if (!$editorError) {
            flash_set('success', 'Who We Are section saved successfully!');
            redirect($currentUrl . '?tab=' . $activeTab);
        }
    }
}

// Decode Data Arrays
$whoMilestones = json_decode(setting('who_sec4_milestones', '[]'), true) ?: [];
$whoValCards = json_decode(setting('who_sec5_cards', '[]'), true) ?: [];
$whoTeam = json_decode(setting('who_sec6_team', '[]'), true) ?: [];
$whoPillars = json_decode(setting('who_sec7_pillars', '[]'), true) ?: [];
$whoStats = json_decode(setting('who_sec7_stats', '[]'), true) ?: [];
$whoMarqueeRows = json_decode(setting('who_sec3_rows', '[]'), true) ?: [];
$whoHeroSlidesList = Hero::getAll('who_we_are');
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

.visual-label-upper {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--wdr-teal);
  display: block;
  margin-bottom: 8px;
}

.visual-display-heading {
  font-family: var(--wdr-font-display);
  font-size: 28px;
  font-weight: 700;
  color: var(--wdr-navy);
  line-height: 1.25;
  margin: 12px 0 16px;
}

.visual-quote-box {
  border-left: 3.5px solid var(--wdr-teal);
  background: #FFFFFF;
  padding: 16px 20px;
  border-radius: 0 12px 12px 0;
  margin: 18px 0;
  font-family: var(--wdr-font-display);
  font-style: italic;
  font-size: 17px;
  color: var(--wdr-deep-navy);
  box-shadow: 0 4px 14px rgba(15,30,54,0.04);
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
      <i class="ri-team-line" style="color: var(--admin-teal);"></i> Who We Are Live Visual Studio
    </h2>
    <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
      Who We Are Page ke sabhi 8 sections (01 to 08) ka complete editable visual content &amp; media editor.
    </p>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="<?= url('who-we-are.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
      <i class="ri-external-link-line"></i> View Live Who We Are
    </a>
  </div>
</div>

<?php if ($editorError): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
    <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
  </div>
<?php endif; ?>

<!-- Section Navigation Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
  <?php
  $tabs = [
      'sec01' => ['num' => '01', 'name' => 'Hero Banner', 'icon' => 'ri-slideshow-line'],
      'sec02' => ['num' => '02', 'name' => 'Our Mission', 'icon' => 'ri-quill-pen-line'],
      'sec03' => ['num' => '03', 'name' => 'Capabilities Marquee', 'icon' => 'ri-apps-line'],
      'sec04' => ['num' => '04', 'name' => 'Our Journey', 'icon' => 'ri-route-line'],
      'sec05' => ['num' => '05', 'name' => 'Core Values', 'icon' => 'ri-shield-star-line'],
      'sec06' => ['num' => '06', 'name' => 'Meet The Team', 'icon' => 'ri-team-line'],
      'sec07' => ['num' => '07', 'name' => 'Why Choose Us', 'icon' => 'ri-building-line'],
      'sec08' => ['num' => '08', 'name' => 'Signature CTA', 'icon' => 'ri-sparkling-fill'],
  ];
  foreach ($tabs as $k => $t):
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
    <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Who We Are — Hero Presentation &amp; Multi-Mode Manager</h2>
    <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Multi-slide carousel slides, background videos, single artwork, and custom copy for the Who We Are page.</p>
  </div>

  <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
    <?= CSRF::field() ?>
    <input type="hidden" name="who_we_are_editor_submit" value="1">
    <input type="hidden" name="tab" value="sec01">

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper">Active Hero Display Mode (Who We Are Page)</label>
      <?php $curWhoMode = Hero::getHeroMode('who_we_are'); ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
        
        <label class="hero-mode-option-card <?= ($curWhoMode === 'slider') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="slider" <?= ($curWhoMode === 'slider') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">✨ Multi-Slide Carousel (Slider)</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Smooth Swiper.js fade slider where each slide has its own full background artwork and distinct text.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curWhoMode === 'single' || $curWhoMode === 'single_image') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="single" <?= ($curWhoMode === 'single' || $curWhoMode === 'single_image') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🖼️ Single Background Image</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Renders 1 static high-impact background image hero without slide transitions.</div>
          </div>
        </label>

        <label class="hero-mode-option-card <?= ($curWhoMode === 'video') ? 'is-active' : '' ?>">
          <input type="radio" name="hero_mode" value="video" <?= ($curWhoMode === 'video') ? 'checked' : '' ?> onchange="updateHeroModeCards(this)" style="accent-color: var(--wdr-teal); margin-top: 4px;">
          <div>
            <div style="font-weight: 800; font-size: 14px; color: var(--wdr-navy);">🎬 Background Video Hero</div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 4px;">Autoplay looping MP4/WebM video hero with dark overlay.</div>
          </div>
        </label>

      </div>

      <!-- Video Hero Configuration Box -->
      <?php $curWhoHeroVideo = setting('who_hero_video_url', ''); ?>
      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 20px; margin-bottom: 20px;">
        <label class="visual-label-upper"><i class="ri-video-line"></i> Background Video Configuration (Upload File or Enter Direct URL)</label>

        <?php if (!empty($curWhoHeroVideo)): ?>
          <div id="preview_who_hero_video" style="margin: 10px 0 14px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
            <div style="width: 80px; height: 50px; background: #0F1E36; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--admin-teal); font-size: 24px; overflow: hidden; flex-shrink: 0;">
              <video src="<?= media_url($curWhoHeroVideo) ?>" style="width: 100%; height: 100%; object-fit: cover;" muted autoplay loop playsinline></video>
            </div>
            <div style="flex: 1; min-width: 0;">
              <div style="font-size: 13px; font-weight: 700; color: var(--admin-navy);">Active Video Source</div>
              <div style="font-size: 11px; color: var(--admin-teal); word-break: break-all;"><?= e($curWhoHeroVideo) ?></div>
            </div>
            <button type="button" onclick="document.getElementById('remove_who_hero_video').value='1'; document.getElementById('preview_who_hero_video').style.display='none'; document.querySelector('input[name=hero_video_url]').value='';" class="btn-adm-action btn-adm-delete" style="font-size: 11px; padding: 6px 12px;">
              <i class="ri-delete-bin-line"></i> Remove Video
            </button>
          </div>
        <?php endif; ?>
        <input type="hidden" name="remove_who_hero_video" id="remove_who_hero_video" value="0">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 8px;">
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-upload-cloud-2-line"></i> Option 1: Upload Video File (MP4, WebM)</label>
            <input type="file" name="who_hero_video_file" class="visual-input-styled" accept="video/mp4,video/webm">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Max 50MB MP4/WebM video.</small>
          </div>
          <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 6px;"><i class="ri-link"></i> Option 2: Or Paste Direct Video URL</label>
            <input type="text" name="hero_video_url" class="visual-input-styled" value="<?= e($curWhoHeroVideo) ?>" placeholder="/uploads/who_hero.mp4 or https://...">
            <small style="display: block; font-size: 11px; color: var(--admin-muted); margin-top: 4px;">External CDN or hosted video URL.</small>
          </div>
        </div>
      </div>

      <div style="margin-top: 16px;">
        <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Display Mode &amp; Video</button>
      </div>
    </div>
  </form>

  <!-- Full Hero Slides List for Who We Are -->
  <div class="admin-card" style="margin-bottom: 0; background: #FFFFFF; border-radius: 16px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
    <div class="card-header" style="padding: 20px 24px; border-bottom: 1px solid var(--admin-border);">
      <div>
        <h2 class="card-title" style="font-size: 18px; font-weight: 700; color: var(--admin-navy); margin: 0;">
          <i class="ri-slideshow-line" style="color: var(--admin-teal);"></i> Manage Who We Are Hero Slides (<?= count($whoHeroSlidesList) ?>)
        </h2>
        <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;">
          Each slide sets its own Background Image/Video, Heading, Sub-heading, Description, and Buttons.
        </div>
      </div>
      <a href="<?= url('admin/hero/edit.php?page=who_we_are&return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm btn-adm-primary">
        <i class="ri-add-line"></i> Add New Slide
      </a>
    </div>

    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width: 80px;">Background</th>
            <th>Sub-Heading &amp; Main Heading</th>
            <th>Buttons</th>
            <th>Sort</th>
            <th>Status</th>
            <th style="text-align: right; width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($whoHeroSlidesList)): ?>
            <?php foreach ($whoHeroSlidesList as $slide): ?>
            <tr>
              <td>
                <?php if (!empty($slide['media_url'])): ?>
                  <img src="<?= media_url($slide['media_url']) ?>" alt="" style="width: 64px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);">
                <?php elseif (!empty($slide['video_url'])): ?>
                  <div style="width: 64px; height: 42px; background: #0F1E36; color: #4A8B8C; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="ri-video-fill"></i>
                  </div>
                <?php else: ?>
                  <div style="width: 64px; height: 42px; background: #F1F5F9; color: #94A3B8; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="ri-image-line"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($slide['eyebrow'])): ?>
                  <div style="font-size: 11px; text-transform: uppercase; color: var(--admin-teal); font-weight: 700; letter-spacing: 0.05em;"><?= e($slide['eyebrow']) ?></div>
                <?php endif; ?>
                <div style="font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-top: 2px;"><?= e($slide['title']) ?></div>
                <?php if (!empty($slide['subtitle'])): ?>
                  <div style="font-size: 12px; color: var(--admin-muted); margin-top: 2px;"><?= e(truncate($slide['subtitle'], 65)) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <div style="font-size: 12px;"><strong>Primary:</strong> <?= e($slide['button_primary_text'] ?: 'None') ?></div>
                <?php if (!empty($slide['button_secondary_text'])): ?>
                  <div style="font-size: 12px; color: var(--admin-muted);"><strong>Secondary:</strong> <?= e($slide['button_secondary_text']) ?></div>
                <?php endif; ?>
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
                  <a href="<?= url('admin/hero/edit.php?id=' . $slide['id'] . '&page=who_we_are&return_to=' . urlencode($currentUrl . '?tab=sec01')) ?>" class="btn-adm-action btn-adm-edit" title="Edit Slide">
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
              <td colspan="6" style="text-align: center; color: var(--admin-muted); padding: 24px;">No hero slides created for Who We Are yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 02: OUR MISSION
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec02'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec02" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec02">

  <div class="visual-studio-card">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      <div>
        <span class="visual-badge"><i class="ri-quill-pen-line"></i> SECTION 02 — MISSION STATEMENT</span>
        
        <div style="margin-top: 14px; margin-bottom: 12px;">
          <label class="visual-label-upper">Section Badge Text</label>
          <input type="text" name="who_sec2_badge" class="visual-input-styled" value="<?= e(setting('who_sec2_badge', 'OUR MISSION')) ?>" style="max-width: 320px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Main Heading (Supports &lt;br&gt;)</label>
          <input type="text" name="who_sec2_title" class="visual-input-styled" value="<?= e(setting('who_sec2_title', "We believe words<br>shape worlds.")) ?>" style="font-family: var(--wdr-font-display); font-size: 20px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Lead Paragraph 1</label>
          <textarea name="who_sec2_p1" class="visual-input-styled" rows="3"><?= e(setting('who_sec2_p1', "WORDORA was founded on a simple truth: the right words, at the right moment, can transform a brand. We don't just create content — we craft narratives that connect, captivate, persuade, and leave a lasting impression.")) ?></textarea>
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Magazine Pull Quote</label>
          <div class="visual-quote-box">
            <input type="text" name="who_sec2_quote" class="visual-input-styled" value="<?= e(setting('who_sec2_quote', "“Good content fills a page. Great content moves someone.”")) ?>" style="font-family: var(--wdr-font-display); font-style: italic; font-size: 16px; border-color: transparent; background: transparent; padding: 0;">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper">Sub-Paragraph 2</label>
          <textarea name="who_sec2_p2" class="visual-input-styled" rows="3"><?= e(setting('who_sec2_p2', "From our base in Agra to brands across India and beyond, we've evolved from a two-person editorial team into a full-service content agency trusted by 170+ brands across SaaS, E-commerce, FinTech, Education, Gaming, and more.")) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
          <div style="background: #FFF; padding: 12px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--wdr-teal); font-size: 11px; margin-bottom: 4px;">BUTTON 1</div>
            <input type="text" name="who_sec2_btn1_text" class="visual-input-styled" placeholder="Button Text" value="<?= e(setting('who_sec2_btn1_text', 'Read Our Journey')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="who_sec2_btn1_url" class="visual-input-styled" placeholder="Button URL" value="<?= e(setting('who_sec2_btn1_url', '#journey')) ?>">
          </div>
          <div style="background: #FFF; padding: 12px; border-radius: 8px; border: 1px solid var(--admin-border);">
            <div style="font-weight: 700; color: var(--wdr-teal); font-size: 11px; margin-bottom: 4px;">BUTTON 2</div>
            <input type="text" name="who_sec2_btn2_text" class="visual-input-styled" placeholder="Button Text" value="<?= e(setting('who_sec2_btn2_text', 'Explore Services')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="who_sec2_btn2_url" class="visual-input-styled" placeholder="Button URL" value="<?= e(setting('who_sec2_btn2_url', 'services.php')) ?>">
          </div>
        </div>
      </div>

      <!-- Right Column: Artwork Uploader -->
      <div>
        <div class="visual-media-frame">
          <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;">Philosophy Artwork Illustration</label>
          <?php $curSec2Art = setting('who_sec2_artwork', '/img/why choose us.png'); ?>
          <img src="<?= media_url($curSec2Art) ?>" alt="Mission Artwork" style="max-height: 240px; width: auto; object-fit: contain; margin: 0 auto 16px; display: block; border-radius: 12px;">
          
          <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload New Artwork (PNG / JPG / WebP)</label>
            <input type="file" name="who_sec2_artwork_file" class="visual-input-styled" accept="image/*">
            <?php if (!empty($curSec2Art) && $curSec2Art !== '/img/why choose us.png'): ?>
              <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #991B1B; margin-top: 8px; cursor: pointer;">
                <input type="checkbox" name="remove_sec2_artwork" value="1"> Reset to Default Illustration
              </label>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 02 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 03: CAPABILITIES MARQUEE
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec03'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec03" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec03">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-apps-line"></i> SECTION 03 — CAPABILITIES MARQUEE</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Editorial Capabilities Parallax Streams</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Headings, background paper texture, and 3 continuous glass pill streams.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper">Section Label</label>
        <input type="text" name="who_sec3_badge" class="visual-input-styled" value="<?= e(setting('who_sec3_badge', 'EDITORIAL CAPABILITIES')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Main Section Title</label>
        <input type="text" name="who_sec3_title" class="visual-input-styled" value="<?= e(setting('who_sec3_title', 'Content engineered for ambitious market leaders.')) ?>">
      </div>
    </div>

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper"><i class="ri-image-line"></i> Background Banner Texture (Paper / Canvas Texture)</label>
      <div style="display: flex; gap: 20px; align-items: center;">
        <?php $curMarqueeBg = setting('who_sec3_marquee_bg', '/img/papaer banner.png'); ?>
        <img src="<?= media_url($curMarqueeBg) ?>" alt="Texture" style="height: 60px; width: 140px; object-fit: cover; border-radius: 8px; border: 1px solid var(--admin-border);">
        <div style="flex: 1;">
          <input type="file" name="who_sec3_marquee_bg_file" class="visual-input-styled" accept="image/*">
        </div>
      </div>
    </div>

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-top: 0; margin-bottom: 16px;">
        <i class="ri-stack-line" style="color: var(--wdr-teal);"></i> 3-Row Parallax Streams (Comma Separated Pills)
      </h3>
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy);">Row 1 Pills (Left-to-Right)</label>
          <input type="text" name="who_marquee_row1" class="visual-input-styled" value="<?= e($whoMarqueeRows['row1'] ?? 'SEO Content Writing, Brand Voice Architecture, Thought Leadership Essays, Social Editorial Calendars, Email Sequences & Newsletters, Technical Whitepapers, Full-Funnel Content Strategy') ?>">
        </div>
        <div>
          <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy);">Row 2 Pills (Right-to-Left)</label>
          <input type="text" name="who_marquee_row2" class="visual-input-styled" value="<?= e($whoMarqueeRows['row2'] ?? 'Conversion Copywriting, Case Study Narratives, Topic Cluster Frameworks, Enterprise B2B Whitepapers, Fact-Checked Research, Executive Ghostwriting, Content Audits & Roadmaps') ?>">
        </div>
        <div>
          <label style="font-size: 12px; font-weight: 700; color: var(--wdr-navy);">Row 3 Pills (Fast Left-to-Right)</label>
          <input type="text" name="who_marquee_row3" class="visual-input-styled" value="<?= e($whoMarqueeRows['row3'] ?? 'Keyword Intent Mapping, Long-Form Authority Guides, High-Converting Pitch Decks, Onboarding Email Sequences, Industry Authority Benchmarks, Viral LinkedIn Carousels, Multi-Format Repurposing') ?>">
        </div>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 03 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 04: OUR JOURNEY & TIMELINE
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec04'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec04" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec04">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-route-line"></i> SECTION 04 — JOURNEY &amp; MILESTONES</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Our Journey Timeline &amp; Editorial History</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Sticky journey illustration &amp; 5 tilted milestone timeline cards.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper">Section Badge Text</label>
        <input type="text" name="who_sec4_badge" class="visual-input-styled" value="<?= e(setting('who_sec4_badge', 'OUR JOURNEY')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Main Heading (Supports &lt;br&gt;)</label>
        <input type="text" name="who_sec4_title" class="visual-input-styled" value="<?= e(setting('who_sec4_title', "Words got us started.<br>Ideas took us further.")) ?>">
      </div>
      <div style="grid-column: 1 / -1;">
        <label class="visual-label-upper">Journey Subtitle / Introduction</label>
        <textarea name="who_sec4_desc" class="visual-input-styled" rows="2"><?= e(setting('who_sec4_desc', "What began as a small writing studio in Agra slowly became a place where brands come to find their voice, sharpen their story, and say something worth remembering.")) ?></textarea>
      </div>
    </div>

    <!-- Sticky Artwork Uploader -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper"><i class="ri-image-line"></i> Sticky Journey Architecture Artwork (journey.png)</label>
      <div style="display: flex; gap: 20px; align-items: center;">
        <?php $curSec4Art = setting('who_sec4_artwork', '/img/journey.png'); ?>
        <img src="<?= media_url($curSec4Art) ?>" alt="Journey Artwork" style="height: 80px; width: auto; object-fit: contain; border-radius: 8px; border: 1px solid var(--admin-border); background: #edf7f7; padding: 6px;">
        <div style="flex: 1;">
          <input type="file" name="who_sec4_artwork_file" class="visual-input-styled" accept="image/*">
        </div>
      </div>
    </div>

    <!-- 5 Milestones Editor -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-top: 0; margin-bottom: 16px;">
        <i class="ri-calendar-line" style="color: var(--wdr-teal);"></i> 5 Editorial Milestones (Interactive Timeline Cards)
      </h3>

      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($whoMilestones as $idx => $m): ?>
        <div style="border: 1px solid #E2E8EE; border-radius: 12px; padding: 18px; background: <?= !empty($m['is_active']) ? 'var(--wdr-navy)' : '#FAF8F5' ?>; color: <?= !empty($m['is_active']) ? '#FFF' : 'inherit' ?>;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <span style="font-family: var(--wdr-font-mono); font-size: 12px; font-weight: 700; color: var(--wdr-teal-light);">MILESTONE #<?= $idx + 1 ?> (<?= e($m['year']) ?>)</span>
            <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; color: <?= !empty($m['is_active']) ? '#FFF' : 'inherit' ?>;">
              <input type="checkbox" name="milestones[<?= $idx ?>][is_active]" value="1" <?= !empty($m['is_active']) ? 'checked' : '' ?>> Active Current Chapter (Dark Card Style)
            </label>
          </div>

          <div style="display: grid; grid-template-columns: 100px 1fr; gap: 12px; margin-bottom: 10px;">
            <input type="text" name="milestones[<?= $idx ?>][year]" class="visual-input-styled <?= !empty($m['is_active']) ? 'visual-input-dark' : '' ?>" placeholder="Year" value="<?= e($m['year'] ?? '') ?>" style="font-weight: 700;">
            <input type="text" name="milestones[<?= $idx ?>][tag]" class="visual-input-styled <?= !empty($m['is_active']) ? 'visual-input-dark' : '' ?>" placeholder="Timeline Tag (e.g. 2018 — THE FIRST SENTENCE)" value="<?= e($m['tag'] ?? '') ?>">
          </div>

          <input type="text" name="milestones[<?= $idx ?>][title]" class="visual-input-styled <?= !empty($m['is_active']) ? 'visual-input-dark' : '' ?>" placeholder="Milestone Title" value="<?= e($m['title'] ?? '') ?>" style="font-family: var(--wdr-font-display); font-weight: 700; margin-bottom: 10px;">

          <textarea name="milestones[<?= $idx ?>][desc]" class="visual-input-styled <?= !empty($m['is_active']) ? 'visual-input-dark' : '' ?>" placeholder="Milestone Description" rows="2"><?= e($m['desc'] ?? '') ?></textarea>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 04 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 05: CORE VALUES
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec05'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec05" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec05">

  <div class="visual-studio-card" style="background: var(--wdr-deep-navy); color: #FFF; border-color: rgba(74, 139, 140, 0.45);">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge" style="background: rgba(74, 139, 140, 0.25); color: var(--wdr-teal-pale); border-color: var(--wdr-teal);"><i class="ri-shield-star-line"></i> SECTION 05 — CORE VALUES</span>
      <h2 class="visual-display-heading" style="color: #FFF; margin: 8px 0 4px;">Core Editorial Principles</h2>
      <p style="color: rgba(255,255,255,0.7); font-size: 13px; margin: 0;">Dark luxury container with tilted values artwork &amp; 3 core value cards.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Section Badge Text</label>
        <input type="text" name="who_sec5_badge" class="visual-input-styled visual-input-dark" value="<?= e(setting('who_sec5_badge', 'OUR CORE VALUES')) ?>">
      </div>
      <div>
        <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Main Heading</label>
        <input type="text" name="who_sec5_title" class="visual-input-styled visual-input-dark" value="<?= e(setting('who_sec5_title', 'What Guides Every Word We Write.')) ?>">
      </div>
      <div style="grid-column: 1 / -1;">
        <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Description Subtitle</label>
        <textarea name="who_sec5_desc" class="visual-input-styled visual-input-dark" rows="2"><?= e(setting('who_sec5_desc', 'Three foundational editorial principles that shape how we think, write, and deliver impact for every partner brand.')) ?></textarea>
      </div>
    </div>

    <!-- Artwork Uploader -->
    <div style="background: rgba(255,255,255,0.05); border: 1.5px dashed rgba(255,255,255,0.25); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper" style="color: var(--wdr-teal-light);"><i class="ri-image-line"></i> Values Tilted Card Artwork (value.png)</label>
      <div style="display: flex; gap: 20px; align-items: center;">
        <?php $curSec5Art = setting('who_sec5_artwork', '/img/value.png'); ?>
        <img src="<?= media_url($curSec5Art) ?>" alt="Values Artwork" style="height: 80px; width: auto; object-fit: contain; border-radius: 8px; background: #FAF8F5; padding: 6px;">
        <div style="flex: 1;">
          <input type="file" name="who_sec5_artwork_file" class="visual-input-styled visual-input-dark" accept="image/*">
        </div>
      </div>
    </div>

    <!-- 3 Core Value Cards -->
    <div style="background: rgba(255,255,255,0.05); border: 1.5px dashed rgba(255,255,255,0.25); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: #FFF; margin-top: 0; margin-bottom: 16px;">
        <i class="ri-file-list-3-line" style="color: var(--wdr-teal-light);"></i> 3 Foundational Principle Cards
      </h3>

      <div style="display: flex; flex-direction: column; gap: 16px;">
        <?php foreach ($whoValCards as $idx => $v): ?>
        <div style="background: rgba(255,255,255,0.06); border: 1px dashed rgba(255,255,255,0.25); border-radius: 12px; padding: 18px;">
          <div style="display: grid; grid-template-columns: 160px 140px 1fr; gap: 12px; margin-bottom: 10px;">
            <input type="text" name="val_cards[<?= $idx ?>][num]" class="visual-input-styled visual-input-dark" placeholder="Num Tag (e.g. 01 / DISCIPLINE)" value="<?= e($v['num'] ?? '') ?>">
            <input type="text" name="val_cards[<?= $idx ?>][icon]" class="visual-input-styled visual-input-dark" placeholder="Icon (e.g. ri-quill-pen-line)" value="<?= e($v['icon'] ?? 'ri-quill-pen-line') ?>">
            <input type="text" name="val_cards[<?= $idx ?>][title]" class="visual-input-styled visual-input-dark" placeholder="Title" value="<?= e($v['title'] ?? '') ?>" style="font-weight: 700;">
          </div>
          <textarea name="val_cards[<?= $idx ?>][desc]" class="visual-input-styled visual-input-dark" placeholder="Description" rows="2"><?= e($v['desc'] ?? '') ?></textarea>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 05 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 06: MEET THE TEAM
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec06'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec06" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec06">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-team-line"></i> SECTION 06 — EDITORIAL TEAM</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Meet The Team (Editorial Leadership)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Editorial team members, roles, specializations, initials avatars, and LinkedIn profiles.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper">Section Badge Text</label>
        <input type="text" name="who_sec6_badge" class="visual-input-styled" value="<?= e(setting('who_sec6_badge', 'MEET THE TEAM')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Main Heading</label>
        <input type="text" name="who_sec6_title" class="visual-input-styled" value="<?= e(setting('who_sec6_title', 'The People Behind the Words')) ?>">
      </div>
      <div style="grid-column: 1 / -1;">
        <label class="visual-label-upper">Section Subtitle</label>
        <textarea name="who_sec6_desc" class="visual-input-styled" rows="2"><?= e(setting('who_sec6_desc', 'Writers. Strategists. Editors. Each one obsessed with research, editorial rhythm, and doing the work right.')) ?></textarea>
      </div>
    </div>

    <!-- 4 Team Members Grid -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-top: 0; margin-bottom: 16px;">
        <i class="ri-user-star-line" style="color: var(--wdr-teal);"></i> 4 Editorial Team Profiles
      </h3>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <?php foreach ($whoTeam as $idx => $t): ?>
        <div style="border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 18px; background: #FAF8F5;">
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="width: 54px; height: 54px; border-radius: 50%; background: <?= e($t['avatar_bg'] ?? '#E8F4F4') ?>; color: <?= e($t['avatar_color'] ?? 'var(--wdr-teal)') ?>; display: flex; align-items: center; justify-content: center; font-weight: 800; font-family: var(--wdr-font-display); font-size: 18px; overflow: hidden; border: 2px solid #FFF; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
              <?php if (!empty($t['avatar_img']) || !empty($t['image'])): ?>
                <img src="<?= media_url($t['avatar_img'] ?? $t['image']) ?>" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <?= e($t['avatar_initials'] ?? 'ED') ?>
              <?php endif; ?>
            </div>
            <div style="flex: 1;">
              <input type="text" name="team[<?= $idx ?>][name]" class="visual-input-styled" placeholder="Name" value="<?= e($t['name'] ?? '') ?>" style="font-weight: 700; margin-bottom: 4px;">
              <input type="text" name="team[<?= $idx ?>][role]" class="visual-input-styled" placeholder="Role" value="<?= e($t['role'] ?? '') ?>" style="font-size: 12px; color: var(--wdr-teal);">
            </div>
          </div>

          <div style="margin-bottom: 10px;">
            <textarea name="team[<?= $idx ?>][spec]" class="visual-input-styled" placeholder="Specialization / Bio" rows="2" style="font-size: 12px;"><?= e($t['spec'] ?? '') ?></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 80px 1fr; gap: 8px; margin-bottom: 10px;">
            <input type="text" name="team[<?= $idx ?>][avatar_initials]" class="visual-input-styled" placeholder="Initials" value="<?= e($t['avatar_initials'] ?? '') ?>" title="Avatar Initials">
            <input type="text" name="team[<?= $idx ?>][linkedin]" class="visual-input-styled" placeholder="LinkedIn URL" value="<?= e($t['linkedin'] ?? 'https://linkedin.com') ?>">
          </div>

          <div style="padding-top: 8px; border-top: 1px dashed rgba(74, 139, 140, 0.25);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
              <label class="visual-label-upper" style="font-size: 10px; margin-bottom: 0;"><i class="ri-image-line"></i> Member Photo (Optional)</label>
              <?php if (!empty($t['avatar_img']) || !empty($t['image'])): ?>
                <button type="button" onclick="document.getElementById('team_img_<?= $idx ?>').value=''; document.getElementById('team_remove_<?= $idx ?>').value='1'; this.closest('.team-photo-row').querySelector('.team-photo-prev-wrap').style.display='none'; this.style.display='none';" class="btn-adm btn-adm-danger" style="padding: 2px 8px; font-size: 10px; height: auto; border-radius: 12px; cursor: pointer;">
                  <i class="ri-delete-bin-line"></i> Remove Image
                </button>
              <?php endif; ?>
            </div>
            <input type="hidden" name="team[<?= $idx ?>][remove_avatar]" id="team_remove_<?= $idx ?>" value="0">
            <div class="team-photo-row" style="display: flex; gap: 8px; align-items: center;">
              <?php if (!empty($t['avatar_img']) || !empty($t['image'])): ?>
                <div class="team-photo-prev-wrap" style="width: 28px; height: 28px; border-radius: 50%; overflow: hidden; flex-shrink: 0; border: 1px solid var(--wdr-teal);">
                  <img src="<?= media_url($t['avatar_img'] ?? $t['image']) ?>" alt="Thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              <?php endif; ?>
              <input type="text" name="team[<?= $idx ?>][avatar_img]" id="team_img_<?= $idx ?>" class="visual-input-styled" value="<?= e($t['avatar_img'] ?? ($t['image'] ?? '')) ?>" placeholder="Path e.g. /uploads/team/member.jpg" style="font-size: 11px; flex: 1;">
              <input type="file" name="team[<?= $idx ?>][avatar_file]" accept="image/*" class="visual-input-styled" style="max-width: 130px; padding: 4px 6px; font-size: 10px;">
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 06 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 07: WHY BRANDS CHOOSE WORDORA
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec07'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec07" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec07">

  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-building-line"></i> SECTION 07 — WHY CHOOSE WORDORA</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Commercial Value Pillars &amp; Proven Metrics</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Editorial philosophy, 6 feature cards, 4 counter metrics, and culture artwork.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
      <div>
        <label class="visual-label-upper">Section Badge Text</label>
        <input type="text" name="who_sec7_badge" class="visual-input-styled" value="<?= e(setting('who_sec7_badge', 'WHY BRANDS CHOOSE WORDORA')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Main Heading (Supports &lt;br&gt;)</label>
        <input type="text" name="who_sec7_title" class="visual-input-styled" value="<?= e(setting('who_sec7_title', "Not just writers.<br>Content thinkers & growth partners.")) ?>">
      </div>
      <div style="grid-column: 1 / -1;">
        <label class="visual-label-upper">Description Paragraph</label>
        <textarea name="who_sec7_desc" class="visual-input-styled" rows="2"><?= e(setting('who_sec7_desc', "We research before we write. We understand before we create. We build every piece around a measurable purpose — establishing industry authority, winning search intent, and converting qualified customers into long-term revenue.")) ?></textarea>
      </div>
      <div>
        <label class="visual-label-upper">Button Text</label>
        <input type="text" name="who_sec7_btn_text" class="visual-input-styled" value="<?= e(setting('who_sec7_btn_text', 'Partner With Us')) ?>">
      </div>
      <div>
        <label class="visual-label-upper">Button URL</label>
        <input type="text" name="who_sec7_btn_url" class="visual-input-styled" value="<?= e(setting('who_sec7_btn_url', 'contact.php')) ?>">
      </div>
    </div>

    <!-- Artwork Uploader -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <label class="visual-label-upper"><i class="ri-image-line"></i> Culture &amp; Team Illustration (culture notes.png)</label>
      <div style="display: flex; gap: 20px; align-items: center;">
        <?php $curSec7Art = setting('who_sec7_artwork', '/img/culture notes.png'); ?>
        <img src="<?= media_url($curSec7Art) ?>" alt="Culture Artwork" style="height: 80px; width: auto; object-fit: contain; border-radius: 8px; border: 1px solid var(--admin-border);">
        <div style="flex: 1;">
          <input type="file" name="who_sec7_artwork_file" class="visual-input-styled" accept="image/*">
        </div>
      </div>
    </div>

    <!-- 6 Pillar Feature Cards -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-top: 0; margin-bottom: 16px;">
        <i class="ri-shield-check-line" style="color: var(--wdr-teal);"></i> 6 Core Feature Pillars
      </h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <?php foreach ($whoPillars as $idx => $p): ?>
        <div style="border: 1px solid #E2E8EE; border-radius: 12px; padding: 16px; background: #FAF8F5;">
          <div style="display: grid; grid-template-columns: 140px 1fr; gap: 8px; margin-bottom: 8px;">
            <input type="text" name="pillars[<?= $idx ?>][icon]" class="visual-input-styled" placeholder="Icon" value="<?= e($p['icon'] ?? '') ?>">
            <input type="text" name="pillars[<?= $idx ?>][title]" class="visual-input-styled" placeholder="Title" value="<?= e($p['title'] ?? '') ?>" style="font-weight: 700;">
          </div>
          <textarea name="pillars[<?= $idx ?>][desc]" class="visual-input-styled" placeholder="Description" rows="2" style="font-size: 12px;"><?= e($p['desc'] ?? '') ?></textarea>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 4 Counter Stats -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-top: 0; margin-bottom: 16px;">
        <i class="ri-bar-chart-2-line" style="color: var(--wdr-teal);"></i> 4 Quantified Achievement Metrics
      </h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
        <?php foreach ($whoStats as $idx => $s): ?>
        <div style="border: 1px solid #E2E8EE; border-radius: 12px; padding: 14px; background: #FAF8F5; text-align: center;">
          <div style="display: flex; gap: 6px; justify-content: center; margin-bottom: 8px;">
            <input type="text" name="stats[<?= $idx ?>][count]" class="visual-input-styled" placeholder="Count" value="<?= e($s['count'] ?? '') ?>" style="font-weight: 800; font-size: 16px; text-align: center;">
            <input type="text" name="stats[<?= $idx ?>][suffix]" class="visual-input-styled" placeholder="Suffix" value="<?= e($s['suffix'] ?? '') ?>" style="max-width: 50px; font-weight: 800; text-align: center;">
          </div>
          <input type="text" name="stats[<?= $idx ?>][label]" class="visual-input-styled" placeholder="Label" value="<?= e($s['label'] ?? '') ?>" style="font-size: 12px; text-align: center;">
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 07 Configuration</button>
  </div>
</form>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     TAB 08: SIGNATURE CTA
     ═══════════════════════════════════════════ -->
<?php if ($activeTab === 'sec08'): ?>
<form method="POST" action="<?= $currentUrl ?>?tab=sec08" enctype="multipart/form-data">
  <?= CSRF::field() ?>
  <input type="hidden" name="who_we_are_editor_submit" value="1">
  <input type="hidden" name="tab" value="sec08">

  <div class="visual-studio-card" style="background: var(--wdr-deep-navy); color: #FFF; border-color: rgba(74, 139, 140, 0.45);">
    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      <div>
        <span class="visual-badge" style="background: rgba(74, 139, 140, 0.25); color: var(--wdr-teal-pale); border-color: var(--wdr-teal);"><i class="ri-sparkling-fill"></i> SECTION 08 — SIGNATURE CTA</span>
        
        <div style="margin-top: 14px; margin-bottom: 12px;">
          <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Badge Pill</label>
          <input type="text" name="who_sec8_badge" class="visual-input-styled visual-input-dark" value="<?= e(setting('who_sec8_badge', "LET'S MAKE SOMETHING MEANINGFUL")) ?>" style="max-width: 340px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Heading (Supports &lt;em&gt;)</label>
          <input type="text" name="who_sec8_title" class="visual-input-styled visual-input-dark" value="<?= e(setting('who_sec8_title', 'Start something <em>worth reading.</em>')) ?>" style="font-family: var(--wdr-font-display); font-size: 22px; font-weight: 700;">
        </div>

        <div style="margin-bottom: 18px;">
          <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Description Paragraph</label>
          <textarea name="who_sec8_desc" class="visual-input-styled visual-input-dark" rows="3"><?= e(setting('who_sec8_desc', "Tell us what you're building. We'll help you find the words to move it forward, engage the right audience, and drive sustainable pipeline growth.")) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
          <div style="background: rgba(255,255,255,0.06); padding: 12px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.2);">
            <div style="font-weight: 700; color: var(--wdr-teal-light); font-size: 11px; margin-bottom: 4px;">BUTTON 1 (PRIMARY)</div>
            <input type="text" name="who_sec8_btn1_text" class="visual-input-styled visual-input-dark" placeholder="Text" value="<?= e(setting('who_sec8_btn1_text', 'Start a Conversation')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="who_sec8_btn1_url" class="visual-input-styled visual-input-dark" placeholder="URL" value="<?= e(setting('who_sec8_btn1_url', 'contact.php')) ?>">
          </div>
          <div style="background: rgba(255,255,255,0.06); padding: 12px; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.2);">
            <div style="font-weight: 700; color: var(--wdr-teal-light); font-size: 11px; margin-bottom: 4px;">BUTTON 2 (GHOST)</div>
            <input type="text" name="who_sec8_btn2_text" class="visual-input-styled visual-input-dark" placeholder="Text" value="<?= e(setting('who_sec8_btn2_text', 'Explore Services')) ?>" style="margin-bottom: 6px;">
            <input type="text" name="who_sec8_btn2_url" class="visual-input-styled visual-input-dark" placeholder="URL" value="<?= e(setting('who_sec8_btn2_url', 'services.php')) ?>">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper" style="color: var(--wdr-teal-light);">Trust Pills (Comma Separated)</label>
          <input type="text" name="who_sec8_trust_pills" class="visual-input-styled visual-input-dark" value="<?= e(setting('who_sec8_trust_pills', '24h Response, NDA Protected, Free Content Audit')) ?>">
        </div>
      </div>

      <!-- Right Column: CTA Artwork -->
      <div>
        <div class="visual-media-frame" style="background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.25);">
          <label class="visual-label-upper" style="color: var(--wdr-teal-light); text-align: center; margin-bottom: 12px;">CTA Artwork Illustration (cta 1.png)</label>
          <?php $curSec8Art = setting('who_sec8_artwork', '/img/cta 1.png'); ?>
          <div id="preview_sec8_wrap">
            <img src="<?= media_url($curSec8Art) ?>" alt="CTA Artwork" style="max-height: 220px; width: auto; object-fit: contain; margin: 0 auto 12px; display: block; border-radius: 12px;">
            <?php if (!empty($curSec8Art)): ?>
              <button type="button" onclick="document.getElementById('remove_sec8_artwork').value='1'; document.getElementById('preview_sec8_wrap').style.display='none'; this.style.display='none';" class="btn-adm btn-adm-danger" style="margin: 0 auto 14px auto; display: block; padding: 4px 12px; font-size: 11px; border-radius: 20px; cursor: pointer;">
                <i class="ri-delete-bin-line"></i> Remove Image
              </button>
            <?php endif; ?>
          </div>
          
          <div style="text-align: left; background: rgba(255,255,255,0.06); padding: 14px; border-radius: 12px; border: 1px dashed rgba(255,255,255,0.25);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-teal-light); display: block; margin-bottom: 4px;">Upload New Artwork</label>
            <input type="hidden" name="remove_sec8_artwork" id="remove_sec8_artwork" value="0">
            <input type="file" name="who_sec8_artwork_file" class="visual-input-styled visual-input-dark" accept="image/*">
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Section 08 Configuration</button>
  </div>
</form>
<?php endif; ?>
