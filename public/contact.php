<?php
/**
 * WORDORA — Contact Us Page
 * Layout: Full Cover Hero + High-Converting 2-Column Consultation & Direct Access Layout + Interactive FAQ Accordion
 */
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';
require_once ROOT_PATH . '/core/CSRF.php';

$meta = [
    'title' => 'Contact WORDORA — Schedule an Editorial Discovery Consultation',
    'description' => 'Get in touch with WORDORA for custom content writing proposals, topic cluster roadmaps, and whitepaper production. We respond within 24 hours.',
];

$preselectedService = trim($_GET['service'] ?? '');

$devServicesEnabled = (setting('home_sec3c_enabled', '1') !== '0');

// All 7 Development & Design Services
$canonicalDevServices = [
    'Web App Development',
    'Mobile App Development',
    'Website Designing and Development',
    'AI Development',
    'Software Development',
    'UI/UX Design',
    'Technical SEO'
];

$activeDevServices = [];
try {
    if (class_exists('Service')) {
        $dbServices = Service::getAll();
        foreach ($dbServices as $srv) {
            if ((int)($srv['id'] ?? 0) > 7) {
                $activeDevServices[] = $srv['title'];
            }
        }
    }
} catch (\Throwable $t) {}

if (empty($activeDevServices)) {
    $activeDevServices = $canonicalDevServices;
}

$devTitlesLower = array_map('strtolower', array_merge($canonicalDevServices, $activeDevServices, [
    'web development', 'mobile development', 'website design', 'seo'
]));

// Preselected service normalization (by title or slug)
if (!empty($preselectedService)) {
    try {
        if (class_exists('Service')) {
            $matchedSrv = Service::getBySlug($preselectedService);
            if ($matchedSrv) {
                $preselectedService = $matchedSrv['title'];
            }
        }
    } catch (\Throwable $t) {}

    // If dev services are toggled OFF and a dev service was requested, reset it
    if (!$devServicesEnabled && in_array(strtolower($preselectedService), $devTitlesLower)) {
        $preselectedService = '';
    }
}

// ═══════════════════════════════════════════
// DYNAMIC SECTION SETTINGS
// ═══════════════════════════════════════════
$formBadge = setting('contact_form_badge', 'PROJECT SCOPE INQUIRY');
$formTitle = setting('contact_form_title', 'Send Us a Project Brief');
$formDesc  = setting('contact_form_desc', 'Fill out the details below and our managing editor will prepare a customized proposal.');
$rawFormServices = json_decode(setting('contact_form_services', '[]'), true) ?: [
    'SEO Content Writing',
    'Technical Writing',
    'Brand Copywriting',
    'Thought Leadership',
    'Social Media Content',
    'Email Marketing',
    'Full Retainer'
];

// Filter out dev services from editorial list to prevent duplicate display and enforce toggle
$editorialServices = [];
foreach ($rawFormServices as $s) {
    $trimmed = trim($s);
    if (empty($trimmed)) continue;
    if (in_array(strtolower($trimmed), $devTitlesLower)) {
        continue;
    }
    $editorialServices[] = $trimmed;
}

// Dev services: only included in dropdown when master toggle is ON
$devServices = $devServicesEnabled ? $activeDevServices : [];
$formNote  = setting('contact_form_note', 'We never share your information. Protected by strict mutual NDA.');

$showcaseImage = setting('contact_showcase_image', '/img/contact from.png');
$showcaseTitle = setting('contact_showcase_title', 'Dedicated Managing Editor Access');
$showcaseDesc  = setting('contact_showcase_desc', 'Every client is paired with an industry-specialist managing editor who oversees research, voice alignment, and delivery milestones.');

$infoCards = json_decode(setting('contact_info_cards', '[]'), true) ?: [
    ['icon' => 'ri-mail-star-line', 'label' => 'Direct Email', 'value' => setting('contact_email', 'info@wordora.in'), 'link_type' => 'email'],
    ['icon' => 'ri-phone-line', 'label' => 'Dedicated Line', 'value' => setting('contact_phone', '+91-XXXXXXXXXX'), 'link_type' => 'phone'],
    ['icon' => 'ri-map-pin-line', 'label' => 'Studio HQ', 'value' => setting('address', 'Jaipur, Rajasthan, India'), 'link_type' => 'none'],
    ['icon' => 'ri-time-line', 'label' => 'Response SLA', 'value' => 'Under 24 Hours', 'link_type' => 'none'],
];

