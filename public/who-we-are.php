<?php
/**
 * WORDORA — Who We Are Page (Master 8-Section Specification)
 * Location: Agra, Uttar Pradesh, India
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';

$meta = [
    'title' => 'Who We Are — WORDORA | Words That Work. Stories That Sell.',
    'description' => 'A team of writers, strategists, and editors based in Agra, India driven by the belief that great words build great brands.',
];

// Fetch marquee background from settings or default
$marqueeBg = setting('marquee_bg_image', '/img/papaer banner.png');
$marqueeBgUrl = !empty($marqueeBg) ? media_url($marqueeBg) : '';

ob_start();
?>

<!-- ==========================================================================
     PAGE-SPECIFIC STYLES: Who We Are Master Editorial
     ========================================================================== -->
<style>
/* --------------------------------------------------------------------------
   01. Hero Cover Banner (Matching Homepage Height & Layout)
   -------------------------------------------------------------------------- */
.hero--who-we-are {
  min-height: 640px;
  position: relative;
  display: flex;
  align-items: center;
  overflow: hidden;
  background-color: var(--color-navy-deep);
  padding: 130px 0 90px;
}

.hero--who-we-are .hero__overlay-radial {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 20% 50%, rgba(74, 139, 140, 0.22) 0%, transparent 65%);
  z-index: 1;
  pointer-events: none;
}

/* --------------------------------------------------------------------------
   02. Journey Section (Tilted Editorial Timeline Cards)
   -------------------------------------------------------------------------- */
.journey-illustration-col {
  position: sticky;
  top: 120px;
}

@media (max-width: 991px) {
  .journey-illustration-col {
    position: static !important;
    top: auto !important;
    margin-bottom: 2rem;
  }
}

.journey-timeline-wrap {
  position: relative;
  padding-left: 2.75rem;
}

.journey-timeline-wrap::before {
  content: '';
  position: absolute;
  left: 9px;
  top: 14px;
  bottom: 24px;
  width: 2px;
  background: linear-gradient(to bottom, var(--color-teal-ink) 0%, var(--color-teal-pale) 75%, transparent 100%);
}

.journey-timeline-item {
  position: relative;
  margin-bottom: 2.25rem;
}

.journey-timeline-item:last-child {
  margin-bottom: 0;
}

.journey-timeline-dot {
  position: absolute;
  left: -2.75rem;
  top: 14px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--color-teal-ink);
  border: 3px solid var(--color-white);
  box-shadow: var(--shadow-glow);
  z-index: 2;
  transform: translateX(2px);
}

.journey-timeline-year {
  font-family: var(--font-mono);
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--color-teal-ink);
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 0.4rem;
  display: block;
}

.journey-card {
  background: var(--color-white);
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: var(--radius-lg);
  padding: 1.5rem 1.75rem;
  box-shadow: none !important;
  transform-origin: right center;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s ease;
}

.journey-timeline-item:nth-child(1) .journey-card { transform: rotate(-0.85deg); }
.journey-timeline-item:nth-child(2) .journey-card { transform: rotate(0.95deg); }
.journey-timeline-item:nth-child(3) .journey-card { transform: rotate(-0.75deg); }
.journey-timeline-item:nth-child(4) .journey-card { transform: rotate(0.85deg); }
.journey-timeline-item:nth-child(5) .journey-card { transform: rotate(0deg); }

.journey-timeline-item:hover .journey-card {
  transform: translateX(-8px) rotate(0deg) scale(1.02);
  border-color: var(--color-teal-ink);
  box-shadow: none !important;
}

.journey-card__title {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.1875rem;
  color: var(--color-navy);
  margin-bottom: 0.5rem;
}

.journey-card__desc {
  font-family: var(--font-body);
  font-size: 0.9375rem;
  color: var(--color-text-muted);
  line-height: 1.7;
  margin: 0;
}

.journey-card--active {
  background: var(--color-navy);
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  box-shadow: none !important;
}

.journey-card--active .journey-timeline-year {
  color: var(--color-teal-light);
}

