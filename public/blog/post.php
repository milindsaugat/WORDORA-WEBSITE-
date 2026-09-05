<?php
/**
 * WORDORA — Single Blog Post Page
 * Layout: Full Cover Hero (Matching Service Detail & Services Page) + 2-Column Editorial Reading Layout + Sticky Sidebar
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    redirect('blog');
}

// Try to load from DB
$post = null;
try {
    $post = Post::getBySlug($slug);
} catch (Exception $e) {
    // DB error fallback
}

// Fallback: If not a blog post, check if the direct root slug belongs to a Service or Case Study
if (!$post) {
    try {
        if (class_exists('Service')) {
            $srv = Service::getBySlug($slug);
            if ($srv) {
                $isDev = (int)($srv['id'] ?? 0) > 7;
                $devEnabled = (setting('home_sec3c_enabled', '1') !== '0');
                if ($isDev && !$devEnabled) {
                    require_once ROOT_PATH . '/public/404.php';
                    exit;
                }
                $_GET['slug'] = $slug;
                require_once ROOT_PATH . '/public/service-detail.php';
                exit;
            }
        }
    } catch (Exception $e) {}

    try {
        if (class_exists('CaseStudy')) {
            $cs = CaseStudy::getBySlug($slug);
            if ($cs) {
                $_GET['slug'] = $slug;
                require_once ROOT_PATH . '/public/case-study-detail.php';
                exit;
            }
        }
    } catch (Exception $e) {}
}

if (!$post) {
    require_once ROOT_PATH . '/public/404.php';
    exit;
}

// Increment views
Post::incrementViews($post['id']);

$postSeoTitle = setting("seo_post_{$post['slug']}_title");
$postSeoDesc  = setting("seo_post_{$post['slug']}_desc");
$postSeoKw    = setting("seo_post_{$post['slug']}_keywords");
$postSeoOg    = setting("seo_post_{$post['slug']}_og_image");

$meta = [
    'title'       => !empty($postSeoTitle) ? $postSeoTitle : (e($post['meta_title'] ?: $post['title']) . ' — WORDORA Journal'),
    'description' => !empty($postSeoDesc) ? $postSeoDesc : (e($post['meta_desc'] ?: truncate(strip_tags($post['excerpt'] ?: $post['content']), 155))),
    'keywords'    => !empty($postSeoKw) ? $postSeoKw : ($post['meta_keywords'] ?? ''),
    'og_image'    => !empty($postSeoOg) ? $postSeoOg : media_url($post['featured_img'], '/img/blog.png'),
];

// Fetch Sidebar Data: Latest Insights (excluding current post) + Related Posts + Categories
$latestInsights = Post::getLatest(4, (int)$post['id']);
$relatedPosts   = Post::getRelated((int)$post['id'], (int)($post['category_id'] ?? 0), 3);
$allCategories  = Category::getAll();

ob_start();
?>

<!-- Reading Progress Indicator -->
<div class="reading-progress" id="readingProgressBar" style="position: fixed; top: 0; left: 0; height: 4px; background: var(--color-teal-ink); z-index: 10000; width: 0%;"></div>

<?php
// Fetch Parent Blog Section Banner Background
$heroSlide = DB::getInstance()->query("SELECT * FROM hero_slides WHERE page = 'blog' AND is_active = 1 ORDER BY sort_order ASC LIMIT 1")->fetch();
$blogHeroBgUrl = !empty($heroSlide['media_url']) ? media_url($heroSlide['media_url']) : img('Blog service.png');
$heroGradient = get_hero_directional_gradient();
?>

<!-- ═══════════════════════════════════════════
     01 — CLEAN COVER HERO BANNER (USING BLOG SECTION ATMOSPHERE BANNER)
     ═══════════════════════════════════════════ -->
<section class="hero hero--bg-image" id="heroSection" style="background-image: <?= $heroGradient ?>, url('<?= $blogHeroBgUrl ?>');">
  <div class="container container-hero" style="position: relative; z-index: 2;">
    <div class="hero__body-full" style="max-width: 980px;">
      
      <span class="label-upper hero__eyebrow animate-hero-text" style="color: var(--color-teal-light);">
        <i class="ri-article-line"></i> WORDORA JOURNAL &amp; INSIGHTS
      </span>

      <!-- Master Headline in Hero -->
      <h1 class="heading-hero animate-hero-text" style="font-size: clamp(2rem, 3.4vw, 2.9rem); line-height: 1.25; margin-bottom: 0;">
        <?= e($post['title']) ?>
      </h1>

    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — 2-COLUMN EDITORIAL READING LAYOUT
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-10) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">
    
    <div class="blog-detail-grid">
      
      <!-- ═══════════════════════════════════════════
           LEFT COLUMN: MAIN ARTICLE CONTENT (68%)
           ═══════════════════════════════════════════ -->
      <main class="blog-main-content">

        <!-- Top Metadata & Social Share Bar (#ffffff, dashed border) -->
        <div class="blog-meta-bar reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 16px 22px; margin-bottom: var(--space-6); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          
          <div class="blog-meta-bar__info" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <?php if (!empty($post['category_name'])): ?>
              <a href="<?= url('blog/?category=' . urlencode($post['category_slug'] ?? '')) ?>" class="badge badge-teal" style="text-decoration: none;">
                <i class="ri-price-tag-3-line"></i> <?= e($post['category_name']) ?>
              </a>
            <?php endif; ?>
            
            <span class="meta-item" style="font-size: 0.8125rem; color: var(--color-text-muted); font-family: var(--font-mono);">
              <i class="ri-time-line"></i> <?= (int)$post['read_time'] ?> min read
            </span>
            <span class="meta-dot" style="color: var(--color-border);">•</span>
            <span class="meta-item" style="font-size: 0.8125rem; color: var(--color-text-muted); font-family: var(--font-mono);">
              <i class="ri-calendar-line"></i> <?= format_date($post['created_at']) ?>
            </span>
            <span class="meta-dot" style="color: var(--color-border);">•</span>
            <span class="meta-item" style="font-size: 0.8125rem; color: var(--color-navy); font-weight: 600;">
              By <?= e($post['author_name'] ?? 'Admin') ?>
            </span>
          </div>

          <!-- Social Share Buttons -->
          <div class="blog-meta-bar__share" style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
            <span style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; font-family: var(--font-mono); margin-right: 4px;">Share:</span>
            
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(current_url()) ?>" 
               target="_blank" rel="noopener noreferrer" 
               class="btn btn-ghost btn-sm" style="background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); color: var(--color-navy); padding: 4px 10px; font-size: 0.78rem;">
              <i class="ri-linkedin-fill" style="color: #0077b5;"></i> <span>LinkedIn</span>
            </a>
            
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($post['title']) ?>" 
               target="_blank" rel="noopener noreferrer" 
               class="btn btn-ghost btn-sm" style="background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); color: var(--color-navy); padding: 4px 10px; font-size: 0.78rem;">
              <i class="ri-twitter-x-line"></i> <span>X</span>
            </a>

            <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' ' . current_url()) ?>" 
               target="_blank" rel="noopener noreferrer" 
               class="btn btn-ghost btn-sm" style="background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); color: var(--color-navy); padding: 4px 10px; font-size: 0.78rem;">
              <i class="ri-whatsapp-line" style="color: #25d366;"></i> <span>WhatsApp</span>
            </a>
          </div>

        </div>

        <?php if (!empty($post['excerpt'])): ?>
        <!-- Lead Excerpt Quote Box -->
        <div class="reveal-up" style="background: rgba(74, 139, 140, 0.05); border-left: 4px solid var(--color-teal-ink); border-radius: 0 16px 16px 0; padding: 18px 24px; margin-bottom: var(--space-6);">
          <p style="font-size: 1.0625rem; line-height: 1.65; color: var(--color-navy); margin: 0; font-style: italic;">
            "<?= e($post['excerpt']) ?>"
          </p>
        </div>
        <?php endif; ?>
        
        <!-- Large Featured Image Frame (Edge-to-edge cover fit) -->
        <?php if (!empty($post['featured_img'])): ?>
        <div class="blog-featured-img-frame reveal-up" style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: var(--space-8); width: 100%; max-height: 480px; box-shadow: none !important; display: block;">
          <img src="<?= media_url($post['featured_img'], '/img/blog.png') ?>" 
               alt="<?= e($post['title']) ?>" 
               loading="eager"
               style="width: 100%; height: 100%; max-height: 480px; object-fit: cover; object-position: center; display: block; border-radius: 22px;">
        </div>
        <?php endif; ?>

        <!-- Formatted Editorial Copy -->
        <article class="blog-body-copy reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 24px; padding: 2.5rem 2.8rem; box-shadow: none !important; margin-bottom: var(--space-8);">
          <?= $post['content'] ?>
        </article>

        <!-- Category Tag Strip & Back Link -->
        <div class="blog-category-strip reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: var(--space-8);">
          <div style="display: flex; align-items: center; gap: 10px;">
            <i class="ri-price-tag-3-fill" style="color: var(--color-teal-ink);"></i>
            <span style="font-weight: 700; color: var(--color-navy); font-size: 0.9375rem;">Category:</span>
            <a href="<?= url('blog/?category=' . urlencode($post['category_slug'] ?? '')) ?>" class="badge badge-teal">
              <?= e($post['category_name'] ?? 'Editorial Craft') ?>
            </a>
          </div>

          <a href="<?= url('blog/') ?>" style="font-size: 0.875rem; color: var(--color-teal-ink); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
            <i class="ri-arrow-left-line"></i> Back to All Articles
          </a>
        </div>

        <!-- Author Bio Consultation Card -->
        <div class="blog-author-card reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 28px; display: flex; gap: 20px; align-items: flex-start; margin-bottom: var(--space-8);">
          <div class="blog-author-avatar" style="width: 64px; height: 64px; border-radius: 50%; background: var(--color-navy); color: var(--color-teal-light); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; border: 2px solid var(--color-teal-ink);">
            <i class="ri-quill-pen-line"></i>
          </div>
          <div class="blog-author-body" style="flex: 1; min-width: 0;">
            <div class="blog-author-head" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 6px;">
              <h4 style="font-family: var(--font-body); font-weight: 700; font-size: 1.15rem; color: var(--color-navy); margin: 0;">
                Written by <?= e($post['author_name'] ?? 'WORDORA Editorial Board') ?>
              </h4>
              <span class="badge badge-teal" style="font-size: 0.6875rem; padding: 3px 8px;">Verified Senior Editor</span>
            </div>
            <p style="font-size: 0.9375rem; color: var(--color-text-muted); line-height: 1.6; margin: 0 0 14px 0;">
              Our dedicated editorial strategists, SEO topic architects, and copywriters build authoritative search assets, whitepapers, and brand narratives for high-growth brands worldwide.
            </p>
            <a href="<?= url('contact.php') ?>" class="btn btn-ghost btn-sm blog-author-btn" style="border: 1px solid var(--color-border); color: var(--color-navy); padding: 7px 16px;">
              <span>Schedule Strategy Consultation</span> <i class="ri-arrow-right-line"></i>
            </a>
          </div>
        </div>

      </main>


      <!-- ═══════════════════════════════════════════
           RIGHT COLUMN: STICKY EDITORIAL SIDEBAR (32%)
           ═══════════════════════════════════════════ -->
      <aside class="blog-sticky-sidebar">
        
        <!-- Widget 1: Latest Insights (Recent Articles) -->
        <?php if (!empty($latestInsights)): ?>
        <div class="sidebar-widget reveal-up">
          <div class="sidebar-widget__title">
            <i class="ri-flashlight-fill" style="color: var(--color-teal-ink);"></i>
            <span>Latest Insights</span>
          </div>

          <div style="display: flex; flex-direction: column;">
            <?php foreach ($latestInsights as $li): ?>
            <a href="<?= url('blog/' . e($li['slug'])) ?>" class="sidebar-insight-item">
              <div class="sidebar-insight-thumb">
                <img src="<?= media_url($li['featured_img'], '/img/blog.png') ?>" alt="<?= e($li['title']) ?>" loading="lazy">
              </div>
              <div class="sidebar-insight-info">
                <h4><?= e(truncate($li['title'], 55)) ?></h4>
                <div class="sidebar-insight-meta">
                  <span><i class="ri-calendar-line"></i> <?= format_date($li['created_at']) ?></span>
                  <span>•</span>
                  <span><?= (int)$li['read_time'] ?>m read</span>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Widget 2: Dynamic Editorial Studio Discovery CTA Box -->
        <?php
        $sideCtaBadge   = setting('blog_sidebar_cta_badge', 'Content Partnership');
        $sideCtaTitle   = setting('blog_sidebar_cta_title', 'Need High-Authority Content Like This?');
        $sideCtaDesc    = setting('blog_sidebar_cta_desc', 'Partner with our managing editors and senior domain writers to publish topic clusters, thought leadership, and brand narratives.');
        $sideCtaBtnText = setting('blog_sidebar_cta_btn_text', 'Request Scope Audit');
        $sideCtaBtnUrl  = setting('blog_sidebar_cta_btn_url', 'contact.php');
        ?>
        <div class="sidebar-widget reveal-up" style="background: var(--color-navy); border-color: rgba(74, 139, 140, 0.4); text-align: center; color: var(--color-white);">
          <span class="badge" style="background: rgba(74, 139, 140, 0.25); color: var(--color-teal-light); margin-bottom: 12px; border: 1px dashed var(--color-teal-ink);">
            <i class="ri-sparkling-fill"></i> <?= e($sideCtaBadge) ?>
          </span>
          <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-white); margin-bottom: 10px; line-height: 1.3;">
            <?= e($sideCtaTitle) ?>
          </h3>
          <p style="font-size: 0.84375rem; color: rgba(255, 255, 255, 0.78); line-height: 1.55; margin-bottom: 18px;">
            <?= e($sideCtaDesc) ?>
          </p>
          <a href="<?= url($sideCtaBtnUrl) ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
            <span><?= e($sideCtaBtnText) ?></span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>

        <!-- Widget 3: Categories & Topics Cloud -->
        <?php if (!empty($allCategories)): ?>
        <div class="sidebar-widget reveal-up">
          <div class="sidebar-widget__title">
            <i class="ri-folder-open-line" style="color: var(--color-teal-ink);"></i>
            <span>Editorial Categories</span>
          </div>

          <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <?php foreach ($allCategories as $cat): ?>
            <a href="<?= url('blog/?category=' . urlencode($cat['slug'])) ?>" 
               class="btn btn-ghost btn-sm" 
               style="font-size: 0.75rem; padding: 5px 12px; background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); color: var(--color-navy);">
              <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Widget 4: Featured Client Case Study Spotlight -->
        <?php
        require_once ROOT_PATH . '/models/CaseStudy.php';
        CaseStudy::ensureTable();
        $spotlightStudy = CaseStudy::getLatest(1)[0] ?? null;
        if ($spotlightStudy):
        ?>
        <div class="sidebar-widget reveal-up" style="border: 1.5px dashed rgba(74, 139, 140, 0.45); background: #FAF8F5;">
          <div class="sidebar-widget__title">
            <i class="ri-checkbox-circle-fill" style="color: var(--color-teal-ink);"></i>
            <span>Featured Case Study</span>
          </div>

          <div style="margin-bottom: 12px; border-radius: 12px; overflow: hidden; background: #ffffff; padding: 12px; border: 1px dashed rgba(74, 139, 140, 0.25); text-align: center;">
            <img src="<?= img($spotlightStudy['image'] ?: 'service treasure.png') ?>" alt="<?= e($spotlightStudy['title']) ?>" style="max-height: 120px; width: auto; object-fit: contain;">
          </div>

          <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 8px;">
            <span class="badge badge-teal" style="font-size: 0.6875rem; padding: 2px 8px;"><?= e($spotlightStudy['badge'] ?: 'Enterprise') ?></span>
            <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 700;"><?= e($spotlightStudy['headline_metric']) ?></span>
          </div>

          <h4 style="font-family: var(--font-body); font-weight: 700; font-size: 0.95rem; color: var(--color-navy); line-height: 1.35; margin-bottom: 8px;">
            <?= e($spotlightStudy['title']) ?>
          </h4>

          <p style="font-size: 0.8125rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 14px;">
            <?= e(truncate($spotlightStudy['excerpt'] ?: $spotlightStudy['challenge'], 110)) ?>
          </p>

          <a href="<?= url('case-study/' . e($spotlightStudy['slug'])) ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8125rem;">
            <span>Read Case Study</span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <?php endif; ?>

        <!-- Widget 5: The WORDORA Editorial Digest (Newsletter Subscription) -->
        <?php
        $sideNewsBadge   = setting('blog_sidebar_news_badge', 'Weekly Brief');
        $sideNewsTitle   = setting('blog_sidebar_news_title', 'The Executive Editorial Digest');
        $sideNewsDesc    = setting('blog_sidebar_news_desc', 'Join 12,000+ leaders receiving weekly breakdowns of search algorithm updates & topic cluster playbooks.');
        $sideNewsBtnText = setting('blog_sidebar_news_btn_text', 'Subscribe Free');
        ?>
        <div class="sidebar-widget reveal-up" style="background: var(--color-navy); color: #ffffff; border-color: rgba(74, 139, 140, 0.35);">
          <span class="badge" style="background: rgba(74, 139, 140, 0.25); color: var(--color-teal-light); border: 1px dashed var(--color-teal-ink); margin-bottom: 12px;">
            <i class="ri-mail-star-line"></i> <?= e($sideNewsBadge) ?>
          </span>

          <h3 style="font-family: var(--font-display); font-size: 1.2rem; color: #ffffff; margin-bottom: 8px; line-height: 1.3;">
            <?= e($sideNewsTitle) ?>
          </h3>

          <p style="font-size: 0.8125rem; color: rgba(255, 255, 255, 0.78); line-height: 1.5; margin-bottom: 16px;">
            <?= e($sideNewsDesc) ?>
          </p>

          <form action="<?= url('api/subscribe.php') ?>" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
            <input type="email" name="email" placeholder="Enter your work email" required
                   style="width: 100%; padding: 9px 14px; font-size: 0.8125rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.08); color: #ffffff; outline: none;">
            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8125rem;">
              <span><?= e($sideNewsBtnText) ?></span> <i class="ri-send-plane-fill"></i>
            </button>
          </form>
        </div>

        <!-- Widget 6: Editorial Quality & Craft Standards -->
        <?php
        $sideStandards = json_decode(setting('blog_sidebar_standards', '[]'), true);
        if (empty($sideStandards)) {
            $sideStandards = [
                '<strong>100% Human Writers</strong> (Zero AI Hallucinations)',
                '<strong>4-Tier Editorial Review</strong> Protocol',
                '<strong>Turnitin Screening</strong> &amp; Citations',
                '<strong>Full IP &amp; Copyright</strong> Transfer'
            ];
        }
        ?>
        <div class="sidebar-widget reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
          <div class="sidebar-widget__title">
            <i class="ri-shield-star-line" style="color: var(--color-teal-ink);"></i>
            <span>Editorial Standards</span>
          </div>

          <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.8125rem; color: var(--color-navy);">
            <?php foreach ($sideStandards as $stItem): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ri-checkbox-circle-fill" style="color: var(--color-teal-ink);"></i>
                <span><?= $stItem ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </aside>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     03 — CONTINUE READING (MULTI-CARD SWIPER SLIDER)
     ═══════════════════════════════════════════ -->
<?php 
// Load 8+ other articles for smooth multi-card slider
$sliderPosts = Post::getLatest(9, (int)$post['id']);
if (!empty($sliderPosts)): 
?>
<section class="section" style="background: var(--color-white); border-top: 1px dashed var(--color-border); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    
    <div class="reveal-up" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-10); flex-wrap: wrap; gap: 16px;">
      <div>
        <span class="label-upper">CONTINUE READING</span>
        <h2 class="heading-lg" style="margin-top: var(--space-2); color: var(--color-navy); margin-bottom: 0;">Related Editorial Insights</h2>
      </div>

      <!-- Carousel Navigation Controls -->
      <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" class="btn-cr-prev" style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid var(--color-border); background: #FAF8F5; color: var(--color-navy); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.2s;" aria-label="Previous Slide">
          <i class="ri-arrow-left-line"></i>
        </button>
        <button type="button" class="btn-cr-next" style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid var(--color-teal-ink); background: var(--color-teal-ink); color: #FFF; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.2s;" aria-label="Next Slide">
          <i class="ri-arrow-right-line"></i>
        </button>
      </div>
    </div>

    <!-- Swiper Multi-Card Slider Container -->
    <div class="swiper continueReadingSwiper" style="padding-bottom: 30px; overflow: hidden;">
      <div class="swiper-wrapper">
        <?php foreach ($sliderPosts as $rel): ?>
        <div class="swiper-slide" style="height: auto;">
          <article class="blog-card" style="height: 100%; display: flex; flex-direction: column;">
            <div class="blog-card__image">
              <a href="<?= url('blog/' . e($rel['slug'])) ?>" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
                <img src="<?= media_url($rel['featured_img'], '/img/blog.png') ?>" alt="<?= e($rel['title']) ?>" loading="lazy">
              </a>
              <?php if (!empty($rel['category_name'])): ?>
                <span class="badge badge-teal blog-card__tag"><?= e($rel['category_name']) ?></span>
              <?php endif; ?>
            </div>
            <div class="blog-card__body" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
              <div>
                <h3 class="blog-card__title">
                  <a href="<?= url('blog/' . e($rel['slug'])) ?>" style="color: var(--color-navy); text-decoration: none;">
                    <?= e($rel['title']) ?>
                  </a>
                </h3>
                <p class="blog-card__excerpt"><?= e(truncate(strip_tags($rel['excerpt'] ?: $rel['content']), 120)) ?></p>
              </div>
              <div class="blog-card__footer" style="margin-top: 14px;">
                <span><i class="ri-calendar-line"></i> <?= format_date($rel['created_at']) ?></span>
                <a href="<?= url('blog/' . e($rel['slug'])) ?>" style="color: var(--color-teal-ink); font-weight: 700; font-size: 0.8125rem; text-decoration: none;">
                  Read Article <i class="ri-arrow-right-line"></i>
                </a>
              </div>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination" style="position: relative; margin-top: 20px;"></div>
    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof Swiper !== 'undefined') {
    new Swiper('.continueReadingSwiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: false,
      navigation: {
        nextEl: '.btn-cr-next',
        prevEl: '.btn-cr-prev',
      },
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        640: {
          slidesPerView: 2,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 3,
          spaceBetween: 24,
        }
      }
    });
  }
});
</script>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     04 — SIGNATURE CTA PANEL
     ═══════════════════════════════════════════ -->
<section class="section" style="padding-top: var(--space-8); padding-bottom: var(--space-20); background: var(--color-canvas);">
  <div class="container">
    <div class="cta-signature reveal-up">
      <div class="cta-signature__content">
        <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3); border: 1px solid rgba(212, 234, 234, 0.25);">
          <i class="ri-sparkling-fill"></i> SCALE YOUR EDITORIAL PRESENCE
        </span>
        
        <h2 class="cta-signature__title">Ready to build content <em>worth reading?</em></h2>
        
        <p class="cta-signature__text">
          Partner with our senior domain writers and managing editors to publish high-ranking topic clusters, whitepapers, and brand narratives.
        </p>

        <div class="cta-signature__actions">
          <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-lg">
            Schedule Content Audit <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url('services.php') ?>" class="btn btn-ghost btn-lg">
            Explore Services <i class="ri-compass-3-line"></i>
          </a>
        </div>

        <div class="cta-trust-pills">
          <span class="cta-trust-pill"><i class="ri-checkbox-circle-fill"></i> 24h Response</span>
          <span class="cta-trust-pill"><i class="ri-shield-check-fill"></i> NDA Protected</span>
          <span class="cta-trust-pill"><i class="ri-file-list-3-fill"></i> Free Scope Review</span>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= img('cta 1.png') ?>" alt="WORDORA Editorial Partnership" loading="lazy" style="transform: rotate(-5.5deg);">
      </div>
    </div>
  </div>
</section>

<!-- Reading Scroll Progress Bar JS -->
<script>
window.addEventListener('scroll', () => {
  const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
  const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  const scrolled = (winScroll / height) * 100;
  const bar = document.getElementById('readingProgressBar');
  if (bar) bar.style.width = scrolled + '%';
});
</script>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>
