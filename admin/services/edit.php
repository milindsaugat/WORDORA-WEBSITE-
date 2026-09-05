<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$devServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0');
$allServicesList = Service::getAll();
if (!$devServicesEnabled) {
    $allServicesList = array_values(array_filter($allServicesList, function($s) {
        return (int)($s['id'] ?? 0) <= 7;
    }));
}

$isNew = (isset($_GET['id']) && $_GET['id'] === 'new');
$id = isset($_GET['id']) && $_GET['id'] !== 'new' ? (int)$_GET['id'] : null;

// If no ID is specified and not creating new, default to the first service
if (!$id && !$isNew && !empty($allServicesList)) {
    $id = (int)$allServicesList[0]['id'];
}

$activeTab = $_GET['tab'] ?? 'sec01';
$validTabs = ['sec01', 'sec02', 'sec03', 'sec04', 'sec05', 'sec06', 'sec07', 'sec08'];
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'sec01';
}

$service = null;
if ($id) {
    $service = Service::getById($id);
    if (!$service && !$isNew) {
        flash_set('error', 'Service detail page not found.');
        redirect('admin/services/index.php');
    }
    // Block editing dev services when master toggle is OFF
    if ($service && (int)$service['id'] > 7 && !$devServicesEnabled) {
        flash_set('error', 'This service cannot be edited because Development & Design services are currently turned OFF in Homepage Section 3/3C.');
        redirect('admin/services/index.php');
    }
}