.journey-card--active .journey-card__title {
  color: var(--color-white);
}

.journey-card--active .journey-card__desc {
  color: rgba(255, 255, 255, 0.8);
}

.journey-timeline-item--active .journey-timeline-dot {
  background: var(--color-teal-light);
  box-shadow: 0 0 16px var(--color-teal-light);
}

/* --------------------------------------------------------------------------
   03. Core Editorial Values (DARK Luxury Container & #FAF8F5 Tilted Card)
   -------------------------------------------------------------------------- */
.values-dark-section {
  background-color: var(--color-navy-deep);
  position: relative;
  overflow: hidden;
  padding: var(--space-20) 0;
  color: var(--color-white);
  border-top: 1.5px dashed rgba(74, 139, 140, 0.3);
  border-bottom: 1.5px dashed rgba(74, 139, 140, 0.3);
}

.values-dark-section .values-radial-glow {
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 18% 45%, rgba(74, 139, 140, 0.18) 0%, transparent 60%);
  pointer-events: none;
}

.values-canvas-grid {
  display: grid;
  grid-template-columns: 1fr 1.25fr;
  gap: var(--space-12);
  align-items: center;
  position: relative;
  z-index: 2;
}

@media (max-width: 991px) {
  .values-canvas-grid {
    grid-template-columns: 1fr;
    gap: var(--space-8);
  }
}

.values-artwork-card {
  background: #FAF8F5;
  border: 1.5px dashed rgba(74, 139, 140, 0.4);
  border-radius: 28px;
  padding: 28px;
  display: inline-block;
  width: 100%;
  max-width: 440px;
  box-shadow: none !important;
  transform: rotate(-2deg);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
  text-align: center;
}

.values-artwork-card:hover {
  transform: rotate(0deg) scale(1.025);
  border-color: var(--color-teal-ink);
}

.values-cards-stack {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.value-dark-card {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1.5px dashed rgba(212, 234, 234, 0.25);
  border-radius: var(--radius-lg);
  padding: 1.5rem 1.75rem;
  box-shadow: none !important;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.value-dark-card:hover {
  background: rgba(255, 255, 255, 0.09);
  border-color: var(--color-teal-light);
  transform: translateX(4px);
}

.value-dark-card__header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
}

.value-dark-card__icon {
  width: 42px;
  height: 42px;
  border-radius: var(--radius-md);
  background: rgba(74, 139, 140, 0.22);
  color: var(--color-teal-light);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
  border: 1px dashed rgba(74, 139, 140, 0.35);
}

.value-dark-card__num {
  font-family: var(--font-mono);
  font-size: 0.75rem;
  color: var(--color-teal-light);
  letter-spacing: 0.08em;
  font-weight: 700;
  text-transform: uppercase;
}

.value-dark-card__title {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.35rem;
  color: var(--color-white);
  margin: 0 0 6px 0;
  line-height: 1.3;
}

.value-dark-card__desc {
  font-family: var(--font-body);
  font-size: 0.9375rem;
  color: rgba(255, 255, 255, 0.82);
  line-height: 1.65;
  margin: 0;
}

/* --------------------------------------------------------------------------
   04. Meet The Team Section
   -------------------------------------------------------------------------- */
.team-grid-editorial {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--space-6);
  margin-top: var(--space-10);
}

