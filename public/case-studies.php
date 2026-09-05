<?php
/**
 * WORDORA — Case Studies Archive & Commercial Proof
 * Layout: Pure 3-Column Uniform Card Grid (No oversized card, clean grid on All Industries & Filtered Industries)
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/models/CaseStudy.php';
require_once ROOT_PATH . '/models/Post.php';

// Redirect if Case Studies module is disabled in Admin
if (setting('enable_case_studies', '1') === '0') {
    redirect('services');
    exit;
}

CaseStudy::ensureTable();

$industryFilter = trim($_GET['industry'] ?? '');

// Load Case Studies from Database
$dbCaseStudies = CaseStudy::getAll($industryFilter, true);

if (!empty($dbCaseStudies)) {
    $filteredStudies = $dbCaseStudies;
} else {
    // Fallback Databank
    $allCaseStudies = [
        [
            'id' => 1,
            'slug' => 'scalestack-cloud-developer-trust',
            'title' => 'ScaleStack Cloud: Engineering Developer Trust & Technical Authority',
            'client' => 'ScaleStack Cloud',
            'industry' => 'SaaS & DevOps',
            'industry_slug' => 'saas-devops',
            'badge' => 'Enterprise SaaS',
            'headline_metric' => '+420%',
            'headline_label' => 'Developer Signups Lift',
            'secondary_metric' => '1000+',
            'secondary_label' => 'Technical Articles Delivered',
            'excerpt' => 'Architected a 24-part deep-technical whitepaper series and interactive API documentation suite that simplified multi-cloud Kubernetes orchestration for enterprise CTOs.',
            'image' => 'service treasure.png',
            'read_time' => '6 min read'
        ],
        [
            'id' => 2,
            'slug' => 'novapay-global-cross-border-settlement',
            'title' => 'NovaPay Global: Demystifying Cross-Border Settlement & API Rails',
            'client' => 'NovaPay Global',
            'industry' => 'FinTech & Banking',
            'industry_slug' => 'fintech-banking',
            'badge' => 'FinTech Compliance',
            'headline_metric' => '₹1.2B+',
            'headline_label' => 'Annual Volume Scaled',
            'secondary_metric' => '170+',
            'secondary_label' => 'Enterprise Accounts Closed',
            'excerpt' => 'Crafted institutional compliance narratives, enterprise security whitepapers, and merchant onboarding guides that established unshakeable credibility across 40+ international markets.',
            'image' => 'brand content.png',
            'read_time' => '7 min read'
        ]
    ];
    $filteredStudies = $allCaseStudies;
}

$totalPostsCount = Post::countPublished();
$allIndustries = CaseStudy::getIndustries();

$meta = [
    'title' => (!empty($industryFilter) ? e(ucwords(str_replace('-', ' ', $industryFilter))) . ' Case Studies — ' : '') . 'WORDORA Studio',
    'description' => 'Explore verified case studies and measured performance ROI delivered by WORDORA for SaaS, FinTech, healthcare, and enterprise leaders.',
];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     HERO SECTION (DYNAMIC SINGLE ATMOSPHERE BANNER)
     ═══════════════════════════════════════════ -->
<?php 
$heroPage = 'case_studies';
include ROOT_PATH . '/views/partials/hero-banner.php'; 
?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     MAIN CASE STUDIES ARCHIVE SECTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-8) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">

    <!-- Switcher: Articles vs Case Studies -->
    <div class="editorial-tab-switcher-wrap reveal-up" style="margin-bottom: 22px;">
      <div class="editorial-tab-switcher" role="tablist">
        <a href="<?= url('blog/') ?>" class="tab-switch-btn" role="tab">
          <i class="ri-article-line"></i> <span>Editorial Articles (<?= $totalPostsCount ?: 12 ?>)</span>
        </a>
        <a href="<?= url('case-studies.php') ?>" class="tab-switch-btn active" role="tab">
          <i class="ri-checkbox-circle-fill"></i> <span>Case Studies (<?= count($filteredStudies ?? []) ?>)</span>
        </a>
      </div>
    </div>

    <!-- Industry Filter Pills -->
    <div class="editorial-filter-pills-wrap reveal-up" style="margin-bottom: 36px;">
      <a href="<?= url('case-studies.php') ?>" 
         class="editorial-filter-pill <?= empty($industryFilter) ? 'active' : '' ?>">
        <span>All Industries</span>
      </a>
      <?php foreach ($allIndustries as $indItem): ?>
      <a href="<?= url('case-studies.php?industry=' . urlencode($indItem['slug'])) ?>" 
         class="editorial-filter-pill <?= ($industryFilter === $indItem['slug'] || $industryFilter === $indItem['name']) ? 'active' : '' ?>">
        <span><?= e($indItem['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- ═══════════════════════════════════════════
         PURE 3-COLUMN UNIFORM CARD GRID (FOR ALL INDUSTRIES AND FILTERED INDUSTRIES)
         ═══════════════════════════════════════════ -->
    <?php if (!empty($filteredStudies)): ?>
    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
      <?php foreach ($filteredStudies as $cs): ?>
      <article class="blog-card" style="background: #ffffff; border: 1px solid rgba(15, 30, 54, 0.08); border-radius: 20px; overflow: hidden; box-shadow: 0 4px 18px rgba(15, 30, 54, 0.03); display: flex; flex-direction: column; height: 100%;">
        
        <!-- Image Container Frame (Edge-to-edge cover & rounded top corners) -->
        <div class="blog-card__image" style="position: relative; width: 100%; height: 220px; overflow: hidden; background: #FAF8F5; border-radius: 19px 19px 0 0; padding: 0; margin: 0; border-bottom: 1px solid rgba(15, 30, 54, 0.06);">
          <a href="<?= url('case-study/' . e($cs['slug'])) ?>" style="display: block; width: 100%; height: 100%; overflow: hidden;">
            <img src="<?= img($cs['image']) ?>" alt="<?= e($cs['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 19px 19px 0 0;">
          </a>
          <span class="badge badge-teal blog-card__tag" style="position: absolute; top: 14px; left: 14px; z-index: 2; box-shadow: 0 4px 12px rgba(15, 30, 54, 0.15);"><?= e($cs['badge']) ?></span>
        </div>

        <!-- Body & Quantified Metrics -->
        <div class="blog-card__body" style="display: flex; flex-direction: column; flex: 1; padding: 22px;">
          <div>
            <div style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 700; margin-bottom: 6px; text-transform: uppercase;">
              <?= e($cs['industry']) ?>
            </div>
            
            <h3 class="blog-card__title" style="font-size: 1.15rem; line-height: 1.35; margin-bottom: 10px; font-weight: 700;">
              <a href="<?= url('case-study/' . e($cs['slug'])) ?>" style="color: var(--color-navy); text-decoration: none;">
                <?= e($cs['title']) ?>
              </a>
            </h3>
            
            <p class="blog-card__excerpt" style="font-size: 0.875rem; line-height: 1.55; margin-bottom: 18px; color: var(--color-text-muted);">
              <?= e(truncate($cs['excerpt'], 130)) ?>
            </p>
          </div>

          <!-- Mini Metrics Box -->
          <div style="background: #FAF8F5; border: 1px dashed rgba(74, 139, 140, 0.35); border-radius: var(--radius-md); padding: 12px 14px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; margin-top: auto;">
            <div>
              <div style="font-family: var(--font-display); font-size: 1.3rem; font-weight: 700; color: var(--color-teal-ink);"><?= e($cs['headline_metric']) ?></div>
              <div style="font-size: 0.7rem; color: var(--color-text-muted);"><?= e($cs['headline_label']) ?></div>
            </div>
            <div style="text-align: right;">
              <div style="font-family: var(--font-display); font-size: 1.3rem; font-weight: 700; color: var(--color-navy);"><?= e($cs['secondary_metric']) ?></div>
              <div style="font-size: 0.7rem; color: var(--color-text-muted);"><?= e($cs['secondary_label']) ?></div>
            </div>
          </div>

          <div class="blog-card__footer" style="display: flex; align-items: center; justify-content: space-between; border-top: 1px dashed var(--color-border); padding-top: 14px; margin-top: 0;">
            <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--color-text-muted);">Client: <strong><?= e($cs['client']) ?></strong></span>
            <a href="<?= url('case-study/' . e($cs['slug'])) ?>" style="color: var(--color-teal-ink); font-weight: 700; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
              Read Story <i class="ri-arrow-right-line"></i>
            </a>
          </div>
        </div>

      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Clean Empty State -->
    <div style="text-align: center; padding: 60px 24px; background: #ffffff; border: 1.5px dashed rgba(74,139,140,0.35); border-radius: 20px;">
      <i class="ri-folder-info-line" style="font-size: 44px; color: var(--color-teal-ink); margin-bottom: 12px; display: inline-block;"></i>
      <h3 style="font-family: var(--font-display); color: var(--color-navy); font-size: 1.4rem; margin-bottom: 8px;">No Case Studies in This Industry</h3>
      <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 20px; max-width: 480px; margin-left: auto; margin-right: auto;">Explore all verified proof across our full portfolio.</p>
      <a href="<?= url('case-studies.php') ?>" class="editorial-filter-pill active" style="padding: 10px 22px;"><span>View All Industries</span></a>
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
          <i class="ri-sparkling-fill"></i> PROVEN EDITORIAL IMPACT
        </span>
        
        <h2 class="cta-signature__title">Ready to write your <em>success story?</em></h2>
        
        <p class="cta-signature__text">
          Tell us about your product and target audience. We'll build a tailored content strategy that delivers measurable search rankings, qualified traffic, and pipeline growth.
        </p>

        <div class="cta-signature__actions">
          <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-lg">
            Schedule Scope Audit <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url('services.php') ?>" class="btn btn-ghost btn-lg">
            Explore Services <i class="ri-compass-3-line"></i>
          </a>
        </div>

        <div class="cta-trust-pills">
          <span class="cta-trust-pill"><i class="ri-checkbox-circle-fill"></i> 24h Response</span>
          <span class="cta-trust-pill"><i class="ri-shield-check-fill"></i> NDA Protected</span>
          <span class="cta-trust-pill"><i class="ri-file-list-3-fill"></i> Free Scope Audit</span>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= img('cta 1.png') ?>" alt="WORDORA Commercial Success" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>