$adminTitle = $service ? 'Edit: ' . ($service['title'] ?? '') . ' — Service Detail Studio' : 'Add New Service Detail Studio';
$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $title = trim($_POST['title'] ?? ($service['title'] ?? ''));
        $rawSlug = trim($_POST['slug'] ?? ($service['slug'] ?? ''));
        $slug = !empty($rawSlug) ? slugify($rawSlug) : (!empty($title) ? slugify($title) : '');

        if (empty($title)) {
            $error = 'Service title is required.';
        } else {
            $uploader = new Upload('services', 52428800); // 50MB limit

            // 1. Handle Service Mockup Artwork (image_path)
            $existingImg = $service['image_path'] ?? '';
            $imagePath = $existingImg;
            if (isset($_FILES['service_img_file']) && $_FILES['service_img_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['service_img_file']);
                if ($upRes['success']) {
                    if (!empty($existingImg) && $existingImg !== $upRes['path']) {
                        delete_uploaded_file($existingImg);
                    }
                    $imagePath = $upRes['path'];
                } else {
                    $error = 'Service artwork upload error: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_service_img']) && $_POST['remove_service_img'] === '1') {
                delete_uploaded_file($existingImg);
                $imagePath = '';
            }

            // 2. Handle Single Image Hero Background (hero_image)
            $existingHeroImg = $service['hero_image'] ?? '';
            $heroImage = $existingHeroImg;
            if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['hero_image_file']);
                if ($upRes['success']) {
                    if (!empty($existingHeroImg) && $existingHeroImg !== $upRes['path']) {
                        delete_uploaded_file($existingHeroImg);
                    }
                    $heroImage = $upRes['path'];
                } else {
                    $error = 'Hero image upload error: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_hero_image']) && $_POST['remove_hero_image'] === '1') {
                delete_uploaded_file($existingHeroImg);
                $heroImage = '';
            }

            // Parse Capabilities Cards (What's Included Scope)
            $capabilitiesArr = [];
            if (!empty($_POST['capabilities']) && is_array($_POST['capabilities'])) {
                foreach ($_POST['capabilities'] as $c) {
                    if (!empty(trim($c['title'] ?? ''))) {
                        $capabilitiesArr[] = [
                            'icon'  => trim($c['icon'] ?? 'ri-quill-pen-line'),
                            'title' => trim($c['title']),
                            'desc'  => trim($c['desc'] ?? ''),
                            'badge' => trim($c['badge'] ?? 'VERIFIED SCOPE'),
                        ];
                    }
                }
            }
            $capabilitiesJson = !empty($capabilitiesArr) ? json_encode($capabilitiesArr, JSON_UNESCAPED_UNICODE) : ($service['bullets'] ?? null);

            // Parse Why Matters (3 Pillars)
            $whyMattersArr = [];
            if (!empty($_POST['why_matters']) && is_array($_POST['why_matters'])) {
                foreach ($_POST['why_matters'] as $wm) {
                    if (!empty($wm['title'])) {
                        $whyMattersArr[] = [
                            'icon'  => trim($wm['icon'] ?? 'ri-shield-check-line'),
                            'title' => trim($wm['title']),
                            'desc'  => trim($wm['desc'] ?? ''),
                        ];
                    }
                }
            }
            $whyMattersJson = !empty($whyMattersArr) ? json_encode($whyMattersArr, JSON_UNESCAPED_UNICODE) : ($service['why_matters'] ?? null);

            // Parse Who For (4 Personas)
            $whoForArr = [];
            if (!empty($_POST['who_for']) && is_array($_POST['who_for'])) {
                foreach ($_POST['who_for'] as $wf) {
                    if (!empty($wf['role'])) {
                        $whoForArr[] = [
                            'role' => trim($wf['role']),
                            'desc' => trim($wf['desc'] ?? ''),
                        ];
                    }
                }
            }
            $whoForJson = !empty($whoForArr) ? json_encode($whoForArr, JSON_UNESCAPED_UNICODE) : ($service['who_for'] ?? null);

            // Parse Process Steps (4 Framework Steps)
            $processArr = [];
            if (!empty($_POST['process_steps']) && is_array($_POST['process_steps'])) {
                foreach ($_POST['process_steps'] as $ps) {
                    if (!empty($ps['title'])) {
                        $processArr[] = [
                            'num'   => trim($ps['num'] ?? '01'),
                            'title' => trim($ps['title']),
                            'desc'  => trim($ps['desc'] ?? ''),
                        ];
                    }
                }
            }
            $processJson = !empty($processArr) ? json_encode($processArr, JSON_UNESCAPED_UNICODE) : ($service['process_steps'] ?? null);

            // Parse FAQs (5 Dynamic Questions)
            $faqsArr = [];
            if (!empty($_POST['faqs']) && is_array($_POST['faqs'])) {
                foreach ($_POST['faqs'] as $f) {
                    if (!empty($f['q'])) {
                        $faqsArr[] = [
                            'q' => trim($f['q']),
                            'a' => trim($f['a'] ?? ''),
                        ];
                    }
                }
            }
            $faqsJson = !empty($faqsArr) ? json_encode($faqsArr, JSON_UNESCAPED_UNICODE) : ($service['faqs'] ?? null);

            $isActive = ($activeTab === 'sec08' || ($_POST['tab_scope'] ?? '') === 'sec08')
                ? (isset($_POST['is_active']) ? 1 : 0)
                : (int)($service['is_active'] ?? 1);

            if (empty($error)) {
                $data = [
                    'title'           => $title,
                    'slug'            => $slug,
                    'tag'             => isset($_POST['tag']) ? trim($_POST['tag']) : ($service['tag'] ?? 'Core Capability'),
                    'icon'            => isset($_POST['icon']) ? trim($_POST['icon']) : ($service['icon'] ?? 'ri-quill-pen-line'),
                    'description'     => isset($_POST['description']) ? trim($_POST['description']) : ($service['description'] ?? ''),
                    'bullets'         => isset($_POST['capabilities']) ? $capabilitiesJson : ($service['bullets'] ?? ''),
                    'deliverables'    => isset($_POST['deliverables']) ? trim($_POST['deliverables']) : ($service['deliverables'] ?? ''),
                    'metrics_val'     => isset($_POST['metrics_val']) ? trim($_POST['metrics_val']) : ($service['metrics_val'] ?? '+400%'),
                    'metrics_lbl'     => isset($_POST['metrics_lbl']) ? trim($_POST['metrics_lbl']) : ($service['metrics_lbl'] ?? 'Growth Lift'),
                    'image_path'      => $imagePath,
                    'sort_order'      => isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : (int)($service['sort_order'] ?? 0),
                    'is_active'       => $isActive,
                    'hero_headline'   => isset($_POST['hero_headline']) ? trim($_POST['hero_headline']) : ($service['hero_headline'] ?? ''),
                    'hero_intro'      => isset($_POST['hero_intro']) ? trim($_POST['hero_intro']) : ($service['hero_intro'] ?? ''),
                    'hero_image'      => $heroImage,
                    'what_we_do_lead' => isset($_POST['what_we_do_lead']) ? trim($_POST['what_we_do_lead']) : ($service['what_we_do_lead'] ?? ''),
                    'why_matters'     => $whyMattersJson,
                    'who_for'         => $whoForJson,
                    'process_steps'   => $processJson,
                    'faqs'            => $faqsJson,
                    'cta_title'       => isset($_POST['cta_title']) ? trim($_POST['cta_title']) : ($service['cta_title'] ?? ''),
                    'cta_desc'        => isset($_POST['cta_desc']) ? trim($_POST['cta_desc']) : ($service['cta_desc'] ?? ''),
                    'cta_btn_text'    => isset($_POST['cta_btn_text']) ? trim($_POST['cta_btn_text']) : ($service['cta_btn_text'] ?? 'Start a Conversation'),
                    'cta_btn_url'     => isset($_POST['cta_btn_url']) ? trim($_POST['cta_btn_url']) : ($service['cta_btn_url'] ?? 'contact.php'),
                ];

                $savedId = Service::save($data, $id);
                flash_set('success', 'Service Detail Page updated & published successfully!');
                redirect('admin/services/edit.php?id=' . $savedId . '&tab=' . $activeTab);
            }
        }
    }
}