$enterpriseBadge   = setting('contact_enterprise_badge', 'Enterprise SLA');
$enterpriseTitle   = setting('contact_enterprise_title', 'Need an Urgent Custom Briefing?');
$enterpriseDesc    = setting('contact_enterprise_desc', 'We can execute mutual NDAs within 2 hours and initiate multi-author topic cluster production within 5 business days.');
$enterpriseBtnText = setting('contact_enterprise_btn_text', 'Email Enterprise Desk Directly');
$enterpriseBtnUrl  = setting('contact_enterprise_btn_url', 'mailto:info@wordora.in?subject=Urgent%20Enterprise%20Content%20Scope');

$faqBadge = setting('contact_faq_badge', 'FREQUENTLY ASKED QUESTIONS');
$faqTitle = setting('contact_faq_title', 'Everything You Need to Know');
$faqDesc  = setting('contact_faq_desc', 'Clear answers to common questions about our scopes, writer matchmaking, and delivery turnarounds.');
$faqImage = setting('contact_faq_image', '/img/FAQ 2.png');
$faqs     = json_decode(setting('contact_faqs', '[]'), true) ?: [];

ob_start();
?>

<!-- ═══════════════════════════════════════════
     01 — HERO BANNER (MULTI-MODE: SLIDER / SINGLE / VIDEO)
     ═══════════════════════════════════════════ -->
<?php 
$heroPage = 'contact';
include ROOT_PATH . '/views/partials/hero-banner.php'; 
?>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>


