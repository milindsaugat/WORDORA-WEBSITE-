<?php
/**
 * WORDORA — Case Study Detail Page
 * Layout: Full Commercial Proof Story + Quantified ROI Metrics + 2-Column Layout with Sticky Factsheet & CTA
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

// Redirect if Case Studies module is disabled in Admin
if (setting('enable_case_studies', '1') === '0') {
    redirect('services');
    exit;
}

$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) {
    redirect('case-studies.php');
}

require_once ROOT_PATH . '/models/CaseStudy.php';
CaseStudy::ensureTable();

$dbStudy = CaseStudy::getBySlug($slug);
if ($dbStudy) {
    // Canonical 301 redirect if requested via legacy case-study-detail.php query string
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_contains($reqUri, 'case-study-detail.php') && !empty($dbStudy['slug'])) {
        header('Location: ' . url('case-study/' . $dbStudy['slug']), true, 301);
        exit;
    }

    // Format deliverables array
    $deliv = $dbStudy['deliverables'];
    if (is_string($deliv)) {
        $delivArr = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $deliv)));
    } else {
        $delivArr = is_array($deliv) ? $deliv : [];
    }

    $study = [
        'id' => $dbStudy['id'],
        'slug' => $dbStudy['slug'],
        'title' => $dbStudy['title'],
        'client' => $dbStudy['client'],
        'industry' => $dbStudy['industry'],
        'industry_slug' => $dbStudy['industry_slug'],
        'badge' => $dbStudy['badge'] ?: 'Enterprise Proof',
        'timeline' => $dbStudy['timeline'] ?: '6 Month Retainer',
        'location' => $dbStudy['location'] ?: 'Global',
        'headline_metric' => $dbStudy['headline_metric'] ?: '+400%',
        'headline_label' => $dbStudy['headline_label'] ?: 'Commercial Growth',
        'secondary_metric' => $dbStudy['secondary_metric'] ?: '100+',
        'secondary_label' => $dbStudy['secondary_label'] ?: 'Deliverables Shipped',
        'tertiary_metric' => $dbStudy['tertiary_metric'] ?: '100%',
        'tertiary_label' => $dbStudy['tertiary_label'] ?: 'SLA Delivery',
        'image' => $dbStudy['image'] ?: 'service treasure.png',
        'challenge' => $dbStudy['challenge'],
        'solution' => $dbStudy['solution'],
        'deliverables' => $delivArr,
        'results_summary' => $dbStudy['results_summary'],
        'testimonial' => !empty($dbStudy['testimonial_quote']) ? [
            'quote' => $dbStudy['testimonial_quote'],
            'author' => $dbStudy['testimonial_author'] ?: 'Executive Client',
            'role' => $dbStudy['testimonial_role'] ?: 'Leadership'
        ] : null
    ];
} else {
    $caseStudyDataBank = [
        'scalestack-cloud-developer-trust' => [
            'id' => 1,
            'slug' => 'scalestack-cloud-developer-trust',
            'title' => 'ScaleStack Cloud: Engineering Developer Trust & Technical Authority',
            'client' => 'ScaleStack Cloud',
            'industry' => 'SaaS & DevOps',
            'industry_slug' => 'saas-devops',
            'badge' => 'Enterprise SaaS',
            'timeline' => '6 Month Retainer',
            'location' => 'San Francisco, CA',
            'headline_metric' => '+420%',
            'headline_label' => 'Developer Signups Lift',
            'secondary_metric' => '1000+',
            'secondary_label' => 'Technical Articles Delivered',
            'tertiary_metric' => '40k+',
            'tertiary_label' => 'Monthly Active Developers',
            'image' => 'service treasure.png',
            'challenge' => 'ScaleStack had built a breakthrough Kubernetes multi-cloud orchestration engine, but developer adoption was stalled. Their engineering docs were dense, academic, and lacked reproducible code tutorials. Technical CTOs couldn\'t evaluate their value within the critical 5-minute initial onboarding window.',
            'solution' => 'WORDORA deployed a senior developer-advocate writing team to completely overhaul their technical documentation suite. We structured 24 in-depth architectural whitepapers, authored 30+ production code recipes, and built a search-optimized "Cloud Migration Playbook" topic cluster targeting high-intent DevOps keywords.',
            'deliverables' => [
                '24-Part Architectural Whitepaper Series',
                'Interactive API Reference & SDK Guides',
                '30+ Production-Ready Code Recipes (Go, Python, Rust)',
                'Executive Kubernetes Migration Playbook',
                'Developer Newsletter & Release Notes Engine'
            ],
            'results_summary' => 'Within 180 days of launching the revised documentation and topic cluster, organic developer search traffic grew by 310%, trial-to-production conversion jumped by 78%, and total enterprise developer signups increased by 420%.',
            'testimonial' => [
                'quote' => 'WORDORA bridged the gap between our complex engineering architecture and the developer community. Their technical writers write with the precision of senior architects and the clarity of world-class journalists.',
                'author' => 'Marcus Vance',
                'role' => 'Chief Technology Officer, ScaleStack Cloud'
            ]
        ]
    ];
    $study = $caseStudyDataBank[$slug] ?? null;
}

if (!$study) {
    require_once ROOT_PATH . '/public/404.php';
    exit;
}

$csSeoTitle = setting("seo_casestudy_{$study['slug']}_title");
$csSeoDesc  = setting("seo_casestudy_{$study['slug']}_desc");
$csSeoKw    = setting("seo_casestudy_{$study['slug']}_keywords");
$csSeoOg    = setting("seo_casestudy_{$study['slug']}_og_image");

$meta = [
    'title'       => !empty($csSeoTitle) ? $csSeoTitle : (!empty($dbStudy['meta_title']) ? $dbStudy['meta_title'] : (e($study['title']) . ' — WORDORA Case Study')),
    'description' => !empty($csSeoDesc) ? $csSeoDesc : (!empty($dbStudy['meta_desc']) ? $dbStudy['meta_desc'] : (e(truncate(strip_tags($study['excerpt'] ?: $study['challenge'] ?? ''), 160)))),
    'keywords'    => !empty($csSeoKw) ? $csSeoKw : ($dbStudy['meta_keywords'] ?? ''),
    'og_image'    => !empty($csSeoOg) ? $csSeoOg : media_url($dbStudy['image'] ?? 'service treasure.png', '/img/case study.png'),
];

// Other case studies: Take latest 5 (excluding current)
$allDbStudies = CaseStudy::getAll('', true);
$otherStudies = array_values(array_filter($allDbStudies, function($cs) use ($slug) {
    return $cs['slug'] !== $slug;
}));
$otherStudies = array_slice($otherStudies, 0, 5);

// Fetch Parent Case Studies Section Banner Background
$heroSlide = DB::getInstance()->query("SELECT * FROM hero_slides WHERE page = 'case_studies' AND is_active = 1 ORDER BY sort_order ASC LIMIT 1")->fetch();
$csHeroBgUrl = !empty($heroSlide['media_url']) ? media_url($heroSlide['media_url']) : img('case study.png');
$heroGradient = get_hero_directional_gradient();

ob_start();
?>

<!-- ═══════════════════════════════════════════
     01 — CLEAN COVER HERO BANNER (USING SECTION ATMOSPHERE BANNER)
     ═══════════════════════════════════════════ -->
<section class="hero hero--bg-image" id="heroSection" style="background-image: <?= $heroGradient ?>, url('<?= $csHeroBgUrl ?>');">
  <div class="container container-hero" style="position: relative; z-index: 2;">
    <div class="hero__body-full" style="max-width: 980px;">
      
      <span class="label-upper hero__eyebrow animate-hero-text" style="color: var(--color-teal-light);">
        <i class="ri-checkbox-circle-fill"></i> <?= e($study['badge'] ?: 'VERIFIED COMMERCIAL PROOF') ?>
      </span>

      <!-- Main Headline -->
      <h1 class="heading-hero animate-hero-text" style="font-size: clamp(2rem, 3.4vw, 2.9rem); line-height: 1.25; margin-bottom: 0;">
        <?= e($study['title']) ?>
      </h1>

    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — 2-COLUMN CASE STUDY DEEP DIVE
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-10) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">
    
    <div class="blog-detail-grid">
      
      <!-- ═══════════════════════════════════════════
           LEFT COLUMN: CASE STUDY STORY (68%)
           ═══════════════════════════════════════════ -->
      <main class="blog-main-content">

        <!-- Top Client Info & Factsheet Bar -->
        <div class="blog-meta-bar reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 16px 22px; margin-bottom: var(--space-6); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
          <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <span class="badge badge-teal"><i class="ri-checkbox-circle-fill"></i> <?= e($study['badge']) ?></span>
            <span style="font-size: 0.8125rem; color: var(--color-text-muted); font-family: var(--font-mono);">
              Sector: <strong><?= e($study['industry']) ?></strong>
            </span>
            <span style="color: var(--color-border);">•</span>
            <span style="font-size: 0.8125rem; color: var(--color-text-muted); font-family: var(--font-mono);">
              Client: <strong><?= e($study['client']) ?></strong> (<?= e($study['location']) ?>)
            </span>
            <span style="color: var(--color-border);">•</span>
            <span style="font-size: 0.8125rem; color: var(--color-text-muted); font-family: var(--font-mono);">
              <?= e($study['timeline']) ?>
            </span>
          </div>

          <a href="<?= url('case-studies.php') ?>" style="font-size: 0.8125rem; color: var(--color-teal-ink); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
            <i class="ri-arrow-left-line"></i> All Studies
          </a>
        </div>

        <!-- 3 Quantified ROI Metrics Cards (#ffffff, dashed border) -->
        <div class="case-study-roi-grid reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: var(--space-8);">
          <div class="case-study-roi-card" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 16px; padding: 18px 20px; text-align: center;">
            <div class="case-study-roi-val" style="font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; color: var(--color-teal-ink); line-height: 1.1; margin-bottom: 4px;"><?= e($study['headline_metric']) ?></div>
            <div class="case-study-roi-lbl" style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;"><?= e($study['headline_label']) ?></div>
          </div>
          <div class="case-study-roi-card" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 16px; padding: 18px 20px; text-align: center;">
            <div class="case-study-roi-val" style="font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; color: var(--color-navy); line-height: 1.1; margin-bottom: 4px;"><?= e($study['secondary_metric']) ?></div>
            <div class="case-study-roi-lbl" style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;"><?= e($study['secondary_label']) ?></div>
          </div>
          <div class="case-study-roi-card" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 16px; padding: 18px 20px; text-align: center;">
            <div class="case-study-roi-val" style="font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; color: var(--color-teal-ink); line-height: 1.1; margin-bottom: 4px;"><?= e($study['tertiary_metric']) ?></div>
            <div class="case-study-roi-lbl" style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600; text-transform: uppercase;"><?= e($study['tertiary_label']) ?></div>
          </div>
        </div>
        
        <!-- Large Featured Visual Frame (Edge-to-edge cover fit) -->
        <div class="blog-featured-img-frame reveal-up" style="background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 24px; overflow: hidden; padding: 0; margin-bottom: var(--space-8); width: 100%; max-height: 480px; box-shadow: none !important; display: block;">
          <img src="<?= img($study['image']) ?>" 
               alt="<?= e($study['title']) ?>" 
               loading="eager"
               style="width: 100%; height: 100%; max-height: 480px; object-fit: cover; object-position: center; display: block; border-radius: 22px;">
        </div>

        <!-- Section 1: The Challenge -->
        <article class="blog-body-copy reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 24px; padding: 2.5rem 2.8rem; box-shadow: none !important; margin-bottom: var(--space-8);">
          
          <span class="badge badge-teal" style="margin-bottom: 12px;"><i class="ri-alert-line"></i> The Challenge</span>
          <h2>Market Friction &amp; Initial Bottleneck</h2>
          <div class="lead-paragraph" style="color: var(--color-text); font-size: 1.15rem; line-height: 1.7;">
            <?= str_contains($study['challenge'] ?? '', '<') ? ($study['challenge'] ?? '') : '<p>' . nl2br(e($study['challenge'] ?? '')) . '</p>' ?>
          </div>

          <span class="badge badge-teal" style="margin-top: 2rem; margin-bottom: 12px;"><i class="ri-lightbulb-flash-line"></i> The Solution</span>
          <h2>Strategic Editorial Architecture</h2>
          <div style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text);">
            <?= str_contains($study['solution'] ?? '', '<') ? ($study['solution'] ?? '') : '<p>' . nl2br(e($study['solution'] ?? '')) . '</p>' ?>
          </div>

          <!-- Deliverables Produced -->
          <div class="content-callout-box" style="margin-top: 2rem;">
            <h4><i class="ri-file-list-3-line"></i> Deliverables Engineered &amp; Shipped:</h4>
            <ul style="margin: 10px 0 0 16px; padding: 0;">
              <?php foreach ($study['deliverables'] as $item): ?>
              <li style="margin-bottom: 6px; font-weight: 500; color: var(--color-navy);"><?= e($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <span class="badge badge-teal" style="margin-top: 2rem; margin-bottom: 12px;"><i class="ri-trophy-line"></i> Measured ROI</span>
          <h2>Commercial Results &amp; Search Dominance</h2>
          <div style="font-size: 1.05rem; line-height: 1.7; color: var(--color-text);">
            <?= str_contains($study['results_summary'] ?? '', '<') ? ($study['results_summary'] ?? '') : '<p>' . nl2br(e($study['results_summary'] ?? '')) . '</p>' ?>
          </div>

        </article>

        <!-- Client Testimonial Box -->
        <?php if (!empty($study['testimonial'])): ?>
        <div class="case-study-testimonial-box reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 24px; padding: 2.5rem; margin-bottom: var(--space-8);">
          <div style="color: var(--color-teal-ink); font-size: 2.2rem; line-height: 1; margin-bottom: 12px;">
            <i class="ri-double-quotes-l"></i>
          </div>
          <p style="font-family: var(--font-display); font-size: 1.3rem; font-style: italic; color: var(--color-navy); line-height: 1.6; margin-bottom: 20px;">
            "<?= e($study['testimonial']['quote']) ?>"
          </p>
          <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--color-navy); color: var(--color-teal-light); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
              <i class="ri-user-star-line"></i>
            </div>
            <div>
              <div style="font-weight: 700; font-size: 1rem; color: var(--color-navy);"><?= e($study['testimonial']['author']) ?></div>
              <div style="font-size: 0.8125rem; color: var(--color-text-muted);"><?= e($study['testimonial']['role']) ?></div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="reveal-up">
          <a href="<?= url('case-studies.php') ?>" class="btn btn-ghost" style="border: 1px solid var(--color-border); color: var(--color-navy);">
            <i class="ri-arrow-left-line"></i> <span>Explore All 10 Case Studies</span>
          </a>
        </div>

      </main>


      <!-- ═══════════════════════════════════════════
           RIGHT COLUMN: STICKY PROJECT FACTSHEET (32%)
           ═══════════════════════════════════════════ -->
      <aside class="blog-sticky-sidebar">
        
        <!-- Factsheet Widget -->
        <div class="sidebar-widget reveal-up">
          <div class="sidebar-widget__title">
            <i class="ri-file-info-line" style="color: var(--color-teal-ink);"></i>
            <span>Project Factsheet</span>
          </div>

          <div style="display: flex; flex-direction: column; gap: 14px; font-size: 0.875rem;">
            <div>
              <span style="color: var(--color-text-muted); font-size: 0.75rem; text-transform: uppercase;">Client:</span>
              <div style="font-weight: 700; color: var(--color-navy);"><?= e($study['client']) ?></div>
            </div>
            <div>
              <span style="color: var(--color-text-muted); font-size: 0.75rem; text-transform: uppercase;">Sector / Industry:</span>
              <div style="font-weight: 700; color: var(--color-navy);"><?= e($study['industry']) ?></div>
            </div>
            <div>
              <span style="color: var(--color-text-muted); font-size: 0.75rem; text-transform: uppercase;">Duration:</span>
              <div style="font-weight: 700; color: var(--color-navy);"><?= e($study['timeline']) ?></div>
            </div>
            <div>
              <span style="color: var(--color-text-muted); font-size: 0.75rem; text-transform: uppercase;">Location:</span>
              <div style="font-weight: 700; color: var(--color-navy);"><?= e($study['location']) ?></div>
            </div>
          </div>
        </div>

        <!-- Widget 2: Request Similar Scope CTA -->
        <?php
        $csSideCtaBadge   = setting('cs_sidebar_cta_badge', 'Similar ROI');
        $csSideCtaTitle   = setting('cs_sidebar_cta_title', 'Ready to Scale Your Domain Authority?');
        $csSideCtaDesc    = setting('cs_sidebar_cta_desc', 'Book a complimentary 30-minute content audit with our managing editors.');
        $csSideCtaBtnText = setting('cs_sidebar_cta_btn_text', 'Request Scope Audit');
        $csSideCtaBtnUrl  = setting('cs_sidebar_cta_btn_url', 'contact.php?service=' . urlencode($study['industry']));
        ?>
        <div class="sidebar-widget reveal-up" style="background: var(--color-navy); border-color: rgba(74, 139, 140, 0.4); text-align: center; color: var(--color-white);">
          <span class="badge" style="background: rgba(74, 139, 140, 0.25); color: var(--color-teal-light); margin-bottom: 12px; border: 1px dashed var(--color-teal-ink);">
            <i class="ri-sparkling-fill"></i> <?= e($csSideCtaBadge) ?>
          </span>
          <h3 style="font-family: var(--font-display); font-size: 1.35rem; color: var(--color-white); margin-bottom: 10px; line-height: 1.3;">
            <?= e($csSideCtaTitle) ?>
          </h3>
          <p style="font-size: 0.84375rem; color: rgba(255, 255, 255, 0.78); line-height: 1.55; margin-bottom: 18px;">
            <?= e($csSideCtaDesc) ?>
          </p>
          <a href="<?= url($csSideCtaBtnUrl) ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
            <span><?= e($csSideCtaBtnText) ?></span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>

        <!-- Widget 3: Other Case Studies -->
        <?php if (!empty($otherStudies)): ?>
        <div class="sidebar-widget reveal-up">
          <div class="sidebar-widget__title">
            <i class="ri-folder-open-line" style="color: var(--color-teal-ink);"></i>
            <span>Other Client Proof</span>
          </div>

          <div style="display: flex; flex-direction: column;">
            <?php foreach ($otherStudies as $os): ?>
            <a href="<?= url('case-study/' . e($os['slug'])) ?>" class="sidebar-insight-item">
              <div class="sidebar-insight-thumb">
                <img src="<?= img($os['image'] ?: 'service treasure.png') ?>" alt="<?= e($os['title']) ?>" loading="lazy">
              </div>
              <div class="sidebar-insight-info">
                <h4><?= e(truncate($os['title'], 50)) ?></h4>
                <div class="sidebar-insight-meta">
                  <span style="color: var(--color-teal-ink); font-weight: 700;"><?= e($os['headline_metric']) ?></span>
                  <span>•</span>
                  <span><?= e($os['industry']) ?></span>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Widget 4: Featured Editorial Strategy (Related Blog Insight) -->
        <?php
        require_once ROOT_PATH . '/models/Post.php';
        $sideBlogInsight = Post::getLatest(1)[0] ?? null;
        if ($sideBlogInsight):
        ?>
        <div class="sidebar-widget reveal-up" style="border: 1.5px dashed rgba(74, 139, 140, 0.45); background: #FAF8F5;">
          <div class="sidebar-widget__title">
            <i class="ri-article-line" style="color: var(--color-teal-ink);"></i>
            <span>Editorial Framework</span>
          </div>

          <div style="margin-bottom: 12px; border-radius: 12px; overflow: hidden; background: #ffffff; padding: 12px; border: 1px dashed rgba(74, 139, 140, 0.25); text-align: center;">
            <img src="<?= media_url($sideBlogInsight['featured_img'], '/img/blog.png') ?>" alt="<?= e($sideBlogInsight['title']) ?>" style="max-height: 120px; width: auto; object-fit: contain;">
          </div>

          <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 8px;">
            <span class="badge badge-teal" style="font-size: 0.6875rem; padding: 2px 8px;"><?= e($sideBlogInsight['category_name'] ?: 'Strategy') ?></span>
            <span style="font-size: 0.75rem; font-family: var(--font-mono); color: var(--color-teal-ink); font-weight: 700;"><?= (int)$sideBlogInsight['read_time'] ?> min read</span>
          </div>

          <h4 style="font-family: var(--font-body); font-weight: 700; font-size: 0.95rem; color: var(--color-navy); line-height: 1.35; margin-bottom: 8px;">
            <?= e($sideBlogInsight['title']) ?>
          </h4>

          <p style="font-size: 0.8125rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 14px;">
            <?= e(truncate(strip_tags($sideBlogInsight['excerpt'] ?: $sideBlogInsight['content']), 110)) ?>
          </p>

          <a href="<?= url('blog/' . e($sideBlogInsight['slug'])) ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8125rem;">
            <span>Read Strategy Teardown</span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
        <?php endif; ?>

        <!-- Widget 5: The Executive Editorial Brief (Newsletter Subscription) -->
        <?php
        $csSideNewsBadge   = setting('cs_sidebar_news_badge', 'Executive Brief');
        $csSideNewsTitle   = setting('cs_sidebar_news_title', 'Get Our ROI Playbooks');
        $csSideNewsDesc    = setting('cs_sidebar_news_desc', 'Quarterly teardowns of high-growth B2B & SaaS content funnels delivered directly to your inbox.');
        $csSideNewsBtnText = setting('cs_sidebar_news_btn_text', 'Get Free Playbooks');
        ?>
        <div class="sidebar-widget reveal-up" style="background: var(--color-navy); color: #ffffff; border-color: rgba(74, 139, 140, 0.35);">
          <span class="badge" style="background: rgba(74, 139, 140, 0.25); color: var(--color-teal-light); border: 1px dashed var(--color-teal-ink); margin-bottom: 12px;">
            <i class="ri-mail-star-line"></i> <?= e($csSideNewsBadge) ?>
          </span>

          <h3 style="font-family: var(--font-display); font-size: 1.2rem; color: #ffffff; margin-bottom: 8px; line-height: 1.3;">
            <?= e($csSideNewsTitle) ?>
          </h3>

          <p style="font-size: 0.8125rem; color: rgba(255, 255, 255, 0.78); line-height: 1.5; margin-bottom: 16px;">
            <?= e($csSideNewsDesc) ?>
          </p>

          <form action="<?= url('api/subscribe.php') ?>" method="POST" style="display: flex; flex-direction: column; gap: 8px;">
            <input type="email" name="email" placeholder="Enter your work email" required
                   style="width: 100%; padding: 9px 14px; font-size: 0.8125rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.08); color: #ffffff; outline: none;">
            <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8125rem;">
              <span><?= e($csSideNewsBtnText) ?></span> <i class="ri-send-plane-fill"></i>
            </button>
          </form>
        </div>

        <!-- Widget 6: Enterprise Delivery & Quality Guarantees -->
        <?php
        $csSideSlas = json_decode(setting('cs_sidebar_slas', '[]'), true);
        if (empty($csSideSlas)) {
            $csSideSlas = [
                '<strong>Quantitative Milestone</strong> SLAs',
                '<strong>Senior Domain Writers</strong> Only',
                '<strong>Turnitin &amp; Fact-Check</strong> Verified',
                '<strong>100% Commercial IP</strong> Transfer'
            ];
        }
        ?>
        <div class="sidebar-widget reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35);">
          <div class="sidebar-widget__title">
            <i class="ri-shield-star-line" style="color: var(--color-teal-ink);"></i>
            <span>Enterprise SLAs</span>
          </div>

          <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.8125rem; color: var(--color-navy);">
            <?php foreach ($csSideSlas as $slaItem): ?>
              <div style="display: flex; align-items: center; gap: 8px;">
                <i class="ri-checkbox-circle-fill" style="color: var(--color-teal-ink);"></i>
                <span><?= $slaItem ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </aside>

    </div>

  </div>
</section>


<!-- ═══════════════════════════════════════════
     03 — CONTINUE READING (MULTI-CARD CASE STUDY SWIPER SLIDER)
     ═══════════════════════════════════════════ -->
<?php
$allOtherStudies = CaseStudy::getAll('', true);
// Filter out current study
$sliderStudies = array_filter($allOtherStudies, function($cs) use ($study) {
    return (int)$cs['id'] !== (int)$study['id'];
});
if (!empty($sliderStudies)):
?>
<section class="section" style="background: var(--color-white); border-top: 1px dashed var(--color-border); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    
    <div class="reveal-up" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-10); flex-wrap: wrap; gap: 16px;">
      <div>
        <span class="label-upper">CONTINUE READING</span>
        <h2 class="heading-lg" style="margin-top: var(--space-2); color: var(--color-navy); margin-bottom: 0;">Explore More Commercial Proof</h2>
      </div>

      <!-- Carousel Navigation Controls -->
      <div style="display: flex; gap: 10px; align-items: center;">
        <button type="button" class="btn-cs-prev" style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid var(--color-border); background: #FAF8F5; color: var(--color-navy); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.2s;" aria-label="Previous Slide">
          <i class="ri-arrow-left-line"></i>
        </button>
        <button type="button" class="btn-cs-next" style="width: 44px; height: 44px; border-radius: 50%; border: 1.5px solid var(--color-teal-ink); background: var(--color-teal-ink); color: #FFF; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.2s;" aria-label="Next Slide">
          <i class="ri-arrow-right-line"></i>
        </button>
      </div>
    </div>

    <!-- Swiper Multi-Card Slider Container -->
    <div class="swiper continueStudiesSwiper" style="padding-bottom: 30px; overflow: hidden;">
      <div class="swiper-wrapper">
        <?php foreach ($sliderStudies as $sCard): ?>
        <div class="swiper-slide" style="height: auto;">
          <div class="case-card" style="height: 100%; display: flex; flex-direction: column; background: #FAF8F5; border: 1.5px dashed rgba(74, 139, 140, 0.4); border-radius: 20px; overflow: hidden; padding: 24px; transition: all 0.3s ease;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
              <span class="badge badge-teal" style="font-size: 11px;"><?= e($sCard['badge'] ?: 'Enterprise') ?></span>
              <span style="font-family: var(--font-mono); font-size: 12px; color: var(--color-text-muted);"><?= e($sCard['timeline']) ?></span>
            </div>

            <!-- Artwork -->
            <div style="height: 140px; background: #ffffff; border-radius: 12px; border: 1px dashed rgba(74,139,140,0.3); display: flex; align-items: center; justify-content: center; padding: 16px; margin-bottom: 18px;">
              <img src="<?= img($sCard['image'] ?: 'service treasure.png') ?>" alt="<?= e($sCard['title']) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
            </div>

            <!-- Headline ROI Metric -->
            <div style="margin-bottom: 14px;">
              <div style="font-family: var(--font-display); font-size: 26px; font-weight: 700; color: var(--color-teal-ink); line-height: 1.1;">
                <?= e($sCard['headline_metric']) ?>
              </div>
              <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-muted); font-weight: 600; margin-top: 2px;">
                <?= e($sCard['headline_label']) ?>
              </div>
            </div>

            <!-- Title & Client -->
            <div style="flex: 1; margin-bottom: 16px;">
              <h3 style="font-size: 16px; font-weight: 700; color: var(--color-navy); line-height: 1.35; margin-bottom: 6px;">
                <?= e($sCard['title']) ?>
              </h3>
              <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5; margin: 0;">
                <?= e(truncate($sCard['excerpt'] ?: $sCard['challenge'], 100)) ?>
              </p>
            </div>

            <!-- Card Footer -->
            <div style="padding-top: 14px; border-top: 1px dashed rgba(74, 139, 140, 0.25); display: flex; justify-content: space-between; align-items: center;">
              <span style="font-size: 12px; color: var(--color-navy); font-weight: 600;"><?= e($sCard['client']) ?></span>
              <a href="<?= url('case-study/' . e($sCard['slug'])) ?>" class="btn btn-primary btn-sm" style="font-size: 12px; padding: 6px 14px;">
                <span>Read Story</span> <i class="ri-arrow-right-line"></i>
              </a>
            </div>

          </div>
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
    new Swiper('.continueStudiesSwiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      loop: false,
      navigation: {
        nextEl: '.btn-cs-next',
        prevEl: '.btn-cs-prev',
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