// Decode existing JSON fields with structured fallbacks
$currentCapabilities = [];
if (!empty($service['bullets'])) {
    $decodedCaps = json_decode($service['bullets'], true);
    if (is_array($decodedCaps) && !empty($decodedCaps)) {
        $currentCapabilities = $decodedCaps;
    } else {
        $parts = array_filter(array_map('trim', explode(';', (string)$service['bullets'])));
        $iconsList = ['ri-quill-pen-line', 'ri-file-text-line', 'ri-layout-masonry-line', 'ri-search-eye-line', 'ri-focus-3-line', 'ri-sparkling-fill', 'ri-shield-star-line', 'ri-compass-3-line'];
        foreach ($parts as $idx => $pt) {
            $currentCapabilities[] = [
                'icon'  => $iconsList[$idx % count($iconsList)],
                'title' => $pt,
                'desc'  => 'Deeply researched, drafted to institutional standards, and fully aligned with your audience expectations.',
                'badge' => 'VERIFIED SCOPE',
            ];
        }
    }
}
if (empty($currentCapabilities)) {
    $defaultCapTitles = [
        'Strategic Topic Clustering', 'Search Intent Alignment', 'Expert Primary Research',
        'Comprehensive Outline Architecture', 'Human Domain Craftsmanship', 'Multi-Layer Editorial Review',
        'Fact-Checking & Citations', 'CMS-Ready Formatting & Polish'
    ];
    $iconsList = ['ri-quill-pen-line', 'ri-file-text-line', 'ri-layout-masonry-line', 'ri-search-eye-line', 'ri-focus-3-line', 'ri-sparkling-fill', 'ri-shield-star-line', 'ri-compass-3-line'];
    foreach ($defaultCapTitles as $idx => $title) {
        $currentCapabilities[] = [
            'icon'  => $iconsList[$idx % count($iconsList)],
            'title' => $isNew ? '' : $title,
            'desc'  => $isNew ? '' : 'Deeply researched, drafted to institutional standards, and fully aligned with your audience expectations.',
            'badge' => 'VERIFIED SCOPE',
        ];
    }
}

$currentWhyMatters = !empty($service['why_matters']) ? json_decode($service['why_matters'], true) : [];
if (empty($currentWhyMatters)) {
    if ($isNew) {
        $currentWhyMatters = [
            ['title' => '', 'desc' => '', 'icon' => 'ri-shield-check-line'],
            ['title' => '', 'desc' => '', 'icon' => 'ri-line-chart-line'],
            ['title' => '', 'desc' => '', 'icon' => 'ri-funds-box-line'],
        ];
    } else {
        $currentWhyMatters = [
            ['title' => 'Compound Organic Traffic', 'desc' => 'Build high-authority topic clusters that generate qualified inbound leads 24/7.', 'icon' => 'ri-line-chart-line'],
            ['title' => 'Google Helpful Content Proof', 'desc' => '100% human-researched and cited. Immune to algorithmic updates and AI penalties.', 'icon' => 'ri-shield-check-line'],
            ['title' => 'Pipeline Conversion Focus', 'desc' => 'Every article includes strategic internal links, CTA placements, and conversion pathways.', 'icon' => 'ri-funds-box-line'],
        ];
    }
}

$currentWhoFor = !empty($service['who_for']) ? json_decode($service['who_for'], true) : [];
if (empty($currentWhoFor)) {
    if ($isNew) {
        $currentWhoFor = [
            ['role' => '', 'desc' => ''],
            ['role' => '', 'desc' => ''],
            ['role' => '', 'desc' => ''],
            ['role' => '', 'desc' => ''],
        ];
    } else {
        $currentWhoFor = [
            ['role' => 'B2B SaaS Companies', 'desc' => 'Scaling organic demo requests and outranking entrenched enterprise competitors.'],
            ['role' => 'E-Commerce Brands', 'desc' => 'Dominating high-intent transactional search terms and category buying guides.'],
            ['role' => 'Growth Startups', 'desc' => 'Establishing category authority and lowering customer acquisition costs.'],
            ['role' => 'Digital Marketing Agencies', 'desc' => 'White-label editorial support that delivers verified ranking improvements.'],
        ];
    }
}

$currentProcess = !empty($service['process_steps']) ? json_decode($service['process_steps'], true) : [];
if (empty($currentProcess)) {
    if ($isNew) {
        $currentProcess = [
            ['num' => '01', 'title' => '', 'desc' => ''],
            ['num' => '02', 'title' => '', 'desc' => ''],
            ['num' => '03', 'title' => '', 'desc' => ''],
            ['num' => '04', 'title' => '', 'desc' => ''],
        ];
    } else {
        $currentProcess = [
            ['num' => '01', 'title' => 'Search Intent & Audit', 'desc' => 'Competitor keyword gap analysis and search intent mapping before drafting.'],
            ['num' => '02', 'title' => 'Topic Cluster Blueprint', 'desc' => 'Structuring pillar guides, supporting clusters, and schema metadata.'],
            ['num' => '03', 'title' => 'Editorial Craftsmanship', 'desc' => 'Senior writers draft in-depth, authoritative guides with zero AI fluff.'],
            ['num' => '04', 'title' => 'On-Page Polish', 'desc' => 'Meta tags, internal linking anchors, and conversion call-to-actions.'],
        ];
    }
}