<!-- ═══════════════════════════════════════════
     02 — 2-COLUMN CONSULTATION & CONTACT SECTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-12) 0 var(--space-20);">
  <div class="container" style="max-width: 1280px;">
    
    <div class="contact-main-grid" style="display: grid; grid-template-columns: 1.25fr 0.85fr; gap: 40px; align-items: start;">
      
      <!-- ═══════════════════════════════════════════
           LEFT COLUMN: DIRECT CONSULTATION FORM (60%)
           ═══════════════════════════════════════════ -->
      <div class="contact-form-box reveal-up" style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.45); border-radius: 24px; padding: 2.8rem; box-shadow: none !important;">
        
        <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px dashed var(--color-border);">
          <span class="badge badge-teal" style="font-size: 0.72rem; text-transform: uppercase; margin-bottom: 6px; display: inline-block;">
            <?= e($formBadge) ?>
          </span>
          <h2 style="font-family: var(--font-display); font-size: 1.65rem; color: var(--color-navy); margin: 0 0 6px 0;">
            <?= e($formTitle) ?>
          </h2>
          <p style="font-size: 0.875rem; color: var(--color-text-muted); margin: 0;">
            <?= e($formDesc) ?>
          </p>
        </div>

        <form id="contactForm" novalidate style="display: flex; flex-direction: column; gap: 16px;">
          <?= CSRF::field() ?>

          <!-- Full Name & Email -->
          <div class="form-row-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group" style="margin: 0;">
              <label class="form-label" for="contactName">
                Full Name *
              </label>
              <input type="text" id="contactName" name="name" class="form-input" required placeholder="Enter your full name">
              <span class="form-error" data-error="name" style="font-size: 0.75rem; color: #e53e3e;"></span>
            </div>

            <div class="form-group" style="margin: 0;">
              <label class="form-label" for="contactEmail">
                Work Email *
              </label>
              <input type="email" id="contactEmail" name="email" class="form-input" required placeholder="Enter your work email">
              <span class="form-error" data-error="email" style="font-size: 0.75rem; color: #e53e3e;"></span>
            </div>
          </div>

          <!-- Company & Phone -->
          <div class="form-row-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div class="form-group" style="margin: 0;">
              <label class="form-label" for="contactCompany">
                Company Name &amp; Website
              </label>
              <input type="text" id="contactCompany" name="company" class="form-input" placeholder="Company Name &amp; Website URL">
            </div>

            <div class="form-group" style="margin: 0;">
              <label class="form-label" for="contactPhone">
                Phone Number / WhatsApp
              </label>
              <input type="tel" id="contactPhone" name="phone" class="form-input" placeholder="Phone Number / WhatsApp">
            </div>
          </div>

          <!-- Service Needed (Dynamic Dropdown) -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" for="contactService">
              Primary Service Discipline
            </label>
            <div style="position: relative;">
              <select id="contactService" name="service" class="form-select custom-select-styled">
                <option value="" <?= empty($preselectedService) ? 'selected' : '' ?>>Choose Service Discipline...</option>
                
                <optgroup label="Editorial &amp; Content Disciplines">
                  <?php foreach ($editorialServices as $srvOpt): ?>
                    <?php 
                      $isSelected = (!empty($preselectedService) && (strcasecmp($preselectedService, $srvOpt) === 0 || str_contains(strtolower($preselectedService), strtolower($srvOpt))));
                    ?>
                    <option value="<?= e($srvOpt) ?>" <?= $isSelected ? 'selected' : '' ?>><?= e($srvOpt) ?></option>
                  <?php endforeach; ?>
                </optgroup>

                <?php if ($devServicesEnabled && !empty($devServices)): ?>
                <optgroup label="Development &amp; Design Services">
                  <?php foreach ($devServices as $srvOpt): ?>
                    <?php 
                      $isSelected = (!empty($preselectedService) && (strcasecmp($preselectedService, $srvOpt) === 0 || str_contains(strtolower($preselectedService), strtolower($srvOpt))));
                    ?>
                    <option value="<?= e($srvOpt) ?>" <?= $isSelected ? 'selected' : '' ?>><?= e($srvOpt) ?></option>
                  <?php endforeach; ?>
                </optgroup>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- Project Message & Scope -->
          <div class="form-group" style="margin: 0;">
            <label class="form-label" for="contactMessage">
              Project Goals, Audience &amp; Timeline *
            </label>
            <textarea id="contactMessage" name="message" class="form-textarea" required rows="4" placeholder="Describe your content requirements, target audience, deliverables, and timeline..."></textarea>
            <span class="form-error" data-error="message" style="font-size: 0.75rem; color: #e53e3e;"></span>
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; margin-top: 8px;">
            <span>Submit Scope &amp; Request Proposal</span> <i class="ri-send-plane-2-line"></i>
          </button>

          <?php if (!empty($formNote)): ?>
            <div style="font-size: 0.75rem; color: var(--color-text-muted); text-align: center; margin-top: 4px; display: flex; align-items: center; justify-content: center; gap: 6px;">
              <i class="ri-lock-line" style="color: var(--color-teal-ink);"></i>
              <span><?= e($formNote) ?></span>
            </div>
          <?php endif; ?>

        </form>

      </div>


      <!-- ═══════════════════════════════════════════
           RIGHT COLUMN: DIRECT CONTACT & STUDIO HQ (40%)
           ═══════════════════════════════════════════ -->
      <div class="reveal-up" style="display: flex; flex-direction: column; gap: 24px;">
        
        <!-- Visual Showcase Card -->
        <div style="background: #ffffff; border: 1.5px dashed rgba(74, 139, 140, 0.35); border-radius: 20px; padding: 24px; text-align: center;">
          <img src="<?= media_url($showcaseImage) ?>" alt="<?= e($showcaseTitle) ?>" loading="lazy" style="max-height: 220px; width: auto; object-fit: contain; margin: 0 auto 12px;">
          <h3 style="font-family: var(--font-display); font-size: 1.2rem; color: var(--color-navy); margin-bottom: 6px;">
            <?= e($showcaseTitle) ?>
          </h3>
          <p style="font-size: 0.8125rem; color: var(--color-text-muted); line-height: 1.5; margin: 0;">
            <?= e($showcaseDesc) ?>
          </p>
        </div>

        <!-- 4 Direct Contact Cards (2x2 Grid) -->
        <div class="contact-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
          <?php foreach ($infoCards as $card): 
            $cIcon = $card['icon'] ?: 'ri-information-line';
            $cLabel = $card['label'] ?: 'Detail';
            $cVal = $card['value'] ?: '';
            $cType = $card['link_type'] ?? 'none';
          ?>
            <div class="contact-info-card" style="background: #FAF8F5; border: 1px dashed rgba(74, 139, 140, 0.3); border-radius: 16px; padding: 18px; text-align: left;">
              <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(74, 139, 140, 0.15); color: var(--color-teal-ink); display: flex; align-items: center; justify-content: center; font-size: 1.15rem; margin-bottom: 10px;">
                <i class="<?= e($cIcon) ?>"></i>
              </div>
              <span style="font-size: 0.72rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700; font-family: var(--font-mono);"><?= e($cLabel) ?></span>
              <div class="info-card-val" style="font-size: 0.875rem; font-weight: 700; color: var(--color-navy); margin-top: 2px;">
                <?php if ($cType === 'email' || filter_var($cVal, FILTER_VALIDATE_EMAIL)): ?>
                  <a href="mailto:<?= e($cVal) ?>" style="text-decoration: none; color: inherit;">
                    <?= e($cVal) ?>
                  </a>
                <?php elseif ($cType === 'phone'): ?>
                  <a href="tel:<?= e($cVal) ?>" style="text-decoration: none; color: inherit;">
                    <?= e($cVal) ?>
                  </a>
                <?php else: ?>
                  <?= e($cVal) ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Enterprise Retainer Box -->
        <div style="background: var(--color-navy); color: #ffffff; border-radius: 20px; padding: 24px; border: 1.5px dashed rgba(74, 139, 140, 0.4);">
          <span class="badge" style="background: rgba(74, 139, 140, 0.25); color: var(--color-teal-light); border: 1px dashed var(--color-teal-ink); margin-bottom: 10px;">
            <i class="ri-shield-star-line"></i> <?= e($enterpriseBadge) ?>
          </span>
          <h4 style="font-family: var(--font-display); font-size: 1.15rem; color: #ffffff; margin-bottom: 8px;">
            <?= e($enterpriseTitle) ?>
          </h4>
          <p style="font-size: 0.8125rem; color: rgba(255, 255, 255, 0.78); line-height: 1.5; margin-bottom: 14px;">
            <?= e($enterpriseDesc) ?>
          </p>
          <a href="<?= e($enterpriseBtnUrl) ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; font-size: 0.8125rem;">
            <span><?= e($enterpriseBtnText) ?></span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>

      </div>

    </div>

  </div>
