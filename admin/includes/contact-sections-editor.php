<?php
/**
 * WORDORA — Contact Us Visual Section Studio
 * 4-Tab Visual Management matching public/contact.php and standard Section Studio styling
 */

require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/CSRF.php';
require_once ROOT_PATH . '/core/Upload.php';
require_once ROOT_PATH . '/models/Hero.php';
require_once ROOT_PATH . '/models/Contact.php';

Contact::ensureTable();

$db = DB::getInstance();
$activeTab = $_GET['tab'] ?? 'sec01';
$statusFilter = trim($_GET['status'] ?? 'all');
$currentUrl = url('admin/pages/contact.php');

$editorError = '';
$flashInfo = flash_get();
$editorSuccess = is_array($flashInfo) ? ($flashInfo['message'] ?? '') : '';

// ═══════════════════════════════════════════════════════════════════════════
// 1. POST ACTION HANDLER
// ═══════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please reload and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $uploader = new Upload('contact', 52428800);

        // -------------------------------------------------------------
        // TAB 04: UPDATE LEAD STATUS
        // -------------------------------------------------------------
        if ($action === 'update_lead_status') {
            $leadId = (int)($_POST['lead_id'] ?? 0);
            $newStatus = trim($_POST['status'] ?? 'unread');
            if ($leadId > 0 && in_array($newStatus, ['unread', 'read', 'replied'])) {
                Contact::updateStatus($leadId, $newStatus);
                flash_set('success', 'Lead status updated successfully to ' . ucfirst($newStatus) . '.');
            }
            redirect('admin/pages/contact.php?tab=leads&status=' . urlencode($statusFilter));
        }

        // -------------------------------------------------------------
        // TAB 04: DELETE LEAD
        // -------------------------------------------------------------
        elseif ($action === 'delete_lead') {
            $leadId = (int)($_POST['lead_id'] ?? 0);
            if ($leadId > 0) {
                Contact::delete($leadId);
                flash_set('success', 'Contact lead deleted successfully.');
            }
            redirect('admin/pages/contact.php?tab=leads&status=' . urlencode($statusFilter));
        }

        // -------------------------------------------------------------
        // TAB 01: HERO COVER SECTION (SINGLE BANNER — NO BUTTONS)
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec01') {
            $eyebrow  = trim($_POST['hero_eyebrow'] ?? 'START A CONVERSATION');
            $title    = trim($_POST['hero_title'] ?? 'Let’s Build Words That Work.');
            $subtitle = trim($_POST['hero_subtitle'] ?? 'Tell us about your project, timeline, and goals. We’ll get back to you within 24 hours with strategic clarity.');

            // Save Hero Mode to Single
            Setting::set('hero_mode_contact', 'single');

            // Fetch or create slide
            $slide = $db->query("SELECT * FROM hero_slides WHERE page = 'contact' ORDER BY sort_order ASC LIMIT 1")->fetch();
            $mediaUrl = $slide['media_url'] ?? '/img/contact page.png';

            // Handle hero cover image upload
            if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['hero_image_file']);
                if ($upRes['success']) {
                    if (!empty($slide['media_url']) && !str_starts_with($slide['media_url'], '/img/')) {
                        delete_uploaded_file($slide['media_url']);
                    }
                    $mediaUrl = $upRes['path'];
                } else {
                    $editorError = 'Hero image upload failed: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_hero_image']) && $_POST['remove_hero_image'] === '1') {
                if (!empty($slide['media_url']) && !str_starts_with($slide['media_url'], '/img/')) {
                    delete_uploaded_file($slide['media_url']);
                }
                $mediaUrl = '/img/contact page.png';
            }

            if (empty($editorError)) {
                if ($slide) {
                    $stmtSlide = $db->prepare("UPDATE hero_slides SET eyebrow = ?, title = ?, subtitle = ?, media_url = ?, button_primary_text = '', button_primary_url = '', button_secondary_text = '', button_secondary_url = '', banner_type = 'single' WHERE id = ?");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl, $slide['id']]);
                } else {
                    $stmtSlide = $db->prepare("INSERT INTO hero_slides (page, banner_type, eyebrow, title, subtitle, media_url, button_primary_text, button_primary_url, button_secondary_text, button_secondary_url, sort_order, is_active) VALUES ('contact', 'single', ?, ?, ?, ?, '', '', '', '', 1, 1)");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl]);
                }

                flash_set('success', 'Contact Hero Section updated successfully!');
                redirect('admin/pages/contact.php?tab=sec01');
            }
        }

        // -------------------------------------------------------------
        // TAB 02: CONSULTATION FORM & DIRECT CARDS
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec02') {
            $formBadge = trim($_POST['contact_form_badge'] ?? 'PROJECT SCOPE INQUIRY');
            $formTitle = trim($_POST['contact_form_title'] ?? 'Send Us a Project Brief');
            $formDesc  = trim($_POST['contact_form_desc'] ?? 'Fill out the details below and our managing editor will prepare a customized proposal.');
            $formNote  = trim($_POST['contact_form_note'] ?? 'We never share your information. Protected by strict mutual NDA.');

            // Services Dropdown list
            $servicesRaw = $_POST['services'] ?? [];
            $servicesList = [];
            if (is_array($servicesRaw)) {
                foreach ($servicesRaw as $srv) {
                    $sName = trim($srv);
                    if (!empty($sName)) {
                        $servicesList[] = $sName;
                    }
                }
            }
            if (empty($servicesList)) {
                $servicesList = ['SEO Content Writing', 'Technical Writing', 'Brand Copywriting', 'Thought Leadership', 'Social Media Content', 'Email Marketing', 'Full Retainer'];
            }

            // Visual Showcase Card
            $showcaseTitle = trim($_POST['contact_showcase_title'] ?? 'Dedicated Managing Editor Access');
            $showcaseDesc  = trim($_POST['contact_showcase_desc'] ?? 'Every client is paired with an industry-specialist managing editor who oversees research, voice alignment, and delivery milestones.');
            $existingShowcaseImg = setting('contact_showcase_image', '/img/contact from.png');
            $showcaseImg = $existingShowcaseImg;

            if (isset($_FILES['contact_showcase_image_file']) && $_FILES['contact_showcase_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['contact_showcase_image_file']);
                if ($upRes['success']) {
                    if (!empty($existingShowcaseImg) && !str_starts_with($existingShowcaseImg, '/img/')) {
                        delete_uploaded_file($existingShowcaseImg);
                    }
                    $showcaseImg = $upRes['path'];
                } else {
                    $editorError = 'Showcase image upload failed: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_showcase_image']) && $_POST['remove_showcase_image'] === '1') {
                if (!empty($existingShowcaseImg) && !str_starts_with($existingShowcaseImg, '/img/')) {
                    delete_uploaded_file($existingShowcaseImg);
                }
                $showcaseImg = '/img/contact from.png';
            }

            // 4 Direct Contact Cards
            $cardsRaw = $_POST['cards'] ?? [];
            $cardsList = [];
            if (is_array($cardsRaw)) {
                foreach ($cardsRaw as $c) {
                    $cardsList[] = [
                        'icon'      => trim($c['icon'] ?? 'ri-information-line'),
                        'label'     => trim($c['label'] ?? 'Detail'),
                        'value'     => trim($c['value'] ?? ''),
                        'link_type' => trim($c['link_type'] ?? 'none'),
                    ];
                }
            }

            // Enterprise Retainer CTA Box
            $entBadge   = trim($_POST['contact_enterprise_badge'] ?? 'Enterprise SLA');
            $entTitle   = trim($_POST['contact_enterprise_title'] ?? 'Need an Urgent Custom Briefing?');
            $entDesc    = trim($_POST['contact_enterprise_desc'] ?? 'We can execute mutual NDAs within 2 hours and initiate multi-author topic cluster production within 5 business days.');
            $entBtnText = trim($_POST['contact_enterprise_btn_text'] ?? 'Email Enterprise Desk Directly');
            $entBtnUrl  = trim($_POST['contact_enterprise_btn_url'] ?? 'mailto:info@wordora.in?subject=Urgent%20Enterprise%20Content%20Scope');

            if (empty($editorError)) {
                $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
                $stmt->execute(['contact_form_badge', $formBadge]);
                $stmt->execute(['contact_form_title', $formTitle]);
                $stmt->execute(['contact_form_desc', $formDesc]);
                $stmt->execute(['contact_form_services', json_encode($servicesList, JSON_UNESCAPED_UNICODE)]);
                $stmt->execute(['contact_form_note', $formNote]);

                $stmt->execute(['contact_showcase_image', $showcaseImg]);
                $stmt->execute(['contact_showcase_title', $showcaseTitle]);
                $stmt->execute(['contact_showcase_desc', $showcaseDesc]);

                $stmt->execute(['contact_info_cards', json_encode($cardsList, JSON_UNESCAPED_UNICODE)]);

                $stmt->execute(['contact_enterprise_badge', $entBadge]);
                $stmt->execute(['contact_enterprise_title', $entTitle]);
                $stmt->execute(['contact_enterprise_desc', $entDesc]);
                $stmt->execute(['contact_enterprise_btn_text', $entBtnText]);
                $stmt->execute(['contact_enterprise_btn_url', $entBtnUrl]);

                flash_set('success', 'Consultation Form & Direct Contact Hub updated successfully!');
                redirect('admin/pages/contact.php?tab=sec02');
            }
        }

        // -------------------------------------------------------------
        // TAB 03: FAQ ACCORDION SECTION & ARTWORK
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec03') {
            $faqBadge = trim($_POST['contact_faq_badge'] ?? 'FREQUENTLY ASKED QUESTIONS');
            $faqTitle = trim($_POST['contact_faq_title'] ?? 'Everything You Need to Know');
            $faqDesc  = trim($_POST['contact_faq_desc'] ?? 'Clear answers to common questions about our scopes, writer matchmaking, and delivery turnarounds.');

            // FAQ Artwork image
            $existingFaqImg = setting('contact_faq_image', '/img/FAQ 2.png');
            $faqImg = $existingFaqImg;

            if (isset($_FILES['contact_faq_image_file']) && $_FILES['contact_faq_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['contact_faq_image_file']);
                if ($upRes['success']) {
                    if (!empty($existingFaqImg) && !str_starts_with($existingFaqImg, '/img/')) {
                        delete_uploaded_file($existingFaqImg);
                    }
                    $faqImg = $upRes['path'];
                } else {
                    $editorError = 'FAQ image upload failed: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_faq_image']) && $_POST['remove_faq_image'] === '1') {
                if (!empty($existingFaqImg) && !str_starts_with($existingFaqImg, '/img/')) {
                    delete_uploaded_file($existingFaqImg);
                }
                $faqImg = '/img/FAQ 2.png';
            }

            // FAQ List Repeater
            $faqsRaw = $_POST['faqs'] ?? [];
            $faqsList = [];
            if (is_array($faqsRaw)) {
                foreach ($faqsRaw as $f) {
                    $q = trim($f['q'] ?? '');
                    $a = trim($f['a'] ?? '');
                    if (!empty($q)) {
                        $faqsList[] = ['q' => $q, 'a' => $a];
                    }
                }
            }

            if (empty($editorError)) {
                $stmt = $db->prepare("INSERT INTO settings (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
                $stmt->execute(['contact_faq_badge', $faqBadge]);
                $stmt->execute(['contact_faq_title', $faqTitle]);
                $stmt->execute(['contact_faq_desc', $faqDesc]);
                $stmt->execute(['contact_faq_image', $faqImg]);
                $stmt->execute(['contact_faqs', json_encode($faqsList, JSON_UNESCAPED_UNICODE)]);

                flash_set('success', 'FAQ Accordion Section & Artwork updated successfully!');
                redirect('admin/pages/contact.php?tab=sec03');
            }
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// 2. FETCH CURRENT DATA FOR ACTIVE TABS
// ═══════════════════════════════════════════════════════════════════════════

// Hero Section
$heroMode = setting('hero_mode_contact', 'single_image');
$slide = $db->query("SELECT * FROM hero_slides WHERE page = 'contact' ORDER BY sort_order ASC LIMIT 1")->fetch();
$heroEyebrow = $slide['eyebrow'] ?? 'START A CONVERSATION';
$heroTitle   = $slide['title'] ?? 'Let’s Build Words That Work.';
$heroSubtitle= $slide['subtitle'] ?? 'Tell us about your project, timeline, and goals. We’ll get back to you within 24 hours with strategic clarity.';
$heroMediaUrl= $slide['media_url'] ?? '/img/contact page.png';
$heroVideoUrl= setting('hero_video_url_contact', $slide['video_url'] ?? '');
$heroBtn1Text= $slide['button_primary_text'] ?? 'Send an Inquiry';
$heroBtn1Url = $slide['button_primary_url'] ?? '#contact-form';
$heroBtn2Text= $slide['button_secondary_text'] ?? 'Explore Services';
$heroBtn2Url = $slide['button_secondary_url'] ?? 'services.php';

// Form Section
$formBadge = setting('contact_form_badge', 'PROJECT SCOPE INQUIRY');
$formTitle = setting('contact_form_title', 'Send Us a Project Brief');
$formDesc  = setting('contact_form_desc', 'Fill out the details below and our managing editor will prepare a customized proposal.');
$formServices = json_decode(setting('contact_form_services', '[]'), true) ?: [
    'SEO Content Writing',
    'Technical Writing',
    'Brand Copywriting',
    'Thought Leadership',
    'Social Media Content',
    'Email Marketing',
    'Full Retainer'
];
$formNote  = setting('contact_form_note', 'We never share your information. Protected by strict mutual NDA.');

// Showcase & Cards
$showcaseImage = setting('contact_showcase_image', '/img/contact from.png');
$showcaseTitle = setting('contact_showcase_title', 'Dedicated Managing Editor Access');
$showcaseDesc  = setting('contact_showcase_desc', 'Every client is paired with an industry-specialist managing editor who oversees research, voice alignment, and delivery milestones.');

$infoCards = json_decode(setting('contact_info_cards', '[]'), true) ?: [
    ['icon' => 'ri-mail-star-line', 'label' => 'Direct Email', 'value' => setting('contact_email', 'info@wordora.in'), 'link_type' => 'email'],
    ['icon' => 'ri-phone-line', 'label' => 'Dedicated Line', 'value' => setting('contact_phone', '+91-XXXXXXXXXX'), 'link_type' => 'phone'],
    ['icon' => 'ri-map-pin-line', 'label' => 'Studio HQ', 'value' => setting('address', 'Jaipur, Rajasthan, India'), 'link_type' => 'none'],
    ['icon' => 'ri-time-line', 'label' => 'Response SLA', 'value' => 'Under 24 Hours', 'link_type' => 'none'],
];

$enterpriseBadge   = setting('contact_enterprise_badge', 'Enterprise SLA');
$enterpriseTitle   = setting('contact_enterprise_title', 'Need an Urgent Custom Briefing?');
$enterpriseDesc    = setting('contact_enterprise_desc', 'We can execute mutual NDAs within 2 hours and initiate multi-author topic cluster production within 5 business days.');
$enterpriseBtnText = setting('contact_enterprise_btn_text', 'Email Enterprise Desk Directly');
$enterpriseBtnUrl  = setting('contact_enterprise_btn_url', 'mailto:info@wordora.in?subject=Urgent%20Enterprise%20Content%20Scope');

// FAQs
$faqBadge = setting('contact_faq_badge', 'FREQUENTLY ASKED QUESTIONS');
$faqTitle = setting('contact_faq_title', 'Everything You Need to Know');
$faqDesc  = setting('contact_faq_desc', 'Clear answers to common questions about our scopes, writer matchmaking, and delivery turnarounds.');
$faqImage = setting('contact_faq_image', '/img/FAQ 2.png');
$faqs     = json_decode(setting('contact_faqs', '[]'), true) ?: [];

// Leads
$unreadLeadsCount = Contact::countByStatus('unread');
$readLeadsCount   = Contact::countByStatus('read');
$repliedCount     = Contact::countByStatus('replied');
$totalLeadsCount  = Contact::countAll();
$leads = Contact::getAll($statusFilter);

$tabs = [
    'sec01' => ['num' => '01', 'name' => 'Hero Cover', 'icon' => 'ri-image-line'],
    'sec02' => ['num' => '02', 'name' => 'Consultation Form & Hub', 'icon' => 'ri-file-list-2-line'],
    'sec03' => ['num' => '03', 'name' => 'FAQ Accordion & Artwork', 'icon' => 'ri-questionnaire-line'],
    'leads' => ['num' => '04', 'name' => 'Contact Leads', 'icon' => 'ri-mail-star-line', 'badge' => $unreadLeadsCount],
];
?>

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

.visual-studio-wrapper {
  max-width: 1320px;
  margin: 0 auto;
}

.visual-studio-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.visual-title {
  font-family: var(--wdr-font-display);
  font-size: 26px;
  font-weight: 700;
  color: var(--wdr-navy);
}

.visual-subtitle {
  font-size: 13px;
  color: var(--admin-muted);
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

.kpi-stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 12px;
  margin-bottom: 24px;
}

.kpi-stat-card {
  border-radius: 14px;
  padding: 14px 18px;
  text-align: left;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: all 0.2s ease;
}

.kpi-stat-val {
  font-size: 24px;
  font-weight: 800;
  font-family: var(--wdr-font-display);
  line-height: 1;
  margin-bottom: 4px;
}

.kpi-stat-lbl {
  font-size: 10px;
  font-weight: 800;
  font-family: var(--wdr-font-mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.admin-card-table-wrapper {
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(15, 30, 54, 0.04);
  overflow: hidden;
}

.table-top-bar {
  padding: 16px 20px;
  background: #FFFFFF;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
}

.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  text-align: left;
}

.admin-table th {
  background: #F8FAFC !important;
  padding: 14px 18px !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  font-family: var(--wdr-font-mono) !important;
  letter-spacing: 0.05em !important;
  color: #475569 !important;
  border-bottom: 1.5px solid #E2E8F0 !important;
  text-transform: uppercase !important;
  white-space: nowrap !important;
}

.admin-table td {
  padding: 16px 18px !important;
  border-bottom: 1px solid #F1F5F9 !important;
  vertical-align: middle !important;
  background: #FFFFFF;
  font-size: 13px;
  color: #1E293B;
}

.admin-table tr:last-child td {
  border-bottom: none !important;
}

.admin-table tr:hover td {
  background: #F8FAFC !important;
}

.candidate-avatar-circle {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(74, 139, 140, 0.12);
  color: var(--wdr-teal);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 14px;
  font-family: var(--wdr-font-mono);
  flex-shrink: 0;
  border: 1px solid rgba(74, 139, 140, 0.25);
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

<div class="visual-studio-wrapper">

  <!-- ═══════════════════════════════════════════
       STUDIO HEADER
       ═══════════════════════════════════════════ -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
      <h2 style="font-family: var(--wdr-font-display); font-size: 24px; font-weight: 700; color: var(--admin-navy); margin: 0;">
        <i class="ri-contacts-book-2-line" style="color: var(--admin-teal);"></i> Contact Us Visual Section Studio
      </h2>
      <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
        Contact Us page ke sabhi sections (Hero, Brief Form, Direct Access Hub, FAQ Accordion, Leads) ka complete visual editor.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="<?= url('contact.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Contact Page
      </a>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if (!empty($editorSuccess)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; display: flex; align-items: center; gap: 10px;">
      <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #16A34A;"></i>
      <span><?= e($editorSuccess) ?></span>
      <a href="<?= url('contact.php') ?>" target="_blank" style="margin-left: auto; font-size: 12px; text-decoration: underline; color: #166534; font-weight: 700;">View Live Page <i class="ri-external-link-line"></i></a>
    </div>
  <?php endif; ?>

  <?php if (!empty($editorError)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
      <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
    </div>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════
       STUDIO NAVIGATION TABS
       ═══════════════════════════════════════════ -->
  <div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
    <?php foreach ($tabs as $k => $t): 
        $isAct = ($activeTab === $k);
    ?>
    <a href="<?= $currentUrl ?>?tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1.5px solid var(--admin-border);' ?>">
      <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--admin-teal)' : 'var(--admin-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--admin-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
      <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
      <?php if (!empty($t['badge']) && $t['badge'] > 0): ?>
        <span style="background: #EF4444; color: #FFF; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 10px; margin-left: 2px;">
          <?= $t['badge'] ?>
        </span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>


  <!-- ═══════════════════════════════════════════
       TAB 01: HERO COVER SECTION
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec01'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-image-line"></i> SECTION 01 — THE EDITORIAL COVER</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Contact Page Hero &amp; Atmosphere</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure hero presentation mode (Single Cover / Slider / HTML5 Video), headlines, and atmospheric backgrounds.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      
      <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
        <div>
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Eyebrow Badge Tag</label>
            <input type="text" name="hero_eyebrow" class="visual-input-styled" value="<?= e($heroEyebrow) ?>" placeholder="e.g. START A CONVERSATION" style="font-weight: 700;">
          </div>

          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Hero Main Headline <span style="color:#ef4444;">*</span></label>
            <input type="text" name="hero_title" class="visual-input-styled" required value="<?= e($heroTitle) ?>" placeholder="e.g. Let’s Build Words That Work." style="font-weight: 700; font-size: 16px;">
          </div>

          <div style="margin-bottom: 20px;">
            <label class="visual-label-upper">Hero Lead Subtitle</label>
            <textarea name="hero_subtitle" class="visual-input-styled" rows="4" placeholder="Tell us about your project, timeline, and goals..."><?= e($heroSubtitle) ?></textarea>
          </div>
        </div>

        <!-- Right Column: Hero Cover Image Preview -->
        <div>
          <div class="visual-media-frame">
            <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-add-line"></i> Hero Cover Background</label>
            
            <?php 
            $hasCustomHero = !empty($heroMediaUrl) && !str_starts_with($heroMediaUrl, '/img/');
            $resolvedHeroImg = !empty($heroMediaUrl) ? $heroMediaUrl : '/img/contact page.png';
            ?>
            
            <div id="preview_hero_wrap" style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #0F1E36;">
              <img id="preview_hero_img" src="<?= media_url($resolvedHeroImg) ?>" alt="Contact Hero" style="max-height: 200px; width: 100%; object-fit: cover; display: block;">
              <div style="position: absolute; bottom: 8px; left: 8px; z-index: 2;">
                <?php if ($hasCustomHero): ?>
                  <span class="badge badge-teal" style="font-size: 11px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">Custom Cover Uploaded</span>
                <?php else: ?>
                  <span class="badge" style="background: rgba(15,30,54,0.85); color: #FFF; font-size: 11px; border: 1px solid rgba(255,255,255,0.2);">Default Studio Atmosphere</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
              <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload Single Hero Cover Image (PNG / JPG / WEBP)</label>
              <input type="file" name="hero_image_file" class="visual-input-styled" accept="image/*">
              <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
              <?php if ($hasCustomHero): ?>
                <button type="button" onclick="document.getElementById('remove_hero_image').value='1'; document.getElementById('preview_hero_img').src='<?= media_url('/img/contact page.png') ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600; cursor: pointer;">
                  <i class="ri-delete-bin-line"></i> Revert to Default Atmosphere
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Contact Hero</button>
    </form>
  </div>

  <script>
  function toggleHeroMode(mode) {
    const box = document.getElementById('video_url_box');
    if (box) {
      box.style.display = (mode === 'video') ? 'block' : 'none';
    }
  }
  </script>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 02: CONSULTATION FORM & DIRECT HUB
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec02'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-file-text-line"></i> SECTION 02 — CONSULTATION &amp; DIRECT HUB</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Project Brief Form &amp; Direct Access Coordinates</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Customize consultation form title, description, confidentiality guarantee note, and dropdown services list.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec02" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      
      <!-- Part 1: Brief Form Setup -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 12px;"><i class="ri-file-edit-line"></i> PART 01 — PROJECT BRIEF FORM (LEFT COLUMN)</span>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
          <div>
            <label class="visual-label-upper">Form Eyebrow Badge</label>
            <input type="text" name="contact_form_badge" class="visual-input-styled" value="<?= e($formBadge) ?>" placeholder="PROJECT SCOPE INQUIRY" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Form Main Title</label>
            <input type="text" name="contact_form_title" class="visual-input-styled" value="<?= e($formTitle) ?>" placeholder="Send Us a Project Brief" style="font-weight: 700;">
          </div>
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper">Form Subtitle / Intro</label>
          <input type="text" name="contact_form_desc" class="visual-input-styled" value="<?= e($formDesc) ?>" placeholder="Fill out the details below and our managing editor will prepare a customized proposal.">
        </div>

        <div style="margin-bottom: 24px;">
          <label class="visual-label-upper">Security &amp; NDA Guarantee Note (Bottom)</label>
          <input type="text" name="contact_form_note" class="visual-input-styled" value="<?= e($formNote) ?>" placeholder="We never share your information. Protected by strict mutual NDA.">
        </div>

        <!-- Services Dropdown Repeater -->
        <div style="background: #FAF8F5; border-radius: 14px; border: 1.5px dashed rgba(74, 139, 140, 0.35); padding: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
              <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-navy); margin: 0 0 2px;">Primary Service Disciplines (Dropdown Options)</h3>
              <p style="font-size: 12px; color: var(--admin-muted); margin: 0;">These options populate the "Primary Service Discipline" select field in the frontend brief form.</p>
            </div>
            <button type="button" onclick="addServiceOption()" class="btn-adm btn-adm-outline" style="padding: 6px 12px; font-size: 12px;">
              <i class="ri-add-line"></i> Add Service
            </button>
          </div>

          <?php $contactDevServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0'); ?>
          <div style="margin-bottom: 16px; padding: 12px 16px; border-radius: 10px; font-size: 12.5px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; <?= $contactDevServicesEnabled ? 'background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1E40AF;' : 'background: #FEF2F2; border: 1.5px solid #FECACA; color: #991B1B;' ?>">
            <div style="display: flex; align-items: center; gap: 8px;">
              <i class="ri-<?= $contactDevServicesEnabled ? 'checkbox-circle-fill' : 'eye-off-line' ?>" style="font-size: 18px; color: <?= $contactDevServicesEnabled ? '#2563EB' : '#DC2626' ?>;"></i>
              <div>
                <strong>Development &amp; Design Services Sync:</strong>
                <?= $contactDevServicesEnabled 
                  ? 'Master Toggle is <strong style="color: #1D4ED8;">ON</strong>. The 7 Development &amp; Design services are automatically included in the frontend dropdown.' 
                  : 'Master Toggle is <strong style="color: #B91C1C;">OFF</strong>. The 7 Development &amp; Design services are automatically removed from the frontend dropdown.' ?>
              </div>
            </div>
            <a href="<?= url('admin/pages/home.php?tab=sec03c') ?>" style="font-weight: 700; text-decoration: underline; color: inherit; font-size: 11.5px;">
              Manage Toggle in Homepage Sec 3C <i class="ri-external-link-line"></i>
            </a>
          </div>

          <div id="servicesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
            <?php foreach ($formServices as $sIdx => $sName): ?>
              <div class="service-opt-item" id="srv_opt_<?= $sIdx ?>" style="display: flex; align-items: center; gap: 8px; background: #FFF; padding: 8px 12px; border-radius: 10px; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
                <i class="ri-quill-pen-line" style="color: var(--wdr-teal); font-size: 16px;"></i>
                <input type="text" name="services[]" class="visual-input-styled" value="<?= e($sName) ?>" style="padding: 6px 10px; font-size: 13px; font-weight: 600;" placeholder="Service Name">
                <button type="button" onclick="document.getElementById('srv_opt_<?= $sIdx ?>').remove()" style="background: none; border: none; color: #DC2626; cursor: pointer; font-size: 18px; padding: 0 4px;" title="Remove Service">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Part 2: Showcase Card & 4 Direct Access Cards -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 16px;"><i class="ri-shield-star-line"></i> PART 02 — DIRECT ACCESS HUB &amp; CARDS (RIGHT COLUMN)</span>

        <!-- Managing Editor Showcase Card -->
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; background: #FAF8F5; padding: 20px; border-radius: 14px; border: 1.5px dashed rgba(74, 139, 140, 0.35); margin-bottom: 24px; align-items: center;">
          <div>
            <span class="visual-badge" style="margin-bottom: 8px;"><i class="ri-award-line"></i> SHOWCASE CARD</span>
            <div style="margin-bottom: 12px;">
              <label class="visual-label-upper">Showcase Card Title</label>
              <input type="text" name="contact_showcase_title" class="visual-input-styled" value="<?= e($showcaseTitle) ?>" placeholder="Dedicated Managing Editor Access" style="font-weight: 700;">
            </div>
            <div>
              <label class="visual-label-upper">Showcase Card Description</label>
              <textarea name="contact_showcase_desc" class="visual-input-styled" rows="3" placeholder="Every client is paired with an industry-specialist managing editor..."><?= e($showcaseDesc) ?></textarea>
            </div>
          </div>

          <div>
            <label class="visual-label-upper" style="text-align: center;"><i class="ri-image-line"></i> Showcase Artwork</label>
            <div style="text-align: center; background: #FFF; padding: 14px; border-radius: 12px; border: 1.5px dashed rgba(74, 139, 140, 0.35); margin-bottom: 10px;">
              <img id="preview_showcase_img" src="<?= media_url($showcaseImage) ?>" alt="Showcase" style="max-height: 120px; width: auto; object-fit: contain; margin: 0 auto;">
            </div>
            <input type="file" name="contact_showcase_image_file" class="visual-input-styled" accept="image/*">
            <input type="hidden" name="remove_showcase_image" id="remove_showcase_image" value="0">
          </div>
        </div>

        <!-- 4 Direct Contact Metric Cards (2x2 Grid) -->
        <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-navy); margin: 0 0 12px;">4 Direct Contact Information Cards</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
          <?php foreach ($infoCards as $cIdx => $card): ?>
            <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 14px; padding: 16px;">
              <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span class="visual-badge" style="font-size: 10px;">CARD <?= sprintf('%02d', $cIdx + 1) ?></span>
                <div style="font-size: 11px; color: var(--admin-muted); font-family: var(--wdr-font-mono);">
                  <i class="<?= e($card['icon']) ?>"></i> Icon
                </div>
              </div>
              <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; margin-bottom: 10px;">
                <div>
                  <label class="visual-label-upper">Remix Icon Class</label>
                  <input type="text" name="cards[<?= $cIdx ?>][icon]" class="visual-input-styled" value="<?= e($card['icon']) ?>" placeholder="ri-mail-star-line" style="font-size: 12px; font-family: var(--wdr-font-mono);">
                </div>
                <div>
                  <label class="visual-label-upper">Card Label</label>
                  <input type="text" name="cards[<?= $cIdx ?>][label]" class="visual-input-styled" value="<?= e($card['label']) ?>" placeholder="Direct Email" style="font-weight: 700;">
                </div>
              </div>
              <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 10px;">
                <div>
                  <label class="visual-label-upper">Value / Detail Content</label>
                  <input type="text" name="cards[<?= $cIdx ?>][value]" class="visual-input-styled" value="<?= e($card['value']) ?>" placeholder="info@wordora.in">
                </div>
                <div>
                  <label class="visual-label-upper">Link Behavior</label>
                  <select name="cards[<?= $cIdx ?>][link_type]" class="visual-input-styled" style="font-size: 12px;">
                    <option value="none" <?= ($card['link_type'] ?? 'none') === 'none' ? 'selected' : '' ?>>Plain Text</option>
                    <option value="email" <?= ($card['link_type'] ?? '') === 'email' ? 'selected' : '' ?>>Mailto: Link</option>
                    <option value="phone" <?= ($card['link_type'] ?? '') === 'phone' ? 'selected' : '' ?>>Tel: Link</option>
                  </select>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Enterprise Retainer CTA Box -->
        <div style="background: #1B2A4A; color: #FFF; padding: 24px; border-radius: 16px; border: 1.5px dashed rgba(74,139,140,0.5); margin-bottom: 20px;">
          <span class="visual-badge" style="background: rgba(74,139,140,0.25); color: var(--color-teal-light); margin-bottom: 12px;">
            <i class="ri-shield-star-line"></i> ENTERPRISE RETAINER BOX
          </span>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
            <div>
              <label class="visual-label-upper" style="color: #94A3B8;">Badge Text</label>
              <input type="text" name="contact_enterprise_badge" class="visual-input-styled" value="<?= e($enterpriseBadge) ?>" placeholder="Enterprise SLA" style="background: #0F1E36; color: #FFF; border-color: #334155;">
            </div>
            <div>
              <label class="visual-label-upper" style="color: #94A3B8;">Box Main Title</label>
              <input type="text" name="contact_enterprise_title" class="visual-input-styled" value="<?= e($enterpriseTitle) ?>" placeholder="Need an Urgent Custom Briefing?" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
            </div>
          </div>

          <div style="margin-bottom: 14px;">
            <label class="visual-label-upper" style="color: #94A3B8;">Box Description</label>
            <textarea name="contact_enterprise_desc" class="visual-input-styled" rows="2" placeholder="We can execute mutual NDAs within 2 hours..." style="background: #0F1E36; color: #FFF; border-color: #334155;"><?= e($enterpriseDesc) ?></textarea>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
              <label class="visual-label-upper" style="color: #94A3B8;">Button Text</label>
              <input type="text" name="contact_enterprise_btn_text" class="visual-input-styled" value="<?= e($enterpriseBtnText) ?>" placeholder="Email Enterprise Desk Directly" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
            </div>
            <div>
              <label class="visual-label-upper" style="color: #94A3B8;">Button Mailto / Action URL</label>
              <input type="text" name="contact_enterprise_btn_url" class="visual-input-styled" value="<?= e($enterpriseBtnUrl) ?>" placeholder="mailto:info@wordora.in?subject=..." style="background: #0F1E36; color: #FFF; border-color: #334155;">
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Consultation Hub &amp; Cards</button>
    </form>
  </div>

  <script>
  function addServiceOption() {
    const container = document.getElementById('servicesContainer');
    const idx = new Date().getTime();
    const div = document.createElement('div');
    div.className = 'service-opt-item';
    div.id = 'srv_opt_' + idx;
    div.style = 'display: flex; align-items: center; gap: 8px; background: #FFF; padding: 8px 12px; border-radius: 10px; border: 1.5px dashed rgba(74, 139, 140, 0.35);';
    div.innerHTML = `
      <i class="ri-quill-pen-line" style="color: var(--wdr-teal); font-size: 16px;"></i>
      <input type="text" name="services[]" class="visual-input-styled" value="" style="padding: 6px 10px; font-size: 13px; font-weight: 600;" placeholder="New Service Discipline">
      <button type="button" onclick="document.getElementById('srv_opt_${idx}').remove()" style="background: none; border: none; color: #DC2626; cursor: pointer; font-size: 18px; padding: 0 4px;">
        <i class="ri-delete-bin-line"></i>
      </button>
    `;
    container.appendChild(div);
  }
  </script>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 03: FAQ ACCORDION SECTION & ARTWORK
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec03'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-questionnaire-line"></i> SECTION 03 — FAQ ACCORDION &amp; ARTWORK</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Interactive FAQ Accordions &amp; Frame</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Add, modify, or remove frequently asked questions, answers, and upload the right-side artwork frame.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec03" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      
      <!-- Header & Artwork Split -->
      <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 28px; margin-bottom: 28px; align-items: start;">
        <div>
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">FAQ Section Eyebrow Badge</label>
            <input type="text" name="contact_faq_badge" class="visual-input-styled" value="<?= e($faqBadge) ?>" placeholder="FREQUENTLY ASKED QUESTIONS" style="font-weight: 700;">
          </div>

          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">FAQ Section Main Heading</label>
            <input type="text" name="contact_faq_title" class="visual-input-styled" value="<?= e($faqTitle) ?>" placeholder="Everything You Need to Know" style="font-weight: 700; font-size: 16px;">
          </div>

          <div>
            <label class="visual-label-upper">FAQ Section Subtitle Note</label>
            <textarea name="contact_faq_desc" class="visual-input-styled" rows="3" placeholder="Clear answers to common questions about our scopes..."><?= e($faqDesc) ?></textarea>
          </div>
        </div>

        <!-- Right Side FAQ Artwork Frame -->
        <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 20px; text-align: center;">
          <label class="visual-label-upper" style="margin-bottom: 10px;"><i class="ri-image-line"></i> Right Side Artwork Frame</label>
          
          <?php 
          $hasCustomFaq = !empty($faqImage) && !str_starts_with($faqImage, '/img/');
          ?>
          <div style="background: #FAF8F5; padding: 12px; border-radius: 12px; border: 1.5px dashed rgba(74, 139, 140, 0.35); margin-bottom: 10px;">
            <img id="preview_faq_img" src="<?= media_url($faqImage) ?>" alt="FAQ Artwork" style="max-height: 140px; width: auto; object-fit: contain; margin: 0 auto;">
          </div>

          <input type="file" name="contact_faq_image_file" class="visual-input-styled" accept="image/*">
          <input type="hidden" name="remove_faq_image" id="remove_faq_image" value="0">
          <?php if ($hasCustomFaq): ?>
            <button type="button" onclick="document.getElementById('remove_faq_image').value='1'; document.getElementById('preview_faq_img').src='<?= media_url('/img/FAQ 2.png') ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 8px; width: 100%; justify-content: center; padding: 6px 10px; font-size: 11.5px;">
              <i class="ri-delete-bin-line"></i> Revert to Default Artwork
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- FAQ Repeater Container -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
          <div>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--wdr-navy); margin: 0 0 2px;">Accordion Questions &amp; Detailed Answers</h3>
            <p style="font-size: 12.5px; color: var(--admin-muted); margin: 0;">Add and edit the interactive accordion items displayed on the Contact Us page.</p>
          </div>
          <button type="button" onclick="addFaqItem()" class="btn-adm btn-adm-outline" style="font-weight: 700;">
            <i class="ri-add-line"></i> Add New Question
          </button>
        </div>

        <div id="faqsContainer" style="display: flex; flex-direction: column; gap: 16px;">
          <?php foreach ($faqs as $fIdx => $faq): ?>
            <div class="faq-editor-card" id="faq_item_<?= $fIdx ?>" style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 14px; padding: 20px; position: relative;">
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                  <span style="display: inline-block; width: 26px; height: 26px; border-radius: 8px; background: var(--wdr-teal-pale); color: var(--wdr-teal); font-family: var(--wdr-font-mono); font-size: 11.5px; font-weight: 800; line-height: 26px; text-align: center;">
                    <?= sprintf('%02d', $fIdx + 1) ?>
                  </span>
                  <span style="font-weight: 700; font-size: 14px; color: var(--wdr-navy);">Question &amp; Answer</span>
                </div>
                <button type="button" onclick="document.getElementById('faq_item_<?= $fIdx ?>').remove()" class="btn-adm-action btn-adm-delete" title="Delete Question" style="padding: 4px 8px; font-size: 11px;">
                  <i class="ri-delete-bin-line"></i> Delete
                </button>
              </div>

              <div style="margin-bottom: 12px;">
                <label class="visual-label-upper">Question Text <span style="color:#ef4444;">*</span></label>
                <input type="text" name="faqs[<?= $fIdx ?>][q]" class="visual-input-styled" required value="<?= e($faq['q']) ?>" placeholder="e.g. How fast can our project kick off after signing?" style="font-weight: 600; background: #FFF;">
              </div>

              <div>
                <label class="visual-label-upper">Detailed Answer Explanation <span style="color:#ef4444;">*</span></label>
                <textarea name="faqs[<?= $fIdx ?>][a]" class="visual-input-styled" rows="3" required placeholder="Describe turnaround time, process, or deliverable details..." style="background: #FFF;"><?= e($faq['a']) ?></textarea>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save FAQ Accordion Section</button>
    </form>
  </div>

  <script>
  function addFaqItem() {
    const container = document.getElementById('faqsContainer');
    const idx = new Date().getTime();
    const div = document.createElement('div');
    div.className = 'faq-editor-card';
    div.id = 'faq_item_' + idx;
    div.style = 'background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 14px; padding: 20px; position: relative;';
    div.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <span style="display: inline-block; width: 26px; height: 26px; border-radius: 8px; background: var(--wdr-teal-pale); color: var(--wdr-teal); font-family: var(--wdr-font-mono); font-size: 11.5px; font-weight: 800; line-height: 26px; text-align: center;">
            NEW
          </span>
          <span style="font-weight: 700; font-size: 14px; color: var(--wdr-navy);">New FAQ Item</span>
        </div>
        <button type="button" onclick="document.getElementById('faq_item_${idx}').remove()" class="btn-adm-action btn-adm-delete" title="Delete Question" style="padding: 4px 8px; font-size: 11px;">
          <i class="ri-delete-bin-line"></i> Delete
        </button>
      </div>

      <div style="margin-bottom: 12px;">
        <label class="visual-label-upper">Question Text <span style="color:#ef4444;">*</span></label>
        <input type="text" name="faqs[${idx}][q]" class="visual-input-styled" required value="" placeholder="e.g. Do you offer revisions and editorial style adjustments?" style="font-weight: 600; background: #FFF;">
      </div>

      <div>
        <label class="visual-label-upper">Detailed Answer Explanation <span style="color:#ef4444;">*</span></label>
        <textarea name="faqs[${idx}][a]" class="visual-input-styled" rows="3" required placeholder="Describe your answer in detail..." style="background: #FFF;"></textarea>
      </div>
    `;
    container.appendChild(div);
  }
  </script>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 04: CONTACT INQUIRIES & LEADS DIRECTORY
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'leads'): ?>
  <div class="visual-studio-card">
    
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px;">
      <div>
        <span class="visual-badge"><i class="ri-mail-star-line"></i> SECTION 04 — INQUIRIES DIRECTORY</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Client Project Inquiries &amp; Leads</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Review incoming scope briefs, contact coordinates, client requirements, and update response workflow status.</p>
      </div>

      <a href="<?= url('admin/leads/index.php') ?>" class="btn-adm btn-adm-outline" style="font-size: 12px;">
        <i class="ri-external-link-line"></i> Open Dedicated Leads Manager
      </a>
    </div>

    <!-- KPI Summary Stats Grid -->
    <div class="kpi-stats-grid">
      <div class="kpi-stat-card" style="background: #FAF8F5; border: 1.5px dashed rgba(74,139,140,0.35);">
        <div class="kpi-stat-val" style="color: var(--wdr-navy);"><?= $totalLeadsCount ?></div>
        <div class="kpi-stat-lbl" style="color: #64748B;"><i class="ri-mail-line"></i> Total Inquiries</div>
      </div>
      <div class="kpi-stat-card" style="background: #FEF3C7; border: 1.5px solid #FCD34D;">
        <div class="kpi-stat-val" style="color: #92400E;"><?= $unreadLeadsCount ?></div>
        <div class="kpi-stat-lbl" style="color: #B45309;"><i class="ri-time-line"></i> Unread Leads</div>
      </div>
      <div class="kpi-stat-card" style="background: #EFF6FF; border: 1.5px solid #BFDBFE;">
        <div class="kpi-stat-val" style="color: #1E40AF;"><?= $readLeadsCount ?></div>
        <div class="kpi-stat-lbl" style="color: #2563EB;"><i class="ri-eye-line"></i> Read / Reviewed</div>
      </div>
      <div class="kpi-stat-card" style="background: #ECFDF5; border: 1.5px solid #A7F3D0;">
        <div class="kpi-stat-val" style="color: #065F46;"><?= $repliedCount ?></div>
        <div class="kpi-stat-lbl" style="color: #059669;"><i class="ri-reply-line"></i> Contacted / Replied</div>
      </div>
    </div>

    <!-- Status Filters Bar -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
      <a href="?tab=leads&status=all" class="btn-adm-action" style="<?= $statusFilter === 'all' ? 'background: var(--wdr-navy); color: #FFF; border-color: var(--wdr-navy);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
        <span>All Inquiries</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $totalLeadsCount ?>)</strong>
      </a>
      <a href="?tab=leads&status=unread" class="btn-adm-action" style="<?= $statusFilter === 'unread' ? 'background: #D97706; color: #FFF; border-color: #D97706;' : 'background: #FFF; border: 1.5px dashed #FCD34D; color: #92400E;' ?>">
        <span>Unread</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $unreadLeadsCount ?>)</strong>
      </a>
      <a href="?tab=leads&status=read" class="btn-adm-action" style="<?= $statusFilter === 'read' ? 'background: #2563EB; color: #FFF; border-color: #2563EB;' : 'background: #FFF; border: 1.5px dashed #BFDBFE; color: #1E40AF;' ?>">
        <span>Read</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $readLeadsCount ?>)</strong>
      </a>
      <a href="?tab=leads&status=replied" class="btn-adm-action" style="<?= $statusFilter === 'replied' ? 'background: #059669; color: #FFF; border-color: #059669;' : 'background: #FFF; border: 1.5px dashed #A7F3D0; color: #065F46;' ?>">
        <span>Replied</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $repliedCount ?>)</strong>
      </a>
    </div>

    <!-- Leads Directory Table -->
    <?php if (empty($leads)): ?>
      <div style="text-align: center; padding: 56px 24px; background: #FFF; border-radius: 18px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
        <i class="ri-inbox-line" style="font-size: 48px; color: var(--wdr-teal); opacity: 0.6;"></i>
        <h3 style="margin: 14px 0 6px; font-family: var(--wdr-font-display); font-size: 20px; color: var(--wdr-navy);">No Inquiries Found</h3>
        <p style="color: var(--admin-muted); font-size: 13.5px; margin: 0; max-width: 480px; margin-left: auto; margin-right: auto;">
          <?= $statusFilter !== 'all' ? 'No incoming client inquiries currently match this status filter.' : 'When potential clients submit project scopes through the Contact Us form, they will instantly appear here.' ?>
        </p>
      </div>
    <?php else: ?>
      <div class="admin-card-table-wrapper">
        <div class="table-top-bar">
          <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
            <i class="ri-mail-check-line" style="color: var(--wdr-teal); font-size: 16px;"></i> Inquiries Directory
          </div>
          <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
            Showing <?= count($leads) ?> Records
          </span>
        </div>

        <div class="table-responsive">
          <table class="admin-table">
            <thead>
              <tr>
                <th style="width: 140px;">Date &amp; Time</th>
                <th style="min-width: 220px;">Client Profile</th>
                <th style="min-width: 220px;">Service Inquired</th>
                <th style="min-width: 240px;">Contact Coordinates</th>
                <th style="min-width: 240px;">Project Scope Message</th>
                <th style="width: 150px;">Status</th>
                <th style="text-align: right; width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($leads as $lead): 
                  $lStatus = $lead['status'] ?: 'unread';
              ?>
                <tr>
                  <!-- Date -->
                  <td>
                    <div style="font-weight: 700; color: #1E293B; font-size: 12.5px; font-family: var(--wdr-font-mono);">
                      <?= date('M d, Y', strtotime($lead['submitted_at'])) ?>
                    </div>
                    <div style="font-size: 11px; color: #64748B; font-family: var(--wdr-font-mono); margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                      <i class="ri-time-line" style="color: var(--wdr-teal);"></i> <?= date('h:i A', strtotime($lead['submitted_at'])) ?>
                    </div>
                  </td>

                  <!-- Client Profile with Avatar -->
                  <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                      <div class="candidate-avatar-circle">
                        <?= strtoupper(substr($lead['name'] ?: 'C', 0, 1)) ?>
                      </div>
                      <div>
                        <div style="font-weight: 700; color: var(--wdr-navy); font-size: 14px; line-height: 1.3;">
                          <?= e($lead['name']) ?>
                        </div>
                        <?php if (!empty($lead['company'])): ?>
                          <div style="font-size: 11.5px; color: #64748B; margin-top: 2px;">
                            <i class="ri-building-line" style="color: var(--wdr-teal);"></i> <?= e($lead['company']) ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>

                  <!-- Service Inquired -->
                  <td>
                    <span style="display: inline-block; font-size: 12px; font-weight: 700; color: var(--wdr-navy); background: var(--wdr-teal-pale); padding: 4px 10px; border-radius: 8px; border: 1px dashed rgba(74, 139, 140, 0.4);">
                      <?= e($lead['service'] ?: 'General Inquiry') ?>
                    </span>
                    <?php if (!empty($lead['budget'])): ?>
                      <div style="font-size: 11px; color: #64748B; font-family: var(--wdr-font-mono); margin-top: 4px;">
                        Budget: <strong><?= e($lead['budget']) ?></strong>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Contact Coordinates -->
                  <td>
                    <div style="font-size: 12.5px; margin-bottom: 3px;">
                      <a href="mailto:<?= e($lead['email']) ?>" style="color: var(--wdr-navy); text-decoration: none; font-weight: 600;">
                        <i class="ri-mail-line" style="color: var(--wdr-teal); margin-right: 4px;"></i><?= e($lead['email']) ?>
                      </a>
                    </div>
                    <?php if (!empty($lead['phone'])): ?>
                      <div style="font-size: 12px; color: #64748B;">
                        <a href="tel:<?= e($lead['phone']) ?>" style="color: inherit; text-decoration: none;">
                          <i class="ri-phone-line" style="color: var(--wdr-teal); margin-right: 4px;"></i><?= e($lead['phone']) ?>
                        </a>
                      </div>
                    <?php endif; ?>
                  </td>

                  <!-- Project Scope Message -->
                  <td>
                    <div style="font-size: 12.5px; color: #475569; line-height: 1.45; max-width: 260px; word-break: break-word;">
                      <?= e(truncate($lead['message'], 90)) ?>
                    </div>
                  </td>

                  <!-- Status Selector Dropdown -->
                  <td>
                    <form method="POST" action="<?= $currentUrl ?>?tab=leads&status=<?= urlencode($statusFilter) ?>" style="display: inline-block;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="update_lead_status">
                      <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                      
                      <select name="status" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 8px; font-size: 11.5px; font-weight: 700; cursor: pointer; outline: none; transition: all 0.2s ease; <?php 
                        if ($lStatus === 'replied') echo 'background: #ECFDF5; color: #065F46; border: 1.5px solid #A7F3D0;';
                        elseif ($lStatus === 'read') echo 'background: #EFF6FF; color: #1E40AF; border: 1.5px solid #BFDBFE;';
                        else echo 'background: #FEF3C7; color: #92400E; border: 1.5px solid #FCD34D;';
                      ?>">
                        <option value="unread" <?= $lStatus === 'unread' ? 'selected' : '' ?>>⏳ Unread</option>
                        <option value="read" <?= $lStatus === 'read' ? 'selected' : '' ?>>👀 Read</option>
                        <option value="replied" <?= $lStatus === 'replied' ? 'selected' : '' ?>>💬 Replied</option>
                      </select>
                    </form>
                  </td>

                  <!-- Actions Column -->
                  <td style="text-align: right;">
                    <div class="table-actions" style="justify-content: flex-end;">
                      <!-- View Modal Button -->
                      <button type="button" onclick='showLeadDetails(<?= json_encode($lead, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-adm-action btn-adm-edit" title="View Full Project Scope Brief">
                        <i class="ri-eye-line"></i> View
                      </button>

                      <!-- Delete Form -->
                      <form method="POST" action="<?= $currentUrl ?>?tab=leads&status=<?= urlencode($statusFilter) ?>" onsubmit="return confirm('Are you sure you want to permanently delete this lead?');" style="display: inline;">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="action" value="delete_lead">
                        <input type="hidden" name="lead_id" value="<?= (int)$lead['id'] ?>">
                        <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Lead">
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <!-- Lead Details Modal -->
  <div id="leadModal" style="display: none; position: fixed; inset: 0; background: rgba(15,30,54,0.75); z-index: 10000; align-items: center; justify-content: center; padding: 24px;">
    <div style="background: #FFF; border-radius: 20px; max-width: 650px; width: 100%; max-height: 90vh; overflow-y: auto; padding: 28px; position: relative; border: 1.5px dashed rgba(74, 139, 140, 0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
      
      <button type="button" onclick="document.getElementById('leadModal').style.display='none'" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 24px; color: var(--admin-muted); cursor: pointer;">
        <i class="ri-close-line"></i>
      </button>

      <span class="visual-badge" style="margin-bottom: 6px;"><i class="ri-mail-star-line"></i> CLIENT BRIEF DETAILS</span>
      <h2 id="modal_lead_name" style="font-family: var(--wdr-font-display); font-size: 22px; color: var(--wdr-navy); margin: 0 0 4px;"></h2>
      <p id="modal_lead_service" style="color: var(--wdr-teal); font-weight: 700; font-size: 14px; margin: 0 0 20px;"></p>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; background: #FAF8F5; padding: 16px; border-radius: 12px; border: 1.5px dashed rgba(74,139,140,0.35); margin-bottom: 20px;">
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Client Email</div>
          <div id="modal_lead_email" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Phone / WhatsApp</div>
          <div id="modal_lead_phone" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Company Name</div>
          <div id="modal_lead_company" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
        <div>
          <div style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase;">Submission Date</div>
          <div id="modal_lead_date" style="font-size: 13px; font-weight: 600; color: var(--wdr-navy);"></div>
        </div>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="visual-label-upper">Project Goals, Audience &amp; Scope Requirements</label>
        <div id="modal_lead_message" style="background: #FAF8F5; padding: 14px; border-radius: 10px; font-size: 13.5px; line-height: 1.65; white-space: pre-wrap; color: var(--wdr-navy); border: 1.5px dashed rgba(74, 139, 140, 0.35);"></div>
      </div>

      <div id="modal_lead_reply_action" style="text-align: center;"></div>

    </div>
  </div>

  <script>
  function showLeadDetails(lead) {
    document.getElementById('modal_lead_name').innerText = lead.name || 'Client';
    document.getElementById('modal_lead_service').innerText = 'Inquired for: ' + (lead.service || 'General Project Scope');
    document.getElementById('modal_lead_email').innerText = lead.email || '—';
    document.getElementById('modal_lead_phone').innerText = lead.phone || '—';
    document.getElementById('modal_lead_company').innerText = lead.company || '—';
    document.getElementById('modal_lead_date').innerText = lead.submitted_at ? lead.submitted_at : '—';
    document.getElementById('modal_lead_message').innerText = lead.message || 'No project message provided.';

    document.getElementById('modal_lead_reply_action').innerHTML = `
      <a href="mailto:${lead.email}?subject=Re:%20Your%20Project%20Inquiry%20to%20WORDORA" class="btn-adm btn-adm-primary" style="width: 100%; justify-content: center; padding: 12px 18px; font-weight: 700;">
        <i class="ri-mail-send-line"></i> Email Client Proposal / Reply Directly
      </a>
    `;

    document.getElementById('leadModal').style.display = 'flex';
  }
  </script>
  <?php endif; ?>

</div>