$currentFaqs = !empty($service['faqs']) ? json_decode($service['faqs'], true) : [];
if (empty($currentFaqs)) {
    if ($isNew) {
        $currentFaqs = [
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
            ['q' => '', 'a' => ''],
        ];
    } else {
        $currentFaqs = [
            ['q' => 'How do you choose target keywords for our articles?', 'a' => 'We conduct extensive competitor keyword gap analyses and search intent audits using enterprise SEO tooling.'],
            ['q' => 'Are all articles 100% written by humans?', 'a' => 'Yes, unconditionally. Every article is written by experienced domain writers and rigorously fact-checked.'],
            ['q' => 'Do you include on-page SEO, meta schemas, and formatting?', 'a' => 'Yes. Every deliverable includes optimized title tags, meta descriptions, and schema suggestions.'],
            ['q' => 'What is the typical turnaround time?', 'a' => 'Standard long-form articles are delivered within 5 to 7 business days.'],
            ['q' => 'How many revisions are included in the scope?', 'a' => 'Every project includes two full rounds of editorial revisions within 14 days of delivery.'],
        ];
    }
}

include ROOT_PATH . '/admin/includes/header.php';
?>

<!-- Google Fonts for Identical Visual Match with Homepage & Who We Are -->
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

.service-switcher-pill {
  padding: 8px 14px;
  border-radius: 10px;
  font-size: 12.5px;
  font-weight: 700;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.service-switcher-pill.active {
  background: var(--wdr-navy);
  color: #FFFFFF;
  box-shadow: 0 4px 14px rgba(15, 30, 54, 0.15);
}
.service-switcher-pill:not(.active) {
  background: #FFFFFF;
  color: var(--wdr-navy);
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
}
.service-switcher-pill:not(.active):hover {
  border-color: var(--wdr-teal);
  background: rgba(74, 139, 140, 0.08);
}
</style>

<!-- Studio Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-family: var(--wdr-font-display); font-size: 26px; font-weight: 700; color: var(--wdr-navy); margin: 0;">
      <i class="ri-file-list-3-line" style="color: var(--wdr-teal);"></i> <?= $service ? 'Editing: ' . e($service['title']) : 'Create New Service Detail Page' ?>
    </h2>
    <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
      Complete 8-section visual studio for this individual service with Single Image hero option, narrative, pillars, personas, process, and FAQs.
    </p>
  </div>
  <div style="display: flex; gap: 10px; align-items: center;">
    <?php if ($service && !empty($service['slug'])): ?>
      <a href="<?= url('service/' . urlencode($service['slug'])) ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Detail Page
      </a>
    <?php endif; ?>
    <a href="<?= url('admin/services/index.php') ?>" class="btn-adm btn-adm-outline">
      <i class="ri-list-check"></i> All Services Table
    </a>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     TOP SERVICE SWITCHER BAR (ALL 7 SERVICES DIRECT ACCESS)
     ═══════════════════════════════════════════ -->
<div style="background: #FFFFFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 16px 20px; margin-bottom: 24px;">
  <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
    <div style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); text-transform: uppercase; letter-spacing: 0.08em; display: flex; align-items: center; gap: 6px;">
      <i class="ri-folder-shared-line"></i> Select Any Service To Edit:
    </div>
    <a href="?id=new" class="service-switcher-pill <?= $isNew ? 'active' : '' ?>" style="font-size: 11.5px; padding: 5px 12px;">
      <i class="ri-add-line"></i> + Add New Discipline
    </a>
  </div>

  <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
    <?php foreach ($allServicesList as $sItem): 
        $isCurrent = ($service && (int)$sItem['id'] === (int)$service['id'] && !$isNew);
    ?>
      <a href="?id=<?= $sItem['id'] ?>&tab=<?= $activeTab ?>" class="service-switcher-pill <?= $isCurrent ? 'active' : '' ?>">
        <i class="<?= e($sItem['icon'] ?: 'ri-quill-pen-line') ?>" style="color: <?= $isCurrent ? 'var(--wdr-teal-light)' : 'var(--wdr-teal)' ?>;"></i>
        <?= e($sItem['title']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!empty($success)): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; display: flex; align-items: center; gap: 10px;">
    <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #16A34A;"></i>
    <span><?= e($success) ?></span>
    <?php if ($service && !empty($service['slug'])): ?>
      <a href="<?= url('service/' . urlencode($service['slug'])) ?>" target="_blank" style="margin-left: auto; font-size: 12px; text-decoration: underline; color: #166534; font-weight: 700;">View Live Page <i class="ri-external-link-line"></i></a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
  <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
    <i class="ri-error-warning-line"></i> <?= e($error) ?>
  </div>
<?php endif; ?>