</section>


<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>

<!-- ═══════════════════════════════════════════
     03 — INTERACTIVE FAQ ACCORDION SECTION
     ═══════════════════════════════════════════ -->
<section class="section" style="background: var(--color-canvas); padding: var(--space-16) 0;">
  <div class="container" style="max-width: 1280px;">
    
    <div class="section-header reveal-up text-center" style="max-width: 720px; margin: 0 auto var(--space-8);">
      <span class="label-upper"><?= e($faqBadge) ?></span>
      <h2 class="heading-xl" style="margin-top: var(--space-2); margin-bottom: var(--space-3); color: var(--color-navy);"><?= e($faqTitle) ?></h2>
      <p class="body-lg" style="color: var(--color-text-muted);">
        <?= e($faqDesc) ?>
      </p>
    </div>

    <div class="svc-faq-split reveal-up">
      <!-- FAQ Accordions (Left Side) -->
      <div style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($faqs as $fIdx => $faq): ?>
          <details class="svc-faq" <?= $fIdx === 0 ? 'open' : '' ?>>
            <summary>
              <span><?= e($faq['q'] ?? 'Question') ?></span>
              <i class="ri-arrow-down-s-line"></i>
            </summary>
            <div class="svc-faq__body">
              <?= nl2br(e($faq['a'] ?? '')) ?>
            </div>
          </details>
        <?php endforeach; ?>
      </div>

      <!-- FAQ Artwork Illustration Frame (Right Side) -->
      <div class="svc-faq-artwork">
        <div class="svc-faq-artwork-frame">
          <img src="<?= media_url($faqImage) ?>" alt="<?= e($faqTitle) ?>" loading="lazy">
        </div>
      </div>
    </div>

  </div>
</section>

<!-- Ink Stroke Transition -->
<?php include ROOT_PATH . '/views/partials/ink-divider.php'; ?>

<?php
$content = ob_get_clean();
include ROOT_PATH . '/views/layouts/main.php';
