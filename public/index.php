<?php
/**
 * WORDORA — Homepage (Final 9-Section Editorial Master Spec)
 * Fully Dynamic Admin-Configurable Homepage
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

// Fetch recent published posts for Section 08 (Editorial Desk)
$postCount = (int)setting('home_sec8_count', '3');
$recentPosts = [];
try {
    $recentPosts = Post::getPublished($postCount);
} catch (Exception $e) {
    $recentPosts = [];
}

// Structured Homepage Arrays from DB Settings with Fallbacks
$whyCards = json_decode(setting('home_sec4_cards', '[]'), true) ?: [];
if (empty($whyCards)) {
    $whyCards = [
        ['icon' => 'ri-quill-pen-line', 'title' => '100% Human Craftsmanship', 'desc' => 'No generic AI-generated filler. Every piece is written, refined, and polished by seasoned industry writers with real editorial voice.'],
        ['icon' => 'ri-search-eye-line', 'title' => 'Intent-First SEO Strategy', 'desc' => 'We map topic clusters and search intent so your content outranks competition and captures buyers at every evaluation stage.'],
        ['icon' => 'ri-funds-box-line', 'title' => 'Conversion-Focused Copy', 'desc' => 'Landing pages, pitch decks, and brand taglines engineered to clearly communicate value and lift conversion rates.'],
        ['icon' => 'ri-shield-check-line', 'title' => 'Obsessive Fact-Checking', 'desc' => "Every statistic, citation, and technical term is double-verified to protect your brand's market reputation and credibility."],
        ['icon' => 'ri-timer-flash-line', 'title' => 'Predictable Turnaround', 'desc' => 'Strict sprint milestones, transparent project updates, and two full rounds of baked-in revisions on every single delivery.'],
        ['icon' => 'ri-bookmark-3-line', 'title' => 'Style Guide & Tone Governance', 'desc' => 'We create and follow bespoke editorial guidelines so your brand speaks with one unified, authoritative, and recognizable tone.']
    ];
}

$stats = json_decode(setting('home_sec4_stats', '[]'), true) ?: [];
if (empty($stats)) {
    $stats = [
        ['count' => '1000', 'suffix' => '+', 'label' => 'Projects Delivered'],
        ['count' => '170', 'suffix' => '+', 'label' => 'Happy Clients'],
        ['count' => '8', 'suffix' => '+', 'label' => 'Years Experience'],
        ['count' => '98', 'suffix' => '%', 'label' => 'Client Retention']
    ];
}

$industrySlides = json_decode(setting('home_sec5_slides', '[]'), true) ?: [];
if (empty($industrySlides)) {
    $industrySlides = [
        [
            'badge' => 'Verified Deliverable • SaaS & DevOps',
            'title' => 'ScaleStack Cloud: Engineering Developer Trust & Technical Authority',
            'desc' => 'Architected a 24-part deep-technical whitepaper series and interactive API documentation suite that simplified multi-cloud Kubernetes orchestration for enterprise CTOs and technical engineering leads.',
            'm1_val' => '1000+', 'm1_lbl' => 'Projects Delivered', 'm2_val' => '+420%', 'm2_lbl' => 'Developer Signups',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View SaaS Services', 'btn2_url' => 'services.php#technical-writing',
            'media_tag' => 'Client Success Study', 'img' => '/img/case study.png'
        ],
        [
            'badge' => 'Verified Deliverable • Banking & Payments',
            'title' => 'NovaPay Global: Demystifying Cross-Border Settlement & API Rails',
            'desc' => 'Crafted institutional compliance narratives, enterprise security whitepapers, and merchant onboarding guides that established unshakeable credibility across 40+ international markets.',
            'm1_val' => '170+', 'm1_lbl' => 'Happy Clients', 'm2_val' => '₹1.2B+', 'm2_lbl' => 'Annual Volume Scaled',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View FinTech Services', 'btn2_url' => 'services.php#brand-copy',
            'media_tag' => 'Client Success Study', 'img' => '/img/client.png'
        ],
        [
            'badge' => 'Verified Deliverable • D2C & Retail',
            'title' => 'Aura Living: Editorial Product Storytelling for Luxury Goods',
            'desc' => 'Authored immersive collection lookbooks, persuasive high-AOV product copy, and a 12-stage automated customer nurture sequence that drove immediate repeat purchases and high loyalty.',
            'm1_val' => '98%', 'm1_lbl' => 'Client Retention Rate', 'm2_val' => '+3.2x', 'm2_lbl' => 'Email Revenue Scaled',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View D2C Services', 'btn2_url' => 'services.php#email-marketing',
            'media_tag' => 'Client Success Study', 'img' => '/img/case study.png'
        ],
        [
            'badge' => 'Verified Deliverable • Early & Growth Stage',
            'title' => 'HyperVenture AI: Crafting the Seed Manifesto & Founding Story',
            'desc' => 'Transformed complex generative AI model architecture into an irresistible investor deck narrative, founding manifesto, and launch press release that captivated Silicon Valley venture funds.',
            'm1_val' => '8+', 'm1_lbl' => 'Years Experience', 'm2_val' => '₹4.8 Cr', 'm2_lbl' => 'Seed Round Secured',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View Startup Services', 'btn2_url' => 'services.php#brand-copy',
            'media_tag' => 'Client Success Study', 'img' => '/img/client.png'
        ],
        [
            'badge' => 'Verified Deliverable • MedTech & Wellness',
            'title' => 'CarePulse Health: Clinical Precision Meets Patient Empathy',
            'desc' => 'Produced rigorous medical review articles and patient-facing symptom triage guides, establishing certified domain authority and winning zero-click Google featured snippets.',
            'm1_val' => '1000+', 'm1_lbl' => 'Projects Delivered', 'm2_val' => '+210%', 'm2_lbl' => 'Patient Consultations',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View Health Services', 'btn2_url' => 'services.php#seo-content',
            'media_tag' => 'Client Success Study', 'img' => '/img/case study.png'
        ],
        [
            'badge' => 'Verified Deliverable • AI & Infrastructure',
            'title' => 'TensorVector: Open-Source Documentation & Enterprise Funnels',
            'desc' => 'Built developer documentation, quickstart tutorials, and enterprise comparison benchmarks that drove developer adoption from GitHub to paid enterprise cloud contracts.',
            'm1_val' => '170+', 'm1_lbl' => 'Happy Clients', 'm2_val' => '15k+', 'm2_lbl' => 'GitHub Repository Stars',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View Tech Services', 'btn2_url' => 'services.php#technical-writing',
            'media_tag' => 'Client Success Study', 'img' => '/img/client.png'
        ],
        [
            'badge' => 'Verified Deliverable • EdTech & Platforms',
            'title' => 'Cognito Academy: Curriculum Storytelling & Student Acquisition',
            'desc' => 'Structured authoritative course landing pages, student success study essays, and masterclass email campaigns across 12 professional disciplines with exceptional enrollment rates.',
            'm1_val' => '98%', 'm1_lbl' => 'Client Retention Rate', 'm2_val' => '85k+', 'm2_lbl' => 'Enrolled Students',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View EdTech Services', 'btn2_url' => 'services.php#content-strategy',
            'media_tag' => 'Client Success Study', 'img' => '/img/case study.png'
        ],
        [
            'badge' => 'Verified Deliverable • B2B & Consulting',
            'title' => 'Vanguard Advisory: C-Suite Thought Leadership on LinkedIn',
            'desc' => 'Ghostwrote weekly strategic essays and annual industry research reports for managing partners, driving direct inbound consulting requests from Fortune 500 leadership.',
            'm1_val' => '8+', 'm1_lbl' => 'Years Experience', 'm2_val' => '+540%', 'm2_lbl' => 'Inbound Executive Leads',
            'btn1_text' => 'Request Similar Project Scope', 'btn1_url' => 'contact.php', 'btn2_text' => 'View B2B Services', 'btn2_url' => 'services.php#social-media',
            'media_tag' => 'Client Success Study', 'img' => '/img/client.png'
        ]
    ];
}

$processSteps = json_decode(setting('home_sec6_steps', '[]'), true) ?: [];
if (empty($processSteps)) {
    $processSteps = [
        ['step_num' => 'STEP 01', 'title' => 'Discovery & Context Gathering', 'desc' => 'We learn your brand, audience, goals, and competitive landscape inside-out before a single sentence is typed.'],
        ['step_num' => 'STEP 02', 'title' => 'Editorial Strategy & Outlining', 'desc' => 'We turn insights into a structured outline built around what your audience searches for, needs, and remembers.'],
        ['step_num' => 'STEP 03', 'title' => 'Human Craft & Drafting', 'desc' => 'Specialist writers shape raw ideas into sharp, engaging, SEO-optimized, and distinctly human content.'],
        ['step_num' => 'STEP 04', 'title' => 'Polishing & Final Delivery', 'desc' => 'Thoroughly fact-checked, SEO-formatted, and delivered on schedule — with two rounds of revisions baked in.']
    ];
}

$testimonials = json_decode(setting('home_sec7_testimonials', '[]'), true) ?: [];
if (empty($testimonials)) {
    $testimonials = [
        [
            'quote' => 'WORDORA transformed our content from something we routinely published into something our audience actively looked forward to reading. Organic traffic increased 4x in 6 months, and our inbound pipeline has never been healthier.',
            'author_name' => 'Priya Sharma', 'author_role' => 'Founder & CEO, TechGrowth India', 'author_badge' => 'Verified Client • SaaS & FinTech', 'initials' => 'PS', 'avatar_bg' => 'var(--color-navy)', 'stars' => 5
        ],
        [
            'quote' => 'Their brand copywriting gave us the voice we had been searching for. Every tagline, pitch deck, and landing page now sounds distinct, credible, and relentlessly conversion-focused.',
            'author_name' => 'Rahul Verma', 'author_role' => 'Chief Marketing Officer, NovaBrands', 'author_badge' => 'Verified Client • D2C & Retail', 'initials' => 'RV', 'avatar_bg' => 'linear-gradient(135deg, #1B2A4A, #4A8B8C)', 'stars' => 5
        ],
        [
            'quote' => 'Working with WORDORA feels like having an in-house elite editorial board. They understand complex B2B tech nuance and consistently deliver publish-ready whitepapers on schedule.',
            'author_name' => 'Ananya Singh', 'author_role' => 'Content Lead, SaaS Studio', 'author_badge' => 'Verified Client • Enterprise Cloud', 'initials' => 'AS', 'avatar_bg' => 'linear-gradient(135deg, #4A8B8C, #0F1E36)', 'stars' => 5
        ]
    ];
}

$resultMetrics = json_decode(setting('home_sec7_metrics', '[]'), true) ?: [];
if (empty($resultMetrics)) {
    $resultMetrics = [
        ['num' => '+4x', 'label' => 'Organic Search Traffic'],
        ['num' => '+68%', 'label' => 'Reader Engagement'],
        ['num' => '2x', 'label' => 'Content Production Velocity'],
        ['num' => '98%', 'label' => 'Client Retention Rate']
    ];
}

$meta = [
    'title' => 'WORDORA — Words That Work. Stories That Sell.',
    'description' => 'We turn research, ideas and brand thinking into content people remember — and businesses can grow with. Editorial Content & Brand Copywriting Studio.',
];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     01 — HOME / HERO: THE EDITORIAL COVER
     ═══════════════════════════════════════════ -->
<?php include ROOT_PATH . '/views/partials/hero-banner.php'; ?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — THE EDITORIAL STANDARD: WHAT MAKES US DIFFERENT
     (Split Layout — Philosophy + Framed Document Artwork)
     ═══════════════════════════════════════════ -->
<section class="section" id="editorial-standard" style="background: var(--color-canvas);">
  <div class="container">
    <div class="why-split" style="align-items: center;">
      <div class="reveal-up">
        <span class="badge" style="background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink); border: 1px dashed var(--color-teal-ink); margin-bottom: var(--space-3);">
          <i class="ri-quill-pen-line"></i> <?= e(setting('home_sec2_badge', 'THE EDITORIAL MANDATE')) ?>
        </span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-4);">
          <?= setting('home_sec2_title', "We don't just write content.<br>We engineer conviction.") ?>
        </h2>
        
        <p class="body-lg" style="margin-bottom: var(--space-4);">
          <?= e(setting('home_sec2_p1', 'Every piece we produce starts with deep audience research, competitive keyword intelligence, and a clear commercial thesis. The result? Words that rank, resonate, and convert — not filler that fills a content calendar.')) ?>
        </p>

        <!-- Magazine Pull Quote -->
        <?php $sec2Quote = setting('home_sec2_quote', '“Content without strategy is noise. Strategy without craft is forgettable.”'); ?>
        <?php if (!empty($sec2Quote)): ?>
        <blockquote class="editorial-quote">
          <?= e($sec2Quote) ?>
        </blockquote>
        <?php endif; ?>

        <?php $sec2P2 = setting('home_sec2_p2', 'From SEO-led topic clusters to brand manifestos and C-suite thought leadership, we bring editorial rigor and commercial intent to every sentence.'); ?>
        <?php if (!empty($sec2P2)): ?>
        <p class="body-base" style="color: var(--color-text-muted); margin-bottom: var(--space-6);">
          <?= e($sec2P2) ?>
        </p>
        <?php endif; ?>

        <div style="display: flex; gap: var(--space-4); align-items: center; flex-wrap: wrap;">
          <a href="<?= url(setting('home_sec2_btn1_url', 'services.php')) ?>" class="btn btn-navy">
            <?= e(setting('home_sec2_btn1_text', 'Explore What We Do')) ?> <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url(setting('home_sec2_btn2_url', 'who-we-are.php')) ?>" class="btn btn-outline">
            <?= e(setting('home_sec2_btn2_text', 'Read Our Story')) ?> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

      <div class="reveal-up text-center">
        <div class="industry-media-frame" style="background: var(--color-white); padding: 2.25rem 2rem; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: var(--radius-xl); box-shadow: var(--shadow-md); position: relative; max-width: 460px; margin: 0 auto;">
          <span class="industry-media-tag" style="position: absolute; top: -14px; right: 20px;"><i class="ri-sparkling-fill"></i> <?= e(setting('home_sec2_artwork_tag', 'Precision Crafted')) ?></span>
          <img src="<?= media_url(setting('home_sec2_artwork', '/img/service treasure.png')) ?>" alt="WORDORA Editorial Standard — Research-Led Craft" loading="lazy" style="max-height: 340px; margin: 0 auto; object-fit: contain;">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     03 — WHAT WE DO: CORE EDITORIAL DISCIPLINES BENTO
     ═══════════════════════════════════════════ -->
<?php
$bentoTiles = json_decode(setting('home_sec3_bento', '[]'), true) ?: [];
if (empty($bentoTiles)) {
    $bentoTiles = [
        [
            'badge' => 'High Impact & Search', 'icon' => 'ri-search-eye-line',
            'title' => 'SEO Content Writing',
            'desc' => 'Deeply researched articles, search-intent blog posts, and authoritative guides that outrank generic competitor copy and satisfy real humans.',
            'tags' => 'Topic Cluster Frameworks, Keyword Intent Optimization, Long-Form SEO Content',
            'btn_text' => 'Explore SEO Writing', 'btn_url' => 'service/seo-content-writing'
        ],
        [
            'badge' => 'Voice & Positioning', 'icon' => 'ri-pen-nib-line',
            'title' => 'Brand Copywriting',
            'desc' => 'Taglines, manifestos, about pages, and messaging architectures that give ambitious brands an unmistakable, authoritative tone.',
            'tags' => '',
            'btn_text' => 'Craft Brand Voice', 'btn_url' => 'service/brand-copy'
        ],
        [
            'badge' => 'Publishing & Insights', 'icon' => 'ri-article-line',
            'title' => 'Blog & Article Writing',
            'desc' => 'Compelling editorial essays, Quora answers, company profiles, and regular publication series that build trusted reader followings.',
            'tags' => '',
            'btn_text' => 'Explore Blog Writing', 'btn_url' => 'service/blog-writing'
        ],
        [
            'badge' => 'Scholarly Rigor', 'icon' => 'ri-book-open-line',
            'title' => 'Academic & Research Writing',
            'desc' => 'Peer-reviewed syntheses, dissertations, research papers, and scholarly reports fact-checked to institutional standards.',
            'tags' => '',
            'btn_text' => 'Explore Research Writing', 'btn_url' => 'service/academic-writing'
        ],
        [
            'badge' => 'Clarity & Docs', 'icon' => 'ri-file-code-line',
            'title' => 'Technical Writing',
            'desc' => 'Translating complex software architectures, developer APIs, and cloud infrastructure into readable documentation.',
            'tags' => '',
            'btn_text' => 'Explore Technical Docs', 'btn_url' => 'service/technical-writing'
        ],
        [
            'badge' => 'Executive & Founder Authority', 'icon' => '',
            'title' => 'Thought Leadership & Executive Voice',
            'desc' => 'Ghostwritten C-suite LinkedIn essays, founder personal branding, and multi-channel executive communication that establish undeniable industry authority.',
            'tags' => '',
            'btn_text' => 'Explore Thought Leadership', 'btn_url' => 'service/thought-leadership',
            'btn2_text' => 'View All 7 Disciplines', 'btn2_url' => 'services'
        ]
    ];
}
$b1 = $bentoTiles[0] ?? [];
$b2 = $bentoTiles[1] ?? [];
$b3 = $bentoTiles[2] ?? [];
$b4 = $bentoTiles[3] ?? [];
$b5 = $bentoTiles[4] ?? [];
$b6 = $bentoTiles[5] ?? [];
?>
<section class="section" style="background: var(--color-canvas);">
  <div class="container">
    <div class="reveal-up" style="margin-bottom: var(--space-8);">
      <span class="label-upper"><?= e(setting('home_sec3_label', 'WHAT WE DO')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('home_sec3_title', 'Content with a job to do.')) ?></h2>
      <p class="body-lg" style="max-width: 680px;">
        <?= e(setting('home_sec3_desc', 'From search-led topic clusters to C-suite thought leadership and technical documentation, we engineer words built to perform.')) ?>
      </p>
    </div>

    <!-- Asymmetric 7-Tile Bento Grid -->
    <div class="services-bento reveal-up">
      
      <!-- Bento Tile 1: SEO Content Writing (Wide / White Highlight) -->
      <div class="bento-tile bento-tile--white bento-tile-seo">
        <div>
          <div class="bento-tile__header">
            <span class="badge badge-teal"><?= e($b1['badge'] ?? 'High Impact & Search') ?></span>
            <div class="bento-tile__icon"><i class="<?= !empty($b1['icon']) ? e($b1['icon']) : 'ri-search-eye-line' ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($b1['title'] ?? 'SEO Content Writing') ?></h3>
          <p class="bento-tile__desc">
            <?= e($b1['desc'] ?? '') ?>
          </p>
          <?php if (!empty($b1['tags'])): ?>
          <div class="bento-tile__tags">
            <?php foreach (array_map('trim', explode(',', $b1['tags'])) as $tag): if ($tag): ?>
            <span class="bento-tile__tag"><?= e($tag) ?></span>
            <?php endif; endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <a href="<?= url($b1['btn_url'] ?? 'service/seo-content-writing') ?>" class="bento-tile__link">
          <?= e($b1['btn_text'] ?? 'Explore SEO Writing') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>

      <!-- Bento Tile 2: Brand Copywriting (Navy Editorial Tile) -->
      <div class="bento-tile bento-tile--navy bento-tile-brand">
        <div>
          <div class="bento-tile__header">
            <span class="badge" style="background: rgba(255,255,255,0.15); color: var(--color-teal-pale);"><?= e($b2['badge'] ?? 'Voice & Positioning') ?></span>
            <div class="bento-tile__icon"><i class="<?= !empty($b2['icon']) ? e($b2['icon']) : 'ri-quill-pen-line' ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($b2['title'] ?? 'Brand Copywriting') ?></h3>
          <p class="bento-tile__desc">
            <?= e($b2['desc'] ?? '') ?>
          </p>
        </div>
        <a href="<?= url($b2['btn_url'] ?? 'service/brand-copy') ?>" class="bento-tile__link">
          <?= e($b2['btn_text'] ?? 'Craft Brand Voice') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>

      <!-- Bento Tile 3: Blog & Article Writing -->
      <div class="bento-tile bento-tile--white bento-tile-social">
        <div>
          <div class="bento-tile__header">
            <span class="label-upper" style="font-size: 0.7rem;"><?= e($b3['badge'] ?? 'Publishing & Insights') ?></span>
            <div class="bento-tile__icon"><i class="<?= !empty($b3['icon']) ? e($b3['icon']) : 'ri-article-line' ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($b3['title'] ?? 'Blog & Article Writing') ?></h3>
          <p class="bento-tile__desc">
            <?= e($b3['desc'] ?? '') ?>
          </p>
        </div>
        <a href="<?= url($b3['btn_url'] ?? 'service/blog-writing') ?>" class="bento-tile__link">
          <?= e($b3['btn_text'] ?? 'Explore Blog Writing') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>

      <!-- Bento Tile 4: Academic & Research Writing (Pale Teal Accent) -->
      <div class="bento-tile bento-tile--teal-pale bento-tile-email">
        <div>
          <div class="bento-tile__header">
            <span class="label-upper" style="font-size: 0.7rem;"><?= e($b4['badge'] ?? 'Scholarly Rigor') ?></span>
            <div class="bento-tile__icon" style="background: var(--color-white); color: var(--color-teal-ink);"><i class="<?= !empty($b4['icon']) ? e($b4['icon']) : 'ri-book-open-line' ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($b4['title'] ?? 'Academic & Research Writing') ?></h3>
          <p class="bento-tile__desc">
            <?= e($b4['desc'] ?? '') ?>
          </p>
        </div>
        <a href="<?= url($b4['btn_url'] ?? 'service/academic-writing') ?>" class="bento-tile__link">
          <?= e($b4['btn_text'] ?? 'Explore Research Writing') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>

      <!-- Bento Tile 5: Technical Writing -->
      <div class="bento-tile bento-tile--white bento-tile-technical">
        <div>
          <div class="bento-tile__header">
            <span class="label-upper" style="font-size: 0.7rem;"><?= e($b5['badge'] ?? 'Clarity & Docs') ?></span>
            <div class="bento-tile__icon"><i class="<?= !empty($b5['icon']) ? e($b5['icon']) : 'ri-code-s-slash-line' ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($b5['title'] ?? 'Technical Writing') ?></h3>
          <p class="bento-tile__desc">
            <?= e($b5['desc'] ?? '') ?>
          </p>
        </div>
        <a href="<?= url($b5['btn_url'] ?? 'service/technical-writing') ?>" class="bento-tile__link">
          <?= e($b5['btn_text'] ?? 'Explore Technical Docs') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>

      <!-- Bento Tile 6: Thought Leadership & Executive Voice (Full-Width Deep Navy Banner) -->
      <div class="bento-tile bento-tile--dark-wide bento-tile-strategy">
        <div style="display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
          <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: var(--space-4);">
            <div style="max-width: 640px;">
              <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3);"><?= e($b6['badge'] ?? 'Executive & Founder Authority') ?></span>
              <h3 class="bento-tile__title" style="font-size: 1.6rem;"><?= e($b6['title'] ?? 'Thought Leadership & Executive Voice') ?></h3>
              <p class="bento-tile__desc" style="margin-bottom: 0;">
                <?= e($b6['desc'] ?? '') ?>
              </p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
              <a href="<?= url($b6['btn_url'] ?? 'service/thought-leadership') ?>" class="btn btn-ghost" style="border: 1px solid rgba(255,255,255,0.3); color: #fff;">
                <span><?= e($b6['btn_text'] ?? 'Explore Thought Leadership') ?></span> <i class="ri-arrow-right-line"></i>
              </a>
              <a href="<?= url($b6['btn2_url'] ?? 'services') ?>" class="btn btn-primary">
                <span><?= e($b6['btn2_text'] ?? 'View All 7 Disciplines') ?></span> <i class="ri-compass-3-line"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     03C — OTHER / DEVELOPMENT SERVICES BENTO (ADMIN EDITABLE)
     ═══════════════════════════════════════════ -->
<?php
$devBentoEnabled = setting('home_sec3c_enabled', '1') !== '0';
if ($devBentoEnabled):
$devBentoTiles = json_decode(setting('home_sec3c_bento', '[]'), true) ?: [];
if (empty($devBentoTiles)) {
    $devBentoTiles = [
        [
            'badge' => 'High Impact & Search', 'icon' => 'ri-search-eye-line',
            'title' => 'Technical SEO',
            'desc' => 'Advanced technical SEO, Core Web Vitals optimization, schema markup, and data-driven organic growth strategies.',
            'btn_text' => 'Explore SEO', 'btn_url' => 'service/seo'
        ],
        [
            'badge' => 'Scalable Systems', 'icon' => 'ri-code-box-line',
            'title' => 'Web App Development',
            'desc' => 'Custom, high-performance web applications built for speed, security, and scalable enterprise growth.',
            'btn_text' => 'Explore Web Apps', 'btn_url' => 'service/web-development'
        ],
        [
            'badge' => 'iOS & Android', 'icon' => 'ri-smartphone-line',
            'title' => 'Mobile App Development',
            'desc' => 'Native and cross-platform mobile experiences designed to engage users and drive retention.',
            'btn_text' => 'Explore Mobile Apps', 'btn_url' => 'service/mobile-development'
        ],
        [
            'badge' => 'Digital Storefronts', 'icon' => 'ri-macbook-line',
            'title' => 'Website Designing and Development',
            'desc' => 'Stunning, conversion-optimized websites that blend immersive design with flawless functionality.',
            'btn_text' => 'Explore Website Design', 'btn_url' => 'service/website-design'
        ],
        [
            'badge' => 'Future Ready', 'icon' => 'ri-robot-2-line',
            'title' => 'AI Development',
            'desc' => 'Intelligent AI integrations, machine learning models, and automated workflows that modernize operations.',
            'btn_text' => 'Explore AI Solutions', 'btn_url' => 'service/ai-development'
        ],
        [
            'badge' => 'Core Logic', 'icon' => 'ri-code-s-slash-line',
            'title' => 'Software Development',
            'desc' => 'End-to-end software solutions designed to solve complex business challenges with robust architectures.',
            'btn_text' => 'Explore Software', 'btn_url' => 'service/software-development'
        ],
        [
            'badge' => 'User Centric', 'icon' => 'ri-paint-brush-line',
            'title' => 'UI/UX Design',
            'desc' => 'Intuitive, user-centered interfaces and experiences that delight users and streamline interactions.',
            'btn_text' => 'Explore UI/UX', 'btn_url' => 'service/ui-ux-design'
        ]
    ];
}
?>
<section class="section" id="development-bento" style="background: var(--color-navy); color: var(--color-white);">
  <div class="container">
    <div class="reveal-up" style="margin-bottom: var(--space-8);">
      <span class="label-upper" style="color: var(--color-teal-pale); border-color: rgba(255,255,255,0.2);"><?= e(setting('home_sec3c_label', 'DEVELOPMENT & DESIGN')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3); color: var(--color-white);"><?= e(setting('home_sec3c_title', 'Building digital experiences.')) ?></h2>
      <p class="body-lg" style="max-width: 680px; color: rgba(255,255,255,0.75);">
        <?= e(setting('home_sec3c_desc', 'From robust web applications and mobile apps to AI integrations and intuitive UI/UX, we engineer software built to perform.')) ?>
      </p>
    </div>

    <!-- Asymmetric 7-Tile Bento Grid -->
    <div class="services-bento reveal-up">
      <?php foreach ($devBentoTiles as $dtIdx => $dt): 
          $dtClass = 'bento-tile--white';
          if ($dtIdx === 3) $dtClass = 'bento-tile--teal-pale';
          $gridClass = '';
          if ($dtIdx === 0) $gridClass = 'bento-tile-seo';
          elseif ($dtIdx === 1) $gridClass = 'bento-tile-brand';
          elseif ($dtIdx === 2) $gridClass = 'bento-tile-social';
          elseif ($dtIdx === 3) $gridClass = 'bento-tile-email';
          elseif ($dtIdx === 4) $gridClass = 'bento-tile-technical';
          elseif ($dtIdx === 5 || $dtIdx === 6) $gridClass = 'bento-tile-half';
      ?>
      <div class="bento-tile <?= $dtClass ?> <?= $gridClass ?>">
        <div>
          <div class="bento-tile__header">
            <span class="<?= $dtIdx === 3 ? 'label-upper' : 'badge badge-teal' ?>" style="<?= $dtIdx === 3 ? 'font-size: 0.7rem; border-color: rgba(0,0,0,0.1);' : 'font-size: 0.7rem;' ?>"><?= e($dt['badge'] ?? '') ?></span>
            <div class="bento-tile__icon" style="<?= $dtIdx === 3 ? 'background: var(--color-white); color: var(--color-teal-ink);' : '' ?>"><i class="<?= e($dt['icon'] ?: 'ri-code-box-line') ?>"></i></div>
          </div>
          <h3 class="bento-tile__title"><?= e($dt['title'] ?? '') ?></h3>
          <p class="bento-tile__desc">
            <?= e($dt['desc'] ?? '') ?>
          </p>
        </div>
        <a href="<?= url($dt['btn_url'] ?? 'services') ?>" class="bento-tile__link">
          <?= e($dt['btn_text'] ?? 'Explore Service') ?> <i class="ri-arrow-right-line"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>



<!-- ═══════════════════════════════════════════
     03B — EDITORIAL CAPABILITIES MARQUEE (STATIC PAPAER BANNER)
     ═══════════════════════════════════════════ -->
<?php 
$marqueeBg = setting('marquee_bg_image', '/img/papaer banner.png');
$marqueeBgUrl = !empty($marqueeBg) ? media_url($marqueeBg) : '';
$marqueeRows = json_decode(setting('home_sec3_marquee_rows', '[]'), true) ?: [];
$r1Items = !empty($marqueeRows['row1']) ? array_map('trim', explode(',', $marqueeRows['row1'])) : ['SEO Content Writing', 'Brand Voice Architecture', 'Thought Leadership Essays', 'Social Editorial Calendars', 'Email Sequences & Newsletters', 'Technical Whitepapers', 'Full-Funnel Content Strategy'];
$r2Items = !empty($marqueeRows['row2']) ? array_map('trim', explode(',', $marqueeRows['row2'])) : ['Conversion Copywriting', 'Case Study Narratives', 'Topic Cluster Frameworks', 'Enterprise B2B Whitepapers', 'Fact-Checked Research', 'Executive Ghostwriting', 'Content Audits & Roadmaps'];
$r3Items = !empty($marqueeRows['row3']) ? array_map('trim', explode(',', $marqueeRows['row3'])) : ['Keyword Intent Mapping', 'Long-Form Authority Guides', 'High-Converting Pitch Decks', 'Onboarding Email Sequences', 'Industry Authority Benchmarks', 'Viral LinkedIn Carousels', 'Multi-Format Repurposing'];
?>
<section class="marquee-banner-section" aria-label="Creative Capabilities Marquee" style="<?= !empty($marqueeBgUrl) ? "background-image: url('{$marqueeBgUrl}');" : '' ?>">
  <div class="marquee-banner-header reveal-up">
    <span class="label-upper"><?= e(setting('home_sec3_marquee_label', 'EDITORIAL CAPABILITIES')) ?></span>
    <h3 class="heading-lg" style="margin-top: var(--space-2); margin-bottom: 0;">
      <?= e(setting('home_sec3_marquee_title', 'Content engineered for ambitious market leaders.')) ?>
    </h3>
  </div>

  <div class="marquee-parallax-stream">
    <!-- Row 1: Left to Right -->
    <div class="marquee-parallax-row ltr">
      <div class="marquee-parallax-track">
        <?php foreach ($r1Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? 'glass-pill--accent' : (($pi % 3 === 0) ? 'glass-pill--navy' : '') ?>"><i class="ri-quill-pen-fill"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($r1Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? 'glass-pill--accent' : (($pi % 3 === 0) ? 'glass-pill--navy' : '') ?>"><i class="ri-quill-pen-fill"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
    </div>

    <!-- Row 2: Right to Left -->
    <div class="marquee-parallax-row rtl">
      <div class="marquee-parallax-track">
        <?php foreach ($r2Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? '' : (($pi % 3 === 0) ? 'glass-pill--navy' : 'glass-pill--accent') ?>"><i class="ri-sparkling-line"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($r2Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? '' : (($pi % 3 === 0) ? 'glass-pill--navy' : 'glass-pill--accent') ?>"><i class="ri-sparkling-line"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
    </div>

    <!-- Row 3: Left to Right -->
    <div class="marquee-parallax-row ltr-fast">
      <div class="marquee-parallax-track">
        <?php foreach ($r3Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? 'glass-pill--accent' : (($pi % 3 === 0) ? 'glass-pill--navy' : '') ?>"><i class="ri-focus-2-line"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($r3Items as $pi => $pill): if ($pill): ?>
        <span class="glass-pill <?= ($pi % 2 === 0) ? 'glass-pill--accent' : (($pi % 3 === 0) ? 'glass-pill--navy' : '') ?>"><i class="ri-focus-2-line"></i> <?= e($pill) ?></span>
        <?php endif; endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     04 — WHY BUSINESSES CHOOSE WORDORA
     (Philosophy + 6-Pillar Value Grid + Magazine Pull Quote + Unboxed Stats)
     ═══════════════════════════════════════════ -->
<section class="section why-section" id="why-choose-us">
  <div class="container">
    <div class="why-split">
      <div class="reveal-up">
        <span class="label-upper"><?= e(setting('home_sec4_label', 'WHY BUSINESSES CHOOSE WORDORA')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-4);">
          <?= setting('home_sec4_title', "Not just writers.<br>Content thinkers & growth partners.") ?>
        </h2>
        
        <p class="body-lg" style="margin-bottom: var(--space-4);">
          <?= e(setting('home_sec4_desc', 'We research before we write. We understand before we create. We build every piece around a measurable purpose — establishing industry authority, winning search intent, and converting qualified customers into long-term revenue.')) ?>
        </p>

        <!-- Magazine Pull Quote -->
        <?php $sec4Quote = setting('home_sec4_quote', '“Good content fills a page. Great content moves someone.”'); ?>
        <?php if (!empty($sec4Quote)): ?>
        <blockquote class="editorial-quote">
          <?= e($sec4Quote) ?>
        </blockquote>
        <?php endif; ?>

        <div style="margin-top: var(--space-6);">
          <a href="<?= url(setting('home_sec4_btn_url', 'who-we-are.php')) ?>" class="btn btn-outline">
            <?= e(setting('home_sec4_btn_text', 'Read Our Story')) ?> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

      <div class="reveal-up why-illustration-wrap">
        <div class="why-illustration-backdrop"></div>
        <img src="<?= media_url(setting('home_sec4_artwork', '/img/why choose us.png')) ?>" alt="Why Businesses Choose WORDORA Illustration" loading="lazy">
      </div>
    </div>

    <!-- 6-Pillar Why Choose Us Feature Grid -->
    <div class="why-features-grid reveal-up">
      <?php foreach ($whyCards as $card): ?>
      <div class="why-feature-card">
        <div class="why-feature-card__icon"><i class="<?= e($card['icon'] ?? 'ri-quill-pen-line') ?>"></i></div>
        <h3 class="why-feature-card__title"><?= e($card['title'] ?? '') ?></h3>
        <p class="why-feature-card__desc">
          <?= e($card['desc'] ?? '') ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Editorial Unboxed Stats -->
    <div class="editorial-stats reveal-up">
      <?php foreach ($stats as $st): ?>
      <div class="editorial-stat">
        <div class="editorial-stat__num"><span class="stat-count" data-count="<?= e($st['count'] ?? '0') ?>"><?= e($st['count'] ?? '0') ?></span><?= e($st['suffix'] ?? '+') ?></div>
        <div class="editorial-stat__line"></div>
        <div class="editorial-stat__label"><?= e($st['label'] ?? '') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     05 — WHO WE WRITE FOR: DIFFERENT INDUSTRIES. ONE OBSESSION: CLARITY.
     (Horizontal Editorial Dashed Work Showcase Swiper)
     ═══════════════════════════════════════════ -->
<section class="section industry-matrix-section" id="who-we-write-for" style="background: var(--color-surface); overflow: hidden;">
  <div class="container" style="max-width: 1320px; padding: 0 1.5rem; margin: 0 auto;">
    <div class="why-split" style="margin-bottom: var(--space-8);">
      <div class="reveal-up">
        <span class="label-upper"><?= e(setting('home_sec5_label', 'WHO WE WRITE FOR')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= setting('home_sec5_title', "Different industries.<br>One obsession: clarity.") ?></h2>
        <p class="body-lg">
          <?= e(setting('home_sec5_desc', 'Content changes with the audience. Our thinking changes with it. We adapt tone, technical depth, and vocabulary to the exact expectations of your market.')) ?>
        </p>
      </div>
      <div class="reveal-up text-center">
        <img src="<?= media_url(setting('home_sec5_artwork', '/img/industry.png')) ?>" alt="WORDORA Industry Specializations" loading="lazy" style="max-height: 260px; margin: 0 auto; object-fit: contain;">
      </div>
    </div>

    <!-- Horizontal Case Study Swiper for Who We Write For -->
    <div class="swiper industry-work-swiper reveal-up">
      <div class="swiper-wrapper">
        <?php foreach ($industrySlides as $slide): ?>
        <div class="swiper-slide">
          <div class="industry-work-card">
            <div class="industry-work-card__content">
              <div class="industry-work-badge">
                <i class="ri-checkbox-circle-fill"></i> <?= e($slide['badge'] ?? '') ?>
              </div>
              <h3 class="industry-work-title"><?= e($slide['title'] ?? '') ?></h3>
              <p class="industry-work-desc">
                <?= e($slide['desc'] ?? '') ?>
              </p>
              <div class="industry-work-metrics">
                <?php if (!empty($slide['m1_val'])): ?>
                <div class="industry-metric-item">
                  <div class="industry-metric-val"><?= e($slide['m1_val']) ?></div>
                  <div class="industry-metric-lbl"><?= e($slide['m1_lbl'] ?? '') ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($slide['m2_val'])): ?>
                <div class="industry-metric-item">
                  <div class="industry-metric-val"><?= e($slide['m2_val']) ?></div>
                  <div class="industry-metric-lbl"><?= e($slide['m2_lbl'] ?? '') ?></div>
                </div>
                <?php endif; ?>
              </div>
              <div class="industry-work-cta-wrap">
                <a href="<?= url($slide['btn1_url'] ?? 'contact.php') ?>" class="btn btn-primary">
                  <span><?= e($slide['btn1_text'] ?? 'Request Similar Project Scope') ?></span> <i class="ri-arrow-right-line"></i>
                </a>
                <?php if (!empty($slide['btn2_text'])): ?>
                <a href="<?= url($slide['btn2_url'] ?? '#') ?>" class="btn btn-ghost" style="color: var(--color-navy); font-weight: 600;">
                  <span><?= e($slide['btn2_text']) ?></span> <i class="ri-external-link-line"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>
            <div class="industry-work-card__media">
              <div class="industry-media-frame">
                <span class="industry-media-tag"><i class="ri-sparkling-fill"></i> <?= e($slide['media_tag'] ?? 'Client Success Study') ?></span>
                <img src="<?= media_url($slide['img'] ?? '/img/case study.png') ?>" alt="<?= e($slide['title'] ?? '') ?>" loading="lazy">
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Controls: Arrows Only (No Dots) -->
      <div style="display: flex; align-items: center; justify-content: flex-end; gap: var(--space-2); margin-top: var(--space-6);">
        <button class="btn btn-outline btn-sm industry-work-prev" aria-label="Previous Industry Case Study"><i class="ri-arrow-left-line"></i></button>
        <button class="btn btn-outline btn-sm industry-work-next" aria-label="Next Industry Case Study"><i class="ri-arrow-right-line"></i></button>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     06 — HOW WE WORK: FROM BRIEF TO BRILLIANCE
     (Winding Editorial Journey & Roadmap)
     ═══════════════════════════════════════════ -->
<section class="section process-section">
  <div class="container">
    <div class="reveal-up text-center" style="max-width: 680px; margin: 0 auto var(--space-8);">
      <span class="label-upper"><?= e(setting('home_sec6_label', 'OUR PROCESS')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('home_sec6_title', 'From brief to brilliance.')) ?></h2>
      <p class="body-lg">
        <?= e(setting('home_sec6_desc', 'Every project follows a clear editorial rhythm — research first, strategy next, craft throughout.')) ?>
      </p>
    </div>

    <div class="process-winding">
      <div class="reveal-up" style="text-align: center;">
        <img src="<?= media_url(setting('home_sec6_artwork', '/img/process.png')) ?>" alt="WORDORA Editorial Production Journey" loading="lazy" style="max-height: 420px; margin: 0 auto;">
        <div style="margin-top: var(--space-4); font-family: var(--font-mono); font-size: 0.8125rem; color: var(--color-teal-ink); letter-spacing: 0.05em;">
          <?= e(setting('home_sec6_flow_tag', 'Research → Strategy → Writing → Refinement')) ?>
        </div>
      </div>

      <div class="process-steps reveal-up">
        <?php foreach ($processSteps as $step): ?>
        <div class="process-step">
          <div class="process-step__dot"></div>
          <div class="process-step__num"><?= e($step['step_num'] ?? '') ?></div>
          <h3 class="process-step__title"><?= e($step['title'] ?? '') ?></h3>
          <p class="process-step__desc">
            <?= e($step['desc'] ?? '') ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     07 — PROOF / CLIENT STORIES: THE WORK SPEAKS TOO
     (Spotlight Testimonial + Artwork + Results)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas);">
  <div class="container">
    <div class="reveal-up text-center" style="margin-bottom: var(--space-10);">
      <span class="label-upper"><?= e(setting('home_sec7_label', 'CLIENT STORIES')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2);"><?= e(setting('home_sec7_title', '“The work speaks too.”')) ?></h2>
    </div>

    <!-- Editorial Testimonial Stage (Swiper Single-Focus) -->
    <div class="testimonial-stage-wrapper reveal-up">
      <div class="swiper testimonial-stage-swiper">
        <div class="swiper-wrapper">
          <?php foreach ($testimonials as $testi): ?>
          <div class="swiper-slide">
            <div class="testimonial-card-editorial">
              <div class="testimonial-quote-body">
                <span class="testimonial-quote-mark">“</span>
                <p class="testimonial-quote-text">
                  <?= e($testi['quote'] ?? '') ?>
                </p>
              </div>
              <div class="testimonial-author-card">
                <div class="testimonial-author-header">
                  <div class="testimonial-author-avatar" style="<?= !empty($testi['avatar_bg']) ? 'background: ' . e($testi['avatar_bg']) . ';' : '' ?> overflow: hidden; padding: 0;">
                    <?php if (!empty($testi['avatar_img'])): ?>
                      <img src="<?= media_url($testi['avatar_img']) ?>" alt="<?= e($testi['author_name'] ?? 'Client') ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;">
                    <?php else: ?>
                      <?= e($testi['initials'] ?? 'CL') ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="testimonial-author-name"><?= e($testi['author_name'] ?? '') ?></div>
                    <div class="testimonial-author-role"><?= e($testi['author_role'] ?? '') ?></div>
                  </div>
                </div>
                <div class="testimonial-author-badge">
                  <i class="ri-checkbox-circle-fill"></i> <?= e($testi['author_badge'] ?? 'Verified Client') ?>
                </div>
                <div class="testimonial-stars">
                  <?php $stars = (int)($testi['stars'] ?? 5); for ($s = 0; $s < $stars; $s++): ?>
                    <i class="ri-star-fill"></i>
                  <?php endfor; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination Dots -->
        <div class="testimonial-pagination" style="text-align: center; margin-top: var(--space-6);"></div>
      </div>
    </div>

    <!-- Result Metrics Strip -->
    <div class="result-metrics-strip reveal-up">
      <?php foreach ($resultMetrics as $rm): ?>
      <div class="result-metric-card">
        <div class="result-metric-card__num"><?= e($rm['num'] ?? '') ?></div>
        <div class="result-metric-card__label"><?= e($rm['label'] ?? '') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     08 — FROM THE EDITORIAL DESK: IDEAS WORTH READING
     (Staggered Blog Insights Grid)
     ═══════════════════════════════════════════ -->
<section class="editorial-desk-section">
  <div class="container">
    <div class="reveal-up" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-6); gap: var(--space-4);">
      <div>
        <span class="label-upper"><?= e(setting('home_sec8_label', 'FROM THE EDITORIAL DESK')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: 0;"><?= e(setting('home_sec8_title', 'Ideas worth reading.')) ?></h2>
      </div>
      <a href="<?= url(setting('home_sec8_btn_url', 'blog/')) ?>" class="btn btn-outline btn-sm">
        <?= e(setting('home_sec8_btn_text', 'View All Insights')) ?> <i class="ri-arrow-right-line"></i>
      </a>
    </div>

    <div class="editorial-desk-grid reveal-up">
      <?php if (!empty($recentPosts)): ?>
        <!-- Featured Big Post (First Item) -->
        <?php $fp = $recentPosts[0]; ?>
        <article class="desk-post--featured">
          <div class="desk-post__image">
            <img src="<?= media_url($fp['featured_img'], '/img/blog.png') ?>" alt="<?= e($fp['title']) ?>" loading="lazy">
          </div>
          <div class="desk-post__body">
            <span class="badge badge-teal" style="margin-bottom: var(--space-2);"><?= e($fp['category_name'] ?? 'Editorial Strategy') ?></span>
            <h3 class="desk-post__title">
              <a href="<?= url('blog/' . $fp['slug']) ?>"><?= e($fp['title']) ?></a>
            </h3>
            <p class="desk-post__excerpt">
              <?= e(truncate(strip_tags($fp['excerpt'] ?: $fp['content']), 140)) ?>
            </p>
            <div class="desk-post__meta">
              <span><i class="ri-time-line"></i> <?= (int)$fp['read_time'] ?> min read</span>
              <span>•</span>
              <span><?= date('M d, Y', strtotime($fp['created_at'])) ?></span>
            </div>
          </div>
        </article>

        <!-- Secondary Stack Posts (Items 2 & 3) -->
        <div class="desk-post-stack">
          <?php for ($i = 1; $i < count($recentPosts); $i++): $sp = $recentPosts[$i]; ?>
          <article class="desk-post-small">
            <div>
              <span class="label-upper" style="font-size: 0.7rem; color: var(--color-teal-ink); margin-bottom: 4px;"><?= e($sp['category_name'] ?? 'Writing Craft') ?></span>
              <h4 class="desk-post__title">
                <a href="<?= url('blog/' . $sp['slug']) ?>"><?= e($sp['title']) ?></a>
              </h4>
              <p class="desk-post__excerpt" style="font-size: 0.875rem;">
                <?= e(truncate(strip_tags($sp['excerpt'] ?: $sp['content']), 95)) ?>
              </p>
            </div>
            <div class="desk-post__meta">
              <span><i class="ri-time-line"></i> <?= (int)$sp['read_time'] ?> min read</span>
              <span>•</span>
              <a href="<?= url('blog/' . $sp['slug']) ?>" style="font-weight: 600; color: var(--color-teal-ink);">Read Article →</a>
            </div>
          </article>
          <?php endfor; ?>
        </div>
      <?php else: ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: var(--space-8);">
          <p class="body-lg">Stay tuned for upcoming editorial essays and copywriting frameworks.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     09 — START A CONVERSATION: START SOMETHING WORTH READING
     (Signature CTA Panel)
     ═══════════════════════════════════════════ -->
<section class="section" style="padding-top: var(--space-12); padding-bottom: var(--space-20); background: var(--color-canvas);">
  <div class="container">
    <div class="cta-signature reveal-up">
      <div class="cta-signature__content">
        <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3); border: 1px solid rgba(212, 234, 234, 0.25);">
          <i class="ri-sparkling-fill"></i> <?= e(setting('home_sec9_badge', "LET'S MAKE SOMETHING MEANINGFUL")) ?>
        </span>
        
        <h2 class="cta-signature__title"><?= setting('home_sec9_title', "Start something <em>worth reading.</em>") ?></h2>
        
        <p class="cta-signature__text">
          <?= e(setting('home_sec9_desc', "Tell us what you're building. We'll help you find the words to move it forward, engage the right audience, and drive sustainable pipeline growth.")) ?>
        </p>

        <div class="cta-signature__actions">
          <a href="<?= url(setting('home_sec9_btn1_url', 'contact.php')) ?>" class="btn btn-primary btn-lg">
            <?= e(setting('home_sec9_btn1_text', 'Start a Conversation')) ?> <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url(setting('home_sec9_btn2_url', 'who-we-are.php')) ?>" class="btn btn-ghost btn-lg">
            <?= e(setting('home_sec9_btn2_text', 'Explore Studio')) ?> <i class="ri-compass-3-line"></i>
          </a>
        </div>

        <div class="cta-trust-pills">
          <span class="cta-trust-pill"><i class="ri-checkbox-circle-fill"></i> <?= e(setting('home_sec9_pill1', '24h Response')) ?></span>
          <span class="cta-trust-pill"><i class="ri-shield-check-fill"></i> <?= e(setting('home_sec9_pill2', 'NDA Protected')) ?></span>
          <span class="cta-trust-pill"><i class="ri-file-list-3-fill"></i> <?= e(setting('home_sec9_pill3', 'Free Content Audit')) ?></span>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= media_url(setting('home_sec9_artwork', '/img/cta 1.png')) ?>" alt="Start a Conversation with WORDORA" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>
