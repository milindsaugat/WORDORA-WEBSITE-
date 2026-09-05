<?php
/**
 * WORDORA — Dynamic Multi-Mode Hero Banner (Full Background Artwork / Slider / Video)
 * 
 * Supports:
 * - slider: Multi-Slide Carousel with unique full background image + copy per slide.
 * - single: Single background image + copy.
 * - video: Background video loop.
 */

$heroPage = $heroPage ?? 'home';
$heroMode = 'slider';
try {
    $heroMode = Hero::getHeroMode($heroPage);
    $heroSlides = Hero::getActiveSlides($heroPage);
} catch (Exception $e) {
    $heroSlides = [];
}

// Global Directional Gradient (Left Side Text Contrast + Right Side Zero Opacity)
$heroGradient = get_hero_directional_gradient();
?>

<?php if ($heroMode === 'video'): ?>
<!-- ═══════════════════════════════════════════
     MODE: HTML5 BACKGROUND VIDEO HERO
     ═══════════════════════════════════════════ -->
<?php $vs = !empty($heroSlides) ? $heroSlides[0] : [
    'eyebrow' => 'EDITORIAL CONTENT & COPYWRITING STUDIO',
    'title' => 'Words That Work. Stories That Sell.',
    'subtitle' => 'We turn research, ideas and brand thinking into content people remember — and businesses can grow with.',
    'button_primary_text' => 'Explore Our Work',
    'button_primary_url' => 'services.php',
    'button_secondary_text' => 'Start a Conversation',
    'button_secondary_url' => 'contact.php',
]; ?>
  <?php
  $pageVideoKey = ($heroPage === 'home') ? 'hero_video_url' : ($heroPage === 'who_we_are' ? 'who_hero_video_url' : 'hero_video_url_' . $heroPage);
  $activeVideoSource = setting($pageVideoKey, '');
  if (empty($activeVideoSource) && !empty($vs['video_url'])) {
      $activeVideoSource = $vs['video_url'];
  }
  if (empty($activeVideoSource)) {
      $activeVideoSource = setting('hero_video_url', '');
  }
  ?>
<section class="hero hero--video" id="heroSection">
  <div class="hero__video-bg">
    <video autoplay muted loop playsinline poster="<?= !empty($vs['media_url']) ? media_url($vs['media_url']) : '' ?>">
      <source src="<?= media_url($activeVideoSource) ?>" type="video/mp4">
    </video>
    <div class="hero__video-overlay" style="background: <?= $heroGradient ?>;"></div>
  </div>

  <div class="container container-hero">
    <div class="hero__body-full">
      <?php if (!empty($vs['eyebrow'])): ?>
        <span class="label-upper hero__eyebrow animate-hero-text"><?= e($vs['eyebrow']) ?></span>
      <?php endif; ?>
      <h1 class="heading-hero animate-hero-text"><?= e($vs['title']) ?></h1>
      <?php if (!empty($vs['subtitle'])): ?>
        <p class="body-lg animate-hero-text"><?= e($vs['subtitle']) ?></p>
      <?php endif; ?>
      <div class="hero__actions animate-hero-text">
        <?php if (!empty($vs['button_primary_text'])): ?>
          <a href="<?= url($vs['button_primary_url'] ?: 'services.php') ?>" class="btn btn-primary btn-lg">
            <?= e($vs['button_primary_text']) ?> <i class="ri-arrow-right-line"></i>
          </a>
        <?php endif; ?>
        <?php if (!empty($vs['button_secondary_text'])): ?>
          <a href="<?= url($vs['button_secondary_url'] ?: 'contact.php') ?>" class="btn btn-ghost btn-lg">
            <?= e($vs['button_secondary_text']) ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<?php elseif ($heroMode === 'single' || count($heroSlides) <= 1): ?>
<!-- ═══════════════════════════════════════════
     MODE: SINGLE BACKGROUND IMAGE HERO
     ═══════════════════════════════════════════ -->
