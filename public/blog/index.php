<?php
/**
 * WORDORA — Editorial Blog & Journal Archive Page
 * Layout: Pure 3-Column Uniform Card Grid (No oversized card, clean grid on All Articles & Filtered Topics)
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/models/Post.php';
require_once ROOT_PATH . '/models/Category.php';
require_once ROOT_PATH . '/models/CaseStudy.php';

// Handle legacy tab query parameter if any
if (isset($_GET['tab']) && $_GET['tab'] === 'case-studies') {
    redirect('case-studies');
}

$categorySlug = trim($_GET['category'] ?? '');
$searchQuery  = trim($_GET['q'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 9; // 9 posts per page in 3x3 uniform grid
$offset       = ($page - 1) * $limit;

$activeCategory = null;
$categoryId = null;
$categories = [];
$posts = [];
$totalPosts = 0;

try {
    $categories = Category::getAll();

    if (!empty($categorySlug)) {
        $activeCategory = Category::getBySlug($categorySlug);
        if ($activeCategory) {
            $categoryId = (int)$activeCategory['id'];
        }
    }

    if (!empty($searchQuery)) {
        $posts = Post::search($searchQuery, $limit, $offset);
        $totalPosts = count($posts);
    } else {
        $posts = Post::getPublished($limit, $offset, $categoryId);
        $totalPosts = Post::countPublished($categoryId);
    }
} catch (Exception $e) {
    $categories = [];
    $posts = [];
    $totalPosts = 0;
}

$totalPages = max(1, (int)ceil($totalPosts / $limit));

// Total case studies count for switcher badge
$totalStudiesCount = CaseStudy::countAll();

$meta = [
    'title' => ($activeCategory ? e($activeCategory['name']) . ' Articles — ' : '') . 'WORDORA Journal & Articles',
    'description' => 'Actionable insights on SEO topical authority, brand narrative architecture, technical documentation, and content strategy.',
];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     HERO SECTION (DYNAMIC SINGLE ATMOSPHERE BANNER)
     ═══════════════════════════════════════════ -->
<?php 
$heroPage = 'blog';
include ROOT_PATH . '/views/partials/hero-banner.php'; 
?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     MAIN BLOG ARCHIVE SECTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-8) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">

    <!-- Switcher: Articles vs Case Studies -->
    <div class="editorial-tab-switcher-wrap reveal-up" style="margin-bottom: 22px;">
      <div class="editorial-tab-switcher" role="tablist" aria-label="Editorial Content Type">
        <a href="<?= url('blog') ?>" 
           class="tab-switch-btn active" 
           role="tab" 
           aria-selected="true">
          <i class="ri-article-line"></i> <span>Editorial Articles (<?= $totalPosts ?>)</span>
        </a>
        <?php if (setting('enable_case_studies', '1') !== '0'): ?>
        <a href="<?= url('case-studies') ?>" 
           class="tab-switch-btn" 
           role="tab" 
           aria-selected="false">
          <i class="ri-checkbox-circle-fill"></i> <span>Case Studies (<?= $totalStudiesCount ?: 10 ?>)</span>
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Category Pills Filter Bar -->
    <div class="editorial-filter-pills-wrap reveal-up" style="margin-bottom: 36px;">
      <a href="<?= url('blog/') ?>" 
         class="editorial-filter-pill <?= empty($categorySlug) ? 'active' : '' ?>">
        <span>All Articles</span>
      </a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= url('blog/?category=' . urlencode($cat['slug'])) ?>" 
         class="editorial-filter-pill <?= ($categorySlug === $cat['slug']) ? 'active' : '' ?>">
        <span><?= e($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- ═══════════════════════════════════════════
         PURE 3-COLUMN UNIFORM ARTICLES GRID
         ═══════════════════════════════════════════ -->
    <?php if (!empty($posts)): ?>
    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
      <?php foreach ($posts as $p): ?>
      <article class="blog-card" style="background: #ffffff; border: 1px solid rgba(15, 30, 54, 0.08); border-radius: 20px; overflow: hidden; box-shadow: 0 4px 18px rgba(15, 30, 54, 0.03); display: flex; flex-direction: column; height: 100%;">
        
        <!-- Image Container Frame (Edge-to-edge cover & rounded top corners) -->
        <div class="blog-card__image" style="position: relative; width: 100%; height: 220px; overflow: hidden; background: #FAF8F5; border-radius: 19px 19px 0 0; padding: 0; margin: 0; border-bottom: 1px solid rgba(15, 30, 54, 0.06);">
          <a href="<?= url('blog/' . e($p['slug'])) ?>" style="display: block; width: 100%; height: 100%; overflow: hidden;">
            <img src="<?= media_url($p['featured_img'], '/img/blog.png') ?>" alt="<?= e($p['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 19px 19px 0 0;">
          </a>
          <?php if (!empty($p['category_name'])): ?>
            <span class="badge badge-teal blog-card__tag" style="position: absolute; top: 14px; left: 14px; z-index: 2; box-shadow: 0 4px 12px rgba(15, 30, 54, 0.15);"><?= e($p['category_name']) ?></span>
          <?php endif; ?>
        </div>

        <!-- Body & Metadata -->
        <div class="blog-card__body" style="display: flex; flex-direction: column; flex: 1; padding: 22px;">
          <div>
            <h3 class="blog-card__title" style="font-size: 1.15rem; line-height: 1.35; margin-bottom: 10px; font-weight: 700;">
              <a href="<?= url('blog/' . e($p['slug'])) ?>" style="color: var(--color-navy); text-decoration: none;">
                <?= e($p['title']) ?>
              </a>
            </h3>
            <p class="blog-card__excerpt" style="font-size: 0.875rem; line-height: 1.55; margin-bottom: 18px; color: var(--color-text-muted);">
              <?= e(truncate(strip_tags($p['excerpt'] ?: $p['content']), 130)) ?>
            </p>
          </div>

          <div class="blog-card__footer" style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed var(--color-border); padding-top: 14px; margin-top: auto;">
            <span style="font-size: 0.75rem; color: var(--color-text-muted); font-family: var(--font-mono);"><i class="ri-calendar-line"></i> <?= format_date($p['created_at']) ?></span>
            <a href="<?= url('blog/' . e($p['slug'])) ?>" style="color: var(--color-teal-ink); font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
              Read <i class="ri-arrow-right-line"></i>
            </a>
          </div>
        </div>

      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div style="text-align: center; padding: 60px 24px; background: #ffffff; border: 1.5px dashed rgba(74,139,140,0.35); border-radius: 20px;">
      <i class="ri-article-line" style="font-size: 44px; color: var(--color-teal-ink); margin-bottom: 12px; display: inline-block;"></i>
      <h3 style="font-family: var(--font-display); color: var(--color-navy); font-size: 1.4rem; margin-bottom: 8px;">No Articles Found in This Topic</h3>
      <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 20px; max-width: 480px; margin-left: auto; margin-right: auto;">Check back soon for new framework teardowns or explore all articles below.</p>
      <a href="<?= url('blog/') ?>" class="editorial-filter-pill active" style="padding: 10px 22px;"><span>View All Articles</span></a>
    </div>
    <?php endif; ?>

    <!-- Pagination Controls (9 per Page in 3x3 Grid) -->
    <?php if ($totalPages > 1): ?>
    <div class="reveal-up" style="display: flex; justify-content: center; gap: 8px; margin-top: var(--space-12); align-items: center;">
      <?php if ($page > 1): ?>
        <a href="<?= url('blog/?page=' . ($page - 1) . (!empty($categorySlug) ? '&category=' . urlencode($categorySlug) : '')) ?>" 
           class="btn btn-ghost btn-sm" style="background: #ffffff; border: 1px solid var(--color-border); color: var(--color-navy);">
          <i class="ri-arrow-left-s-line"></i> Previous
        </a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="<?= url('blog/?page=' . $i . (!empty($categorySlug) ? '&category=' . urlencode($categorySlug) : '')) ?>" 
           class="btn <?= ($page === $i) ? 'btn-primary' : 'btn-ghost' ?> btn-sm" 
           style="min-width: 40px; text-align: center; <?= ($page === $i) ? '' : 'background: #ffffff; border: 1px solid var(--color-border); color: var(--color-navy);' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="<?= url('blog/?page=' . ($page + 1) . (!empty($categorySlug) ? '&category=' . urlencode($categorySlug) : '')) ?>" 
           class="btn btn-ghost btn-sm" style="background: #ffffff; border: 1px solid var(--color-border); color: var(--color-navy);">
          Next <i class="ri-arrow-right-s-line"></i>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ═══════════════════════════════════════════
     SIGNATURE CTA PANEL
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
        <img src="<?= img('cta 1.png') ?>" alt="WORDORA Editorial Partnership" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>
