<?php
/**
 * WORDORA — What We Do (Editorial Services) Page
 * Master Editorial Structure matching Homepage & About Us luxury design
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

$meta = [
    'title' => 'What We Do — Editorial Writing Disciplines & Capabilities | WORDORA',
    'description' => 'Explore WORDORA bespoke content writing services: SEO Content Architecture, Brand Copywriting, Academic & Research Writing, Technical Documentation, Executive Ghostwriting, and Customer Case Studies.',
];

$activeServices = Service::getActive();

// Separate Editorial services (1-7) and Development services (8-14)
$editorialServices = array_filter($activeServices, fn($s) => in_array((int)$s['id'], [1, 2, 3, 4, 5, 6, 7]));
$devServices = array_filter($activeServices, fn($s) => in_array((int)$s['id'], [8, 9, 10, 11, 12, 13, 14]));
$showDevServices = (setting('home_sec3c_enabled', '1') !== '0');

// Fallback illustration map for the 7 service disciplines
$serviceFallbacks = [
    'seo-content' => 'Blog service.png',
    'social-media-content' => 'social media service.png',
    'technical-writing' => 'service treasure.png',
    'brand-copy' => 'brand content.png',
    'thought-leadership' => 'servcie page.png',
    'academic-writing' => 'acedmic.png',
    'blog-writing' => 'blog.png',
];

// Dynamic overlay for hero (matching homepage pattern)
$heroOpacity = (int)setting('hero_overlay_opacity', '75');
$heroOpacity = max(0, min(100, $heroOpacity));
$alphaLeft   = round(0.85 + (($heroOpacity / 100) * 0.13), 2);
$alphaMid    = round(0.45 + (($heroOpacity / 100) * 0.40), 2);
$heroGradient = "linear-gradient(90deg, rgba(15, 30, 54, {$alphaLeft}) 0%, rgba(15, 30, 54, {$alphaMid}) 34%, rgba(15, 30, 54, 0.18) 50%, rgba(15, 30, 54, 0.0) 62%, rgba(15, 30, 54, 0.0) 100%)";

ob_start();
?>

<!-- ═══════════════════════════════════════════
     01 — HERO COVER (MULTI-MODE: SLIDER / SINGLE / VIDEO)
     ═══════════════════════════════════════════ -->
<?php 
$heroPage = 'services';
include ROOT_PATH . '/views/partials/hero-banner.php'; 
?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — QUICK JUMP PILL BAR (NON-STICKY, NO BOTTOM BORDER, LOCKED TYPOGRAPHY)
     ═══════════════════════════════════════════ -->
<section class="svc-jump-bar" aria-label="Services Navigation">
  <div class="container">
    <div class="svc-jump-bar__inner">
      <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase; letter-spacing: 0.08em; margin-right: 6px;">
        <i class="ri-compass-3-line" style="color: var(--color-teal-ink);"></i> Quick Jump:
      </span>
      <?php foreach ($editorialServices as $srv): ?>
        <a href="#<?= e($srv['slug'] ?: 'service-' . $srv['id']) ?>" class="svc-jump-pill">
          <i class="<?= e($srv['icon'] ?: 'ri-quill-pen-line') ?>"></i>
          <?= e($srv['title']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     03 — MASTER SERVICE SHOWCASE (STACKING STICKY CARDS DECK — ZERO SHADOW)
     ═══════════════════════════════════════════ -->
<section class="section" id="services-matrix" style="background: var(--color-canvas); padding-top: var(--space-8);">
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 760px; margin: 0 auto var(--space-10);">
      <span class="label-upper"><?= e(setting('services_sec2_badge', 'CORE EDITORIAL DISCIPLINES')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('services_sec2_title', 'Engineered for Depth. Refined for Impact.')) ?></h2>
      <p class="body-lg">
        <?= e(setting('services_sec2_desc', 'Each discipline is led by specialized domain writers and subject-matter editors who understand the nuances of your industry.')) ?>
      </p>
    </div>

    <!-- Stacking Sticky Cards Stack -->
    <div class="svc-stack">
      <?php foreach ($editorialServices as $index => $srv): 
        $bullets = [];
        if (!empty($srv['bullets'])) {
            $decoded = json_decode($srv['bullets'], true);
            if (is_array($decoded) && !empty($decoded)) {
                foreach ($decoded as $bItem) {
                    $bTitle = is_array($bItem) ? ($bItem['title'] ?? '') : (string)$bItem;
                    if (!empty($bTitle)) {
                        $bullets[] = $bTitle;
                    }
                }
            } else {
                $bullets = array_filter(array_map('trim', explode(';', $srv['bullets'])));
            }
        }
        $slug = $srv['slug'] ?: 'service-' . $srv['id'];
        $fallbackFile = $serviceFallbacks[$slug] ?? 'culture notes.png';
        
        // Resolve image source with custom or dedicated fallback illustration
        if (!empty($srv['image_path'])) {
            $imgSrc = media_url($srv['image_path'], img($fallbackFile));
        } else {
            $imgSrc = img($fallbackFile);
        }
      ?>
      <div class="svc-stack-card" id="<?= e($slug) ?>">
        <div class="industry-work-card">
          
          <!-- Left Column: Editorial Narrative & Scope -->
          <div class="industry-work-card__content">
            <div class="industry-work-badge">
              <i class="<?= e($srv['icon'] ?: 'ri-checkbox-circle-fill') ?>"></i> Verified Deliverable &bull; <?= e($srv['tag'] ?: 'Editorial Discipline') ?>
            </div>
            
            <h3 class="industry-work-title" style="font-size: 1.85rem; margin-bottom: 0.9rem;">
              <?= e($srv['title']) ?>
            </h3>
            
            <p class="industry-work-desc" style="font-size: 1rem; line-height: 1.7; margin-bottom: 1.25rem;">
              <?= e($srv['description']) ?>
            </p>

            <?php if (!empty($bullets)): ?>
            <div style="margin-bottom: 1.35rem;">
              <div style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem;">
                Core Capabilities &amp; Scope:
              </div>
              <ul class="svc-capabilities-grid">
                <?php foreach ($bullets as $b): ?>
                <li style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.875rem; color: var(--color-text-muted);">
                  <i class="ri-checkbox-circle-fill" style="color: var(--color-teal-ink); font-size: 15px; margin-top: 2px; flex-shrink: 0;"></i>
                  <span><?= e($b) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <!-- Metrics Strip -->
            <div class="industry-work-metrics" style="margin-bottom: 1.5rem;">
              <div class="industry-metric-item">
                <div class="industry-metric-val"><?= e($srv['metrics_val'] ?: '+400%') ?></div>
                <div class="industry-metric-lbl"><?= e($srv['metrics_lbl'] ?: 'Performance Impact') ?></div>
              </div>
              <div class="industry-metric-item">
                <div class="industry-metric-val">100%</div>
                <div class="industry-metric-lbl">Human-Written &amp; Verified</div>
              </div>
            </div>

            <!-- Action Buttons: Explore Detailed Scope + Get Quote -->
            <div class="industry-work-cta-wrap">
              <a href="<?= url('service/' . urlencode($srv['slug'])) ?>" class="btn btn-primary">
                <span>Explore Detailed Scope</span> <i class="ri-arrow-right-line"></i>
              </a>
              <a href="<?= url('contact?service=' . urlencode($srv['title'])) ?>" class="btn btn-ghost">
                <span>Get a Quote</span> <i class="ri-chat-1-line"></i>
              </a>
            </div>
          </div>

          <!-- Right Column: Media Artwork Mockup Frame with Dashed Borders -->
          <div class="industry-work-card__media">
            <div class="industry-media-frame">
              <span class="industry-media-tag"><i class="ri-sparkling-fill"></i> Verified Craft</span>
              <img src="<?= $imgSrc ?>" alt="<?= e($srv['title']) ?> Showcase Illustration" loading="lazy">
            </div>
          </div>

        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     04 — OUR METHODOLOGY: THE 4-STAGE EDITORIAL FRAMEWORK
     (Dark Navy Luxury Container & #FAF8F5 Tilted Card + Glass Cards)
     ═══════════════════════════════════════════ -->
<section class="section svc-dark-section" id="methodology">
  <div class="svc-radial-glow"></div>
  <div class="container">
    <div class="reveal-up text-center" style="max-width: 680px; margin: 0 auto var(--space-12);">
      <span class="label-upper" style="color: var(--color-teal-light);"><?= e(setting('services_sec4_badge', 'OUR METHODOLOGY')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3); color: var(--color-white);"><?= e(setting('services_sec4_title', 'The 4-Stage Editorial Framework')) ?></h2>
      <p class="body-lg" style="color: rgba(255, 255, 255, 0.78);">
        <?= e(setting('services_sec4_desc', 'How we transform a rough brief into authoritative, search-dominant, and commercially potent content.')) ?>
      </p>
    </div>

    <div class="svc-dark-grid reveal-up">
      <!-- Left Column: Tilted #FAF8F5 Artwork Card with Process Graphic (No Badge) -->
      <div class="text-center">
        <div class="svc-artwork-card">
          <img src="<?= media_url(setting('services_sec4_artwork', '/img/process.png'), img('process.png')) ?>" alt="WORDORA Editorial Production Process" loading="lazy">
        </div>
      </div>

      <!-- Right Column: 4 Glowing Dark Glass Cards -->
      <?php 
      $stepsJson = setting('services_sec4_steps', '');
      $steps = !empty($stepsJson) ? json_decode($stepsJson, true) : [];
      if (empty($steps)) {
          $steps = [
              ['num' => '01', 'title' => 'Discovery & Intent Audit', 'desc' => 'We dissect your audience personas, buyer journey stages, competitor keyword gaps, and brand positioning requirements before writing a single word.'],
              ['num' => '02', 'title' => 'Architecture & Thesis', 'desc' => 'Structuring topic clusters, semantic keyword mappings, thesis outlines, and editorial frameworks that give every piece strategic direction.'],
              ['num' => '03', 'title' => 'Human Craftsmanship', 'desc' => 'Senior domain writers draft copy tailored to the exact rhythm, vocabulary, and technical expectations of your sector. Zero AI filler.'],
              ['num' => '04', 'title' => 'Fact-Checking & Polish', 'desc' => 'Multi-layer editorial review, citation verification, search-intent audits, and two comprehensive revision cycles before delivery.']
          ];
      }
      ?>
      <div class="svc-dark-cards-stack">
        <?php foreach ($steps as $st): ?>
        <div class="svc-dark-card">
          <div class="svc-dark-card__header">
            <div class="svc-dark-card__num"><?= e($st['num'] ?? '01') ?></div>
            <span class="svc-dark-card__step-tag">STAGE <?= e($st['num'] ?? '01') ?> &bull; EDITORIAL FRAMEWORK</span>
          </div>
          <h3 class="svc-dark-card__title"><?= e($st['title'] ?? '') ?></h3>
          <p class="svc-dark-card__desc"><?= e($st['desc'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     04.5 — DIGITAL ENGINEERING & DEVELOPMENT (LIGHT BG + DARK CARDS STICKY STACK)
     ═══════════════════════════════════════════ -->
<style>
/* Dark Navy Cards on Light Canvas — Opposite of primary white cards */
.industry-work-card--dark {
    background: var(--color-navy-deep) !important;
    border: 1.5px dashed rgba(74, 139, 140, 0.5) !important;
    box-shadow: none !important;
    color: var(--color-white);
}
.industry-work-card--dark .industry-work-title {
    color: var(--color-white) !important;
}
.industry-work-card--dark .industry-work-desc {
    color: rgba(255, 255, 255, 0.78) !important;
}
.industry-work-card--dark .industry-work-badge {
    background: rgba(74, 139, 140, 0.25) !important;
    color: var(--color-teal-light) !important;
    border: 1px solid rgba(212, 234, 234, 0.3) !important;
}
.industry-work-card--dark li span {
    color: rgba(255, 255, 255, 0.75) !important;
}
.industry-work-card--dark li i {
    color: var(--color-teal-light) !important;
}
.industry-work-card--dark .industry-metric-val {
    color: var(--color-teal-light) !important;
}
.industry-work-card--dark .industry-metric-lbl {
    color: rgba(255, 255, 255, 0.6) !important;
}
.industry-work-card--dark .industry-work-metrics {
    background: rgba(255, 255, 255, 0.06) !important;
    border-color: rgba(255, 255, 255, 0.08) !important;
}
.industry-work-card--dark .btn-primary {
    background: var(--color-teal-light) !important;
    color: var(--color-navy-deep) !important;
    border: none !important;
}
.industry-work-card--dark .btn-ghost {
    color: var(--color-white) !important;
    border: 1.5px dashed rgba(255, 255, 255, 0.3) !important;
}
.industry-work-card--dark .btn-ghost:hover {
    background: rgba(255, 255, 255, 0.1) !important;
}
.industry-work-card--dark .industry-media-frame {
    background: rgba(255, 255, 255, 0.04) !important;
    border-color: rgba(212, 234, 234, 0.2) !important;
}
.industry-work-card--dark .industry-media-tag {
    background: rgba(74, 139, 140, 0.3) !important;
    color: var(--color-teal-light) !important;
    border-color: rgba(74, 139, 140, 0.5) !important;
}
@media (min-width: 992px) {
  .svc-stack-card:hover .industry-work-card--dark {
    border-color: var(--color-teal-light) !important;
  }
}
</style>