<!-- 8 Visual Section Navigation Tabs -->
<!-- 8 Visual Section Navigation Tabs (1:1 Exact Sequence Matching Live Frontend Page) -->
<?php
$serviceTabs = [
    'sec01' => ['num' => '01', 'name' => 'Hero Cover (Single Image)', 'icon' => 'ri-image-line'],
    'sec02' => ['num' => '02', 'name' => 'What We Do (Narrative & Artwork)', 'icon' => 'ri-article-line'],
    'sec03' => ['num' => '03', 'name' => "What's Included (Capabilities)", 'icon' => 'ri-list-check-2'],
    'sec04' => ['num' => '04', 'name' => 'Production Process (4 Stages)', 'icon' => 'ri-node-tree'],
    'sec05' => ['num' => '05', 'name' => 'Why It Matters (3 Pillars)', 'icon' => 'ri-shield-star-line'],
    'sec06' => ['num' => '06', 'name' => 'Who It Is For (4 Personas)', 'icon' => 'ri-user-star-line'],
    'sec07' => ['num' => '07', 'name' => 'Service FAQs (5 Questions)', 'icon' => 'ri-questionnaire-line'],
    'sec08' => ['num' => '08', 'name' => 'Bottom CTA & Status', 'icon' => 'ri-send-plane-fill'],
];
$tabQueryId = $isNew ? 'new' : ($service['id'] ?? '');
?>
<div style="display: flex; gap: 8px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;">
  <?php foreach ($serviceTabs as $k => $t): 
      $isAct = ($activeTab === $k);
  ?>
  <a href="?id=<?= $tabQueryId ?>&tab=<?= $k ?>" style="padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; transition: all 0.2s; <?= $isAct ? 'background: var(--wdr-navy); color: #FFF; box-shadow: 0 4px 14px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--wdr-navy); border: 1.5px solid var(--admin-border);' ?>">
    <span style="display: inline-block; width: 22px; height: 22px; border-radius: 6px; background: <?= $isAct ? 'var(--wdr-teal)' : 'var(--wdr-teal-pale)' ?>; color: <?= $isAct ? '#FFF' : 'var(--wdr-teal)' ?>; font-size: 11px; font-weight: 800; line-height: 22px; text-align: center;"><?= $t['num'] ?></span>
    <i class="<?= $t['icon'] ?>"></i> <?= $t['name'] ?>
  </a>
  <?php endforeach; ?>
</div>