<?php 
$ss = !empty($heroSlides) ? $heroSlides[0] : [
    'eyebrow' => 'EDITORIAL CONTENT & COPYWRITING STUDIO',
    'title' => 'Words That Work. Stories That Sell.',
    'subtitle' => 'We turn research, ideas and brand thinking into content people remember — and businesses can grow with.',
    'media_url' => '/img/home section 2.png',
    'button_primary_text' => 'Explore Our Work',
    'button_primary_url' => 'services.php',
    'button_secondary_text' => 'Start a Conversation',
    'button_secondary_url' => 'contact.php',
];
$bgUrl = !empty($ss['media_url']) ? media_url($ss['media_url']) : img('home section 2.png');
?>
<section class="hero hero--bg-image" id="heroSection" style="background-image: <?= $heroGradient ?>, url('<?= $bgUrl ?>');">
  <div class="container container-hero">
    <div class="hero__body-full">
      <?php if (!empty($ss['eyebrow'])): ?>
        <span class="label-upper hero__eyebrow animate-hero-text"><?= e($ss['eyebrow']) ?></span>
      <?php endif; ?>
      <h1 class="heading-hero animate-hero-text"><?= e($ss['title']) ?></h1>
      <?php if (!empty($ss['subtitle'])): ?>
        <p class="body-lg animate-hero-text"><?= e($ss['subtitle']) ?></p>
      <?php endif; ?>
      <?php if (!empty($ss['button_primary_text']) || !empty($ss['button_secondary_text'])): ?>
      <div class="hero__actions animate-hero-text">
        <?php if (!empty($ss['button_primary_text'])): ?>
          <a href="<?= url($ss['button_primary_url'] ?: 'services.php') ?>" class="btn btn-primary btn-lg">
            <?= e($ss['button_primary_text']) ?> <i class="ri-arrow-right-line"></i>
          </a>
        <?php endif; ?>
        <?php if (!empty($ss['button_secondary_text'])): ?>
          <a href="<?= url($ss['button_secondary_url'] ?: 'contact.php') ?>" class="btn btn-ghost btn-lg">
            <?= e($ss['button_secondary_text']) ?>
          </a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>

<?php else: ?>
<!-- ═══════════════════════════════════════════
     MODE: MULTI-SLIDE CAROUSEL (Swiper.js Fade — Navigation Buttons Only, No Dots)
     ═══════════════════════════════════════════ -->
<section class="hero hero--slider-section" id="heroSection">
  <div class="swiper hero-swiper" style="width: 100%;">
    <div class="swiper-wrapper">
      <?php foreach ($heroSlides as $slide): ?>
      <?php 
      $slideBg = !empty($slide['media_url']) ? media_url($slide['media_url']) : img('home section 2.png');
      ?>
      <div class="swiper-slide hero-slide-bg" style="background-image: <?= $heroGradient ?>, url('<?= $slideBg ?>');">
        <div class="container container-hero">
          <div class="hero__body-full">
            <?php if (!empty($slide['eyebrow'])): ?>
              <span class="label-upper hero__eyebrow animate-hero-text"><?= e($slide['eyebrow']) ?></span>
            <?php endif; ?>
            <h1 class="heading-hero animate-hero-text"><?= e($slide['title']) ?></h1>
            <?php if (!empty($slide['subtitle'])): ?>
              <p class="body-lg animate-hero-text"><?= e($slide['subtitle']) ?></p>
            <?php endif; ?>
            <div class="hero__actions animate-hero-text">
              <?php if (!empty($slide['button_primary_text'])): ?>
                <a href="<?= url($slide['button_primary_url'] ?: 'services.php') ?>" class="btn btn-primary btn-lg">
                  <?= e($slide['button_primary_text']) ?> <i class="ri-arrow-right-line"></i>
                </a>
              <?php endif; ?>
              <?php if (!empty($slide['button_secondary_text'])): ?>
                <a href="<?= url($slide['button_secondary_url'] ?: 'contact.php') ?>" class="btn btn-ghost btn-lg">
                  <?= e($slide['button_secondary_text']) ?>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Navigation Buttons Only (No Dots) -->
    <div class="hero-swiper-button-prev" aria-label="Previous Slide"><i class="ri-arrow-left-s-line"></i></div>
    <div class="hero-swiper-button-next" aria-label="Next Slide"><i class="ri-arrow-right-s-line"></i></div>
  </div>

  <?php include ROOT_PATH . '/views/partials/floating-icons.php'; ?>
</section>
<?php endif; ?>