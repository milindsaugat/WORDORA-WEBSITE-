<?php
/**
 * WORDORA — Complete SEO & Meta Tag Manager for All Pages
 * Supports Core Pages, Service Detail Pages, Case Studies, and Blog Articles.
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
Auth::requireAuth();

$adminTitle = 'SEO & Meta Tag Manager for All Pages';
$error = '';

// ── 1. CORE PUBLIC PAGES ──
$corePages = [
    'home' => [
        'category'      => 'core',
        'name'          => 'Home Page',
        'type_label'    => 'Main Landing',
        'url'           => '/',
        'icon'          => 'ri-home-4-line',
        'default_title' => 'WORDORA — Words That Work. Stories That Sell.',
        'default_desc'  => 'Professional content writing and editorial services that convert readers into clients.',
        'default_kw'    => 'content writing agency, SEO content strategy, enterprise ghostwriting, B2B copywriting',
        'default_og'    => '/img/wordorga logo.png'
    ],
    'who_we_are' => [
        'category'      => 'core',
        'name'          => 'Who We Are',
        'type_label'    => 'Brand & Editorial Guild',
        'url'           => '/who-we-are',
        'icon'          => 'ri-team-line',
        'default_title' => 'Who We Are — Editorial Craft & Literary Discipline | WORDORA',
        'default_desc'  => 'Meet the managing editors, domain journalists, and editorial strategists behind WORDORA. Built on uncompromising human craftsmanship.',
        'default_kw'    => 'editorial team, content writers, human craftsmanship, writing agency jaipur, brand storytellers',
        'default_og'    => '/img/cta 1.png'
    ],
    'services' => [
        'category'      => 'core',
        'name'          => 'What We Do / All Services',
        'type_label'    => 'Services Hub Archive',
        'url'           => '/services',
        'icon'          => 'ri-briefcase-4-line',
        'default_title' => 'What We Do — Bespoke Editorial & Content Engineering | WORDORA',
        'default_desc'  => 'Explore our full spectrum of editorial services: SEO authority clusters, brand copywriting, executive thought leadership, and technical whitepapers.',
        'default_kw'    => 'SEO content writing, brand copywriting, LinkedIn ghostwriting, technical whitepapers, email marketing copy',
        'default_og'    => '/img/service.png'
    ],
    'case_studies' => [
        'category'      => 'core',
        'name'          => 'Case Studies Archive',
        'type_label'    => 'Commercial Proof Archive',
        'url'           => '/case-studies',
        'icon'          => 'ri-folder-shield-2-line',
        'default_title' => 'Verified Commercial Proof & Case Studies | WORDORA',
        'default_desc'  => 'Real words measured in pipeline growth and revenue. Explore how venture-backed startups and enterprise platforms scale organic authority with WORDORA.',
        'default_kw'    => 'content marketing case studies, SaaS SEO ROI, B2B pipeline growth proof, editorial results',
        'default_og'    => '/img/case study.png'
    ],
    'blog' => [
        'category'      => 'core',
        'name'          => 'Blog & Journal Archive',
        'type_label'    => 'Editorial Journal Hub',
        'url'           => '/blog',
        'icon'          => 'ri-article-line',
        'default_title' => 'The Wordora Journal — Editorial Insights & Search Strategy',
        'default_desc'  => 'Essays, teardowns, and master guides on topical authority, conversion copywriting, and the modern digital publishing landscape.',
        'default_kw'    => 'content marketing blog, SEO writing guides, brand narrative teardowns, digital publishing insights',
        'default_og'    => '/img/Blog service.png'
    ],
    'careers' => [
        'category'      => 'core',
        'name'          => 'Careers & Culture',
        'type_label'    => 'Talent & Culture Portal',
        'url'           => '/careers',
        'icon'          => 'ri-user-search-line',
        'default_title' => 'Join the Wordora Editorial Guild — Careers & Open Roles',
        'default_desc'  => 'We are looking for elite domain writers, investigative editors, and content strategists who hold language to the highest possible standard.',
        'default_kw'    => 'content writing jobs, editor vacancies, remote copywriting careers, freelance journalism positions',
        'default_og'    => '/img/culture notes.png'
    ],
    'contact' => [
        'category'      => 'core',
        'name'          => 'Contact Us & Scope Audit',
        'type_label'    => 'Consultation & Inquiries',
        'url'           => '/contact',
        'icon'          => 'ri-contacts-book-2-line',
        'default_title' => 'Start a Conversation — Request Scope Consultation | WORDORA',
        'default_desc'  => 'Tell us about your brand, your timeline, and what you need written. Receive a tailored scope audit and sprint roadmap within 24 hours.',
        'default_kw'    => 'hire content writers, content audit consultation, book editorial sprint, contact wordora',
        'default_og'    => '/img/cta 1.png'
    ]
];

// ── 2. DYNAMIC SERVICE DETAIL PAGES ──
$servicePages = [];
$devServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0');
try {
    if (class_exists('Service')) {
        $dbServices = Service::getAll();
        foreach ($dbServices as $srv) {
            $isDevService = (int)($srv['id'] ?? 0) > 7;
            // If dev services are turned OFF in homepage Section 3/3C, hide and block them from SEO Meta Manager
            if ($isDevService && !$devServicesEnabled) {
                continue;
            }
            $slug = $srv['slug'];
            $k = 'service_' . $slug;
            $servicePages[$k] = [
                'category'      => 'services',
                'name'          => $srv['title'],
                'type_label'    => 'Service Detail' . ($isDevService ? ' (Dev/Design)' : ''),
                'url'           => '/service/' . $slug,
                'icon'          => !empty($srv['icon']) ? $srv['icon'] : 'ri-quill-pen-line',
                'default_title' => $srv['title'] . ' — Bespoke Editorial & Content Engineering | WORDORA',
                'default_desc'  => !empty($srv['description']) ? $srv['description'] : 'Topic cluster architecture, search-intent optimization, and rigorous editorial craft by WORDORA.',
                'default_kw'    => $srv['title'] . ', SEO writing, content agency, editorial engineering',
                'default_og'    => !empty($srv['image_path']) ? $srv['image_path'] : '/img/service.png'
            ];
        }
    }
} catch (\Throwable $t) {}

// ── 3. DYNAMIC CASE STUDIES PAGES ──
$caseStudyPages = [];
try {
    if (class_exists('CaseStudy')) {
        $dbStudies = CaseStudy::getAll();
        foreach ($dbStudies as $cs) {
            $slug = $cs['slug'];
            $k = 'casestudy_' . $slug;
            $caseStudyPages[$k] = [
                'category'      => 'case_studies',
                'name'          => $cs['title'],
                'type_label'    => 'Case Study',
                'url'           => '/case-study/' . $slug,
                'icon'          => 'ri-folder-shield-2-line',
                'default_title' => $cs['title'] . ' — Commercial Proof | WORDORA',
                'default_desc'  => !empty($cs['challenge']) ? truncate(strip_tags($cs['challenge']), 155) : 'Verified commercial proof, conversion lifts, and quantified ROI metrics by WORDORA.',
                'default_kw'    => ($cs['industry'] ?? '') . ' case study, content marketing ROI, editorial results',
                'default_og'    => !empty($cs['image']) ? $cs['image'] : '/img/case study.png'
            ];
        }
    }
} catch (\Throwable $t) {}

// ── 4. DYNAMIC BLOG POST PAGES ──
$blogPages = [];
try {
    if (class_exists('Post')) {
        $dbPosts = Post::getAll();
        foreach ($dbPosts as $p) {
            $slug = $p['slug'];
            $k = 'post_' . $slug;
            $blogPages[$k] = [
                'category'      => 'blog',
                'name'          => $p['title'],
                'type_label'    => 'Blog Article',
                'url'           => '/blog/' . $slug,
                'icon'          => 'ri-article-line',
                'default_title' => (!empty($p['meta_title']) ? $p['meta_title'] : $p['title']) . ' — WORDORA Journal',
                'default_desc'  => !empty($p['meta_desc']) ? $p['meta_desc'] : truncate(strip_tags($p['excerpt'] ?: $p['content']), 155),
                'default_kw'    => !empty($p['meta_keywords']) ? $p['meta_keywords'] : 'content marketing blog, SEO strategy, editorial insights',
                'default_og'    => !empty($p['featured_img']) ? $p['featured_img'] : '/img/Blog service.png',
                'post_id'       => $p['id']
            ];
        }
    }
} catch (\Throwable $t) {}

// Combined Registry of All Public Pages
$pagesList = array_merge($corePages, $servicePages, $caseStudyPages, $blogPages);

$activePageTab = $_GET['page'] ?? 'home';
$activeCategory = $_GET['cat'] ?? '';

if (empty($activeCategory) || $activeCategory === '') {
    $activeCategory = $pagesList[$activePageTab]['category'] ?? 'core';
}

$currentCategoryPages = match($activeCategory) {
    'services'     => $servicePages,
    'case_studies' => $caseStudyPages,
    'blog'         => $blogPages,
    default        => $corePages
};

if ($activeCategory !== 'audit') {
    if (!array_key_exists($activePageTab, $currentCategoryPages) && !empty($currentCategoryPages)) {
        $activePageTab = array_key_first($currentCategoryPages);
    } elseif (!array_key_exists($activePageTab, $pagesList)) {
        $activePageTab = 'home';
    }
}

$activePage = $pagesList[$activePageTab] ?? reset($pagesList);

// ── FORM SUBMISSION ──
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!CSRF::verify($_POST['csrf_token'] ?? '')) {
        $error = 'Security token expired. Please try again.';
    } else {
        $uploader = new Upload('seo');
        $savePageKey = $_POST['page_key'] ?? $activePageTab;
        
        // Access control: If dev services are turned OFF, do not allow editing their SEO Meta
        if (!$devServicesEnabled && preg_match('/^service_/', $savePageKey)) {
            $checkSlug = preg_replace('/^service_/', '', $savePageKey);
            $chk = Service::getBySlug($checkSlug);
            if ($chk && (int)$chk['id'] > 7) {
                $error = 'SEO Meta for this service cannot be edited because Development & Design services are currently turned OFF in Homepage Section 3/3C.';
                flash_set('error', $error);
                redirect('admin/settings/seo.php?cat=services');
            }
        }
        
        if (empty($error) && array_key_exists($savePageKey, $pagesList)) {
            $pConfig = $pagesList[$savePageKey];
            
            // Meta Title, Description, Keywords
            $metaTitle = trim($_POST['meta_title'] ?? '');
            $metaDesc  = trim($_POST['meta_desc'] ?? '');
            $metaKw    = trim($_POST['meta_keywords'] ?? '');

            Setting::set("seo_{$savePageKey}_title", $metaTitle);
            Setting::set("seo_{$savePageKey}_desc", $metaDesc);
            Setting::set("seo_{$savePageKey}_keywords", $metaKw);

            // Handle OG Image
            $currentOg = setting("seo_{$savePageKey}_og_image", $pConfig['default_og']);
            $finalOg = $currentOg;

            if (isset($_FILES['og_image_file']) && $_FILES['og_image_file']['error'] === UPLOAD_ERR_OK) {
                $upRes = $uploader->handle($_FILES['og_image_file']);
                if ($upRes['success']) {
                    if (!empty($currentOg) && !str_starts_with($currentOg, '/img/') && !str_ends_with($currentOg, '.png')) {
                        delete_uploaded_file($currentOg);
                    }
                    $finalOg = $upRes['path'];
                } else {
                    $error = 'OG Image upload error: ' . $upRes['msg'];
                }
            } elseif (!empty($_POST['remove_og_image']) && $_POST['remove_og_image'] === '1') {
                if (!empty($currentOg) && !str_starts_with($currentOg, '/img/') && !str_ends_with($currentOg, '.png')) {
                    delete_uploaded_file($currentOg);
                }
                $finalOg = $pConfig['default_og'];
            }

            Setting::set("seo_{$savePageKey}_og_image", $finalOg);

            // If it's a blog post, also sync with posts table
            if (!empty($pConfig['post_id'])) {
                try {
                    $db = DB::getInstance();
                    $db->prepare("UPDATE posts SET meta_title = ?, meta_desc = ?, meta_keywords = ? WHERE id = ?")
                       ->execute([$metaTitle, $metaDesc, $metaKw, $pConfig['post_id']]);
                } catch (\Throwable $t) {}
            }

            setting('__CLEAR_CACHE__');

            if (empty($error)) {
                flash_set('success', "SEO Meta tags for \"{$pConfig['name']}\" saved successfully!");
                $cat = $pConfig['category'] ?? 'core';
                redirect("admin/settings/seo.php?page={$savePageKey}&cat={$cat}");
            }
        }

        if (!empty($error)) {
            flash_set('error', $error);
            $cat = !empty($activeCategory) ? $activeCategory : 'core';
            redirect("admin/settings/seo.php?page={$savePageKey}&cat={$cat}");
        }
    }
}

// Current Values for Active Tab
$curTitle    = setting("seo_{$activePageTab}_title", $activePage['default_title'] ?? '');
$curDesc     = setting("seo_{$activePageTab}_desc", $activePage['default_desc'] ?? '');
$curKeywords = setting("seo_{$activePageTab}_keywords", $activePage['default_kw'] ?? '');
$curOgImage  = setting("seo_{$activePageTab}_og_image", $activePage['default_og'] ?? '');

// Calculate summary stats
$totalCount = count($pagesList);
$configuredCount = 0;
foreach ($pagesList as $pKey => $pInfo) {
    if (!empty(setting("seo_{$pKey}_title")) || !empty(setting("seo_{$pKey}_desc"))) {
        $configuredCount++;
    }
}
$defaultCount = $totalCount - $configuredCount;

include ROOT_PATH . '/admin/includes/header.php';
?>

<div class="admin-card">
  <!-- Card Header with Stats -->
  <div class="card-header" style="background: linear-gradient(135deg, #FAF8F5 0%, #F0F7F7 100%); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 22px 24px;">
    <div>
      <h2 class="card-title" style="color: var(--admin-navy); margin: 0; display: flex; align-items: center; gap: 10px; font-size: 1.35rem;">
        <i class="ri-search-eye-line" style="color: var(--admin-teal);"></i> Master SEO &amp; Meta Manager
      </h2>
      <div style="font-size: 13px; color: var(--admin-muted); margin-top: 5px;">
        Manage Page Title Tags, Meta Descriptions, Focus Keywords, and Social OpenGraph Media for <strong>all <?= (int)$totalCount ?> pages</strong> across the website.
      </div>
    </div>

    <!-- Quick Stats Pills -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
      <div style="background: #FFFFFF; border: 1.5px solid var(--admin-border); border-radius: 20px; padding: 6px 14px; font-size: 12px; font-weight: 700; color: var(--admin-navy); display: flex; align-items: center; gap: 6px;">
        <i class="ri-pages-line" style="color: var(--admin-teal);"></i> <?= (int)$totalCount ?> Total Pages
      </div>
      <div style="background: #DCFCE7; border: 1.5px solid #BBF7D0; border-radius: 20px; padding: 6px 14px; font-size: 12px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
        <i class="ri-checkbox-circle-fill"></i> <?= (int)$configuredCount ?> Configured
      </div>
      <?php if ($defaultCount > 0): ?>
      <div style="background: #FEF3C7; border: 1.5px solid #FDE68A; border-radius: 20px; padding: 6px 14px; font-size: 12px; font-weight: 700; color: #92400E; display: flex; align-items: center; gap: 6px;">
        <i class="ri-information-line"></i> <?= (int)$defaultCount ?> Using Defaults
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card-body" style="padding: 24px;">

    <!-- ── Category Selector Tabs ── -->
    <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1.5px solid var(--admin-border); align-items: center; justify-content: space-between;">
      <div style="display: flex; flex-wrap: wrap; gap: 8px;">
        <!-- Core Pages -->
        <?php $coreFirstKey = key($corePages); ?>
        <a href="<?= url("admin/settings/seo.php?cat=core&page={$coreFirstKey}") ?>" 
           class="btn-adm <?= ($activeCategory === 'core') ? 'btn-adm-primary' : 'btn-adm-outline' ?>" 
           style="font-size: 13px; padding: 9px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 10px;">
          <i class="ri-global-line"></i> Main Pages
          <span style="font-size: 11px; background: <?= ($activeCategory === 'core') ? 'rgba(255,255,255,0.25)' : 'var(--admin-teal-pale)' ?>; color: <?= ($activeCategory === 'core') ? '#FFF' : 'var(--admin-teal)' ?>; padding: 1px 7px; border-radius: 10px; font-weight: 800;">
            <?= count($corePages) ?>
          </span>
        </a>

        <!-- Service Detail Pages -->
        <?php $svcFirstKey = key($servicePages) ?: 'home'; ?>
        <a href="<?= url("admin/settings/seo.php?cat=services&page={$svcFirstKey}") ?>" 
           class="btn-adm <?= ($activeCategory === 'services') ? 'btn-adm-primary' : 'btn-adm-outline' ?>" 
           style="font-size: 13px; padding: 9px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 10px;">
          <i class="ri-quill-pen-line"></i> Service Pages
          <span style="font-size: 11px; background: <?= ($activeCategory === 'services') ? 'rgba(255,255,255,0.25)' : 'var(--admin-teal-pale)' ?>; color: <?= ($activeCategory === 'services') ? '#FFF' : 'var(--admin-teal)' ?>; padding: 1px 7px; border-radius: 10px; font-weight: 800;">
            <?= count($servicePages) ?>
          </span>
        </a>

        <!-- Case Studies -->
        <?php $csFirstKey = key($caseStudyPages) ?: 'home'; ?>
        <a href="<?= url("admin/settings/seo.php?cat=case_studies&page={$csFirstKey}") ?>" 
           class="btn-adm <?= ($activeCategory === 'case_studies') ? 'btn-adm-primary' : 'btn-adm-outline' ?>" 
           style="font-size: 13px; padding: 9px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 10px;">
          <i class="ri-folder-shield-2-line"></i> Case Studies
          <span style="font-size: 11px; background: <?= ($activeCategory === 'case_studies') ? 'rgba(255,255,255,0.25)' : 'var(--admin-teal-pale)' ?>; color: <?= ($activeCategory === 'case_studies') ? '#FFF' : 'var(--admin-teal)' ?>; padding: 1px 7px; border-radius: 10px; font-weight: 800;">
            <?= count($caseStudyPages) ?>
          </span>
        </a>

        <!-- Blog Articles -->
        <?php $blogFirstKey = key($blogPages) ?: 'home'; ?>
        <a href="<?= url("admin/settings/seo.php?cat=blog&page={$blogFirstKey}") ?>" 
           class="btn-adm <?= ($activeCategory === 'blog') ? 'btn-adm-primary' : 'btn-adm-outline' ?>" 
           style="font-size: 13px; padding: 9px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 10px;">
          <i class="ri-article-line"></i> Blog Articles
          <span style="font-size: 11px; background: <?= ($activeCategory === 'blog') ? 'rgba(255,255,255,0.25)' : 'var(--admin-teal-pale)' ?>; color: <?= ($activeCategory === 'blog') ? '#FFF' : 'var(--admin-teal)' ?>; padding: 1px 7px; border-radius: 10px; font-weight: 800;">
            <?= count($blogPages) ?>
          </span>
        </a>

        <!-- All Pages Audit Matrix -->
        <a href="<?= url("admin/settings/seo.php?cat=audit") ?>" 
           class="btn-adm <?= ($activeCategory === 'audit') ? 'btn-adm-primary' : 'btn-adm-outline' ?>" 
           style="font-size: 13px; padding: 9px 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border-radius: 10px;">
          <i class="ri-table-line"></i> All Pages Audit Matrix
        </a>
      </div>

      <!-- Quick Search / Filter Input -->
      <div style="position: relative; min-width: 260px;">
        <i class="ri-search-line" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--admin-muted); font-size: 14px;"></i>
        <input type="text" id="pageSearchFilter" placeholder="Filter pages by name, url..." oninput="filterPagesList(this.value)" 
               style="width: 100%; padding: 8px 12px 8px 34px; border: 1.5px solid var(--admin-border); border-radius: 20px; font-size: 12.5px; outline: none; transition: border-color 0.2s;">
      </div>
    </div>

    <!-- ═══════════════════════════════════════════
         VIEW A: COMPLETE ALL-PAGES AUDIT MATRIX
         ═══════════════════════════════════════════ -->
    <?php if ($activeCategory === 'audit'): ?>
      <div style="background: #FFFFFF; border: 1.5px solid var(--admin-border); border-radius: 14px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
        <div style="padding: 16px 20px; background: #FAF8F5; border-bottom: 1.5px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div>
            <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--admin-navy);">
              <i class="ri-file-list-3-line" style="color: var(--admin-teal);"></i> Complete Site SEO Matrix (All <?= count($pagesList) ?> Pages)
            </h3>
            <p style="margin: 2px 0 0; font-size: 12px; color: var(--admin-muted);">
              Inspect meta tags across all public routes. Click "Edit SEO" on any row to configure its parameters.
            </p>
          </div>
        </div>

        <div style="overflow-x: auto;">
          <table class="table-adm" style="width: 100%; border-collapse: collapse; font-size: 12.5px;" id="seoAuditTable">
            <thead>
              <tr style="background: #F8FAFC; text-align: left; border-bottom: 1.5px solid var(--admin-border); color: var(--admin-navy);">
                <th style="padding: 12px 16px; width: 220px;">Page Name &amp; Type</th>
                <th style="padding: 12px 16px; width: 180px;">Public Route</th>
                <th style="padding: 12px 16px;">Meta Title (Title Tag)</th>
                <th style="padding: 12px 16px;">Meta Description</th>
                <th style="padding: 12px 16px; width: 140px;">Focus Keywords</th>
                <th style="padding: 12px 16px; text-align: center; width: 110px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pagesList as $pKey => $pInfo): 
                $titleVal = setting("seo_{$pKey}_title");
                $descVal  = setting("seo_{$pKey}_desc");
                $kwVal    = setting("seo_{$pKey}_keywords");
                $isCustom = (!empty($titleVal) || !empty($descVal));
                $dispTitle = !empty($titleVal) ? $titleVal : $pInfo['default_title'];
                $dispDesc  = !empty($descVal) ? $descVal : $pInfo['default_desc'];
                $catBadge = match($pInfo['category']) {
                    'core' => ['name' => 'Main', 'bg' => '#E0F2FE', 'col' => '#0369A1'],
                    'services' => ['name' => 'Service', 'bg' => '#DCFCE7', 'col' => '#15803D'],
                    'case_studies' => ['name' => 'Case Study', 'bg' => '#F3E8FF', 'col' => '#7E22CE'],
                    'blog' => ['name' => 'Blog', 'bg' => '#FEF3C7', 'col' => '#B45309'],
                    default => ['name' => 'Page', 'bg' => '#F1F5F9', 'col' => '#475569']
                };
              ?>
              <tr class="audit-row" style="border-bottom: 1px solid var(--admin-border); transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <td style="padding: 14px 16px;">
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="<?= $pInfo['icon'] ?>" style="color: var(--admin-teal); font-size: 15px;"></i>
                    <div>
                      <strong style="color: var(--admin-navy); font-size: 13px;"><?= e($pInfo['name']) ?></strong>
                      <div style="margin-top: 2px;">
                        <span style="font-size: 10px; font-weight: 700; background: <?= $catBadge['bg'] ?>; color: <?= $catBadge['col'] ?>; padding: 2px 7px; border-radius: 10px; text-transform: uppercase;">
                          <?= $catBadge['name'] ?>
                        </span>
                        <?php if ($isCustom): ?>
                          <span style="font-size: 10px; font-weight: 700; background: #DCFCE7; color: #166534; padding: 2px 7px; border-radius: 10px;">Custom</span>
                        <?php else: ?>
                          <span style="font-size: 10px; font-weight: 700; background: #F1F5F9; color: #64748B; padding: 2px 7px; border-radius: 10px;">Default</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </td>
                <td style="padding: 14px 16px;">
                  <code style="font-size: 11.5px; background: #F1F5F9; padding: 3px 6px; border-radius: 4px; color: var(--admin-navy); word-break: break-all;"><?= e($pInfo['url']) ?></code>
                </td>
                <td style="padding: 14px 16px;">
                  <div style="font-weight: 600; color: var(--admin-navy); line-height: 1.35; margin-bottom: 4px;">
                    <?= e($dispTitle) ?>
                  </div>
                  <span style="font-size: 10.5px; font-weight: 700; color: <?= strlen($dispTitle) > 65 ? '#D97706' : '#166534' ?>;">
                    <?= strlen($dispTitle) ?> chars
                  </span>
                </td>
                <td style="padding: 14px 16px;">
                  <div style="color: var(--admin-muted); line-height: 1.4; margin-bottom: 4px; max-width: 320px;">
                    <?= e(truncate($dispDesc, 110)) ?>
                  </div>
                  <span style="font-size: 10.5px; font-weight: 700; color: <?= strlen($dispDesc) > 165 ? '#D97706' : '#166534' ?>;">
                    <?= strlen($dispDesc) ?> chars
                  </span>
                </td>
                <td style="padding: 14px 16px;">
                  <div style="color: var(--admin-muted); font-size: 11px; max-width: 140px; word-break: break-word;">
                    <?= e(truncate($kwVal ?: ($pInfo['default_kw'] ?? '—'), 60)) ?>
                  </div>
                </td>
                <td style="padding: 14px 16px; text-align: center;">
                  <a href="<?= url("admin/settings/seo.php?cat={$pInfo['category']}&page={$pKey}") ?>" 
                     class="btn-adm btn-adm-primary" style="font-size: 11.5px; padding: 6px 12px; border-radius: 6px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="ri-edit-line"></i> Edit
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <!-- ═══════════════════════════════════════════
         VIEW B: FOCUSED CATEGORY & PAGE SEO EDITOR
         ═══════════════════════════════════════════ -->
    <?php else: ?>

      <!-- 1. Pages Navigation Cards in Selected Category -->
      <?php
      $currentCategoryPages = match($activeCategory) {
          'services'     => $servicePages,
          'case_studies' => $caseStudyPages,
          'blog'         => $blogPages,
          default        => $corePages
      };
      ?>

      <?php if ($activeCategory === 'services' && !$devServicesEnabled): ?>
      <div style="margin-bottom: 16px; padding: 12px 18px; border-radius: 12px; font-size: 13px; background: #FEF2F2; color: #991B1B; border: 1.5px solid #FECACA; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 10px;">
          <i class="ri-eye-off-line" style="font-size: 20px; color: #DC2626;"></i>
          <div>
            <strong>7 Development &amp; Design Services Are Turned OFF:</strong> SEO Meta configuration for these services is currently disabled and hidden.
            <div style="font-size: 12px; color: #7F1D1D; margin-top: 2px;">
              To re-enable them, navigate to <a href="<?= url('admin/pages/home.php?tab=sec03c') ?>" style="color: #991B1B; font-weight: 700; text-decoration: underline;">Homepage Section 3 / 3C</a> and switch the Master Toggle to ON.
            </div>
          </div>
        </div>
        <span style="font-size: 11px; font-weight: 800; background: #DC2626; color: #FFF; padding: 4px 10px; border-radius: 6px;">7 SERVICES DISABLED</span>
      </div>
      <?php endif; ?>

      <div style="margin-bottom: 24px;">
        <div style="font-size: 11.5px; font-weight: 700; text-transform: uppercase; color: var(--admin-muted); letter-spacing: 0.08em; margin-bottom: 10px;">
          Select Page to Configure (Showing <?= count($currentCategoryPages) ?> Pages in this section):
        </div>

        <div id="pagesCardGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; max-height: 280px; overflow-y: auto; padding: 4px; border: 1.5px solid var(--admin-border); border-radius: 12px; background: #FAF9F6;">
          <?php foreach ($currentCategoryPages as $pKey => $pInfo): 
            $isActive = ($activePageTab === $pKey);
            $hasCustom = (!empty(setting("seo_{$pKey}_title")) || !empty(setting("seo_{$pKey}_desc")));
          ?>
          <a href="<?= url("admin/settings/seo.php?cat={$activeCategory}&page={$pKey}") ?>" 
             class="page-selector-card <?= $isActive ? 'is-active' : '' ?>"
             data-name="<?= strtolower(e($pInfo['name'])) ?>"
             data-url="<?= strtolower(e($pInfo['url'])) ?>"
             style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px; text-decoration: none; transition: all 0.18s; <?= $isActive ? 'background: var(--admin-navy); color: #FFF; box-shadow: 0 4px 12px rgba(15,30,54,0.18);' : 'background: #FFF; color: var(--admin-navy); border: 1px solid var(--admin-border);' ?>">
            
            <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
              <i class="<?= $pInfo['icon'] ?>" style="font-size: 16px; color: <?= $isActive ? 'var(--admin-teal-pale)' : 'var(--admin-teal)' ?>; flex-shrink: 0;"></i>
              <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <div style="font-size: 13px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?= e($pInfo['name']) ?>
                </div>
                <div style="font-size: 11px; opacity: 0.75; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?= e($pInfo['url']) ?>
                </div>
              </div>
            </div>

            <div style="flex-shrink: 0; margin-left: 8px;">
              <?php if ($hasCustom): ?>
                <span title="Custom SEO Configured" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22C55E;"></span>
              <?php else: ?>
                <span title="Using Default SEO" style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #CBD5E1;"></span>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. Active Page Context Banner -->
      <div style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 14px; padding: 16px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <div style="width: 44px; height: 44px; border-radius: 10px; background: var(--admin-navy); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 20px;">
            <i class="<?= $activePage['icon'] ?>"></i>
          </div>
          <div>
            <div style="font-size: 15px; font-weight: 800; color: var(--admin-navy);">
              <?= e($activePage['name']) ?>
              <span style="font-size: 11px; font-weight: 700; background: var(--admin-teal-pale); color: var(--admin-teal); padding: 2px 8px; border-radius: 12px; margin-left: 6px; text-transform: uppercase;">
                <?= e($activePage['type_label'] ?? 'Page') ?>
              </span>
            </div>
            <div style="font-size: 12px; color: var(--admin-muted); margin-top: 3px; display: flex; align-items: center; gap: 6px;">
              <span>Public Route:</span>
              <code style="background: #E2E8F0; padding: 2px 6px; border-radius: 4px; color: var(--admin-navy); font-weight: 600;"><?= e($activePage['url']) ?></code>
            </div>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
          <a href="<?= url(ltrim($activePage['url'], '/')) ?>" target="_blank" class="btn-adm btn-adm-secondary" style="font-size: 12.5px; padding: 8px 16px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ri-external-link-line"></i> View Page Live
          </a>
        </div>
      </div>

      <!-- 3. Form & Live Google SERP Mockup -->
      <form method="POST" action="<?= url("admin/settings/seo.php?cat={$activeCategory}&page={$activePageTab}") ?>" enctype="multipart/form-data">
        <?= CSRF::field() ?>
        <input type="hidden" name="page_key" value="<?= e($activePageTab) ?>">

        <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">

          <!-- Google Search Result Live Preview -->
          <div style="background: #FFFFFF; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 22px; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--admin-teal); letter-spacing: 0.08em; margin-bottom: 14px; display: flex; align-items: center; gap: 6px;">
              <i class="ri-google-fill" style="color: #4285F4; font-size: 16px;"></i> Live Google Search Snippet Simulation
            </div>

            <div style="max-width: 680px; font-family: Arial, sans-serif; background: #FAF9F6; padding: 18px; border-radius: 10px; border: 1px solid #E5E7EB;">
              <div style="font-size: 12px; color: #202124; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                <span style="font-weight: 600; color: #202124;">wordora.in</span>
                <span style="color: #5f6368;">› <?= trim($activePage['url'], '/') ?: 'home' ?></span>
              </div>
              <div id="serpTitlePreview" style="font-size: 19px; color: #1a0dab; line-height: 1.3; font-weight: 400; cursor: pointer; margin-bottom: 6px; word-break: break-word;">
                <?= e($curTitle ?: ($activePage['default_title'] ?? '')) ?>
              </div>
              <div id="serpDescPreview" style="font-size: 13.5px; color: #4d5156; line-height: 1.5; word-break: break-word;">
                <?= e($curDesc ?: ($activePage['default_desc'] ?? '')) ?>
              </div>
            </div>
          </div>

          <!-- SEO Meta Title Input -->
          <div class="form-field full">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label class="field-label" for="metaTitle" style="margin: 0; font-weight: 700; color: var(--admin-navy); font-size: 14px;">
                <i class="ri-heading" style="color: var(--admin-teal);"></i> SEO Meta Title (Title Tag) *
              </label>
              <span id="titleCounter" style="font-size: 11.5px; color: var(--admin-muted); font-weight: 700;">0 / 60 characters</span>
            </div>
            <input type="text" id="metaTitle" name="meta_title" class="field-input" required 
                   value="<?= e($curTitle) ?>" 
                   placeholder="<?= e($activePage['default_title'] ?? '') ?>" 
                   oninput="updateSerpPreview()"
                   style="font-size: 14.5px; font-weight: 600; padding: 10px 14px;">
            <div class="field-help" style="margin-top: 4px;">Recommended: 50–60 characters. Appears as the clickable headline in Google search results and on browser tabs.</div>
          </div>

          <!-- SEO Meta Description Input -->
          <div class="form-field full">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
              <label class="field-label" for="metaDesc" style="margin: 0; font-weight: 700; color: var(--admin-navy); font-size: 14px;">
                <i class="ri-file-text-line" style="color: var(--admin-teal);"></i> SEO Meta Description *
              </label>
              <span id="descCounter" style="font-size: 11.5px; color: var(--admin-muted); font-weight: 700;">0 / 160 characters</span>
            </div>
            <textarea id="metaDesc" name="meta_desc" class="field-textarea" rows="3" required 
                      placeholder="<?= e($activePage['default_desc'] ?? '') ?>" 
                      oninput="updateSerpPreview()"
                      style="font-size: 14px; line-height: 1.5; padding: 10px 14px;"><?= e($curDesc) ?></textarea>
            <div class="field-help" style="margin-top: 4px;">Recommended: 140–160 characters. A compelling call-to-action summary displayed under the title in search engine result pages.</div>
          </div>

          <!-- SEO Focus Keywords Input -->
          <div class="form-field full">
            <label class="field-label" for="metaKeywords" style="font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-bottom: 6px;">
              <i class="ri-key-2-line" style="color: var(--admin-teal);"></i> Meta Focus Keywords (Comma-Separated)
            </label>
            <input type="text" id="metaKeywords" name="meta_keywords" class="field-input" 
                   value="<?= e($curKeywords) ?>" 
                   placeholder="e.g. content marketing, SEO strategy, B2B copywriting"
                   style="font-size: 13.5px; padding: 10px 14px;">
            <div class="field-help" style="margin-top: 4px;">Comma-separated primary and secondary keywords for search engine indexing (e.g. <code>seo content, brand storytelling, editorial agency</code>). Injected into <code>&lt;meta name="keywords"&gt;</code>.</div>
          </div>

          <!-- Social Share OpenGraph Image -->
          <div class="form-field full" style="background: #FAF8F5; padding: 20px; border-radius: 12px; border: 1.5px solid var(--admin-border);">
            <label class="field-label" style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: var(--admin-navy); font-size: 14px; margin-bottom: 10px;">
              <i class="ri-share-forward-line" style="color: var(--admin-teal);"></i> Social Media Share Image (OpenGraph / Twitter Card)
            </label>

            <?php if (!empty($curOgImage)): ?>
              <div id="preview_og_img" style="margin: 10px 0 16px; display: flex; align-items: center; gap: 16px; background: #FFF; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--admin-border); transition: all 0.25s ease;">
                <img src="<?= media_url($curOgImage) ?>" alt="Active OG Share Image" style="max-height: 80px; max-width: 140px; border-radius: 6px; border: 1px solid var(--admin-border); object-fit: cover;">
                <div style="flex: 1;">
                  <div style="font-size: 13px; font-weight: 700; color: var(--admin-navy);">Active Social Share Media</div>
                  <div style="font-size: 11.5px; color: var(--admin-muted); word-break: break-all; margin-top: 2px;"><?= e($curOgImage) ?></div>
                </div>
                <button type="button" onclick="instantRemoveMedia('remove_og_image', 'preview_og_img')" style="background: #FEE2E2; color: #DC2626; border: 1px solid #FECACA; padding: 7px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;">
                  <i class="ri-delete-bin-line"></i> Revert to Default
                </button>
              </div>
            <?php endif; ?>
            <input type="hidden" name="remove_og_image" id="remove_og_image" value="0">

            <input type="file" name="og_image_file" class="field-input" accept="image/*" style="background: #FFF;">
            <div class="field-help" style="margin-top: 6px;">Recommended dimensions: 1200 × 630 px. Displayed in social link previews when shared on WhatsApp, LinkedIn, X (Twitter), and Facebook.</div>
          </div>

        </div>

        <div style="margin-top: 26px; padding-top: 20px; border-top: 1.5px solid var(--admin-border); display: flex; gap: 14px; align-items: center;">
          <button type="submit" class="btn-adm btn-adm-primary" style="padding: 12px 28px; font-weight: 700; font-size: 14px; border-radius: 8px;">
            <i class="ri-save-line"></i> Save SEO Settings for <?= e($activePage['name']) ?>
          </button>
          <a href="<?= url("admin/settings/seo.php?cat=audit") ?>" class="btn-adm btn-adm-outline" style="font-size: 13px; padding: 12px 20px;">
            <i class="ri-list-check"></i> View in All Pages Matrix
          </a>
        </div>
      </form>

    <?php endif; ?>

  </div>
</div>

<script>
function updateSerpPreview() {
  const titleInput = document.getElementById('metaTitle');
  const descInput  = document.getElementById('metaDesc');
  const titlePreview = document.getElementById('serpTitlePreview');
  const descPreview  = document.getElementById('serpDescPreview');
  const titleCounter = document.getElementById('titleCounter');
  const descCounter  = document.getElementById('descCounter');

  if (titleInput && titlePreview) {
    const defaultTitle = '<?= e(addslashes($activePage['default_title'] ?? '')) ?>';
    const tVal = titleInput.value.trim() || defaultTitle;
    titlePreview.textContent = tVal;
    if (titleCounter) {
      const tLen = titleInput.value.length;
      titleCounter.textContent = `${tLen} / 60 characters`;
      titleCounter.style.color = (tLen > 65 || (tLen < 30 && tLen > 0)) ? '#D97706' : (tLen >= 30 && tLen <= 65 ? '#166534' : 'var(--admin-muted)');
    }
  }

  if (descInput && descPreview) {
    const defaultDesc = '<?= e(addslashes($activePage['default_desc'] ?? '')) ?>';
    const dVal = descInput.value.trim() || defaultDesc;
    descPreview.textContent = dVal;
    if (descCounter) {
      const dLen = descInput.value.length;
      descCounter.textContent = `${dLen} / 160 characters`;
      descCounter.style.color = (dLen > 165 || (dLen < 70 && dLen > 0)) ? '#D97706' : (dLen >= 70 && dLen <= 165 ? '#166534' : 'var(--admin-muted)');
    }
  }
}

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

// Client-side quick filter for page cards in category view & audit table
function filterPagesList(query) {
  const q = (query || '').toLowerCase().trim();
  
  // Filter category page cards if present
  const cards = document.querySelectorAll('.page-selector-card');
  cards.forEach(card => {
    const name = card.getAttribute('data-name') || '';
    const url = card.getAttribute('data-url') || '';
    if (!q || name.includes(q) || url.includes(q)) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });

  // Filter audit table rows if present
  const rows = document.querySelectorAll('#seoAuditTable tbody tr');
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    if (!q || text.includes(q)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

document.addEventListener('DOMContentLoaded', updateSerpPreview);
</script>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