.team-card-v2 {
  background: var(--color-white);
  border: 1.5px dashed rgba(74, 139, 140, 0.35);
  border-radius: var(--radius-lg);
  padding: 2rem 1.5rem;
  text-align: center;
  box-shadow: none !important;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.team-card-v2:hover {
  transform: translateY(-6px);
  border-color: var(--color-teal-ink);
}

.team-card-v2__avatar {
  width: 104px;
  height: 104px;
  border-radius: 50%;
  margin: 0 auto 1.35rem auto;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-size: 2rem;
  font-weight: 700;
  box-shadow: 0 10px 24px rgba(15, 30, 54, 0.08);
  border: 3px solid #FFFFFF;
  outline: 1.5px solid rgba(74, 139, 140, 0.35);
  overflow: hidden;
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
}

.team-card-v2:hover .team-card-v2__avatar {
  transform: scale(1.06);
  box-shadow: 0 14px 32px rgba(74, 139, 140, 0.2);
  outline-color: var(--color-teal-ink);
}

.team-card-v2__name {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.125rem;
  color: var(--color-navy);
  margin-bottom: 0.25rem;
}

.team-card-v2__role {
  font-family: var(--font-ui);
  font-weight: 600;
  font-size: 0.8125rem;
  color: var(--color-teal-ink);
  margin-bottom: 0.75rem;
}

.team-card-v2__spec {
  font-family: var(--font-body);
  font-size: 0.8125rem;
  color: var(--color-text-muted);
  line-height: 1.5;
  margin-bottom: 1rem;
}

.team-card-v2__divider {
  width: 100%;
  height: 1px;
  background: var(--color-border);
  margin: auto 0 1rem;
}

.team-card-v2__linkedin {
  color: var(--color-text-muted);
  font-size: 1.2rem;
  transition: color 0.2s ease, transform 0.2s ease;
  display: inline-flex;
}

.team-card-v2__linkedin:hover {
  color: var(--color-teal-ink);
  transform: scale(1.15);
}

/* --------------------------------------------------------------------------
   05. Responsive Breakpoints
   -------------------------------------------------------------------------- */
@media (max-width: 1023px) {
  .hero--who-we-are {
    min-height: 520px;
    padding: 110px 0 70px;
  }
  .values-canvas-grid {
    grid-template-columns: 1fr;
    gap: var(--space-8);
  }
  .team-grid-editorial {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 767px) {
  .hero--who-we-are {
    min-height: 480px;
    padding: 90px 0 60px;
  }
  .team-grid-editorial {
    grid-template-columns: 1fr;
  }
}
</style>

<?php
// Fetch Who We Are dynamic settings arrays
$whoMilestones = json_decode(setting('who_sec4_milestones', '[]'), true) ?: [];
$whoValCards = json_decode(setting('who_sec5_cards', '[]'), true) ?: [];
$whoTeam = json_decode(setting('who_sec6_team', '[]'), true) ?: [];
$whoPillars = json_decode(setting('who_sec7_pillars', '[]'), true) ?: [];
$whoStats = json_decode(setting('who_sec7_stats', '[]'), true) ?: [];
$whoMarqueeRows = json_decode(setting('who_sec3_rows', '[]'), true) ?: [];
$whoMarqueeBg = setting('who_sec3_marquee_bg', '/img/papaer banner.png');
$whoMarqueeBgUrl = !empty($whoMarqueeBg) ? media_url($whoMarqueeBg) : '';
?>

<!-- ═══════════════════════════════════════════
     01 — HERO: THE EDITORIAL COVER (MULTI-MODE: SLIDER / SINGLE / VIDEO)
     ═══════════════════════════════════════════ -->
<?php 
$heroPage = 'who_we_are';
include ROOT_PATH . '/views/partials/hero-banner.php'; 
?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — OUR MISSION (TOP-NOTCH EDITORIAL SPLIT)
     ═══════════════════════════════════════════ -->
<section class="section" id="our-mission" style="background: var(--color-canvas);">
  <div class="container">
    <div class="why-split">
      <div class="reveal-up">
        <span class="label-upper"><?= e(setting('who_sec2_badge', 'OUR MISSION')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-4);">
          <?= setting('who_sec2_title', "We believe words<br>shape worlds.") ?>
        </h2>
        
        <p class="body-lg" style="margin-bottom: var(--space-4);">
          <?= nl2br(e(setting('who_sec2_p1', "WORDORA was founded on a simple truth: the right words, at the right moment, can transform a brand. We don't just create content — we craft narratives that connect, captivate, persuade, and leave a lasting impression."))) ?>
        </p>

        <!-- Magazine Pull Quote -->
        <blockquote class="editorial-quote">
          <?= e(setting('who_sec2_quote', "“Good content fills a page. Great content moves someone.”")) ?>
        </blockquote>

        <p class="body-base" style="color: var(--color-text-muted); margin-bottom: var(--space-6);">
          <?= nl2br(e(setting('who_sec2_p2', "From our base in Agra to brands across India and beyond, we've evolved from a two-person editorial team into a full-service content agency trusted by 170+ brands across SaaS, E-commerce, FinTech, Education, Gaming, and more."))) ?>
        </p>

        <div style="display: flex; gap: var(--space-4); align-items: center; flex-wrap: wrap;">
          <a href="<?= e(setting('who_sec2_btn1_url', '#journey')) ?>" class="btn btn-outline">
            <?= e(setting('who_sec2_btn1_text', 'Read Our Journey')) ?> <i class="ri-arrow-down-line"></i>
          </a>
          <a href="<?= e(setting('who_sec2_btn2_url', 'services.php')) ?>" class="btn btn-navy">
            <?= e(setting('who_sec2_btn2_text', 'Explore Services')) ?> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

      <div class="reveal-up why-illustration-wrap">
        <div class="why-illustration-backdrop"></div>
        <img src="<?= media_url(setting('who_sec2_artwork', '/img/why choose us.png')) ?>" alt="WORDORA Editorial Philosophy and Content Strategy" loading="lazy">
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     03 — FULL-WIDTH 3-ROW CAPABILITIES MARQUEE
     ═══════════════════════════════════════════ -->
<section class="marquee-banner-section" aria-label="Creative Capabilities Marquee" style="<?= !empty($whoMarqueeBgUrl) ? "background-image: url('{$whoMarqueeBgUrl}');" : '' ?>">
  <div class="marquee-banner-header reveal-up">
    <span class="label-upper"><?= e(setting('who_sec3_badge', 'EDITORIAL CAPABILITIES')) ?></span>
    <h3 class="heading-lg" style="margin-top: var(--space-2); margin-bottom: 0;">
      <?= e(setting('who_sec3_title', 'Content engineered for ambitious market leaders.')) ?>
    </h3>
  </div>

  <div class="marquee-parallax-stream">
    <?php
    $row1Pills = !empty($whoMarqueeRows['row1']) ? array_map('trim', explode(',', $whoMarqueeRows['row1'])) : ['SEO Content Writing', 'Brand Voice Architecture', 'Thought Leadership Essays', 'Social Editorial Calendars', 'Email Sequences & Newsletters', 'Technical Whitepapers', 'Full-Funnel Content Strategy'];
    $row2Pills = !empty($whoMarqueeRows['row2']) ? array_map('trim', explode(',', $whoMarqueeRows['row2'])) : ['Conversion Copywriting', 'Case Study Narratives', 'Topic Cluster Frameworks', 'Enterprise B2B Whitepapers', 'Fact-Checked Research', 'Executive Ghostwriting', 'Content Audits & Roadmaps'];
    $row3Pills = !empty($whoMarqueeRows['row3']) ? array_map('trim', explode(',', $whoMarqueeRows['row3'])) : ['Keyword Intent Mapping', 'Long-Form Authority Guides', 'High-Converting Pitch Decks', 'Onboarding Email Sequences', 'Industry Authority Benchmarks', 'Viral LinkedIn Carousels', 'Multi-Format Repurposing'];
    ?>
    <!-- Row 1: Left to Right -->
    <div class="marquee-parallax-row ltr">
      <div class="marquee-parallax-track">
        <?php foreach ($row1Pills as $p): ?>
          <span class="glass-pill"><i class="ri-sparkling-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($row1Pills as $p): ?>
          <span class="glass-pill"><i class="ri-sparkling-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Row 2: Right to Left -->
    <div class="marquee-parallax-row rtl">
      <div class="marquee-parallax-track">
        <?php foreach ($row2Pills as $p): ?>
          <span class="glass-pill glass-pill--navy"><i class="ri-book-open-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($row2Pills as $p): ?>
          <span class="glass-pill glass-pill--navy"><i class="ri-book-open-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Row 3: Left to Right (Parallax Depth Layer) -->
    <div class="marquee-parallax-row ltr-fast">
      <div class="marquee-parallax-track">
        <?php foreach ($row3Pills as $p): ?>
          <span class="glass-pill glass-pill--accent"><i class="ri-focus-2-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="marquee-parallax-track" aria-hidden="true">
        <?php foreach ($row3Pills as $p): ?>
          <span class="glass-pill glass-pill--accent"><i class="ri-focus-2-line"></i> <?= e($p) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════
     04 — OUR JOURNEY (TILTED TIMELINE CARDS)
     ═══════════════════════════════════════════ -->
<section class="section" id="journey" style="background: var(--color-white);">
  <div class="container">
    <div class="reveal-up" style="margin-bottom: var(--space-12);">
      <span class="label-upper"><?= e(setting('who_sec4_badge', 'OUR JOURNEY')) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3);">
        <?= setting('who_sec4_title', "Words got us started.<br>Ideas took us further.") ?>
      </h2>
      <p class="body-lg" style="max-width: 720px;">
        <?= nl2br(e(setting('who_sec4_desc', "What began as a small writing studio in Agra slowly became a place where brands come to find their voice, sharpen their story, and say something worth remembering."))) ?>
      </p>
    </div>

    <div class="why-split" style="align-items: start;">
      <!-- Left Illustration (Sticky on desktop, clean static block on mobile) -->
      <div class="journey-illustration-col reveal-up">
        <div style="background: #edf7f7; border-radius: 28px; padding: 24px; display: inline-block; width: 100%; box-shadow: 0 10px 30px rgba(74, 139, 140, 0.08);">
          <img src="<?= media_url(setting('who_sec4_artwork', '/img/journey.png')) ?>" alt="WORDORA Journey Architecture" loading="lazy" style="width: 100%; display: block; mix-blend-mode: multiply; border: none; background: transparent; box-shadow: none;">
        </div>
      </div>

      <!-- Right Column: Milestone Timeline -->
      <div class="journey-timeline-wrap">
        <?php foreach ($whoMilestones as $idx => $m): 
            $isActive = !empty($m['is_active']);
        ?>
        <div class="journey-timeline-item <?= $isActive ? 'journey-timeline-item--active' : '' ?> reveal-up">
          <div class="journey-timeline-dot" aria-hidden="true"></div>
          <div class="journey-card <?= $isActive ? 'journey-card--active' : '' ?>">
            <span class="journey-timeline-year"><?= e($m['tag'] ?? ($m['year'] . ' — MILESTONE')) ?></span>
            <h3 class="journey-card__title"><?= e($m['title'] ?? '') ?></h3>
            <p class="journey-card__desc">
              <?= nl2br(e($m['desc'] ?? '')) ?>
            </p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     05 — CORE EDITORIAL VALUES
     ═══════════════════════════════════════════ -->
<section class="values-dark-section" aria-label="Core Editorial Values">
  <div class="values-radial-glow"></div>
  <div class="container">
    <div class="values-canvas-grid">
      <!-- Left Column -->
      <div class="reveal-up">
        <span class="label-upper" style="color: var(--color-teal-light);"><?= e(setting('who_sec5_badge', 'OUR CORE VALUES')) ?></span>
        <h2 class="heading-xl" style="color: var(--color-white); margin-top: var(--space-2); margin-bottom: var(--space-4);">
          <?= e(setting('who_sec5_title', 'What Guides Every Word We Write.')) ?>
        </h2>
        <p class="body-lg" style="color: rgba(255, 255, 255, 0.8); margin-bottom: var(--space-8);">
          <?= nl2br(e(setting('who_sec5_desc', 'Three foundational editorial principles that shape how we think, write, and deliver impact for every partner brand.'))) ?>
        </p>
        <div class="values-artwork-card">
          <img src="<?= media_url(setting('who_sec5_artwork', '/img/value.png')) ?>" alt="WORDORA Core Values Artwork" loading="lazy" style="width: 100%; max-width: 380px; margin: 0 auto; display: block; mix-blend-mode: multiply; border: none; background: transparent; box-shadow: none;">
        </div>
      </div>

      <!-- Right Column: Value Cards Stack (Full-width typography) -->
      <div class="values-cards-stack reveal-up">
        <?php foreach ($whoValCards as $v): ?>
        <div class="value-dark-card">
          <div class="value-dark-card__header">
            <div class="value-dark-card__icon"><i class="<?= e($v['icon'] ?? 'ri-quill-pen-line') ?>"></i></div>
            <span class="value-dark-card__num"><?= e($v['num'] ?? '01 / DISCIPLINE') ?></span>
          </div>
          <h3 class="value-dark-card__title"><?= e($v['title'] ?? '') ?></h3>
          <p class="value-dark-card__desc">
            <?= nl2br(e($v['desc'] ?? '')) ?>
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
     06 — MEET THE TEAM
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-white);">
  <div class="container">
    <div class="reveal-up" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; margin-bottom: var(--space-6); gap: var(--space-4);">
      <div>
        <span class="label-upper"><?= e(setting('who_sec6_badge', 'MEET THE TEAM')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: 0;">
          <?= e(setting('who_sec6_title', 'The People Behind the Words')) ?>
        </h2>
      </div>
      <p class="body-base" style="max-width: 480px; color: var(--color-text-muted); margin-bottom: 0;">
        <?= nl2br(e(setting('who_sec6_desc', 'Writers. Strategists. Editors. Each one obsessed with research, editorial rhythm, and doing the work right.'))) ?>
      </p>
    </div>

    <!-- Team Grid -->
    <div class="team-grid-editorial reveal-up">
      <?php foreach ($whoTeam as $t): ?>
      <div class="team-card-v2">
        <div class="team-card-v2__avatar" style="background: <?= e($t['avatar_bg'] ?? '#E8F4F4') ?>; color: <?= e($t['avatar_color'] ?? 'var(--color-teal-ink)') ?>;">
          <?php if (!empty($t['image']) || !empty($t['avatar_img'])): ?>
            <img src="<?= media_url($t['image'] ?? $t['avatar_img']) ?>" alt="<?= e($t['name'] ?? 'Team Member') ?>" style="width: 100%; height: 100%; object-fit: cover; display: block; border-radius: 50%;">
          <?php else: ?>
            <?= e($t['avatar_initials'] ?? 'ED') ?>
          <?php endif; ?>
        </div>
        <h3 class="team-card-v2__name"><?= e($t['name'] ?? '') ?></h3>
        <div class="team-card-v2__role"><?= e($t['role'] ?? '') ?></div>
        <p class="team-card-v2__spec"><?= e($t['spec'] ?? '') ?></p>
        <div class="team-card-v2__divider"></div>
        <a href="<?= e($t['linkedin'] ?? 'https://linkedin.com') ?>" target="_blank" rel="noopener noreferrer" class="team-card-v2__linkedin" aria-label="<?= e($t['name'] ?? 'Team Member') ?> LinkedIn Profile">
          <i class="ri-linkedin-box-fill"></i>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     07 — WHY BRANDS CHOOSE WORDORA
     ═══════════════════════════════════════════ -->
<section class="section why-section" id="why-choose-us" style="background: var(--color-canvas);">
  <div class="container">
    <div class="why-split">
      <div class="reveal-up">
        <span class="label-upper"><?= e(setting('who_sec7_badge', 'WHY BRANDS CHOOSE WORDORA')) ?></span>
        <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-4);">
          <?= setting('who_sec7_title', "Not just writers.<br>Content thinkers & growth partners.") ?>
        </h2>
        
        <p class="body-lg" style="margin-bottom: var(--space-4);">
          <?= nl2br(e(setting('who_sec7_desc', "We research before we write. We understand before we create. We build every piece around a measurable purpose — establishing industry authority, winning search intent, and converting qualified customers into long-term revenue."))) ?>
        </p>

        <div style="margin-top: var(--space-6);">
          <a href="<?= e(setting('who_sec7_btn_url', 'contact.php')) ?>" class="btn btn-outline">
            <?= e(setting('who_sec7_btn_text', 'Partner With Us')) ?> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

      <div class="reveal-up why-illustration-wrap">
        <div class="why-illustration-backdrop"></div>
        <img src="<?= media_url(setting('who_sec7_artwork', '/img/culture notes.png')) ?>" alt="WORDORA Collaborative Team and Culture" loading="lazy">
      </div>
    </div>

    <!-- 6-Pillar Feature Grid -->
    <div class="why-features-grid reveal-up">
      <?php foreach ($whoPillars as $p): ?>
      <div class="why-feature-card">
        <div class="why-feature-card__icon"><i class="<?= e($p['icon'] ?? 'ri-quill-pen-line') ?>"></i></div>
        <h3 class="why-feature-card__title"><?= e($p['title'] ?? '') ?></h3>
        <p class="why-feature-card__desc">
          <?= nl2br(e($p['desc'] ?? '')) ?>
        </p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Result Metrics Strip -->
    <div class="result-metrics-strip reveal-up">
      <?php foreach ($whoStats as $s): ?>
      <div class="result-metric-card">
        <div class="result-metric-card__num"><span class="stat-count" data-count="<?= (int)$s['count'] ?>"><?= (int)$s['count'] ?></span><?= e($s['suffix'] ?? '+') ?></div>
        <div class="result-metric-card__label"><?= e($s['label'] ?? '') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     08 — START A CONVERSATION: SIGNATURE CTA
     ═══════════════════════════════════════════ -->
