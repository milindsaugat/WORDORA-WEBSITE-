<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Mobile Off-Canvas Drawer -->
<aside class="mobile-drawer" id="mobileDrawer" aria-label="Mobile navigation">
  <div class="mobile-drawer__header">
    <img src="<?= media_url(setting('site_logo'), '/img/wordorga logo.png') ?>" alt="<?= e(setting('site_name', 'WORDORA')) ?>" height="28">
    <button class="mobile-drawer__close" id="mobileCloseBtn" aria-label="Close menu">
      <i class="ri-close-line"></i>
    </button>
  </div>

  <nav class="mobile-drawer__nav">
    <a href="<?= url('/') ?>" class="mobile-drawer__link <?= is_active('/') ?>">
      <i class="ri-home-4-line"></i> Home
    </a>
    <a href="<?= url('who-we-are') ?>" class="mobile-drawer__link <?= is_active('who-we-are') ?>">
      <i class="ri-team-line"></i> Who We Are
    </a>
    <a href="<?= url('services') ?>" class="mobile-drawer__link <?= is_active('services') ?>">
      <i class="ri-quill-pen-line"></i> What We Do
    </a>
    <a href="<?= url('blog') ?>" class="mobile-drawer__link <?= is_active('blog') ?>">
      <i class="ri-article-line"></i> Blog &amp; Journal
    </a>
    <?php if (setting('enable_case_studies', '1') !== '0'): ?>
      <a href="<?= url('case-studies') ?>" class="mobile-drawer__link <?= is_active('case-studies') ?>">
        <i class="ri-checkbox-circle-fill"></i> Case Studies
      </a>
    <?php endif; ?>
    <a href="<?= url('careers') ?>" class="mobile-drawer__link <?= is_active('careers') ?>">
      <i class="ri-briefcase-line"></i> Careers
    </a>
    <a href="<?= url('contact') ?>" class="mobile-drawer__link <?= is_active('contact') ?>">
      <i class="ri-mail-line"></i> Contact Us
    </a>
  </nav>

  <div class="mobile-drawer__cta" style="margin-top: 24px; margin-bottom: 24px;">
    <a href="<?= url('contact') ?>" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; font-size: 0.95rem; padding: 14px 20px; font-weight: 700;">
      <span>Get a Quote</span> <i class="ri-arrow-right-line"></i>
    </a>
  </div>

  <div class="mobile-drawer__socials">
    <a href="#" aria-label="LinkedIn"><i class="ri-linkedin-fill"></i></a>
    <a href="#" aria-label="Twitter / X"><i class="ri-twitter-x-fill"></i></a>
    <a href="#" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
  </div>
</aside>