<form method="POST" action="?id=<?= $tabQueryId ?>&tab=<?= $activeTab ?>" enctype="multipart/form-data">
  <?= CSRF::field() ?>

  <!-- ═══════════════════════════════════════════
       TAB 01: HERO COVER (SINGLE IMAGE HERO OPTION)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec01'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-image-line"></i> SECTION 01 — HERO COVER (SINGLE IMAGE HERO)</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Service Detail Hero Section (Single Image Option)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Dedicated single image background artwork, primary headline, and strategic value proposition for this service.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      
      <!-- Left Column: Copy & Settings -->
      <div>
        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px; margin-bottom: 16px;">
          <div>
            <label class="visual-label-upper">Service Title <span style="color:#ef4444;">*</span></label>
            <input type="text" name="title" class="visual-input-styled" required value="<?= e($service['title'] ?? '') ?>" placeholder="e.g. SEO Content Writing" style="font-weight: 700; font-size: 15px;">
          </div>
          <div>
            <label class="visual-label-upper">URL Slug / Identifier</label>
            <input type="text" name="slug" class="visual-input-styled" value="<?= e($service['slug'] ?? '') ?>" placeholder="e.g. seo-content">
            <?php if (!empty($service['slug'])): ?>
            <div style="margin-top: 4px; font-size: 11px; color: var(--admin-muted); font-family: var(--wdr-font-mono);">
              URL: <a href="<?= url('service/' . urlencode($service['slug'])) ?>" target="_blank" style="color: var(--wdr-teal); text-decoration: underline; font-weight: 600;">/service/<?= e($service['slug']) ?> <i class="ri-external-link-line"></i></a>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Category Tag / Badge</label>
          <input type="text" name="tag" class="visual-input-styled" value="<?= e($service['tag'] ?? 'Core Editorial Discipline') ?>" placeholder="e.g. Search Engine Optimization">
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Hero Main Headline</label>
          <input type="text" name="hero_headline" class="visual-input-styled" value="<?= e($service['hero_headline'] ?? '') ?>" placeholder="e.g. Search-Dominant Content That Turns Traffic into Pipeline" style="font-weight: 700; font-size: 16px;">
        </div>

        <div style="margin-bottom: 20px;">
          <label class="visual-label-upper">Hero Subtitle / Lead Introduction</label>
          <textarea name="hero_intro" class="visual-input-styled" rows="3" placeholder="Topic cluster architecture, search-intent optimization, and rigorous long-form guides engineered to capture page-one rankings..."><?= e($service['hero_intro'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
          <div>
            <label class="visual-label-upper">CTA Button 1 (Text &amp; URL)</label>
            <input type="text" name="cta_btn_text" class="visual-input-styled" value="<?= e($service['cta_btn_text'] ?? 'Start a Conversation') ?>" placeholder="Button Text" style="margin-bottom: 6px;">
            <input type="text" name="cta_btn_url" class="visual-input-styled" value="<?= e($service['cta_btn_url'] ?? 'contact.php') ?>" placeholder="Button URL">
          </div>
          <div>
            <label class="visual-label-upper">CTA Button 2 (Text &amp; URL)</label>
            <input type="text" value="Explore All Services" class="visual-input-styled" style="margin-bottom: 6px; background: #F8FAFC;" disabled>
            <input type="text" value="services.php" class="visual-input-styled" style="background: #F8FAFC;" disabled>
          </div>
        </div>
      </div>

      <!-- Right Column: Single Image Hero Background Frame -->
      <div>
        <div class="visual-media-frame">
          <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-add-line"></i> Single Image Hero Artwork</label>
          <?php 
          $serviceIllustrations = [
              'seo-content' => 'Blog service.png',
              'social-media-content' => 'social media service.png',
              'technical-writing' => 'service treasure.png',
              'brand-copy' => 'brand content.png',
              'thought-leadership' => 'servcie page.png',
              'academic-writing' => 'acedmic.png',
              'blog-writing' => 'blog.png',
          ];
          $curSlug = $service['slug'] ?? '';
          $defaultIllustration = $serviceIllustrations[$curSlug] ?? 'home section 2.png';
          $hasCustomHero = !empty($service['hero_image']);
          $resolvedHeroImg = $hasCustomHero 
              ? $service['hero_image'] 
              : (!empty($service['image_path']) ? $service['image_path'] : 'img/' . $defaultIllustration);
          ?>
          
          <div id="preview_hero_wrap" style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #0F1E36;">
            <img id="preview_hero_img" src="<?= media_url($resolvedHeroImg) ?>" alt="Hero Artwork" style="max-height: 180px; width: 100%; object-fit: cover; display: block;">
            <div style="position: absolute; bottom: 8px; left: 8px; z-index: 2;">
              <?php if ($hasCustomHero): ?>
                <span class="badge badge-teal" style="font-size: 11px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">Custom Hero Uploaded</span>
              <?php else: ?>
                <span class="badge" style="background: rgba(15,30,54,0.85); color: #FFF; font-size: 11px; border: 1px solid rgba(255,255,255,0.2);">Default Service Artwork</span>
              <?php endif; ?>
            </div>
          </div>

          <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload Single Hero Image (PNG / JPG / WEBP)</label>
            <input type="file" name="hero_image_file" class="visual-input-styled" accept="image/*">
            <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
            <?php if ($hasCustomHero): ?>
              <button type="button" onclick="document.getElementById('remove_hero_image').value='1'; document.getElementById('preview_hero_img').src='<?= media_url('img/' . $defaultIllustration) ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600; cursor: pointer;">
                <i class="ri-delete-bin-line"></i> Revert to Default Artwork
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Hero Section</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 02: WHAT WE DO (NARRATIVE, METRICS & ARTWORK)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec02'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-article-line"></i> SECTION 02 — WHAT WE DO INTRODUCTION</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">What We Do (Detailed Introduction &amp; Artwork)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Lead bold paragraph, in-depth editorial narrative, performance metrics strip, and service mockup artwork.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
      <div>
        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Lead Bold Paragraph</label>
          <textarea name="what_we_do_lead" class="visual-input-styled" rows="2" placeholder="We don’t write generic keyword-stuffed articles. We engineer search-dominant content assets..."><?= e($service['what_we_do_lead'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom: 16px;">
          <label class="visual-label-upper">Full In-Depth Narrative Description</label>
          <textarea name="description" class="visual-input-styled" rows="5" placeholder="Every piece begins with semantic keyword clustering, competitor search-gap audits..."><?= e($service['description'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #FAF8F5; padding: 16px; border-radius: 12px; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
          <div>
            <label class="visual-label-upper">Metrics Lift Value</label>
            <input type="text" name="metrics_val" class="visual-input-styled" value="<?= e($service['metrics_val'] ?? '+400%') ?>" placeholder="e.g. +420% or 4.2x" style="font-weight: 700; font-size: 15px;">
          </div>
          <div>
            <label class="visual-label-upper">Metrics Impact Label</label>
            <input type="text" name="metrics_lbl" class="visual-input-styled" value="<?= e($service['metrics_lbl'] ?? 'Target Growth Lift') ?>" placeholder="e.g. Organic Search Lift">
          </div>
        </div>
      </div>

      <!-- Right Column: Service Mockup Artwork Frame -->
      <div>
        <div class="visual-media-frame">
          <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-line"></i> Service Master Artwork Mockup</label>
          <?php 
          $serviceIllustrations = [
              'seo-content' => 'Blog service.png',
              'social-media-content' => 'social media service.png',
              'technical-writing' => 'service treasure.png',
              'brand-copy' => 'brand content.png',
              'thought-leadership' => 'servcie page.png',
              'academic-writing' => 'acedmic.png',
              'blog-writing' => 'blog.png',
          ];
          $curSlug = $service['slug'] ?? '';
          $defaultIllustration = $serviceIllustrations[$curSlug] ?? 'home section 2.png';
          $hasCustomArt = !empty($service['image_path']);
          $resolvedArtImg = $hasCustomArt ? $service['image_path'] : 'img/' . $defaultIllustration;
          ?>
          
          <div id="preview_art_wrap" style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #FAF8F5;">
            <img id="preview_art_img" src="<?= media_url($resolvedArtImg) ?>" alt="Artwork" style="max-height: 180px; width: 100%; object-fit: contain; display: block; margin: 0 auto; padding: 6px;">
            <div style="position: absolute; bottom: 8px; left: 8px; z-index: 2;">
              <?php if ($hasCustomArt): ?>
                <span class="badge badge-teal" style="font-size: 11px; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">Custom Artwork</span>
              <?php else: ?>
                <span class="badge" style="background: rgba(15,30,54,0.85); color: #FFF; font-size: 11px; border: 1px solid rgba(255,255,255,0.2);">Default Service Artwork</span>
              <?php endif; ?>
            </div>
          </div>

          <div style="text-align: left; background: #FAF8F5; padding: 14px; border-radius: 12px; border: 1px dashed rgba(74, 139, 140, 0.35);">
            <label style="font-size: 11px; font-weight: 700; color: var(--wdr-navy); display: block; margin-bottom: 4px;">Upload Service Artwork</label>
            <input type="file" name="service_img_file" class="visual-input-styled" accept="image/*">
            <input type="hidden" name="remove_service_img" id="remove_service_img" value="0">
            <?php if ($hasCustomArt): ?>
              <button type="button" onclick="document.getElementById('remove_service_img').value='1'; document.getElementById('preview_art_img').src='<?= media_url('img/' . $defaultIllustration) ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600; cursor: pointer;">
                <i class="ri-delete-bin-line"></i> Revert to Default Artwork
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save What We Do Section</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 03: WHAT'S INCLUDED (CORE CAPABILITIES & SCOPE CARDS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec03'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-list-check-2"></i> SECTION 03 — WHAT'S INCLUDED (CORE CAPABILITIES)</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Core Capabilities &amp; Scope Grid Cards</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Interactive visual editor for all capability cards displayed in the 8-grid scope section of this service detail page.</p>
    </div>

    <!-- Capabilities Cards Grid in Admin -->
    <div id="capabilities_container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 24px;">
      <?php foreach ($currentCapabilities as $idx => $cap): ?>
        <div class="cap-card-item" style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px; position: relative;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px dashed rgba(74, 139, 140, 0.25); padding-bottom: 8px;">
            <span style="font-family: var(--wdr-font-mono); font-size: 11px; font-weight: 800; color: var(--wdr-teal); text-transform: uppercase;">
              <i class="ri-checkbox-circle-fill"></i> Capability Card #<?= sprintf('%02d', $idx + 1) ?>
            </span>
            <input type="text" name="capabilities[<?= $idx ?>][badge]" class="visual-input-styled" value="<?= e($cap['badge'] ?? 'VERIFIED SCOPE') ?>" placeholder="Card Scope Tag" style="width: 140px; font-size: 11px; padding: 4px 8px; font-weight: 700; text-align: right; text-transform: uppercase; font-family: var(--wdr-font-mono);">
          </div>

          <div style="display: grid; grid-template-columns: 120px 1fr; gap: 12px; margin-bottom: 12px;">
            <div>
              <label class="visual-label-upper">RemixIcon</label>
              <input type="text" name="capabilities[<?= $idx ?>][icon]" class="visual-input-styled" value="<?= e($cap['icon'] ?? 'ri-quill-pen-line') ?>" placeholder="e.g. ri-quill-pen-line" style="font-size: 12px;">
            </div>
            <div>
              <label class="visual-label-upper">Capability Title <span style="color:#ef4444;">*</span></label>
              <input type="text" name="capabilities[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($cap['title'] ?? '') ?>" placeholder="e.g. Strategic Topic Clustering" style="font-weight: 700;">
            </div>
          </div>

          <div>
            <label class="visual-label-upper">Card Description / In-Depth Scope</label>
            <textarea name="capabilities[<?= $idx ?>][desc]" class="visual-input-styled" rows="2" placeholder="Deeply researched, drafted to institutional standards..."><?= e($cap['desc'] ?? '') ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Deliverables & Discipline Badges -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px; margin-bottom: 24px;">
      <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 16px;">
        <div>
          <label class="visual-label-upper">Deliverables Summary (Short Scope Tagline)</label>
          <input type="text" name="deliverables" class="visual-input-styled" value="<?= e($service['deliverables'] ?? '') ?>" placeholder="e.g. Topic cluster blueprints, on-page SEO schemas, publication-ready markdown">
        </div>
        <div>
          <label class="visual-label-upper">Discipline Category Badge</label>
          <input type="text" name="tag" class="visual-input-styled" value="<?= e($service['tag'] ?? 'Core Capability') ?>">
        </div>
      </div>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Core Capabilities Cards</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 04: OUR PRODUCTION PROCESS (4-STAGE PROCESS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec04'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-node-tree"></i> SECTION 04 — PRODUCTION PROCESS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">The 4-Stage Production Framework</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Step-by-step milestone workflow from initial brief discovery to final CMS-ready delivery.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
      <?php foreach ($currentProcess as $idx => $st): ?>
        <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
          <div style="display: flex; gap: 10px; margin-bottom: 10px;">
            <input type="text" name="process_steps[<?= $idx ?>][num]" class="visual-input-styled" value="<?= e($st['num'] ?? sprintf('%02d', $idx + 1)) ?>" style="width: 60px; text-align: center; font-weight: 800; font-family: var(--wdr-font-mono);">
            <input type="text" name="process_steps[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($st['title'] ?? '') ?>" placeholder="Step Title" style="flex: 1; font-weight: 700;">
          </div>
          <textarea name="process_steps[<?= $idx ?>][desc]" class="visual-input-styled" rows="2" placeholder="Step description..."><?= e($st['desc'] ?? '') ?></textarea>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Production Process</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 05: WHY THIS DISCIPLINE MATTERS (3 IMPACT PILLARS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec05'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-shield-star-line"></i> SECTION 05 — WHY IT MATTERS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">3 Core Strategic Impact Pillars</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">3 distinct value cards explaining why this specific service generates compounding commercial ROI.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
      <?php foreach ($currentWhyMatters as $idx => $wm): ?>
        <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Pillar <?= $idx + 1 ?> RemixIcon</label>
            <input type="text" name="why_matters[<?= $idx ?>][icon]" class="visual-input-styled" value="<?= e($wm['icon'] ?? 'ri-shield-check-line') ?>" placeholder="e.g. ri-line-chart-line">
          </div>
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Pillar <?= $idx + 1 ?> Title</label>
            <input type="text" name="why_matters[<?= $idx ?>][title]" class="visual-input-styled" value="<?= e($wm['title'] ?? '') ?>" placeholder="Title" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Pillar <?= $idx + 1 ?> Description</label>
            <textarea name="why_matters[<?= $idx ?>][desc]" class="visual-input-styled" rows="3" placeholder="Description..."><?= e($wm['desc'] ?? '') ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Why It Matters Pillars</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 06: WHO THIS SERVICE IS BUILT FOR (4 PERSONAS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec06'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-user-star-line"></i> SECTION 06 — TARGET AUDIENCE PERSONAS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Who This Service Is Built For (4 Personas)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">4 persona cards articulating the exact buyer personas, founders, or enterprise teams who need this discipline.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
      <?php foreach ($currentWhoFor as $idx => $wf): ?>
        <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 14px; padding: 20px;">
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Target Persona #<?= $idx + 1 ?> (Role / Organization)</label>
            <input type="text" name="who_for[<?= $idx ?>][role]" class="visual-input-styled" value="<?= e($wf['role'] ?? '') ?>" placeholder="e.g. B2B SaaS Companies" style="font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper">Persona Mandate &amp; Benefit</label>
            <textarea name="who_for[<?= $idx ?>][desc]" class="visual-input-styled" rows="2" placeholder="Scaling organic product demo requests..."><?= e($wf['desc'] ?? '') ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Target Personas</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 07: FREQUENTLY ASKED QUESTIONS (FAQS)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec07'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-questionnaire-line"></i> SECTION 07 — SERVICE FAQS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Service-Specific Frequently Asked Questions</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">5 dedicated questions and authoritative answers directly resolving client objections for this service.</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px;">
      <?php foreach ($currentFaqs as $idx => $f): ?>
        <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 12px; padding: 18px;">
          <div style="margin-bottom: 10px;">
            <label class="visual-label-upper">Question #<?= $idx + 1 ?></label>
            <input type="text" name="faqs[<?= $idx ?>][q]" class="visual-input-styled" style="font-weight: 700;" value="<?= e($f['q'] ?? '') ?>">
          </div>
          <div>
            <label class="visual-label-upper">Answer Narrative</label>
            <textarea name="faqs[<?= $idx ?>][a]" class="visual-input-styled" rows="3"><?= e($f['a'] ?? '') ?></textarea>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Service FAQs</button>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 08: BOTTOM CTA SIGNATURE & PAGE STATUS
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec08'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-send-plane-fill"></i> SECTION 08 — BOTTOM CTA &amp; STATUS</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Start a Conversation (Bottom CTA Signature &amp; Status)</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Signature conversion banner closing the service detail page, sort order, and active published visibility toggle.</p>
    </div>

    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <div style="margin-bottom: 16px;">
        <label class="visual-label-upper">CTA Headline</label>
        <input type="text" name="cta_title" class="visual-input-styled" value="<?= e($service['cta_title'] ?? ('Ready to elevate your ' . strtolower($service['title'] ?? 'content') . '?')) ?>" style="font-weight: 700; font-size: 16px;">
      </div>

      <div style="margin-bottom: 20px;">
        <label class="visual-label-upper">CTA Supporting Description</label>
        <textarea name="cta_desc" class="visual-input-styled" rows="2"><?= e($service['cta_desc'] ?? 'Tell us about your brand, goals, and upcoming requirements. We\'ll deliver a tailored proposal within 24 hours.') ?></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
        <div>
          <label class="visual-label-upper">Primary Button Text</label>
          <input type="text" name="cta_btn_text" class="visual-input-styled" value="<?= e($service['cta_btn_text'] ?? 'Start a Conversation') ?>">
        </div>
        <div>
          <label class="visual-label-upper">Primary Button URL</label>
          <input type="text" name="cta_btn_url" class="visual-input-styled" value="<?= e($service['cta_btn_url'] ?? ('contact.php?service=' . urlencode($service['title'] ?? ''))) ?>">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 16px; border-top: 1px dashed rgba(74, 139, 140, 0.35);">
        <div>
          <label class="visual-label-upper">Display Sort Order</label>
          <input type="number" name="sort_order" class="visual-input-styled" value="<?= (int)($service['sort_order'] ?? 1) ?>" min="0" max="99">
        </div>
        <div style="display: flex; align-items: center; padding-top: 24px;">
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 700; color: var(--wdr-navy);">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($service) || !empty($service['is_active'])) ? 'checked' : '' ?> style="width: 20px; height: 20px; accent-color: var(--wdr-teal);">
            <span>Service Page is Active &amp; Published</span>
          </label>
        </div>
      </div>
    </div>

    <input type="hidden" name="tab_scope" value="sec08">
    <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Bottom CTA &amp; Status</button>
  </div>
  <?php endif; ?>

</form>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
