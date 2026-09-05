<?php
/**
 * WORDORA — Blog Visual Section Studio
 * 5-Tab Management matching public/blog/index.php & public/blog/post.php
 */

require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/DB.php';
require_once ROOT_PATH . '/core/CSRF.php';
require_once ROOT_PATH . '/core/Upload.php';
require_once ROOT_PATH . '/models/Hero.php';
require_once ROOT_PATH . '/models/Post.php';
require_once ROOT_PATH . '/models/Category.php';

$db = DB::getInstance();
$activeTab = $_GET['tab'] ?? 'sec01';
$catFilter = trim($_GET['category'] ?? '');
$currentUrl = url('admin/pages/blog.php');

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
        $uploader = new Upload('blog', 52428800);

        // -------------------------------------------------------------
        // CATEGORY ACTIONS (CREATE / EDIT / DELETE)
        // -------------------------------------------------------------
        if ($action === 'create_category') {
            $cName = trim($_POST['cat_name'] ?? '');
            $cSlug = trim($_POST['cat_slug'] ?? '') ?: slugify($cName);
            $cDesc = trim($_POST['cat_desc'] ?? '');

            if (!empty($cName)) {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
                $stmt->execute([$cName, $cSlug, $cDesc]);
                flash_set('success', 'Category added successfully!');
            }
            redirect('admin/pages/blog.php?tab=sec03');
        } elseif ($action === 'delete_category') {
            $catId = (int)($_POST['category_id'] ?? 0);
            if ($catId > 0) {
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$catId]);
                flash_set('success', 'Category deleted successfully.');
            }
            redirect('admin/pages/blog.php?tab=sec03');
        }

        // -------------------------------------------------------------
        // DELETE ARTICLE
        // -------------------------------------------------------------
        elseif ($action === 'delete_post') {
            $postId = (int)($_POST['post_id'] ?? 0);
            if ($postId > 0) {
                Post::delete($postId);
                flash_set('success', 'Article deleted successfully.');
            }
            redirect('admin/pages/blog.php?tab=articles');
        }

        // -------------------------------------------------------------
        // TAB 01: HERO SECTION (SINGLE BANNER — NO BUTTONS)
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec01') {
            $eyebrow  = trim($_POST['hero_eyebrow'] ?? 'THE WORDORA EDITORIAL DISPATCH');
            $title    = trim($_POST['hero_title'] ?? 'Perspectives on Content Architecture & Brand Storytelling');
            $subtitle = trim($_POST['hero_subtitle'] ?? 'Actionable playbooks, strategic teardowns, and deep editorial insights for marketing leaders, founders, and content architects.');

            // Fetch or create slide
            $slide = $db->query("SELECT * FROM hero_slides WHERE page = 'blog' ORDER BY sort_order ASC LIMIT 1")->fetch();
            $mediaUrl = $slide['media_url'] ?? '/img/Blog service.png';

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
                $mediaUrl = '/img/Blog service.png';
            }

            if (empty($editorError)) {
                Setting::set('hero_mode_blog', 'single');
                if ($slide) {
                    $stmtSlide = $db->prepare("UPDATE hero_slides SET eyebrow = ?, title = ?, subtitle = ?, media_url = ?, button_primary_text = '', button_primary_url = '', button_secondary_text = '', button_secondary_url = '', banner_type = 'single' WHERE id = ?");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl, $slide['id']]);
                } else {
                    $stmtSlide = $db->prepare("INSERT INTO hero_slides (page, banner_type, eyebrow, title, subtitle, media_url, button_primary_text, button_primary_url, button_secondary_text, button_secondary_url, sort_order, is_active) VALUES ('blog', 'single', ?, ?, ?, ?, '', '', '', '', 1, 1)");
                    $stmtSlide->execute([$eyebrow, $title, $subtitle, $mediaUrl]);
                }

                flash_set('success', 'Blog Hero Banner updated successfully!');
                redirect('admin/pages/blog.php?tab=sec01');
            }
        }

        // -------------------------------------------------------------
        // TAB 02: NEWSLETTER CAPTURE BAR
        // -------------------------------------------------------------
        elseif ($activeTab === 'sec02') {
            Setting::set('blog_news_badge', trim($_POST['news_badge'] ?? ''));
            Setting::set('blog_news_title', trim($_POST['news_title'] ?? ''));
            Setting::set('blog_news_desc', trim($_POST['news_desc'] ?? ''));
            Setting::set('blog_news_btn_text', trim($_POST['news_btn_text'] ?? ''));
            Setting::set('blog_news_note', trim($_POST['news_note'] ?? ''));

            flash_set('success', 'Newsletter & Lead Capture Section updated successfully!');
            redirect('admin/pages/blog.php?tab=sec02');
        }

        // -------------------------------------------------------------
        // TAB 05: DETAIL PAGE SIDEBAR WIDGETS
        // -------------------------------------------------------------
        elseif ($activeTab === 'sidebar') {
            Setting::set('blog_sidebar_cta_badge', trim($_POST['sidebar_cta_badge'] ?? ''));
            Setting::set('blog_sidebar_cta_title', trim($_POST['sidebar_cta_title'] ?? ''));
            Setting::set('blog_sidebar_cta_desc', trim($_POST['sidebar_cta_desc'] ?? ''));
            Setting::set('blog_sidebar_cta_btn_text', trim($_POST['sidebar_cta_btn_text'] ?? ''));
            Setting::set('blog_sidebar_cta_btn_url', trim($_POST['sidebar_cta_btn_url'] ?? ''));

            Setting::set('blog_sidebar_news_badge', trim($_POST['sidebar_news_badge'] ?? ''));
            Setting::set('blog_sidebar_news_title', trim($_POST['sidebar_news_title'] ?? ''));
            Setting::set('blog_sidebar_news_desc', trim($_POST['sidebar_news_desc'] ?? ''));
            Setting::set('blog_sidebar_news_btn_text', trim($_POST['sidebar_news_btn_text'] ?? ''));

            flash_set('success', 'Blog Detail Right Sidebar Widgets updated successfully!');
            redirect('admin/pages/blog.php?tab=sidebar');
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// 2. FETCH CURRENT DATA
// ═══════════════════════════════════════════════════════════════════════════

// Hero Section
$heroMode = setting('hero_mode_blog', 'single_image');
$slide = $db->query("SELECT * FROM hero_slides WHERE page = 'blog' ORDER BY sort_order ASC LIMIT 1")->fetch();
$heroEyebrow = $slide['eyebrow'] ?? 'THE WORDORA EDITORIAL DISPATCH';
$heroTitle   = $slide['title'] ?? 'Perspectives on Content Architecture & Brand Storytelling';
$heroSubtitle= $slide['subtitle'] ?? 'Actionable playbooks, strategic teardowns, and deep editorial insights for marketing leaders, founders, and content architects.';
$heroMediaUrl= $slide['media_url'] ?? '/img/Blog service.png';
$heroVideoUrl= setting('hero_video_url_blog', $slide['video_url'] ?? '');
$heroBtn1Text= $slide['button_primary_text'] ?? 'Browse Latest Articles';
$heroBtn1Url = $slide['button_primary_url'] ?? '#articles-grid';
$heroBtn2Text= $slide['button_secondary_text'] ?? 'Subscribe to Dispatch';
$heroBtn2Url = $slide['button_secondary_url'] ?? '#newsletter';

// Newsletter Section
$newsBadge   = setting('blog_news_badge', 'THE EDITORIAL DISPATCH');
$newsTitle   = setting('blog_news_title', 'Insights That Sharpen Your Commercial Narrative');
$newsDesc    = setting('blog_news_desc', 'Join 14,000+ founders, heads of marketing, and technical editors who read our bi-weekly strategy teardown.');
$newsBtnText = setting('blog_news_btn_text', 'Join The Dispatch');
$newsNote    = setting('blog_news_note', 'Strict zero-spam policy. One-click unsubscribe at any time.');

// Blog Detail Sidebar Settings
$sideCtaBadge   = setting('blog_sidebar_cta_badge', 'Content Partnership');
$sideCtaTitle   = setting('blog_sidebar_cta_title', 'Need High-Authority Content Like This?');
$sideCtaDesc    = setting('blog_sidebar_cta_desc', 'Partner with our managing editors and senior domain writers to publish topic clusters, thought leadership, and brand narratives.');
$sideCtaBtnText = setting('blog_sidebar_cta_btn_text', 'Request Scope Audit');
$sideCtaBtnUrl  = setting('blog_sidebar_cta_btn_url', 'contact.php');

$sideNewsBadge   = setting('blog_sidebar_news_badge', 'Weekly Brief');
$sideNewsTitle   = setting('blog_sidebar_news_title', 'The Executive Editorial Digest');
$sideNewsDesc    = setting('blog_sidebar_news_desc', 'Join 12,000+ leaders receiving weekly breakdowns of search algorithm updates & topic cluster playbooks.');
$sideNewsBtnText = setting('blog_sidebar_news_btn_text', 'Subscribe Free');

// Categories
$categories = Category::getAll();
$categoriesWithCount = [];
foreach ($categories as $c) {
    $c['count'] = (int)$db->query("SELECT COUNT(*) FROM posts WHERE category_id = " . (int)$c['id'])->fetchColumn();
    $categoriesWithCount[] = $c;
}

// Articles
$articlesSql = "SELECT p.*, c.name AS category_name, u.name AS author_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN users u ON p.author_id = u.id";
if ($catFilter) {
    $articlesSql .= " WHERE p.category_id = " . (int)$catFilter;
}
$articlesSql .= " ORDER BY p.id DESC";
$allPosts = $db->query($articlesSql)->fetchAll(PDO::FETCH_ASSOC);
$totalArticlesCount = count($allPosts);

$tabs = [
    'sec01'    => ['num' => '01', 'name' => 'Hero Cover', 'icon' => 'ri-image-line'],
    'sec02'    => ['num' => '02', 'name' => 'Newsletter Capture Bar', 'icon' => 'ri-mail-send-line'],
    'sec03'    => ['num' => '03', 'name' => 'Categories Manager', 'icon' => 'ri-price-tag-3-line', 'badge' => count($categories)],
    'articles' => ['num' => '04', 'name' => 'Blog Articles & Editor', 'icon' => 'ri-article-line', 'badge' => $totalArticlesCount],
    'sidebar'  => ['num' => '05', 'name' => 'Article Detail Sidebar', 'icon' => 'ri-layout-right-line'],
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
</style>

<div class="visual-studio-wrapper">

  <!-- Studio Header -->
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
      <h2 style="font-family: var(--wdr-font-display); font-size: 24px; font-weight: 700; color: var(--admin-navy); margin: 0;">
        <i class="ri-article-line" style="color: var(--admin-teal);"></i> Blog &amp; Journal Visual Section Studio
      </h2>
      <p style="font-size: 13px; color: var(--admin-muted); margin: 4px 0 0;">
        Manage blog cover presentation, newsletter capture bar, topics/categories, and rich WYSIWYG editorial articles.
      </p>
    </div>
    <div style="display: flex; gap: 10px;">
      <a href="<?= url('blog') ?>" target="_blank" class="btn-adm btn-adm-outline">
        <i class="ri-external-link-line"></i> View Live Blog
      </a>
      <a href="<?= url('admin/posts/edit.php') ?>" class="btn-adm btn-adm-primary">
        <i class="ri-edit-line"></i> Write New Article
      </a>
    </div>
  </div>

  <!-- Flash Alerts -->
  <?php if (!empty($editorSuccess)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; display: flex; align-items: center; gap: 10px;">
      <i class="ri-checkbox-circle-fill" style="font-size: 18px; color: #16A34A;"></i>
      <span><?= e($editorSuccess) ?></span>
      <a href="<?= url('blog') ?>" target="_blank" style="margin-left: auto; font-size: 12px; text-decoration: underline; color: #166534; font-weight: 700;">View Live Page <i class="ri-external-link-line"></i></a>
    </div>
  <?php endif; ?>

  <?php if (!empty($editorError)): ?>
    <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; font-size: 13px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;">
      <i class="ri-error-warning-line"></i> <?= e($editorError) ?>
    </div>
  <?php endif; ?>

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
       TAB 01: HERO BANNER SECTION (SINGLE BANNER)
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec01'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-image-line"></i> SECTION 01 — THE EDITORIAL BANNER</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Blog &amp; Journal Hero Banner &amp; Atmosphere</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure hero atmosphere banner image, headlines, and action buttons shown on Blog archive and detail pages.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec01" enctype="multipart/form-data">
      <?= CSRF::field() ?>

      <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 32px; align-items: start;">
        <div>
          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Eyebrow Badge Tag</label>
            <input type="text" name="hero_eyebrow" class="visual-input-styled" value="<?= e($heroEyebrow) ?>" placeholder="e.g. THE WORDORA EDITORIAL DISPATCH" style="font-weight: 700;">
          </div>

          <div style="margin-bottom: 16px;">
            <label class="visual-label-upper">Hero Main Headline <span style="color:#ef4444;">*</span></label>
            <input type="text" name="hero_title" class="visual-input-styled" required value="<?= e($heroTitle) ?>" placeholder="e.g. Perspectives on Content Architecture & Brand Storytelling" style="font-weight: 700; font-size: 16px;">
          </div>

          <div style="margin-bottom: 20px;">
            <label class="visual-label-upper">Hero Lead Subtitle</label>
            <textarea name="hero_subtitle" class="visual-input-styled" rows="4" placeholder="Actionable playbooks, strategic teardowns, and deep editorial insights..."><?= e($heroSubtitle) ?></textarea>
          </div>

        </div>

        <!-- Cover Image Preview -->
        <div>
          <div style="background: #FFF; padding: 24px; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 24px; text-align: center;">
            <label class="visual-label-upper" style="text-align: center; margin-bottom: 12px;"><i class="ri-image-add-line"></i> Hero Atmosphere Banner Artwork</label>
            
            <?php 
            $hasCustomHero = !empty($heroMediaUrl) && !str_starts_with($heroMediaUrl, '/img/');
            $resolvedHeroImg = !empty($heroMediaUrl) ? $heroMediaUrl : '/img/Blog service.png';
            ?>
            
            <div style="position: relative; border-radius: 12px; overflow: hidden; margin-bottom: 16px; border: 1.5px solid var(--admin-border); background: #0F1E36;">
              <img id="preview_hero_img" src="<?= media_url($resolvedHeroImg) ?>" alt="Blog Hero" style="max-height: 200px; width: 100%; object-fit: cover; display: block;">
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
                <button type="button" onclick="document.getElementById('remove_hero_image').value='1'; document.getElementById('preview_hero_img').src='<?= media_url('/img/Blog service.png') ?>'; this.style.display='none';" class="btn-adm-action btn-adm-delete" style="margin-top: 10px; width: 100%; justify-content: center; padding: 8px 12px; font-size: 12px; font-weight: 600;">
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
       TAB 02: NEWSLETTER CAPTURE BAR
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec02'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-mail-send-line"></i> SECTION 02 — NEWSLETTER CAPTURE BAR</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Editorial Dispatch Newsletter Bar</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure the newsletter signup call-to-action bar displayed below blog archive listings.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sec02">
      <?= CSRF::field() ?>
      
      <div style="background: #1B2A4A; color: #FFF; padding: 24px; border-radius: 16px; border: 1.5px dashed rgba(74,139,140,0.5); margin-bottom: 24px;">
        <span class="visual-badge" style="background: rgba(74,139,140,0.25); color: var(--wdr-teal-light); margin-bottom: 12px;">
          <i class="ri-sparkling-fill"></i> NEWSLETTER PREVIEW
        </span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Badge Text</label>
            <input type="text" name="news_badge" class="visual-input-styled" value="<?= e($newsBadge) ?>" placeholder="THE EDITORIAL DISPATCH" style="background: #0F1E36; color: #FFF; border-color: #334155;">
          </div>
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Main Heading</label>
            <input type="text" name="news_title" class="visual-input-styled" value="<?= e($newsTitle) ?>" placeholder="Insights That Sharpen Your Commercial Narrative" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper" style="color: #94A3B8;">Description Subtitle</label>
          <textarea name="news_desc" class="visual-input-styled" rows="2" placeholder="Join 14,000+ founders..." style="background: #0F1E36; color: #FFF; border-color: #334155;"><?= e($newsDesc) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Button Text</label>
            <input type="text" name="news_btn_text" class="visual-input-styled" value="<?= e($newsBtnText) ?>" placeholder="Join The Dispatch" style="background: #0F1E36; color: #FFF; border-color: #334155; font-weight: 700;">
          </div>
          <div>
            <label class="visual-label-upper" style="color: #94A3B8;">Privacy / Zero-Spam Note</label>
            <input type="text" name="news_note" class="visual-input-styled" value="<?= e($newsNote) ?>" placeholder="Strict zero-spam policy..." style="background: #0F1E36; color: #FFF; border-color: #334155;">
          </div>
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Newsletter Section</button>
    </form>
  </div>
  <?php endif; ?>


  <!-- ═══════════════════════════════════════════
       TAB 03: CATEGORIES MANAGER
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sec03'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-price-tag-3-line"></i> SECTION 03 — TOPICS &amp; CATEGORIES</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Article Topics &amp; Taxonomic Categories</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Add, manage, and delete article categories displayed on the blog archive filter bar.</p>
    </div>

    <!-- Add Category Form -->
    <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
      <h3 style="font-size: 15px; font-weight: 700; color: var(--wdr-navy); margin: 0 0 14px;"><i class="ri-add-circle-line" style="color: var(--wdr-teal);"></i> Create New Category</h3>
      
      <form method="POST" action="<?= $currentUrl ?>?tab=sec03">
        <?= CSRF::field() ?>
        <input type="hidden" name="action" value="create_category">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 2fr auto; gap: 14px; align-items: flex-end;">
          <div>
            <label class="visual-label-upper">Category Name <span style="color:#ef4444;">*</span></label>
            <input type="text" name="cat_name" class="visual-input-styled" required placeholder="e.g. SEO Architecture" style="font-weight: 600;">
          </div>
          <div>
            <label class="visual-label-upper">URL Slug</label>
            <input type="text" name="cat_slug" class="visual-input-styled" placeholder="e.g. seo-architecture" style="font-family: var(--wdr-font-mono);">
          </div>
          <div>
            <label class="visual-label-upper">Description (Optional)</label>
            <input type="text" name="cat_desc" class="visual-input-styled" placeholder="Short description for archive header...">
          </div>
          <div>
            <button type="submit" class="btn-adm btn-adm-primary" style="padding: 10px 18px; font-weight: 700; white-space: nowrap;">
              <i class="ri-add-line"></i> Add Category
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Categories List Table -->
    <div class="admin-card-table-wrapper">
      <div class="table-top-bar">
        <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
          <i class="ri-list-check-2" style="color: var(--wdr-teal); font-size: 16px;"></i> Active Topics Directory
        </div>
        <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
          <?= count($categoriesWithCount) ?> Categories
        </span>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="min-width: 200px;">Category Name</th>
              <th style="min-width: 180px;">URL Slug</th>
              <th style="min-width: 260px;">Description</th>
              <th style="width: 120px;">Total Articles</th>
              <th style="text-align: right; width: 100px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categoriesWithCount as $cat): ?>
              <tr>
                <td>
                  <div style="font-weight: 700; color: var(--wdr-navy); font-size: 14px;">
                    <?= e($cat['name']) ?>
                  </div>
                </td>
                <td>
                  <code style="font-family: var(--wdr-font-mono); font-size: 12px; color: var(--wdr-teal); background: var(--wdr-teal-pale); padding: 2px 6px; border-radius: 4px;">
                    <?= e($cat['slug']) ?>
                  </code>
                </td>
                <td>
                  <div style="font-size: 12.5px; color: #64748B;">
                    <?= e($cat['description'] ?? '—') ?>
                  </div>
                </td>
                <td>
                  <span style="display: inline-block; font-size: 11.5px; font-weight: 700; color: var(--wdr-navy); background: #FAF8F5; padding: 4px 10px; border-radius: 8px; border: 1.5px dashed rgba(74,139,140,0.35);">
                    <?= $cat['count'] ?> Posts
                  </span>
                </td>
                <td style="text-align: right;">
                  <div class="table-actions" style="justify-content: flex-end;">
                    <form method="POST" action="<?= $currentUrl ?>?tab=sec03" onsubmit="return confirm('Delete category <?= e($cat['name']) ?>?');" style="display: inline;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="delete_category">
                      <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                      <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Category">
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
       TAB 04: ARTICLES DIRECTORY & WYSIWYG EDITOR
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'articles'): ?>
  <div class="visual-studio-card">
    
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
      <div>
        <span class="visual-badge"><i class="ri-article-line"></i> SECTION 04 — ARTICLES DIRECTORY</span>
        <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Published Articles &amp; WYSIWYG Editor</h2>
        <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Author, format, and publish full-length thought leadership essays, SEO guides, and technical blog posts.</p>
      </div>

      <div style="display: flex; gap: 10px;">
        <a href="<?= url('admin/posts/edit.php') ?>" class="btn-adm btn-adm-primary">
          <i class="ri-quill-pen-line"></i> Write New Article
        </a>
      </div>
    </div>

    <!-- Category Filter Bar -->
    <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
      <a href="?tab=articles&category=" class="btn-adm-action" style="<?= empty($catFilter) ? 'background: var(--wdr-navy); color: #FFF; border-color: var(--wdr-navy);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
        <span>All Articles</span> <strong style="font-family: var(--wdr-font-mono);">(<?= $totalArticlesCount ?>)</strong>
      </a>
      <?php foreach ($categoriesWithCount as $c): ?>
        <a href="?tab=articles&category=<?= (int)$c['id'] ?>" class="btn-adm-action" style="<?= ((int)$catFilter === (int)$c['id']) ? 'background: var(--wdr-teal); color: #FFF; border-color: var(--wdr-teal);' : 'background: #FFF; border: 1.5px dashed rgba(74,139,140,0.35); color: var(--wdr-navy);' ?>">
          <span><?= e($c['name']) ?></span> <strong style="font-family: var(--wdr-font-mono);">(<?= $c['count'] ?>)</strong>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Articles Table -->
    <div class="admin-card-table-wrapper">
      <div class="table-top-bar">
        <div style="font-size: 13.5px; font-weight: 700; color: var(--wdr-navy); display: flex; align-items: center; gap: 8px;">
          <i class="ri-file-text-line" style="color: var(--wdr-teal); font-size: 16px;"></i> Articles Directory
        </div>
        <span class="visual-badge" style="padding: 4px 12px; font-size: 11px; font-weight: 700;">
          Showing <?= count($allPosts) ?> Articles
        </span>
      </div>

      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width: 60px;">Image</th>
              <th style="min-width: 260px;">Article Title</th>
              <th style="min-width: 140px;">Category</th>
              <th style="min-width: 120px;">Read Time</th>
              <th style="width: 110px;">Status</th>
              <th style="min-width: 120px;">Published Date</th>
              <th style="text-align: right; width: 140px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allPosts as $p): ?>
              <tr>
                <!-- Image -->
                <td>
                  <div style="width: 48px; height: 38px; border-radius: 8px; overflow: hidden; background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); display: flex; align-items: center; justify-content: center;">
                    <img src="<?= media_url($p['featured_img'] ?: '/img/Blog service.png') ?>" alt="<?= e($p['title']) ?>" style="max-width: 100%; max-height: 100%; object-fit: cover;">
                  </div>
                </td>

                <!-- Title & Slug -->
                <td>
                  <div style="font-weight: 700; color: var(--wdr-navy); font-size: 13.5px; line-height: 1.35; margin-bottom: 2px;">
                    <?= e($p['title']) ?>
                  </div>
                  <div style="font-size: 11.5px; color: #64748B; font-family: var(--wdr-font-mono);">
                    <code><?= e($p['slug']) ?></code>
                  </div>
                </td>

                <!-- Category -->
                <td>
                  <span style="display: inline-block; font-size: 11.5px; font-weight: 700; color: var(--wdr-navy); background: var(--wdr-teal-pale); padding: 3px 8px; border-radius: 6px; border: 1px dashed rgba(74, 139, 140, 0.4);">
                    <?= e($p['category_name'] ?: 'Uncategorized') ?>
                  </span>
                </td>

                <!-- Read Time -->
                <td>
                  <div style="font-size: 12px; color: #64748B; font-family: var(--wdr-font-mono); display: flex; align-items: center; gap: 4px;">
                    <i class="ri-time-line" style="color: var(--wdr-teal);"></i> <?= (int)($p['read_time'] ?: 5) ?> min read
                  </div>
                </td>

                <!-- Status -->
                <td>
                  <?php if (($p['status'] ?? '') === 'published'): ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0;">
                      <i class="ri-checkbox-circle-fill"></i> Published
                    </span>
                  <?php else: ?>
                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">
                      <i class="ri-eye-off-line"></i> Draft
                    </span>
                  <?php endif; ?>
                </td>

                <!-- Date -->
                <td>
                  <div style="font-size: 12px; color: #64748B; font-family: var(--wdr-font-mono);">
                    <?= date('M d, Y', strtotime($p['created_at'])) ?>
                  </div>
                </td>

                <!-- Actions -->
                <td style="text-align: right;">
                  <div class="table-actions" style="justify-content: flex-end;">
                    <a href="<?= url('blog/' . e($p['slug'])) ?>" target="_blank" class="btn-adm-action btn-adm-edit" title="View Live Article">
                      <i class="ri-external-link-line"></i>
                    </a>
                    <a href="<?= url('admin/posts/edit.php?id=' . (int)$p['id']) ?>" class="btn-adm-action btn-adm-edit" title="Edit in WYSIWYG Editor">
                      <i class="ri-edit-line"></i> Edit
                    </a>
                    <form method="POST" action="<?= $currentUrl ?>?tab=articles" onsubmit="return confirm('Delete article <?= e($p['title']) ?>?');" style="display: inline;">
                      <?= CSRF::field() ?>
                      <input type="hidden" name="action" value="delete_post">
                      <input type="hidden" name="post_id" value="<?= (int)$p['id'] ?>">
                      <button type="submit" class="btn-adm-action btn-adm-delete" title="Delete Article">
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
       TAB 05: BLOG DETAIL SIDEBAR WIDGETS
       ═══════════════════════════════════════════ -->
  <?php if ($activeTab === 'sidebar'): ?>
  <div class="visual-studio-card">
    <div style="margin-bottom: 20px;">
      <span class="visual-badge"><i class="ri-layout-right-line"></i> SECTION 05 — ARTICLE DETAIL RIGHT SIDEBAR</span>
      <h2 class="visual-display-heading" style="margin: 8px 0 4px;">Blog Post Sidebar Customization</h2>
      <p style="color: var(--admin-muted); font-size: 13px; margin: 0;">Configure dynamic right-sidebar widgets shown across all blog post reading pages.</p>
    </div>

    <form method="POST" action="<?= $currentUrl ?>?tab=sidebar">
      <?= CSRF::field() ?>
      
      <!-- 1. Content Partnership CTA Box -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-sparkling-fill"></i> WIDGET 01 — CONTENT PARTNERSHIP DISCOVERY BOX</span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Badge Text</label>
            <input type="text" name="sidebar_cta_badge" class="visual-input-styled" value="<?= e($sideCtaBadge) ?>" placeholder="Content Partnership">
          </div>
          <div>
            <label class="visual-label-upper">Heading Title</label>
            <input type="text" name="sidebar_cta_title" class="visual-input-styled" value="<?= e($sideCtaTitle) ?>" placeholder="Need High-Authority Content Like This?">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Description</label>
          <textarea name="sidebar_cta_desc" class="visual-input-styled" rows="2"><?= e($sideCtaDesc) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label class="visual-label-upper">Button Text</label>
            <input type="text" name="sidebar_cta_btn_text" class="visual-input-styled" value="<?= e($sideCtaBtnText) ?>" placeholder="Request Scope Audit">
          </div>
          <div>
            <label class="visual-label-upper">Button URL</label>
            <input type="text" name="sidebar_cta_btn_url" class="visual-input-styled" value="<?= e($sideCtaBtnUrl) ?>" placeholder="contact.php">
          </div>
        </div>
      </div>

      <!-- 2. Weekly Brief Newsletter Box -->
      <div style="background: #FFF; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <span class="visual-badge" style="margin-bottom: 14px;"><i class="ri-mail-star-line"></i> WIDGET 02 — EXECUTIVE EDITORIAL DIGEST BRIEF</span>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
          <div>
            <label class="visual-label-upper">Badge Text</label>
            <input type="text" name="sidebar_news_badge" class="visual-input-styled" value="<?= e($sideNewsBadge) ?>" placeholder="Weekly Brief">
          </div>
          <div>
            <label class="visual-label-upper">Heading Title</label>
            <input type="text" name="sidebar_news_title" class="visual-input-styled" value="<?= e($sideNewsTitle) ?>" placeholder="The Executive Editorial Digest">
          </div>
        </div>

        <div style="margin-bottom: 14px;">
          <label class="visual-label-upper">Description</label>
          <textarea name="sidebar_news_desc" class="visual-input-styled" rows="2"><?= e($sideNewsDesc) ?></textarea>
        </div>

        <div>
          <label class="visual-label-upper">Subscribe Button Text</label>
          <input type="text" name="sidebar_news_btn_text" class="visual-input-styled" value="<?= e($sideNewsBtnText) ?>" placeholder="Subscribe Free">
        </div>
      </div>

      <button type="submit" class="btn-adm btn-adm-primary"><i class="ri-save-line"></i> Save Sidebar Settings</button>
    </form>
  </div>
  <?php endif; ?>

</div>
