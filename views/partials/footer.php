<footer class="footer">
  <div class="container" style="position: relative; z-index: 2;">
    <div class="footer__grid">
      
      <!-- Column 1: Brand & Editorial Identity -->
      <div class="footer__col-brand">
        <div class="footer__brand-logo" style="margin-bottom: 18px;">
          <a href="<?= url('/') ?>" class="footer-brand-pill" aria-label="WORDORA Home">
            <img src="<?= media_url(setting('site_logo'), '/img/wordorga logo.png') ?>" alt="<?= e(setting('site_name', 'WORDORA')) ?>" loading="lazy">
          </a>
        </div>
        
        <p class="footer__brand-tagline" style="font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--color-teal-light); margin-bottom: 10px;">
          <?= e(setting('tagline', 'Words That Work. Stories That Sell.')) ?>
        </p>

        <p class="footer__brand-text">
          Premium content writing and editorial services engineered to transform brands, establish search authority, and convert readers into lifelong clients.
        </p>

        <!-- Social Media Profiles (Only renders if configured in Admin) -->
        <?php
        $socialProfiles = [
            'linkedin'  => ['url' => setting('linkedin', ''),  'icon' => 'ri-linkedin-fill',  'label' => 'LinkedIn',  'title' => 'Connect on LinkedIn'],
            'facebook'  => ['url' => setting('facebook', ''),  'icon' => 'ri-facebook-fill',  'label' => 'Facebook',  'title' => 'Follow on Facebook'],
            'youtube'   => ['url' => setting('youtube', ''),   'icon' => 'ri-youtube-fill',   'label' => 'YouTube',   'title' => 'Watch on YouTube'],
            'twitter'   => ['url' => setting('twitter', ''),   'icon' => 'ri-twitter-x-fill', 'label' => 'Twitter/X', 'title' => 'Follow on X'],
            'instagram' => ['url' => setting('instagram', ''), 'icon' => 'ri-instagram-line', 'label' => 'Instagram', 'title' => 'Follow on Instagram'],
        ];
        $activeSocials = array_filter($socialProfiles, fn($s) => !empty(trim($s['url'] ?? '')));
        ?>
        <?php if (!empty($activeSocials)): ?>
          <div class="footer__socials">
            <?php foreach ($activeSocials as $key => $soc): ?>
              <a href="<?= e($soc['url']) ?>" target="_blank" rel="noopener noreferrer" class="footer__social-icon" aria-label="<?= e($soc['label']) ?>" title="<?= e($soc['title']) ?>">
                <i class="<?= e($soc['icon']) ?>"></i>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Column 2: Navigation -->
      <div>
        <h4 class="footer__heading">Navigation</h4>
        <div class="footer__links-list">
          <a href="<?= url('/') ?>" class="footer__link">Home</a>
          <a href="<?= url('who-we-are') ?>" class="footer__link">Who We Are</a>
          <a href="<?= url('services') ?>" class="footer__link">What We Do</a>
          <?php if (setting('enable_case_studies', '1') !== '0'): ?>
            <a href="<?= url('case-studies') ?>" class="footer__link">Case Studies</a>
          <?php endif; ?>
          <a href="<?= url('blog') ?>" class="footer__link">Blog &amp; Journal</a>
          <a href="<?= url('careers') ?>" class="footer__link">Careers</a>
          <a href="<?= url('contact') ?>" class="footer__link">Contact Us</a>
        </div>
      </div>

      <!-- Column 3: Core Disciplines (Services) -->
      <div>
        <h4 class="footer__heading">Disciplines</h4>
        <div class="footer__links-list">
          <a href="<?= url('service/seo-content') ?>" class="footer__link">SEO Content Writing</a>
          <a href="<?= url('service/brand-copy') ?>" class="footer__link">Brand Copywriting</a>
          <a href="<?= url('service/blog-writing') ?>" class="footer__link">Blog &amp; Article Series</a>
          <a href="<?= url('service/academic-writing') ?>" class="footer__link">Academic &amp; Research</a>
          <a href="<?= url('service/technical-writing') ?>" class="footer__link">Technical Writing</a>
          <a href="<?= url('service/thought-leadership') ?>" class="footer__link">Thought Leadership</a>
        </div>
      </div>

      <!-- Column 4: Studio & Direct Inquiries -->
      <div>
        <h4 class="footer__heading">Direct Inquiries</h4>
        
        <div class="footer__contact-items">
          <a href="mailto:<?= e(setting('contact_email', 'info@wordora.in')) ?>" class="footer__contact-link">
            <div class="footer__contact-icon"><i class="ri-mail-send-line"></i></div>
            <div class="footer__contact-text">
              <span class="footer__contact-label">Email Us</span>
              <strong><?= e(setting('contact_email', 'info@wordora.in')) ?></strong>
            </div>
          </a>

          <?php 
          $phone = setting('contact_phone', '');
          if (!empty($phone) && !str_contains($phone, 'XXXX')): 
          ?>
          <a href="tel:<?= preg_replace('/[^0-9\+]/', '', $phone) ?>" class="footer__contact-link">
            <div class="footer__contact-icon"><i class="ri-phone-line"></i></div>
            <div class="footer__contact-text">
              <span class="footer__contact-label">Direct Line</span>
              <strong><?= e($phone) ?></strong>
            </div>
          </a>
          <?php endif; ?>

          <?php 
          $address = setting('address', 'Jaipur, Rajasthan, India');
          if (empty($address) || strtolower($address) === 'editorial studio' || strtolower($address) === 'wordora studio') {
              $address = 'Jaipur, Rajasthan, India';
          }
          ?>
          <div class="footer__contact-link footer__contact-static">
            <div class="footer__contact-icon"><i class="ri-map-pin-2-line"></i></div>
            <div class="footer__contact-text">
              <span class="footer__contact-label">Wordora Studio</span>
              <span><?= e($address) ?></span>
            </div>
          </div>
        </div>

        <div style="margin-top: 18px;">
          <a href="<?= url('contact') ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; padding: 11px 16px; font-size: 0.8125rem; font-weight: 700; border-radius: 8px;">
            <span>Start a Project</span> <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

    </div>

    <!-- Footer Bottom Line -->
    <div class="footer__bottom">
      <div class="footer__bottom-inner">
        <div>
          &copy; <?= date('Y') ?> <strong>WORDORA</strong>. All rights reserved.
        </div>
        <div class="footer__bottom-meta">
          <span>Words That Work. Stories That Sell.</span>
          <span class="footer__bullet">•</span>
          <span>Crafted by Wordora Studio</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Edge-to-Edge Full-Width Minimalist Wordmark (Equal Lowercase Letters & #ebf4f5 Luxury Fade) -->
  <div class="footer-wordmark-bottom" aria-hidden="true">
    <div class="footer-wordmark-track">
      <span class="wm-letter">w</span>
      <span class="wm-letter">o</span>
      <span class="wm-letter">r</span>
      <span class="wm-letter">d</span>
      <span class="wm-letter">o</span>
      <span class="wm-letter">r</span>
      <span class="wm-letter">a</span>
    </div>
  </div>
</footer>
