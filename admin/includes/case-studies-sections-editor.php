<?php
/**
 * WORDORA — Case Studies Visual Section Studio
 * 5-Tab Management matching public/case-studies.php & public/case-study-detail.php
 */

require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/CSRF.php';
require_once ROOT_PATH . '/core/Upload.php';
require_once ROOT_PATH . '/models/Hero.php';
require_once ROOT_PATH . '/models/CaseStudy.php';

CaseStudy::ensureTable();

$db = DB::getInstance();
$activeTab = $_GET['tab'] ?? 'sec01';
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$industryFilter = trim($_GET['industry'] ?? 'all');
$currentUrl = strtok($_SERVER['REQUEST_URI'] ?? url('admin/pages/case-studies.php'), '?');

$editorError = '';
$flashInfo = flash_get();
$editorSuccess = is_array($flashInfo) ? ($flashInfo['message'] ?? '') : '';

// ═══════════════════════════════════════════════════════════════════════════
// 1. POST ACTION HANDLER
//    NOTE: case-studies.php wraps everything in ob_start() so redirect()
//    can always send clean HTTP 302 headers even after header.php output.
// ═══════════════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $editorError = 'Security token expired. Please reload and try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $uploader = new Upload('case-studies', 52428800);

        // -------------------------------------------------------------
        // TOGGLE MODULE VISIBILITY (ON / OFF)
        // -------------------------------------------------------------
        if ($action === 'toggle_case_studies_module') {
            $newVal = (trim($_POST['enable_case_studies'] ?? '1') === '1') ? '1' : '0';
            Setting::set('enable_case_studies', $newVal);
            setting('__CLEAR_CACHE__');
            if ($newVal === '1') {
                flash_set('success', 'Case Studies module is now LIVE and visible across the website!');
            } else {
                flash_set('success', 'Case Studies module has been TEMPORARILY HIDDEN from public navigation and pages.');
            }
            redirect($currentUrl . '?tab=' . urlencode($activeTab));
        }

        // -------------------------------------------------------------
        // CATEGORY / INDUSTRY ACTIONS
        // -------------------------------------------------------------
        if ($action === 'create_industry') {
            $indName = trim($_POST['industry_name'] ?? '');
            $indSlug = trim($_POST['industry_slug'] ?? '') ?: slugify($indName);
            if (!empty($indName)) {
                CaseStudy::saveIndustry($indName, $indSlug);
                flash_set('success', 'Industry sector category added successfully!');
            }
            redirect('admin/pages/case-studies.php?tab=categories');
        } elseif ($action === 'delete_industry') {
            $delSlug = trim($_POST['industry_slug'] ?? '');
            if (!empty($delSlug)) {
                CaseStudy::deleteIndustry($delSlug);
                flash_set('success', 'Industry sector category deleted successfully.');
            }
            redirect('admin/pages/case-studies.php?tab=categories');
        }

        // -------------------------------------------------------------
        // DELETE CASE STUDY
        // -------------------------------------------------------------
        elseif ($action === 'delete_case_study') {
            $delId = (int)($_POST['case_study_id'] ?? 0);
            if ($delId > 0) {
                $csObj = CaseStudy::getById($delId);
                if ($csObj && !empty($csObj['image']) && !str_starts_with($csObj['image'], '/img/') && !str_ends_with($csObj['image'], '.png')) {
                    delete_uploaded_file($csObj['image']);
                }
                CaseStudy::delete($delId);
                flash_set('success', 'Case study deleted successfully.');
            }
            redirect('admin/pages/case-studies.php?tab=directory');
        }

        // -------------------------------------------------------------
        // TAB 01: SINGLE HERO BANNER SECTION (NO BUTTONS)
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec01') {
            $eyebrow  = trim($_POST['hero_eyebrow'] ?? 'VERIFIED COMMERCIAL PROOF • CASE STUDIES');
            $title    = trim($_POST['hero_title'] ?? 'Real Words. Measured in Revenue & Pipeline.');
            $subtitle = trim($_POST['hero_subtitle'] ?? 'Explore how fast-growth startups, enterprise SaaS platforms, and global advisory firms partner with WORDORA to scale search authority and conversion velocity.');

            // Fetch or create slide
            $slide = $db->query("SELECT * FROM hero_slides WHERE page = 'case_studies' ORDER BY sort_order ASC LIMIT 1")->fetch();
            $mediaUrl = $slide['media_url'] ?? '/img/case study.png';

            if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['hero_image_file']);
                if ($upRes['success']) {
                    if (!empty($slide['media_url']) && !str_starts_with($slide['media_url'], '/img/')) {
                        delete_uploaded_file($slide['media_url']);
                    }
                    $mediaUrl = $upRes['path'];
                } else {
                    $editorError = 'Hero banner image upload failed: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_hero_image']) && $_POST['remove_hero_image'] === '1') {
                if (!empty($slide['media_url']) && !str_starts_with($slide['media_url'], '/img/')) {
                    delete_uploaded_file($slide['media_url']);
                }
                $mediaUrl = '/img/case study.png';
            }

            if (empty($editorError)) {
                Setting::set('hero_mode_case_studies', 'single');
                if ($slide) {
                    $stmtSlide = $db->prepare("UPDATE hero_slides SET eyebrow = ?, title = ?, subtitle = ?, media_url = ?, button_primary_text = '', button_primary_url = '', button_secondary_text = '', button_secondary_url = '', banner_type = 'single' WHERE id = ?");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl, $slide['id']]);
                } else {
                    $stmtSlide = $db->prepare("INSERT INTO hero_slides (page, banner_type, eyebrow, title, subtitle, media_url, button_primary_text, button_primary_url, button_secondary_text, button_secondary_url, sort_order, is_active) VALUES ('case_studies', 'single', ?, ?, ?, ?, '', '', '', '', 1, 1)");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl]);
                }

                flash_set('success', 'Case Studies Hero Banner updated successfully!');
                redirect('admin/pages/case-studies.php?tab=sec01');
            }
        }

        // -------------------------------------------------------------
        // TAB 04: CASE STUDY EDIT / CREATE SUBMISSION
        // -------------------------------------------------------------
        elseif ($activeTab === 'editor' || $action === 'save_case_study') {
            $saveId = (int)($_POST['case_study_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $slug  = trim($_POST['slug'] ?? '');

            if (empty($title)) {
                $editorError = 'Case study title is required.';
            } else {
                $existingImage = $_POST['existing_image'] ?? 'service treasure.png';
                $finalImage = $existingImage;

                if (isset($_FILES['case_study_image_file']) && $_FILES['case_study_image_file']['error'] === UPLOAD_ERR_OK) {
                    $upRes = $uploader->handle($_FILES['case_study_image_file']);
                    if ($upRes['success']) {
                        if (!empty($existingImage) && !str_starts_with($existingImage, '/img/') && !str_ends_with($existingImage, '.png')) {
                            delete_uploaded_file($existingImage);
                        }
                        $finalImage = $upRes['path'];
                    } else {
                        $editorError = 'Image upload error: ' . $upRes['msg'];
                    }
                } elseif (!empty($_POST['remove_case_study_image']) && $_POST['remove_case_study_image'] === '1') {
                    if (!empty($existingImage) && !str_starts_with($existingImage, '/img/') && !str_ends_with($existingImage, '.png')) {
                        delete_uploaded_file($existingImage);
                    }
                    $finalImage = 'service treasure.png';
                }

                if (empty($editorError)) {
                    $industryName = trim($_POST['industry'] ?? 'SaaS & DevOps');
                    $data = [
                        'title'              => $title,
                        'slug'               => $slug,
                        'client'             => trim($_POST['client'] ?? ''),
                        'industry'           => $industryName,
                        'industry_slug'      => slugify($industryName),
                        'badge'              => trim($_POST['badge'] ?? 'Enterprise Proof'),
                        'headline_metric'    => trim($_POST['headline_metric'] ?? ''),
                        'headline_label'     => trim($_POST['headline_label'] ?? ''),
                        'secondary_metric'   => trim($_POST['secondary_metric'] ?? ''),
                        'secondary_label'    => trim($_POST['secondary_label'] ?? ''),
                        'tertiary_metric'    => trim($_POST['tertiary_metric'] ?? ''),
                        'tertiary_label'     => trim($_POST['tertiary_label'] ?? ''),
                        'timeline'           => trim($_POST['timeline'] ?? '6 Month Retainer'),
                        'location'           => trim($_POST['location'] ?? 'Global'),
                        'excerpt'            => trim($_POST['excerpt'] ?? ''),
                        'challenge'          => $_POST['challenge'] ?? '',
                        'solution'           => $_POST['solution'] ?? '',
                        'deliverables'       => trim($_POST['deliverables'] ?? ''),
                        'results_summary'    => $_POST['results_summary'] ?? '',
                        'testimonial_quote'  => trim($_POST['testimonial_quote'] ?? ''),
                        'testimonial_author' => trim($_POST['testimonial_author'] ?? ''),
                        'testimonial_role'   => trim($_POST['testimonial_role'] ?? ''),
                        'image'              => $finalImage,
                        'read_time'          => trim($_POST['read_time'] ?? '6 min read'),
                        'sort_order'         => (int)($_POST['sort_order'] ?? 0),
                        'is_featured'        => isset($_POST['is_featured']) ? 1 : 0,
                        'is_active'          => isset($_POST['is_active']) ? 1 : 0,
                        'meta_title'         => trim($_POST['meta_title'] ?? ''),
                        'meta_desc'          => trim($_POST['meta_desc'] ?? ''),
                        'meta_keywords'      => trim($_POST['meta_keywords'] ?? ''),
                    ];

                    $savedId = CaseStudy::save($data, $saveId > 0 ? $saveId : null);
                    flash_set('success', $saveId > 0 ? 'Case study updated successfully!' : 'New case study published successfully!');
                    redirect('admin/pages/case-studies.php?tab=directory');
                }
            }
        }

        // -------------------------------------------------------------
        // TAB 05: SIDEBAR WIDGETS & BOTTOM CTA BAR
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec04') {
            Setting::set('case_studies_cta_badge', trim($_POST['cta_badge'] ?? ''));
            Setting::set('case_studies_cta_title', trim($_POST['cta_title'] ?? ''));
            Setting::set('case_studies_cta_desc', trim($_POST['cta_desc'] ?? ''));
            Setting::set('case_studies_cta_btn_text', trim($_POST['cta_btn_text'] ?? ''));
            Setting::set('case_studies_cta_btn_url', trim($_POST['cta_btn_url'] ?? ''));

            Setting::set('cs_sidebar_cta_badge', trim($_POST['side_cta_badge'] ?? ''));
            Setting::set('cs_sidebar_cta_title', trim($_POST['side_cta_title'] ?? ''));
            Setting::set('cs_sidebar_cta_desc', trim($_POST['side_cta_desc'] ?? ''));
            Setting::set('cs_sidebar_cta_btn_text', trim($_POST['side_cta_btn_text'] ?? ''));
            Setting::set('cs_sidebar_cta_btn_url', trim($_POST['side_cta_btn_url'] ?? ''));

            Setting::set('cs_sidebar_news_badge', trim($_POST['side_news_badge'] ?? ''));
            Setting::set('cs_sidebar_news_title', trim($_POST['side_news_title'] ?? ''));
            Setting::set('cs_sidebar_news_desc', trim($_POST['side_news_desc'] ?? ''));
            Setting::set('cs_sidebar_news_btn_text', trim($_POST['side_news_btn_text'] ?? ''));

            flash_set('success', 'Case Studies Sidebar & Bottom CTA Bar updated successfully!');
            redirect('admin/pages/case-studies.php?tab=sec04');
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// 2. FETCH CURRENT DATA
// ═══════════════════════════════════════════════════════════════════════════

// Hero Section
$slide = $db->query("SELECT * FROM hero_slides WHERE page = 'case_studies' ORDER BY sort_order ASC LIMIT 1")->fetch();
$heroEyebrow = $slide['eyebrow'] ?? 'VERIFIED COMMERCIAL PROOF • CASE STUDIES';
$heroTitle   = $slide['title'] ?? 'Real Words. Measured in Revenue & Pipeline.';
$heroSubtitle= $slide['subtitle'] ?? 'Explore how fast-growth startups, enterprise SaaS platforms, and global advisory firms partner with WORDORA to scale search authority and conversion velocity.';
$heroMediaUrl= $slide['media_url'] ?? '/img/case study.png';
$heroBtn1Text= $slide['button_primary_text'] ?? 'Explore All Case Studies';
$heroBtn1Url = $slide['button_primary_url'] ?? '#case-studies-grid';
$heroBtn2Text= $slide['button_secondary_text'] ?? 'Request a Proposal';
$heroBtn2Url = $slide['button_secondary_url'] ?? 'contact.php';

// CTA Section & Sidebar Settings
$ctaBadge   = setting('case_studies_cta_badge', 'COMMERCIAL ROI AUDIT');
$ctaTitle   = setting('case_studies_cta_title', 'Ready to Scale Your Domain Authority?');
$ctaDesc    = setting('case_studies_cta_desc', 'Book a complimentary 30-minute content audit with our managing editors and receive a tailored topic architecture roadmap.');
$ctaBtnText = setting('case_studies_cta_btn_text', 'Schedule Scope Consultation');
$ctaBtnUrl  = setting('case_studies_cta_btn_url', 'contact.php');

$csSideCtaBadge   = setting('cs_sidebar_cta_badge', 'Similar ROI');
$csSideCtaTitle   = setting('cs_sidebar_cta_title', 'Ready to Scale Your Domain Authority?');
$csSideCtaDesc    = setting('cs_sidebar_cta_desc', 'Book a complimentary 30-minute content audit with our managing editors.');
$csSideCtaBtnText = setting('cs_sidebar_cta_btn_text', 'Request Scope Audit');
$csSideCtaBtnUrl  = setting('cs_sidebar_cta_btn_url', 'contact.php');

$csSideNewsBadge   = setting('cs_sidebar_news_badge', 'Executive Brief');
$csSideNewsTitle   = setting('cs_sidebar_news_title', 'Get Our ROI Playbooks');
$csSideNewsDesc    = setting('cs_sidebar_news_desc', 'Quarterly teardowns of high-growth B2B & SaaS content funnels delivered directly to your inbox.');
$csSideNewsBtnText = setting('cs_sidebar_news_btn_text', 'Get Free Playbooks');

// Industries / Categories
$allIndustries = CaseStudy::getIndustries();
$industriesWithCount = [];
foreach ($allIndustries as $ind) {
    $indSlug = $ind['slug'];
    $cnt = (int)$db->query("SELECT COUNT(*) FROM case_studies WHERE industry_slug = " . $db->quote($indSlug) . " OR industry = " . $db->quote($ind['name']))->fetchColumn();
    $ind['count'] = $cnt;
    $industriesWithCount[] = $ind;
}

// Directory & Active Case Study for Editor
$allStudies = CaseStudy::getAll($industryFilter === 'all' ? '' : $industryFilter);
$totalStudiesCount = CaseStudy::countAll();

$currentStudy = null;
if ($editId) {
    $currentStudy = CaseStudy::getById($editId);
}

$tabs = [
    'sec01'      => ['num' => '01', 'name' => 'Hero Banner', 'icon' => 'ri-image-line'],
    'directory'  => ['num' => '02', 'name' => 'Case Studies Directory', 'icon' => 'ri-folder-shield-2-line', 'badge' => $totalStudiesCount],
    'categories' => ['num' => '03', 'name' => 'Industry Sectors', 'icon' => 'ri-price-tag-3-line', 'badge' => count($allIndustries)],
    'editor'     => ['num' => '04', 'name' => ($editId ? 'Edit Study: #' . $editId : 'Write New Case Study'), 'icon' => 'ri-quill-pen-line'],
    'sec04'      => ['num' => '05', 'name' => 'Sidebar & CTA Bar', 'icon' => 'ri-flag-line'],
];
?>

<!-- Google Fonts for WYSIWYG and Studio -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Playfair+Display:ital,wght@0,500;0,700;0,800;1,500;1,700&display=swap" rel="stylesheet">

<!-- Quill.js WYSIWYG Editor CDN -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

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
}

.btn-adm-action.btn-adm-delete {
  background: #FEF2F2;
  color: #DC2626;
  border-color: #FECACA;
}
.btn-adm-action.btn-adm-delete:hover {
  background: #DC2626;
  color: #FFFFFF;
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

.admin-table tr:hover td {
  background: #F8FAFC !important;
}

/* Quill Styling Matching Blog Post Editor */
.ql-toolbar.ql-snow {
  background: #FAF8F5;
  border: 1.5px dashed rgba(74, 139, 140, 0.35) !important;
  border-bottom: 1.5px solid var(--admin-border) !important;
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
  padding: 10px 14px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px 10px;
}
.ql-toolbar.ql-snow .ql-formats {
  margin-right: 0 !important;
  display: inline-flex;
  align-items: center;
  gap: 2px;
}
.ql-snow .ql-picker.ql-font {
  width: 175px !important;
}
.ql-snow .ql-picker.ql-header {
  width: 115px !important;
}
.ql-snow .ql-picker-label {
  display: flex !important;
  align-items: center !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  padding-right: 22px !important;
  font-size: 13px !important;
}
.ql-snow .ql-picker-label::before {
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}
.ql-snow .ql-picker-options {
  min-width: 190px !important;
  border-radius: 8px !important;
  box-shadow: 0 10px 25px rgba(15, 30, 54, 0.12) !important;
  border: 1px solid rgba(74, 139, 140, 0.25) !important;
  padding: 6px 0 !important;
}
.ql-snow .ql-picker-item {
  padding: 6px 12px !important;
  font-size: 13px !important;
}

.ql-container.ql-snow {
  border: 1.5px dashed rgba(74, 139, 140, 0.35) !important;
  border-top: none !important;
  border-bottom-left-radius: 12px;
  border-bottom-right-radius: 12px;
  background: #FFFFFF;
  font-family: 'Inter', sans-serif;
}
.ql-editor {
  min-height: 180px;
  font-size: 15px;
  line-height: 1.8;
  color: var(--wdr-navy);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
.ql-editor h1, .ql-editor h2, .ql-editor h3, .ql-editor h4 {
  font-family: 'Playfair Display', Georgia, serif;
  color: var(--wdr-navy);
  margin-top: 1.3em;
  margin-bottom: 0.5em;
  font-weight: 700;
}
.ql-editor blockquote {
  border-left: 4px solid var(--wdr-teal);
  padding: 12px 18px;
  color: #475569;
  font-style: italic;
  background: rgba(74, 139, 140, 0.06);
  border-radius: 0 8px 8px 0;
}
.ql-editor pre.ql-syntax {
  background: #0F1E36;
  color: #F8FAFC;
  border-radius: 8px;
  padding: 14px;
  font-family: 'JetBrains Mono', monospace;
}
.ql-editor code {
  font-family: 'JetBrains Mono', monospace;
  background: #F1F5F9;
  color: var(--wdr-teal);
  padding: 2px 6px;
  border-radius: 4px;
}

/* Font Whitelist Mappings */
.ql-font-inter { font-family: 'Inter', sans-serif !important; }
.ql-font-playfair { font-family: 'Playfair Display', Georgia, serif !important; }
.ql-font-dmsans { font-family: 'DM Sans', sans-serif !important; }
.ql-font-jetbrains { font-family: 'JetBrains Mono', monospace !important; }
.ql-font-georgia { font-family: Georgia, serif !important; }

/* Custom Font Dropdown Labels */
.ql-snow .ql-picker.ql-font .ql-picker-label::before,
.ql-snow .ql-picker.ql-font .ql-picker-item::before,
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="inter"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="inter"]::before {
  content: 'Inter (Body)';
  font-family: 'Inter', sans-serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="playfair"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="playfair"]::before {
  content: 'Playfair (Headings)';
  font-family: 'Playfair Display', Georgia, serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="dmsans"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="dmsans"]::before {
  content: 'DM Sans (Clean)';
  font-family: 'DM Sans', sans-serif;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="jetbrains"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="jetbrains"]::before {
  content: 'JetBrains Mono (Code)';
  font-family: 'JetBrains Mono', monospace;
}
.ql-snow .ql-picker.ql-font .ql-picker-label[data-value="georgia"]::before,
.ql-snow .ql-picker.ql-font .ql-picker-item[data-value="georgia"]::before {
  content: 'Georgia (Editorial)';
  font-family: Georgia, serif;
}
</style>

<div class="visual-studio-wrapper">

  <!-- Studio Header -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
      <h2 style="font-family: var(--wdr-font-display); font-size: 24px; font-weight: 700; color: var(--admin-navy); margin: 0;">
        <i class="ri-folder-shield-2-line" style="color: var(--admin-teal);"></i> Case Studies Visual Section Studio
      </h2>
      <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
        Case studies archive, industry sectors, commercial metrics, client deep-dives, and rich WYSIWYG editorial builder.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="<?= url('case-studies.php') ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Case Studies
      </a>
      <a href="<?= $currentUrl ?>?tab=editor" class="btn-adm btn-adm-primary">
        <i class="ri-add-line"></i> Write New Study
      </a>
    </div>
  </div>

  <!-- Flash Alerts -->
  <?php if (!empty($editorSuccess)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; display: flex; align-items: center; gap: 10px;">
      <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #16A34A;"></i>
      <span><?= e($editorSuccess) ?></span>
      <a href="<?= url('case-studies.php') ?>" target="_blank" style="margin-left: auto; font-size: 12px; text-decoration: underline; color: #166534; font-weight: 700;">View Live Page <i class="ri-external-link-line"></i></a>
    </div>
  <?php endif; ?>

  <?php if (!empty($editorError)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
      <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
    </div>
  <?php endif; ?>

  <?php $isCsLive = (setting('enable_case_studies', '1') !== '0'); ?>
  <!-- Public Visibility Master Switch Card (Styled with studio dashed border & real iOS toggle) -->
  <div style="background: var(--wdr-canvas); border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 20px; padding: 22px 28px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 18px; box-shadow: 0 2px 10px rgba(15, 30, 54, 0.03);">
    <div style="display: flex; align-items: center; gap: 16px; min-width: 280px; flex: 1;">
      <div style="width: 48px; height: 48px; border-radius: 14px; background: <?= $isCsLive ? 'rgba(74, 139, 140, 0.12)' : 'rgba(239, 68, 68, 0.1)' ?>; color: <?= $isCsLive ? 'var(--admin-teal)' : '#DC2626' ?>; border: 1.5px dashed <?= $isCsLive ? 'var(--admin-teal)' : '#FCA5A5' ?>; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
        <i class="<?= $isCsLive ? 'ri-folder-shield-2-line' : 'ri-eye-off-line' ?>"></i>
      </div>
      <div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <span style="font-family: var(--wdr-font-display); font-size: 17px; font-weight: 700; color: var(--admin-navy);">
            Case Studies Public Visibility
          </span>
          <span class="visual-badge" style="background: <?= $isCsLive ? 'rgba(74, 139, 140, 0.12)' : 'rgba(239, 68, 68, 0.12)' ?>; color: <?= $isCsLive ? 'var(--admin-teal)' : '#DC2626' ?>; border-color: <?= $isCsLive ? 'var(--admin-teal)' : '#DC2626' ?>;">
            <i class="<?= $isCsLive ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' ?>"></i>
            <?= $isCsLive ? 'STATUS: LIVE & VISIBLE' : 'STATUS: TEMPORARILY HIDDEN' ?>
          </span>
        </div>
        <p style="font-size: 13px; color: var(--admin-muted); margin: 5px 0 0; line-height: 1.45;">
          <?= $isCsLive 
              ? 'Archive directory, individual case study deep-dives, navbar dropdown proof tiles, and footer links are active on the website.' 
              : 'Case studies archive, dropdown links, and footer links are hidden. Direct visits redirect safely to What We Do.' ?>
        </p>
      </div>
    </div>

    <!-- Interactive iOS Toggle Switch -->
    <form method="POST" action="<?= $currentUrl ?>?tab=<?= urlencode($activeTab) ?>" style="margin: 0; display: flex; align-items: center; gap: 14px;">
      <?= CSRF::field() ?>
      <input type="hidden" name="action" value="toggle_case_studies_module">
      <input type="hidden" name="enable_case_studies" value="<?= $isCsLive ? '0' : '1' ?>">
      
      <span style="font-size: 13px; font-weight: 700; color: <?= $isCsLive ? 'var(--admin-navy)' : '#94A3B8' ?>; font-family: var(--wdr-font-mono);">
        <?= $isCsLive ? 'ACTIVE (ON)' : 'DISABLED (OFF)' ?>
      </span>

      <button type="submit" title="Click to <?= $isCsLive ? 'Hide' : 'Enable' ?> Case Studies" style="background: transparent; border: none; padding: 0; cursor: pointer; display: inline-flex; align-items: center;">
        <div style="width: 56px; height: 30px; border-radius: 16px; background: <?= $isCsLive ? 'var(--admin-teal)' : '#CBD5E1' ?>; padding: 3px; box-sizing: border-box; transition: background 0.3s ease; display: flex; align-items: center; box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);">
          <div style="width: 24px; height: 24px; border-radius: 50%; background: #FFFFFF; box-shadow: 0 2px 6px rgba(0,0,0,0.25); transform: translateX(<?= $isCsLive ? '26px' : '0px' ?>); transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
        </div>
      </button>
    </form>
  </div>

  <!-- Studio Navigation Tabs -->
  <div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
    <?php foreach ($tabs as $k => $t): 
        $isAct = ($activeTab === $k);
    ?>
    <a href="<?= $currentUrl ?>?tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1.5px solid var(--admin-border);' ?>">
      <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--admin-teal)' : 'var(--admin-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--admin-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
      <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
      <?php if (!empty($t['badge']) && $t['badge'] > 0): ?>
        <span style="background: var(--wdr-teal); color: #FFF; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 10px; margin-left: 2px;">
          <?= $t['badge'] ?>
        </span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>


  <!-- ═══════════════════════════════════════════
       TAB 01: SINGLE HERO BANNER SECTION
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec01'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-image-line"></i> SECTION 01 — THE EDITORIAL BANNER</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Case Studies Hero Banner &amp; Atmosphere</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure hero atmosphere banner image, headlines, and action buttons shown on Case Studies archive and detail pages.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      
      <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
        <div>
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Eyebrow Badge Tag</label>
            <input type="text" name="hero_eyebrow" class="visual-input-styled" value="<?= e($heroEyebrow) ?>" placeholder="e.g. VERIFIED COMMERCIAL PROOF • CASE STUDIES" style="font-weight: 700;">
          </div>

          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Hero Main Headline <span style="color:#ef4444;">*</span></label>
            <input type="text" name="hero_title" class="visual-input-styled" required value="<?= e($heroTitle) ?>" placeholder="e.g. Real Words. Measured in Revenue & Pipeline." style="font-weight: 700; font-size: 16px;">
          </div>

          <div style="margin-bottom: 20px;">
            <label class="visual-label-upper">Hero Lead Subtitle</label>
            <textarea name="hero_subtitle" class="visual-input-styled" rows="4" placeholder="Explore how fast-growth startups, enterprise SaaS platforms..."><?= e($heroSubtitle) ?></textarea>
          </div>

        </div>

        <!-- Cover Image Preview -->
        <div>
          <div style="background: #FFF; padding: 24px; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 24px; text-align: center;">
            <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-add-line"></i> Hero Atmosphere Banner Artwork</label>
            
            <?php 
            $hasCustomHero = !empty($heroMediaUrl) && !str_starts_with($heroMediaUrl, '/img/');
            $resolvedHeroImg = !empty($heroMediaUrl) ? $heroMediaUrl : '/img/case study.png';
            ?>
            
            <div style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #0F1E36;">
              <img id="preview_hero_img" src="<?= media_url($resolvedHeroImg) ?>" alt="Case Studies Hero" style="max-height: 200px; width: 100%; object-fit: cover; display: block;">
              <div style="position: absolute; bottom: 8px; left: 8px; z-index: 2;">
                <?php if ($hasCustomHero): ?>
                  <span class="badge badge-teal" style="font-size: 11px;">Custom Cover Uploaded</span>
                <?php else: ?>
                  <span class="badge" style="background: rgba(15,30,54,0.85); color: #FFF; font-size: 11px; border: 1px solid rgba(255,255,255,0.2);">Default Atmosphere Banner</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
              <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload Single Banner Artwork (PNG / JPG / WEBP)</label>
              <input type="file" name="hero_image_file" class="visual-input-styled" accept="image/*">
              <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
              <?php if ($hasCustomHero): ?>
                <button type="button" onclick="document.getElementById('remove_hero_image').value='1'; document.getElementById('preview_hero_img').src='<?= media_url('/img/case study.png') ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600;">
                  <i class="ri-delete-bin-line"></i> Revert to Default Banner
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Hero Banner</button>
    </form>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 02: CASE STUDIES DIRECTORY TABLE
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'directory'): ?>
  <div class="visual-studio-card">
    
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
      <div>
        <span class="visual-badge"><i class="ri-folder-shield-2-line"></i> SECTION 02 — CASE STUDIES DIRECTORY</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Commercial Proof &amp; Case Studies Databank</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Manage your commercial case studies, measured metrics, deliverables, and client testimonials.</p>
      </div>

      <div style="display: flex; gap: 10px;">
        <a href="<?= $currentUrl ?>?tab=editor" class="btn-adm btn-adm-primary">
          <i class="ri-add-line"></i> Write New Case Study
        </a>
      </div>
    </div>

    <!-- Industry Filter Bar -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
      <a href="?tab=directory&industry=all" class="btn-adm-action" style="<?= ($industryFilter === 'all' || empty($industryFilter)) ? 'background: var(--wdr-navy); color: #FFF; border-color: var(--wdr-navy);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
        <span>All Industries</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $totalStudiesCount ?>)</strong>
      </a>
      <?php foreach ($industriesWithCount as $ic): ?>
        <a href="?tab=directory&industry=<?= urlencode($ic['slug']) ?>" class="btn-adm-action" style="<?= ($industryFilter === $ic['slug']) ? 'background: var(--wdr-teal); color: #FFF; border-color: var(--wdr-teal);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
          <span><?= e($ic['name']) ?></span> <strong style="font-family: var(--wdr-font-mono);">(<?= $ic['count'] ?>)</strong>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Directory Table -->
    <div class="admin-card-table-wrapper">
      <div class="table-top-bar">
        <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
          <i class="ri-file-shield-line" style="color: var(--wdr-teal); font-size: 16px;"></i> Active Case Studies
        </div>
        <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
          Showing <?= count($allStudies) ?> Studies
        </span>
      </div>

      <div class="table-responsive" style="overflow-x: auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 60px;">Artwork</th>
              <th style="min-width: 240px;">Title &amp; Client</th>
              <th style="min-width: 150px;">Sector / Badge</th>
              <th style="min-width: 180px;">Headline ROI Metric</th>
              <th style="width: 100px;">Sort Order</th>
              <th style="width: 100px;">Status</th>
              <th style="text-align: right; width: 140px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allStudies as $cs): ?>
              <tr>
                <!-- Artwork Thumbnail -->
                <td>
                  <div style="width: 48px; height: 38px; border-radius: 8px; overflow: hidden; background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); display: flex; align-items: center; justify-content: center;">
                    <img src="<?= img($cs['image'] ?: 'service treasure.png') ?>" alt="<?= e($cs['title']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                  </div>
                </td>

                <!-- Title & Client -->
                <td>
                  <div style="font-weight: 700; color: var(--wdr-navy); font-size: 13.5px; line-height: 1.35; margin-bottom: 2px;">
                    <?= e($cs['title']) ?>
                  </div>
                  <div style="font-size: 11.5px; color: #64748B; font-family: var(--wdr-font-mono);">
                    Client: <strong><?= e($cs['client']) ?></strong> • <code><?= e($cs['slug']) ?></code>
                  </div>
                </td>

                <!-- Sector & Badge -->
                <td>
                  <span style="display: inline-block; font-size: 11.5px; font-weight: 700; color: var(--wdr-navy); background: var(--wdr-teal-pale); padding: 3px 8px; border-radius: 6px; border: 1px dashed rgba(74, 139, 140, 0.4); margin-bottom: 3px;">
                    <?= e($cs['badge'] ?: 'Enterprise') ?>
                  </span>
                  <div style="font-size: 11px; color: #64748B;">
                    <?= e($cs['industry']) ?>
                  </div>
                </td>

                <!-- Headline ROI Metric -->
                <td>
                  <div style="font-family: var(--wdr-font-display); font-size: 16px; font-weight: 700; color: var(--wdr-teal);">
                    <?= e($cs['headline_metric']) ?>
                  </div>
                  <div style="font-size: 10.5px; color: #64748B; text-transform: uppercase; font-weight: 600;">
                    <?= e($cs['headline_label']) ?>
                  </div>
                </td>

                <!-- Sort Order -->
                <td>
                  <span style="font-family: var(--wdr-font-mono); font-weight: 700; font-size: 12px; color: var(--wdr-navy);">
                    #<?= (int)$cs['sort_order'] ?>
                  </span>
                </td>

                <!-- Status -->
                <td>
                  <?php if ((int)$cs['is_active'] === 1): ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;">
                      <i class="ri-checkbox-circle-fill"></i> Live
                    </span>
                  <?php else: ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">
                      <i class="ri-eye-off-line"></i> Draft
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Actions -->
                <td style="text-align: right;">
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= url('case-study/' . e($cs['slug'])) ?>" target="_blank" class="btn-adm-action btn-adm-edit" title="View Public Page">
                      <i class="ri-external-link-line"></i>
                    </a>
                    <a href="<?= $currentUrl ?>?tab=editor&id=<?= (int)$cs['id'] ?>" class="btn-adm-action btn-adm-edit" title="Edit Case Study">
                      <i class="ri-edit-line"></i> Edit
                    </a>
                    <form method="POST" action="<?= $currentUrl ?>?tab=directory" onsubmit="return confirm('Are you sure you want to permanently delete this case study?');" style="display: inline;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="delete_case_study">
                      <input type="hidden" name="case_study_id" value="<?= (int)$cs['id'] ?>">
                      <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Case Study">
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

  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 03: INDUSTRY SECTORS & CATEGORIES MANAGER
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'categories'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-price-tag-3-line"></i> SECTION 03 — INDUSTRY SECTOR CATEGORIES</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Case Study Industry Sectors</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Add, manage, and delete industry categories used across case study filter pills and authoring forms.</p>
    </div>

    <!-- Add Industry Form -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-navy); margin: 0 0 14px;"><i class="ri-add-circle-line" style="color: var(--wdr-teal);"></i> Add New Industry Sector</h3>
      
      <form method="POST" action="<?= $currentUrl ?>?tab=categories">
        <?= CSRF::field() ?>
        <input type="hidden" name="action" value="create_industry">
        
        <div style="display: grid; grid-template-columns: 1.5fr 1.5fr auto; gap: 14px; align-items: flex-end;">
          <div>
            <label class="visual-label-upper">Industry Sector Name <span style="color:#ef4444;">*</span></label>
            <input type="text" name="industry_name" class="visual-input-styled" required placeholder="e.g. Cybersecurity &amp; Web3" style="font-weight: 600;">
          </div>
          <div>
            <label class="visual-label-upper">URL Slug (leave blank to auto-generate)</label>
            <input type="text" name="industry_slug" class="visual-input-styled" placeholder="e.g. cybersecurity-web3" style="font-family: var(--wdr-font-mono);">
          </div>
          <div>
            <button type="submit" class="btn-adm btn-adm-primary" style="padding: 10px 20px; font-weight: 700; white-space: nowrap;">
              <i class="ri-add-line"></i> Add Category
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Industries List Table -->
    <div class="admin-card-table-wrapper">
      <div class="table-top-bar">
        <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
          <i class="ri-list-check-2" style="color: var(--wdr-teal); font-size: 16px;"></i> Active Industry Sectors
        </div>
        <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
          <?= count($industriesWithCount) ?> Categories
        </span>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="min-width: 220px;">Sector Name</th>
              <th style="min-width: 200px;">Filter Slug</th>
              <th style="width: 140px;">Total Studies</th>
              <th style="text-align: right; width: 120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($industriesWithCount as $ind): ?>
              <tr>
                <td>
                  <div style="font-weight: 700; color: var(--wdr-navy); font-size: 14px;">
                    <?= e($ind['name']) ?>
                  </div>
                </td>
                <td>
                  <code style="font-family: var(--wdr-font-mono); font-size: 12px; color: var(--wdr-teal); background: var(--wdr-teal-pale); padding: 2px 6px; border-radius: 4px;">
                    <?= e($ind['slug']) ?>
                  </code>
                </td>
                <td>
                  <span style="display: inline-block; font-size: 11.5px; font-weight: 700; color: var(--wdr-navy); background: #FAF8F5; padding: 4px 10px; border-radius: 8px; border: 1.5px dashed rgba(74,139,140,0.35);">
                    <?= $ind['count'] ?> Studies
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="table-actions" style="justify-content: flex-end;">
                    <form method="POST" action="<?= $currentUrl ?>?tab=categories" onsubmit="return confirm('Delete industry sector <?= e($ind['name']) ?>?');" style="display: inline;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="delete_industry">
                      <input type="hidden" name="industry_slug" value="<?= e($ind['slug']) ?>">
                      <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Category">
                        <i class="ri-delete-bin-line"></i> Remove
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

  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 04: CASE STUDY AUTHORING & WYSIWYG STUDIO
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'editor'): 
      $csVal = $currentStudy ?: [];
  ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
      <div>
        <span class="visual-badge"><i class="ri-quill-pen-line"></i> SECTION 04 — EDITORIAL WYSIWYG AUTHORING STUDIO</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;"><?= $editId ? 'Edit Case Study: ' . e($csVal['title'] ?? '') : 'Author New Commercial Case Study' ?></h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Full-featured rich WYSIWYG authoring for Challenge, Solution, Deliverables, Results, and Client Testimonials with website fonts.</p>
      </div>

      <a href="<?= $currentUrl ?>?tab=directory" class="btn-adm btn-adm-outline">
        <i class="ri-arrow-left-line"></i> Back to Directory
      </a>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=editor" enctype="multipart/form-data" id="caseStudyForm">
      <?= CSRF::field() ?>
      <input type="hidden" name="action" value="save_case_study">
      <input type="hidden" name="case_study_id" value="<?= (int)($csVal['id'] ?? 0) ?>">
      <input type="hidden" name="existing_image" value="<?= e($csVal['image'] ?? 'service treasure.png') ?>">

      <!-- Part 1: Core Details -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-file-text-line"></i> PART 01 — CORE CASE STUDY IDENTITY</span>
        
        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Case Study Full Title <span style="color:#ef4444;">*</span></label>
          <input type="text" name="title" class="visual-input-styled" required value="<?= e($csVal['title'] ?? '') ?>" placeholder="e.g. ScaleStack Cloud: Engineering Developer Trust & Technical Authority" style="font-size: 16px; font-weight: 700;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper">Client Name</label>
            <input type="text" name="client" class="visual-input-styled" value="<?= e($csVal['client'] ?? '') ?>" placeholder="e.g. ScaleStack Cloud" style="font-weight: 600;">
          </div>
          <div>
            <label class="visual-label-upper">URL Slug (leave blank to auto-generate)</label>
            <input type="text" name="slug" class="visual-input-styled" value="<?= e($csVal['slug'] ?? '') ?>" placeholder="e.g. scalestack-cloud-developer-trust" style="font-family: var(--wdr-font-mono);">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper" style="display: flex; justify-content: space-between;">
              <span>Industry Sector</span>
              <a href="?tab=categories" target="_blank" style="font-size: 10px; color: var(--wdr-teal); text-decoration: none;"><i class="ri-add-line"></i> Manage</a>
            </label>
            <select name="industry" class="visual-input-styled" style="font-weight: 600;">
              <?php
              $curInd = $csVal['industry'] ?? 'SaaS & DevOps';
              foreach ($allIndustries as $sec): ?>
                <option value="<?= e($sec['name']) ?>" <?= ($curInd === $sec['name'] || ($csVal['industry_slug'] ?? '') === $sec['slug']) ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="visual-label-upper">Badge Tag</label>
            <input type="text" name="badge" class="visual-input-styled" value="<?= e($csVal['badge'] ?? 'Enterprise SaaS') ?>" placeholder="e.g. Enterprise SaaS" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Read Time</label>
            <input type="text" name="read_time" class="visual-input-styled" value="<?= e($csVal['read_time'] ?? '6 min read') ?>" placeholder="e.g. 6 min read">
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper">Engagement Timeline</label>
            <input type="text" name="timeline" class="visual-input-styled" value="<?= e($csVal['timeline'] ?? '6 Month Retainer') ?>" placeholder="e.g. 6 Month Retainer">
          </div>
          <div>
            <label class="visual-label-upper">Client Location</label>
            <input type="text" name="location" class="visual-input-styled" value="<?= e($csVal['location'] ?? 'San Francisco, CA') ?>" placeholder="e.g. San Francisco, CA">
          </div>
        </div>

        <div>
          <label class="visual-label-upper">Short Excerpt / Search Teaser</label>
          <textarea name="excerpt" class="visual-input-styled" rows="2" placeholder="Brief 1-2 sentence summary displayed on case study cards..."><?= e($csVal['excerpt'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Part 2: Quantified Commercial Metrics -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-line-chart-line"></i> PART 02 — 3 QUANTIFIED ROI COMMERCIAL METRICS</span>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
          <!-- Metric 1 -->
          <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 14px;">
            <label class="visual-label-upper">Headline Metric (Primary)</label>
            <input type="text" name="headline_metric" class="visual-input-styled" value="<?= e($csVal['headline_metric'] ?? '+420%') ?>" placeholder="+420%" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-teal); margin-bottom: 8px;">
            <label class="visual-label-upper">Headline Label</label>
            <input type="text" name="headline_label" class="visual-input-styled" value="<?= e($csVal['headline_label'] ?? 'Developer Signups Lift') ?>" placeholder="Developer Signups Lift" style="font-size: 12px;">
          </div>

          <!-- Metric 2 -->
          <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 14px;">
            <label class="visual-label-upper">Secondary Metric</label>
            <input type="text" name="secondary_metric" class="visual-input-styled" value="<?= e($csVal['secondary_metric'] ?? '1000+') ?>" placeholder="1000+" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-navy); margin-bottom: 8px;">
            <label class="visual-label-upper">Secondary Label</label>
            <input type="text" name="secondary_label" class="visual-input-styled" value="<?= e($csVal['secondary_label'] ?? 'Technical Articles Delivered') ?>" placeholder="Technical Articles Delivered" style="font-size: 12px;">
          </div>

          <!-- Metric 3 -->
          <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 14px;">
            <label class="visual-label-upper">Tertiary Metric</label>
            <input type="text" name="tertiary_metric" class="visual-input-styled" value="<?= e($csVal['tertiary_metric'] ?? '40k+') ?>" placeholder="40k+" style="font-family: var(--wdr-font-display); font-size: 18px; font-weight: 700; color: var(--wdr-teal); margin-bottom: 8px;">
            <label class="visual-label-upper">Tertiary Label</label>
            <input type="text" name="tertiary_label" class="visual-input-styled" value="<?= e($csVal['tertiary_label'] ?? 'Monthly Active Developers') ?>" placeholder="Monthly Active Developers" style="font-size: 12px;">
          </div>
        </div>
      </div>

      <!-- Part 3: Deep Dive Story with WYSIWYG Editors -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-article-line"></i> PART 03 — EDITORIAL STORY &amp; DELIVERABLES (WEBSITE FONTS)</span>

        <!-- Challenge WYSIWYG -->
        <div style="margin-bottom: 22px;">
          <label class="visual-label-upper"><i class="ri-alert-line"></i> Section 1: The Challenge (Market Friction &amp; Bottleneck)</label>
          <input type="hidden" name="challenge" id="challengeInput" value="<?= htmlspecialchars($csVal['challenge'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div id="challengeEditor" style="min-height: 140px;">
            <?= $csVal['challenge'] ?? '<p>Describe the client bottleneck, legacy limitations, or market friction before partnering with WORDORA...</p>' ?>
          </div>
        </div>

        <!-- Solution WYSIWYG -->
        <div style="margin-bottom: 22px;">
          <label class="visual-label-upper"><i class="ri-lightbulb-flash-line"></i> Section 2: The Solution (Strategic Editorial Architecture)</label>
          <input type="hidden" name="solution" id="solutionInput" value="<?= htmlspecialchars($csVal['solution'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div id="solutionEditor" style="min-height: 140px;">
            <?= $csVal['solution'] ?? '<p>Describe how WORDORA structured topic clusters, developer whitepapers, and narrative frameworks...</p>' ?>
          </div>
        </div>

        <!-- Deliverables Produced -->
        <div style="margin-bottom: 22px;">
          <label class="visual-label-upper"><i class="ri-file-list-3-line"></i> Deliverables Engineered &amp; Shipped (1 item per line)</label>
          <textarea name="deliverables" class="visual-input-styled" rows="4" placeholder="24-Part Architectural Whitepaper Series&#10;Interactive API Reference &amp; SDK Guides&#10;30+ Production-Ready Code Recipes..."><?= e($csVal['deliverables'] ?? '') ?></textarea>
          <div style="font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Enter each deliverable bullet on a new line. They will be rendered as interactive checklist items.</div>
        </div>

        <!-- Results Summary WYSIWYG -->
        <div>
          <label class="visual-label-upper"><i class="ri-trophy-line"></i> Section 3: Commercial Results &amp; Search Dominance</label>
          <input type="hidden" name="results_summary" id="resultsInput" value="<?= htmlspecialchars($csVal['results_summary'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <div id="resultsEditor" style="min-height: 140px;">
            <?= $csVal['results_summary'] ?? '<p>Summarize the quantified revenue lift, conversion jump, and long-term organic moat achieved...</p>' ?>
          </div>
        </div>
      </div>

      <!-- Part 4: Testimonial, Media & Publication Settings -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-double-quotes-l"></i> PART 04 — CLIENT TESTIMONIAL &amp; MEDIA ARTWORK</span>

        <!-- Testimonial -->
        <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 14px; padding: 18px; margin-bottom: 20px;">
          <div style="margin-bottom: 12px;">
            <label class="visual-label-upper">Client Testimonial Quote</label>
            <textarea name="testimonial_quote" class="visual-input-styled" rows="2" placeholder="WORDORA bridged the gap between our complex engineering architecture and the developer community..."><?= e($csVal['testimonial_quote'] ?? '') ?></textarea>
          </div>
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
            <div>
              <label class="visual-label-upper">Testimonial Author Name</label>
              <input type="text" name="testimonial_author" class="visual-input-styled" value="<?= e($csVal['testimonial_author'] ?? '') ?>" placeholder="e.g. Marcus Vance">
            </div>
            <div>
              <label class="visual-label-upper">Testimonial Author Role / Title</label>
              <input type="text" name="testimonial_role" class="visual-input-styled" value="<?= e($csVal['testimonial_role'] ?? '') ?>" placeholder="e.g. Chief Technology Officer, ScaleStack Cloud">
            </div>
          </div>
        </div>

        <!-- Featured Media Image -->
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; align-items: center; margin-bottom: 20px;">
          <div>
            <label class="visual-label-upper">Upload Case Study Artwork (PNG / JPG / WebP)</label>
            <input type="file" name="case_study_image_file" class="visual-input-styled" accept="image/*">
            <input type="hidden" name="remove_case_study_image" id="remove_case_study_image" value="0">
          </div>
          <div style="text-align: center; background: #FAF8F5; padding: 12px; border-radius: 12px; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
            <img id="preview_cs_img" src="<?= img($csVal['image'] ?? 'service treasure.png') ?>" alt="Artwork Preview" style="max-height: 90px; width: auto; object-fit: contain; margin: 0 auto;">
          </div>
        </div>

        <!-- Sort Order & Toggles -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; align-items: center; padding-top: 14px; border-top: 1px solid #E2E8F0;">
          <div>
            <label class="visual-label-upper">Display Sort Order</label>
            <input type="number" name="sort_order" class="visual-input-styled" value="<?= (int)($csVal['sort_order'] ?? 1) ?>" min="0">
          </div>
          <div>
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--wdr-navy); font-size: 13.5px; cursor: pointer;">
              <input type="checkbox" name="is_featured" value="1" <?= (!empty($csVal['is_featured']) && (int)$csVal['is_featured'] === 1) ? 'checked' : '' ?> style="accent-color: var(--wdr-teal); width: 18px; height: 18px;">
              ⭐ Featured Master Case Study (Big Hero Card)
            </label>
          </div>
          <div>
            <label style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--wdr-navy); font-size: 13.5px; cursor: pointer;">
              <input type="checkbox" name="is_active" value="1" <?= (!isset($csVal['is_active']) || (int)$csVal['is_active'] === 1) ? 'checked' : '' ?> style="accent-color: var(--wdr-teal); width: 18px; height: 18px;">
              🟢 Active &amp; Published on Public Website
            </label>
          </div>
        </div>

      </div>

      <!-- Part 5: Search Engine Optimization (SEO Metadata) -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-search-eye-line"></i> PART 05 — SEARCH ENGINE OPTIMIZATION (SEO METADATA)</span>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">SEO Meta Title (Title Tag)</label>
          <input type="text" name="meta_title" class="visual-input-styled" value="<?= e($csVal['meta_title'] ?? '') ?>" placeholder="e.g. Scaling ScaleStack DevOps Authority by +420% | WORDORA Case Study">
          <div style="font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Recommended length: 50–60 characters. Displayed on search result snippets and browser tabs.</div>
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">SEO Meta Description</label>
          <textarea name="meta_desc" class="visual-input-styled" rows="2" placeholder="e.g. Discover how ScaleStack partnered with WORDORA to engineer high-velocity developer content that drove +420% signups..."><?= e($csVal['meta_desc'] ?? '') ?></textarea>
          <div style="font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Recommended length: 140–160 characters. Displayed as the snippet below the title in search engines.</div>
        </div>

        <div>
          <label class="visual-label-upper">SEO Focus Keywords (Comma-Separated)</label>
          <input type="text" name="meta_keywords" class="visual-input-styled" value="<?= e($csVal['meta_keywords'] ?? '') ?>" placeholder="e.g. B2B SaaS Case Study, DevOps Content Strategy, Pipeline Conversion">
          <div style="font-size: 11px; color: var(--admin-muted); margin-top: 4px;">Separate multiple keyword phrases with commas.</div>
        </div>
      </div>

      <div style="display: flex; gap: 12px;">
        <button type="submit" class="btn-adm btn-adm-primary" style="padding: 12px 24px; font-size: 14px; font-weight: 700;">
          <i class="ri-save-line"></i> <?= $editId ? 'Update Case Study' : 'Publish Case Study' ?>
        </button>
        <a href="<?= $currentUrl ?>?tab=directory" class="btn-adm btn-adm-outline">Cancel</a>
      </div>
    </form>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Register Website Fonts in Quill
    const Font = Quill.import('formats/font');
    Font.whitelist = ['inter', 'playfair', 'dmsans', 'jetbrains', 'georgia'];
    Quill.register(Font, true);

    const toolbarOptions = [
      [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
      [{ 'font': ['inter', 'playfair', 'dmsans', 'jetbrains', 'georgia'] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'color': [] }, { 'background': [] }],
      [{ 'script': 'sub'}, { 'script': 'super' }],
      ['blockquote', 'code-block'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      [{ 'indent': '-1'}, { 'indent': '+1' }],
      [{ 'direction': 'rtl' }],
      [{ 'align': [] }],
      ['link', 'image', 'video'],
      ['clean']
    ];

    const quillChallenge = new Quill('#challengeEditor', { modules: { toolbar: toolbarOptions }, theme: 'snow', placeholder: 'Describe the client bottleneck and initial challenges...' });
    const quillSolution  = new Quill('#solutionEditor',  { modules: { toolbar: toolbarOptions }, theme: 'snow', placeholder: 'Describe the strategic editorial architecture and topic clustering...' });
    const quillResults   = new Quill('#resultsEditor',   { modules: { toolbar: toolbarOptions }, theme: 'snow', placeholder: 'Describe quantified commercial ROI and search rankings achieved...' });

    const form = document.getElementById('caseStudyForm');
    if (form) {
      form.addEventListener('submit', function() {
        document.getElementById('challengeInput').value = quillChallenge.root.innerHTML;
        document.getElementById('solutionInput').value  = quillSolution.root.innerHTML;
        document.getElementById('resultsInput').value   = quillResults.root.innerHTML;
      });
    }
  });
  </script>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 05: SIDEBAR WIDGETS & BOTTOM CTA BAR
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec04'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-flag-line"></i> SECTION 05 — SIDEBAR &amp; BOTTOM CTA STUDIO</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Detail Page Sidebar &amp; Bottom Audit CTA Bar</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure dynamic right-sidebar widgets for Case Study detail pages and the bottom consultation banner.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec04">
      <?= CSRF::field() ?>
      
      <!-- 1. Detail Page Right Sidebar CTA Box -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-layout-right-line"></i> WIDGET 01 — RIGHT SIDEBAR CONSULTATION BOX</span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Sidebar Badge Text</label>
            <input type="text" name="side_cta_badge" class="visual-input-styled" value="<?= e($csSideCtaBadge) ?>" placeholder="Similar ROI">
          </div>
          <div>
            <label class="visual-label-upper">Sidebar Heading</label>
            <input type="text" name="side_cta_title" class="visual-input-styled" value="<?= e($csSideCtaTitle) ?>" placeholder="Ready to Scale Your Domain Authority?">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Sidebar Description</label>
          <textarea name="side_cta_desc" class="visual-input-styled" rows="2"><?= e($csSideCtaDesc) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label class="visual-label-upper">Button Text</label>
            <input type="text" name="side_cta_btn_text" class="visual-input-styled" value="<?= e($csSideCtaBtnText) ?>" placeholder="Request Scope Audit">
          </div>
          <div>
            <label class="visual-label-upper">Button URL</label>
            <input type="text" name="side_cta_btn_url" class="visual-input-styled" value="<?= e($csSideCtaBtnUrl) ?>" placeholder="contact.php">
          </div>
        </div>
      </div>

      <!-- 2. Detail Page Right Sidebar Newsletter Box -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-mail-line"></i> WIDGET 02 — RIGHT SIDEBAR EXECUTIVE PLAYBOOKS BRIEF</span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Brief Badge Text</label>
            <input type="text" name="side_news_badge" class="visual-input-styled" value="<?= e($csSideNewsBadge) ?>" placeholder="Executive Brief">
          </div>
          <div>
            <label class="visual-label-upper">Brief Heading</label>
            <input type="text" name="side_news_title" class="visual-input-styled" value="<?= e($csSideNewsTitle) ?>" placeholder="Get Our ROI Playbooks">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Brief Description</label>
          <textarea name="side_news_desc" class="visual-input-styled" rows="2"><?= e($csSideNewsDesc) ?></textarea>
        </div>

        <div>
          <label class="visual-label-upper">Subscribe Button Text</label>
          <input type="text" name="side_news_btn_text" class="visual-input-styled" value="<?= e($csSideNewsBtnText) ?>" placeholder="Get Free Playbooks">
        </div>
      </div>

      <!-- 3. Bottom CTA Banner -->
      <div style="background: #1B2A4A; color: #FFF; padding: 24px; border-radius: 16px; border: 1.5px dashed rgba(74,139,140,0.5); margin-bottom: 24px;">
        <span class="visual-badge" style="background: rgba(74,139,140,0.25); color: var(--wdr-teal-light); margin-bottom: 12px;">
          <i class="ri-sparkling-fill"></i> ARCHIVE BOTTOM CTA BANNER PREVIEW
        </span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Badge Text</label>
            <input type="text" name="cta_badge" class="visual-input-styled" value="<?= e($ctaBadge) ?>" placeholder="COMMERCIAL ROI AUDIT" style="background: #0F1E36; color: #FFF; border-color: #334155;">
          </div>
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Main Heading</label>
            <input type="text" name="cta_title" class="visual-input-styled" value="<?= e($ctaTitle) ?>" placeholder="Ready to Scale Your Domain Authority?" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper" style="color: #94A3B8;">Description Subtitle</label>
          <textarea name="cta_desc" class="visual-input-styled" rows="2" placeholder="Book a complimentary 30-minute content audit..." style="background: #0F1E36; color: #FFF; border-color: #334155;"><?= e($ctaDesc) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Action Button Text</label>
            <input type="text" name="cta_btn_text" class="visual-input-styled" value="<?= e($ctaBtnText) ?>" placeholder="Schedule Scope Consultation" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Action Button URL</label>
            <input type="text" name="cta_btn_url" class="visual-input-styled" value="<?= e($ctaBtnUrl) ?>" placeholder="contact.php" style="background: #0F1E36; color: #FFF; border-color: #334155;">
          </div>
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Sidebar &amp; Bottom CTA Settings</button>
    </form>
  </div>
  <?php endif; ?>

</div>