<section class="section" style="padding-top: var(--space-8); padding-bottom: var(--space-20); background: var(--color-canvas);">
  <div class="container">
    <div class="cta-signature reveal-up">
      <div class="cta-signature__content">
        <span class="badge" style="background: rgba(74, 139, 140, 0.35); color: var(--color-teal-pale); margin-bottom: var(--space-3); border: 1px solid rgba(212, 234, 234, 0.25);">
          <i class="ri-sparkling-fill"></i> <?= e(setting('who_sec8_badge', "LET'S MAKE SOMETHING MEANINGFUL")) ?>
        </span>
        
        <h2 class="cta-signature__title"><?= setting('who_sec8_title', 'Start something <em>worth reading.</em>') ?></h2>
        
        <p class="cta-signature__text">
          <?= nl2br(e(setting('who_sec8_desc', "Tell us what you're building. We'll help you find the words to move it forward, engage the right audience, and drive sustainable pipeline growth."))) ?>
        </p>

        <div class="cta-signature__actions">
          <a href="<?= e(setting('who_sec8_btn1_url', 'contact.php')) ?>" class="btn btn-primary btn-lg">
            <?= e(setting('who_sec8_btn1_text', 'Start a Conversation')) ?> <i class="ri-arrow-right-line"></i>
          </a>
          <a href="<?= e(setting('who_sec8_btn2_url', 'services.php')) ?>" class="btn btn-ghost btn-lg">
            <?= e(setting('who_sec8_btn2_text', 'Explore Services')) ?> <i class="ri-compass-3-line"></i>
          </a>
        </div>

        <?php
        $pills = array_map('trim', explode(',', setting('who_sec8_trust_pills', '24h Response, NDA Protected, Free Content Audit')));
        ?>
        <div class="cta-trust-pills">
          <?php foreach ($pills as $pill): ?>
            <span class="cta-trust-pill"><i class="ri-checkbox-circle-fill"></i> <?= e($pill) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="cta-artwork-wrap">
        <img src="<?= media_url(setting('who_sec8_artwork', '/img/cta 1.png')) ?>" alt="Start a Conversation with WORDORA" loading="lazy">
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
?>