<?php if ($showDevServices && !empty($devServices)): ?>
<section class="section" id="development-matrix" style="background: var(--color-canvas); padding-top: var(--space-8);">
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 760px; margin: 0 auto var(--space-10);">
      <span class="label-upper">DIGITAL ENGINEERING & ARCHITECTURE</span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);">Build Scalable Systems That Dominate.</h2>
      <p class="body-lg">
        Beyond words, we engineer high-performance digital products, native apps, and proprietary AI workflows that transform your operational capabilities.
      </p>
    </div>

    <!-- Quick Jump for Dev Services -->
    <div style="display: flex; justify-content: center; margin-bottom: var(--space-8);">
      <div class="svc-jump-bar__inner" style="margin-bottom: 0;">
        <span style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-navy); text-transform: uppercase; letter-spacing: 0.08em; margin-right: 6px;">
          <i class="ri-compass-3-line" style="color: var(--color-teal-ink);"></i> Tech Stack:
        </span>
        <?php foreach ($devServices as $srv): ?>
          <a href="#<?= e($srv['slug'] ?: 'service-' . $srv['id']) ?>" class="svc-jump-pill">
            <i class="<?= e($srv['icon'] ?: 'ri-code-box-line') ?>"></i>
            <?= e($srv['title']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Stacking Sticky Cards Stack -->
    <div class="svc-stack">
      <?php foreach ($devServices as $index => $srv): 
        $bullets = [];
        if (!empty($srv['bullets'])) {
            $decoded = json_decode($srv['bullets'], true);
            if (is_array($decoded) && !empty($decoded)) {
                foreach ($decoded as $bItem) {
                    $bTitle = is_array($bItem) ? ($bItem['title'] ?? '') : (string)$bItem;
                    if (!empty($bTitle)) {
                        $bullets[] = $bTitle;
                    }
                }
            } else {
                $bullets = array_filter(array_map('trim', explode(';', $srv['bullets'])));
            }
        }
        $slug = $srv['slug'] ?: 'service-' . $srv['id'];
        $fallbackFile = $serviceFallbacks[$slug] ?? 'process.png';
        
        if (!empty($srv['image_path'])) {
            $imgSrc = media_url($srv['image_path'], img($fallbackFile));
        } else {
            $imgSrc = img($fallbackFile);
        }
      ?>
      <div class="svc-stack-card" id="<?= e($slug) ?>">
        <div class="industry-work-card industry-work-card--dark">
          
          <!-- Left Column -->
          <div class="industry-work-card__content">
            <div class="industry-work-badge">
              <i class="<?= e($srv['icon'] ?: 'ri-code-box-line') ?>"></i> Enterprise Grade &bull; <?= e($srv['tag'] ?: 'Development Service') ?>
            </div>
            
            <h3 class="industry-work-title" style="font-size: 1.85rem; margin-bottom: 0.9rem;">
              <?= e($srv['title']) ?>
            </h3>
            
            <p class="industry-work-desc" style="font-size: 1rem; line-height: 1.7; margin-bottom: 1.25rem;">
              <?= e($srv['description']) ?>
            </p>

            <?php if (!empty($bullets)): ?>
            <div style="margin-bottom: 1.35rem;">
              <div style="font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; color: var(--color-teal-light); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.6rem;">
                Core Capabilities &amp; Stack:
              </div>
              <ul class="svc-capabilities-grid">
                <?php foreach (array_slice($bullets, 0, 6) as $b): ?>
                <li style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.875rem;">
                  <i class="ri-check-double-line" style="color: var(--color-teal-light); font-size: 15px; margin-top: 2px; flex-shrink: 0;"></i>
                  <span><?= e($b) ?></span>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <!-- Metrics Strip -->
            <div class="industry-work-metrics" style="margin-bottom: 1.5rem;">
              <div class="industry-metric-item">
                <div class="industry-metric-val"><?= e($srv['metrics_val'] ?: '99.9%') ?></div>
                <div class="industry-metric-lbl"><?= e($srv['metrics_lbl'] ?: 'Uptime SLA') ?></div>
              </div>
              <div class="industry-metric-item">
                <div class="industry-metric-val">100%</div>
                <div class="industry-metric-lbl">Custom Engineered</div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="industry-work-cta-wrap">
              <a href="<?= url('service/' . urlencode($srv['slug'])) ?>" class="btn btn-primary">
                <span>Explore Technical Scope</span> <i class="ri-arrow-right-line"></i>
              </a>
              <a href="<?= url('contact?service=' . urlencode($srv['title'])) ?>" class="btn btn-ghost">
                <span>Request Consultation</span> <i class="ri-macbook-line"></i>
              </a>
            </div>
          </div>

          <!-- Right Column -->
          <div class="industry-work-card__media">
            <div class="industry-media-frame">
              <span class="industry-media-tag"><i class="ri-terminal-window-fill"></i> Production Ready</span>
              <img src="<?= $imgSrc ?>" alt="<?= e($srv['title']) ?> Architecture" loading="lazy">
            </div>
          </div>

        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>
<?php endif; ?>


<!-- ═══════════════════════════════════════════
     05 — COMMODITY vs. WORDORA EDITORIAL (Comparison Table — Standard Typography)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas);">
  <div class="container">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-8);">
      <span class="label-upper"><?= e(setting('services_sec5_badge', 'THE EDITORIAL ADVANTAGE')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('services_sec5_title', 'Commodity Content vs. Wordora Editorial')) ?></h2>
      <p class="body-lg">
        <?= e(setting('services_sec5_desc', 'Why discerning market leaders partner with Wordora instead of generic freelance platforms or automated AI tools.')) ?>
      </p>
    </div>

    <?php 
    $tableJson = setting('services_sec5_table', '');
    $tableRows = !empty($tableJson) ? json_decode($tableJson, true) : [];
    if (empty($tableRows)) {
        $tableRows = [
            ['pillar' => 'Research Depth', 'commodity' => 'Surface-level summaries regurgitated from search snippets.', 'wordora' => 'Primary data sourcing, expert quotes & academic synthesis.'],
            ['pillar' => 'Search Engine Intent', 'commodity' => 'Keyword stuffing that gets flagged or demoted by Google.', 'wordora' => 'Topic cluster architecture & high-intent conversion paths.'],
            ['pillar' => 'Voice & Nuance', 'commodity' => 'Repetitive, robotic cadence with zero brand personality.', 'wordora' => 'Bespoke tone governance matching your unique market stature.'],
            ['pillar' => 'Turnaround Governance', 'commodity' => 'Unpredictable deadlines, ghosting, and endless rework.', 'wordora' => 'Strict sprint schedules with dedicated managing editors.'],
            ['pillar' => 'Commercial ROI', 'commodity' => 'Zero reader trust, high bounce rates, wasted budget.', 'wordora' => 'High organic rankings, pipeline velocity & qualified inbound leads.']
        ];
    }
    ?>

    <div class="reveal-up" style="background: var(--color-white); border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: var(--radius-xl); overflow: hidden;">
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9375rem;">
          <thead>
            <tr style="background: var(--color-navy); color: var(--color-white);">
              <th style="padding: 18px 24px; font-family: var(--font-body); font-weight: 700; font-size: 0.875rem; letter-spacing: 0.05em; text-transform: uppercase;">Evaluation Pillar</th>
              <th style="padding: 18px 24px; font-family: var(--font-body); font-weight: 700; font-size: 0.875rem; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(255,255,255,0.65);">Commodity / AI Content</th>
              <th style="padding: 18px 24px; font-family: var(--font-body); font-weight: 700; font-size: 0.875rem; letter-spacing: 0.05em; text-transform: uppercase; color: var(--color-teal-light);">WORDORA Editorial</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tableRows as $r): ?>
            <tr style="border-bottom: 1px dashed var(--color-border);">
              <td style="padding: 18px 24px; font-weight: 700; color: var(--color-navy);"><?= e($r['pillar']) ?></td>
              <td style="padding: 18px 24px; color: var(--color-muted);"><?= e($r['commodity']) ?></td>
              <td style="padding: 18px 24px; font-weight: 600; color: var(--color-teal-ink); background: rgba(74, 139, 140, 0.04);"><?= e($r['wordora']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     06 — ENGAGEMENT MODELS & SCOPE TIERS
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-white);">
  <div class="container">
    <div class="reveal-up text-center" style="max-width: 680px; margin: 0 auto var(--space-8);">
      <span class="label-upper"><?= e(setting('services_sec6_badge', 'ENGAGEMENT MODELS')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('services_sec6_title', 'Flexible Editorial Scopes')) ?></h2>
      <p class="body-lg">
        <?= e(setting('services_sec6_desc', 'Whether you require a one-time brand manifesto or a high-velocity monthly content engine, we structure transparent, predictable engagements.')) ?>
      </p>
    </div>

    <?php 
    $tiersJson = setting('services_sec6_tiers', '');
    $tiers = !empty($tiersJson) ? json_decode($tiersJson, true) : [];
    if (empty($tiers)) {
        $tiers = [
            [
                'badge' => 'Sprint Model',
                'title' => 'Topic Cluster Engine',
                'desc' => 'For growth-stage SaaS and B2B firms scaling organic search footprint and outranking incumbents.',
                'bullets' => "4 In-Depth Long-Form Pillars (2,000+ words)\nSemantic Keyword & Topic Map\nMeta Descriptions & Schema Schematics\n2 Full Rounds of Editorial Revisions",
                'btn_text' => 'Request Cluster Scope',
                'btn_url' => 'contact.php?plan=topic-cluster',
                'is_featured' => 0
            ],
            [
                'badge' => 'Most Popular',
                'title' => 'Brand Voice & Launch',
                'desc' => 'Complete messaging architecture, website copy decks, and brand manifesto for new product launches.',
                'bullets' => "Full Homepage & Core Service Copy\nBrand Manifesto & Tagline Matrix\nExecutive Pitch Deck Narrative\nComprehensive Tone & Style Guide",
                'btn_text' => 'Request Launch Scope',
                'btn_url' => 'contact.php?plan=brand-voice',
                'is_featured' => 1
            ],
            [
                'badge' => 'Executive Retainer',
                'title' => 'C-Suite Thought Leadership',
                'desc' => 'Ghostwriting for founders, managing partners, and venture leaders to build undeniable market authority.',
                'bullets' => "Weekly Strategic LinkedIn Essays\nMonthly Industry Whitepaper or Digest\nMulti-Slide Figma Carousel Graphics\nGhostwritten Op-Eds & Guest Posts",
                'btn_text' => 'Request Retainer Scope',
                'btn_url' => 'contact.php?plan=executive-thought-leadership',
                'is_featured' => 0
            ]
        ];
    }
    ?>

    <div class="reveal-up" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px;">
      <?php foreach ($tiers as $t): 
        $isFeatured = !empty($t['is_featured']);
        $tierBullets = !empty($t['bullets']) ? array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $t['bullets']))) : [];
      ?>
      <div style="background: <?= $isFeatured ? 'var(--color-navy)' : 'var(--color-canvas)' ?>; color: <?= $isFeatured ? 'var(--color-white)' : 'inherit' ?>; border: 1.5px dashed <?= $isFeatured ? 'var(--color-teal-light)' : 'rgba(74, 139, 140, 0.45)' ?>; border-radius: var(--radius-xl); padding: 32px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-4px)';" onmouseout="this.style.transform='translateY(0)';">
        <div>
          <?php if ($isFeatured): ?>
            <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-light); border: 1px dashed var(--color-teal-light); margin-bottom: 16px;"><?= e($t['badge']) ?></span>
          <?php else: ?>
            <span class="badge" style="background: var(--color-white); border: 1px dashed var(--color-teal-ink); color: var(--color-teal-ink); margin-bottom: 16px;"><?= e($t['badge']) ?></span>
          <?php endif; ?>

          <h3 style="font-family: var(--font-display); font-size: 1.5rem; color: <?= $isFeatured ? 'var(--color-white)' : 'var(--color-navy)' ?>; margin-bottom: 8px;"><?= e($t['title']) ?></h3>
          <p style="font-size: 0.9375rem; color: <?= $isFeatured ? 'rgba(255, 255, 255, 0.8)' : 'var(--color-text-muted)' ?>; line-height: 1.6; margin-bottom: 20px;">
            <?= e($t['desc']) ?>
          </p>

          <?php if (!empty($tierBullets)): ?>
          <ul style="list-style: none; padding: 0; margin: 0 0 24px; font-size: 0.875rem; color: <?= $isFeatured ? 'var(--color-white)' : 'var(--color-navy)' ?>; display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($tierBullets as $tb): ?>
            <li style="display: flex; gap: 8px;"><i class="ri-check-line" style="color: <?= $isFeatured ? 'var(--color-teal-light)' : 'var(--color-teal-ink)' ?>;"></i> <?= e($tb) ?></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>

        <a href="<?= url($t['btn_url'] ?? 'contact.php') ?>" class="<?= $isFeatured ? 'btn btn-primary' : 'btn btn-outline' ?>" style="width: 100%; text-align: center;">
          <span><?= e($t['btn_text'] ?? 'Request Scope') ?></span> <i class="ri-arrow-right-line"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     07 — FAQ SECTION (WITH TILTED #FAF8F5 FAQ.PNG ARTWORK)
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas);">
  <div class="container" style="max-width: 1280px;">
    <div class="reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-8);">
      <span class="label-upper"><?= e(setting('services_sec7_badge', 'FREQUENTLY ASKED QUESTIONS')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);"><?= e(setting('services_sec7_title', 'Everything You Need to Know')) ?></h2>
      <p class="body-lg"><?= e(setting('services_sec7_desc', 'Clear answers on how we scope, draft, refine, and deliver high-impact content.')) ?></p>
    </div>

    <?php 
    $faqsJson = setting('services_sec7_faqs', '');
    $faqs = !empty($faqsJson) ? json_decode($faqsJson, true) : [];
    if (empty($faqs)) {
        $faqs = [
            ['q' => 'How do you ensure writers understand our complex domain?', 'a' => 'Every project is assigned to a senior writer with relevant background in your domain (e.g. computer science, finance, biomedicine, B2B SaaS). We conduct a comprehensive discovery interview and review your technical docs before writing.'],
            ['q' => 'What is your policy on AI-generated content?', 'a' => '100% human-crafted and fact-checked. We use technology only for semantic keyword clustering and grammar audits. Every sentence, thesis, and argument is constructed by experienced human journalists and editors.'],
            ['q' => 'How many revisions are included in a project scope?', 'a' => 'All scopes include two complete rounds of revisions within 14 days of delivery. Because we align on detailed outlines beforehand, most deliverables are approved on the first review.'],
            ['q' => 'Do you sign Non-Disclosure Agreements (NDAs)?', 'a' => 'Yes, unconditionally. We protect all proprietary data, pre-release roadmaps, and ghostwriting arrangements under strict mutual NDAs.'],
            ['q' => 'What is the typical turnaround timeline?', 'a' => 'Standard blog articles are delivered in 5 to 7 business days. Deep-technical whitepapers and full brand messaging bibles typically require 10 to 14 business days.']
        ];
    }
    ?>

    <div class="svc-faq-split reveal-up">
      <!-- FAQ Accordions (Left Side) -->
      <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($faqs as $f): ?>
        <details class="svc-faq">
          <summary>
            <span><?= e($f['q']) ?></span>
            <i class="ri-arrow-down-s-line"></i>
          </summary>
          <div class="svc-faq__body">
            <?= e($f['a']) ?>
          </div>
        </details>
        <?php endforeach; ?>
      </div>

      <!-- FAQ Artwork Illustration Frame (Right Side — Tilted #FAF8F5 Frame) -->
      <div class="svc-faq-artwork">
        <div class="svc-faq-artwork-frame">
          <img src="<?= media_url(setting('services_sec7_artwork', '/img/FAQ 2.png'), img('FAQ 2.png')) ?>" alt="Frequently Asked Questions Illustration" loading="lazy">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     08 — START A CONVERSATION CTA (Matching Homepage Section 09)
     ═══════════════════════════════════════════ -->
<section class="section" style="padding-top: var(--space-12); padding-bottom: var(--space-20); background: var(--color-canvas);">
  <div class="container">
    <div class="cta-signature reveal-up">
      <div class="cta-signature__content">
        <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3); border: 1px solid rgba(212, 234, 234, 0.25);">
          <i class="ri-sparkling-fill"></i> <?= e(setting('services_sec8_badge', 'READY TO ELEVATE YOUR WORDS?')) ?>
        </span>
        
        <h2 class="cta-signature__title"><?= e(setting('services_sec8_title', 'Let\'s build content worth reading.')) ?></h2>
        
        <p class="cta-signature__text">
          <?= e(setting('services_sec8_desc', 'Tell us about your brand, your goals, and what you need written. We\'ll deliver a tailored proposal within 24 hours.')) ?>
        </p>

        <div class="cta-signature__actions">
          <a href="<?= url(setting('services_sec8_btn1_url', 'contact.php')) ?>" class="btn btn-primary btn-lg">
            <?= e(setting('services_sec8_btn1_text', 'Start a Conversation')) ?> <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= url(setting('services_sec8_btn2_url', 'who-we-are.php')) ?>" class="btn btn-ghost btn-lg">
            <?= e(setting('services_sec8_btn2_text', 'Our Editorial Story')) ?> <i class="ri-compass-3-line"></i>
          </a>
        </div>

        <?php 
        $pillsJson = setting('services_sec8_pills', '');
        $pills = !empty($pillsJson) ? json_decode($pillsJson, true) : [];
        if (empty($pills)) {
            $pills = [
                ['icon' => 'ri-checkbox-circle-fill', 'text' => '24h Response'],
                ['icon' => 'ri-shield-check-fill', 'text' => 'NDA Protected'],
                ['icon' => 'ri-file-list-3-fill', 'text' => 'Free Content Audit']
            ];
        }
        ?>
        <div class="cta-trust-pills">
          <?php foreach ($pills as $p): ?>
          <span class="cta-trust-pill"><i class="<?= e($p['icon'] ?? 'ri-checkbox-circle-fill') ?>"></i> <?= e($p['text'] ?? '') ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= media_url(setting('services_sec8_artwork', '/img/cta 1.png'), img('cta 1.png')) ?>" alt="Start a Conversation with WORDORA" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>

