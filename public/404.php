<?php
/**
 * WORDORA — Master Full-Width Zero-Scroll 404 Error Page
 * 
 * Features:
 * - Wide Full-Width Container (max-width: 1260px)
 * - Single-Screen Viewport Fit (No vertical scroll on desktop/laptops)
 * - Wordora Signature 1.5px Dashed Teal Border Box (#FFFFFF)
 * - Eyebrow → Headline → Tilted Dashed Artwork (-1.5deg) → Description → Action Buttons → Assistance Strip
 * - Fully Responsive
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/core/helpers.php';

// Set HTTP 404 Status Code for Google & Search Engines
if (!headers_sent()) {
    http_response_code(404);
}

$meta = [
    'title'       => '404 — Page Not Found | WORDORA',
    'description' => 'The article, resource, or case study at this URL might have been updated, relocated, or unpublished during our latest editorial sync. Explore WORDORA content writing services and journal articles.',
    'robots'      => 'noindex, follow',
    'og_image'    => '/img/404.png',
];

ob_start();
?>

<style>
  /* Zero-Scroll Full-Width 404 Styling */
  .section--404-fullwidth {
    background: var(--color-canvas, #FAF8F5);
    min-height: calc(100vh - 120px);
    padding: 112px 24px 32px; /* Clears floating navbar with precise breathing room */
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
  }

  .error-master-box-wide {
    background: var(--color-white, #FFFFFF);
    border: 1.5px dashed rgba(74, 139, 140, 0.45);
    border-radius: 28px;
    padding: clamp(24px, 3.2vw, 38px) clamp(20px, 4vw, 54px);
    max-width: 1260px;
    width: 100%;
    margin: 0 auto;
    text-align: center;
    position: relative;
    z-index: 2;
    box-shadow: 0 8px 30px rgba(15, 30, 54, 0.04);
    box-sizing: border-box;
  }

  .error-media-card-compact {
    background: var(--color-canvas, #FAF8F5);
    padding: 1.25rem;
    border: 1.5px dashed rgba(74, 139, 140, 0.4);
    border-radius: 24px;
    position: relative;
    max-width: 540px;
    margin: 20px auto 26px;
    text-align: center;
    transform: rotate(-1.5deg);
    transform-origin: center center;
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), border-color 0.25s ease, box-shadow 0.25s ease;
  }

  .error-media-card-compact:hover {
    transform: rotate(0deg) scale(1.02);
    border-color: var(--color-teal-ink, #4A8B8C);
    box-shadow: 0 10px 28px rgba(15, 30, 54, 0.08);
  }

  .error-media-card-compact img {
    max-height: 290px;
    width: 100%;
    object-fit: cover;
    border-radius: 16px;
    margin: 0 auto;
    display: block;
    transition: transform 0.35s ease;
  }

  .error-media-card-compact:hover img {
    transform: scale(1.02);
  }

  @media (max-width: 768px) {
    .section--404-fullwidth {
      padding: 100px 14px 40px !important;
      min-height: auto !important;
    }
    .error-master-box-wide {
      padding: 24px 16px 28px !important;
      border-radius: 20px !important;
    }
    .error-media-card-compact {
      transform: rotate(0deg) !important;
      padding: 1rem !important;
      margin: 14px auto 18px !important;
      border-radius: 18px !important;
      max-width: 100% !important;
    }
    .error-media-card-compact img {
      max-height: 220px !important;
      border-radius: 12px !important;
    }
    .error-master-box-wide .heading-xl {
      font-size: clamp(1.6rem, 5.5vw, 2.1rem) !important;
      margin-bottom: 6px !important;
    }
    .error-master-box-wide .body-lg {
      font-size: 13px !important;
      line-height: 1.55 !important;
      margin-bottom: 18px !important;
    }
    .error-actions-group-wide {
      flex-direction: column !important;
      width: 100% !important;
      gap: 9px !important;
    }
    .error-actions-group-wide .btn {
      width: 100% !important;
      justify-content: center !important;
      padding: 11px 16px !important;
    }
    .error-assistance-strip-compact {
      padding: 10px 12px !important;
      flex-direction: column !important;
      text-align: center !important;
      gap: 5px !important;
    }
  }
</style>

<!-- ═══════════════════════════════════════════
     WORDORA 404 — FULL-WIDTH ZERO-SCROLL EXPERIENCE
     ═══════════════════════════════════════════ -->
<section class="section--404-fullwidth">
  
  <!-- Atmosphere Radial Glows -->
  <div style="position: absolute; top: -120px; right: -120px; width: 480px; height: 480px; border-radius: 50%; background: radial-gradient(circle, rgba(74, 139, 140, 0.12) 0%, rgba(250, 248, 245, 0) 70%); pointer-events: none;"></div>
  <div style="position: absolute; bottom: -100px; left: -100px; width: 440px; height: 440px; border-radius: 50%; background: radial-gradient(circle, rgba(15, 30, 54, 0.05) 0%, rgba(250, 248, 245, 0) 70%); pointer-events: none;"></div>

  <div class="container" style="max-width: 1280px; margin: 0 auto; width: 100%;">
    
    <!-- Full-Width Master Box with Wordora Signature Dashed Border -->
    <div class="error-master-box-wide reveal-up">
      
      <!-- 01. Eyebrow Badge (JetBrains Mono & Dashed Teal Ink Border) -->
      <div style="margin-bottom: 12px;">
        <span class="badge" style="background: rgba(74, 139, 140, 0.12); color: var(--color-teal-ink, #4A8B8C); border: 1px dashed var(--color-teal-ink, #4A8B8C); font-family: var(--font-mono, 'JetBrains Mono', monospace); font-size: 10.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 5px 15px; border-radius: 30px; display: inline-flex; align-items: center; gap: 7px;">
          <i class="ri-error-warning-line"></i> HTTP 404 • PAGE NOT FOUND
        </span>
      </div>

      <!-- 02. Display Headline (Playfair Display) -->
      <h1 class="heading-xl" style="font-family: var(--font-display, 'Playfair Display', Georgia, serif); font-weight: 800; color: var(--color-navy, #1B2A4A); font-size: clamp(1.9rem, 3.2vw, 2.75rem); line-height: 1.16; margin: 0 auto 0; letter-spacing: -0.015em; max-width: 820px;">
        The Page You Seek Has Vanished into Ink.
      </h1>

      <!-- 03. Tilted Dashed Artwork Box (Positioned between Heading & Paragraph) -->
      <div class="error-media-card-compact">
        
        <!-- Floating Corner Tag -->
        <span style="position: absolute; top: -11px; right: 18px; background: var(--color-navy, #1B2A4A); color: #FFFFFF; padding: 3px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; font-family: var(--font-mono, monospace); border: 1px dashed var(--color-teal-ink, #4A8B8C); letter-spacing: 0.06em; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 4px 10px rgba(15, 30, 54, 0.12);">
          <i class="ri-compass-discover-line" style="color: var(--color-teal-light, #6BA8A9);"></i>
          <span>404 • Lost in Transition</span>
        </span>

        <!-- 404 Illustration -->
        <img src="<?= media_url('/img/404.png') ?>" 
             alt="404 — Page Not Found | WORDORA" 
             loading="eager">
      </div>

      <!-- 04. Editorial Copy (Inter - Positioned after Artwork) -->
      <p class="body-lg" style="font-family: var(--font-body, 'Inter', sans-serif); color: var(--color-text-muted, #4A627A); line-height: 1.62; font-size: clamp(14px, 0.98vw, 15.5px); max-width: 700px; margin: 0 auto 22px;">
        The article, resource, or case study at this URL might have been updated, relocated, or unpublished during our latest editorial sync. Don't worry — all our core services, journals, and verified client proof are fully live and accessible below.
      </p>

      <!-- 05. Action Buttons Row (DM Sans) -->
      <div class="error-actions-group-wide" style="display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 12px; margin-bottom: 22px;">
        
        <!-- Primary Action: Return to Homepage -->
        <a href="<?= url('/') ?>" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; border-radius: 10px; font-weight: 700; font-size: 13.5px; font-family: var(--font-ui, 'DM Sans', sans-serif); text-decoration: none; box-shadow: 0 4px 16px rgba(74, 139, 140, 0.28); transition: all 0.25s ease;">
          <i class="ri-home-4-line" style="font-size: 16px;"></i>
          <span>Return to Homepage</span>
        </a>

        <!-- Secondary Action: Explore Services (Dashed Border) -->
        <a href="<?= url('services') ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 10px; font-weight: 700; font-size: 13.5px; font-family: var(--font-ui, 'DM Sans', sans-serif); text-decoration: none; border: 1.5px dashed rgba(74, 139, 140, 0.45); color: var(--color-navy, #1B2A4A); background: #FFFFFF; transition: all 0.25s ease;">
          <i class="ri-service-line" style="font-size: 16px; color: var(--color-teal-ink, #4A8B8C);"></i>
          <span>Explore Services</span>
        </a>

        <!-- Tertiary Action: Wordora Journal (Dashed Border) -->
        <a href="<?= url('blog') ?>" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; border-radius: 10px; font-weight: 700; font-size: 13.5px; font-family: var(--font-ui, 'DM Sans', sans-serif); text-decoration: none; border: 1.5px dashed rgba(74, 139, 140, 0.45); color: var(--color-navy, #1B2A4A); background: #FFFFFF; transition: all 0.25s ease;">
          <i class="ri-article-line" style="font-size: 16px; color: var(--color-teal-ink, #4A8B8C);"></i>
          <span>Wordora Journal</span>
        </a>

      </div>

      <!-- 06. Signature Assistance Strip with Dashed Border -->
      <div class="error-assistance-strip-compact" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 10px 20px; border-radius: 12px; background: var(--color-canvas, #FAF8F5); border: 1.5px dashed rgba(74, 139, 140, 0.35); max-width: 540px; width: 100%; box-sizing: border-box; margin: 0 auto;">
        <i class="ri-question-answer-line" style="font-size: 18px; color: var(--color-teal-ink, #4A8B8C); flex-shrink: 0;"></i>
        <span style="font-size: 12.5px; color: var(--color-text-muted, #4A627A); line-height: 1.4; text-align: left;">
          Looking for something specific? <a href="<?= url('contact') ?>" style="color: var(--color-navy, #1B2A4A); font-weight: 700; text-decoration: underline;">Contact our editorial team</a> or request a direct proposal.
        </span>
      </div>

    </div>

  </div>

</section>

<!-- Signature Ink Divider Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
