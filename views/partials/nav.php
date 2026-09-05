<?php
// Dynamically fetch up to 8 latest published blog posts for Navbar
$allNavBlogs = [];
try {
    $allNavBlogs = Post::getLatest(8);
} catch (Exception $e) {
    $allNavBlogs = [];
}
$navLatestBlogs = array_slice($allNavBlogs, 0, 4);
$navCol2Blogs   = (count($allNavBlogs) > 4) ? array_slice($allNavBlogs, 4, 4) : $allNavBlogs;

// Dynamically fetch 4 latest case studies for Navbar
$navLatestStudies = [];
try {
    $navLatestStudies = CaseStudy::getLatest(4);
} catch (Exception $e) {
    $navLatestStudies = [];
}
?>
<nav class="navbar" id="mainNavbar">
  <!-- Logo -->
  <a href="<?= url('/') ?>" class="navbar__logo">
    <img src="<?= media_url(setting('site_logo'), '/img/wordorga logo.png') ?>" alt="<?= e(setting('site_name', 'WORDORA')) ?> Logo" width="140" height="30">
  </a>

  <!-- Desktop Links -->
  <div class="navbar__links">
    <a href="<?= url('/') ?>" class="navbar__link <?= is_active('/') ?>">Home</a>
    <a href="<?= url('who-we-are') ?>" class="navbar__link <?= is_active('who-we-are') ?>">Who We Are</a>

<?php
// Dynamically fetch active services for Navbar
$navServices = [];
try {
    $navServices = Service::getActive();
} catch (Exception $e) {
    $navServices = [];
}
// Split into Editorial (1-7) and Digital & Engineering (8-14)
$editorialNavServices = array_filter($navServices, fn($s) => (int)$s['id'] <= 7);
$devNavServices = array_filter($navServices, fn($s) => (int)$s['id'] > 7);
$showDevServices = (setting('home_sec3c_enabled', '1') !== '0');
?>
    <!-- What We Do Mega-Dropdown (Dynamic Core Disciplines + Featured Card) -->
    <div class="nav-dropdown-trigger">
      <a href="<?= url('services') ?>" class="navbar__link <?= is_active('services') ?>">
        What We Do <i class="ri-arrow-down-s-line" style="font-size: 1rem; vertical-align: middle;"></i>
      </a>
      <div class="nav-dropdown" id="servicesDropdown">
        <?php if ($showDevServices && !empty($devNavServices)): ?>
        <div class="dropdown-mega-layout dropdown-mega-layout--services">
          
          <!-- Left Area: 2-Column Split by Category + Bottom Highlight Ribbon -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px 22px; align-content: space-between;">
            
            <!-- Category 1: Editorial & Content Disciplines -->
            <div>
              <div class="nav-mega-heading">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span><i class="ri-quill-pen-line"></i> EDITORIAL &amp; CONTENT</span>
                  <span class="nav-sub-tag">Editorial</span>
                </div>
                <a href="<?= url('services') ?>">All <?= count($editorialNavServices) ?> →</a>
              </div>
              
              <div class="nav-service-items-list">
                <?php foreach ($editorialNavServices as $srvNav): ?>
                  <a href="<?= url('service/' . urlencode($srvNav['slug'])) ?>" class="dropdown-item dropdown-item--svc">
                    <div class="dropdown-item__icon"><i class="<?= e($srvNav['icon'] ?: 'ri-quill-pen-line') ?>"></i></div>
                    <div class="dropdown-item__text">
                      <h4><?= e($srvNav['title']) ?></h4>
                      <p><?= e($srvNav['tag'] ?: truncate($srvNav['description'], 36)) ?></p>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Category 2: Other Services (Tech, Dev & Design) -->
            <div>
              <div class="nav-mega-heading nav-mega-heading--dev">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span><i class="ri-apps-2-line" style="color: var(--color-teal-ink);"></i> OTHER SERVICES</span>
                  <span class="nav-sub-tag nav-sub-tag--dev">Digital &amp; Tech</span>
                </div>
                <a href="<?= url('services') ?>#development-matrix" style="color: var(--color-teal-ink);">All <?= count($devNavServices) ?> →</a>
              </div>
              
              <div class="nav-service-items-list">
                <?php foreach ($devNavServices as $srvNav): ?>
                  <a href="<?= url('service/' . urlencode($srvNav['slug'])) ?>" class="dropdown-item dropdown-item--svc dropdown-item--dev">
                    <div class="dropdown-item__icon"><i class="<?= e($srvNav['icon'] ?: 'ri-code-box-line') ?>"></i></div>
                    <div class="dropdown-item__text">
                      <h4><?= e($srvNav['title']) ?></h4>
                      <p><?= e($srvNav['tag'] ?: truncate($srvNav['description'], 36)) ?></p>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Bottom Full-Width Highlight Ribbon -->
            <div style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; background: rgba(74, 139, 140, 0.05); border: 1.5px dashed rgba(74, 139, 140, 0.3); border-radius: 10px; margin-top: 2px;">
              <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--color-navy);">
                <i class="ri-compass-3-line" style="color: var(--color-teal-ink); font-size: 1.05rem;"></i>
                <span><strong style="color: var(--color-navy);">Complete Capabilities:</strong> <span style="color: var(--color-text-muted);"><?= count($editorialNavServices) ?> Core Editorial + <?= count($devNavServices) ?> Other Services</span></span>
              </div>
              <a href="<?= url('services') ?>" style="font-size: 0.78rem; color: var(--color-teal-ink); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Explore All <?= count($navServices) ?> <i class="ri-arrow-right-line"></i>
              </a>
            </div>

          </div>

          <!-- Right Column: Featured Showcase Card -->
          <div class="dropdown-featured-card">
            <span class="dropdown-featured-card__badge"><i class="ri-sparkling-fill"></i> Wordora Studio</span>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; padding: 4px 0;">
              <img src="<?= img('service treasure.png') ?>" alt="WORDORA Studio Craft" loading="lazy" style="max-height: 175px; width: auto; object-fit: contain;">
            </div>
            <a href="<?= url('services') ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
              <span>Explore All <?= count($navServices) ?> Disciplines</span> <i class="ri-arrow-right-line"></i>
            </a>
          </div>

        </div>
        <?php else: ?>
        <!-- Original 1-Column Layout when Dev Services are OFF -->
        <div class="dropdown-mega-layout">
          
          <!-- Left Column: The Core Disciplines -->
          <div class="dropdown-grid">
            <?php foreach ($editorialNavServices as $srvNav): ?>
              <a href="<?= url('service/' . urlencode($srvNav['slug'])) ?>" class="dropdown-item">
                <div class="dropdown-item__icon"><i class="<?= e($srvNav['icon'] ?: 'ri-quill-pen-line') ?>"></i></div>
                <div class="dropdown-item__text">
                  <h4><?= e($srvNav['title']) ?></h4>
                  <p><?= e($srvNav['tag'] ?: truncate($srvNav['description'], 42)) ?></p>
                </div>
              </a>
            <?php endforeach; ?>

            <a href="<?= url('services') ?>" class="dropdown-item" style="background: rgba(74, 139, 140, 0.05); border-color: rgba(74, 139, 140, 0.25);">
              <div class="dropdown-item__icon" style="background: var(--color-teal-ink); color: #fff;"><i class="ri-arrow-right-up-line"></i></div>
              <div class="dropdown-item__text">
                <h4 style="color: var(--color-teal-ink);">All <?= count($editorialNavServices) ?> Disciplines</h4>
                <p>Master overview &amp; scope comparison</p>
              </div>
            </a>
          </div>

          <!-- Right Column: Featured Showcase Card -->
          <div class="dropdown-featured-card">
            <span class="dropdown-featured-card__badge"><i class="ri-sparkling-fill"></i> Wordora Studio</span>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; padding: 4px 0;">
              <img src="<?= img('service treasure.png') ?>" alt="WORDORA Studio Craft" loading="lazy" style="max-height: 175px; width: auto; object-fit: contain;">
            </div>
            <a href="<?= url('services') ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
              <span>Explore All <?= count($editorialNavServices) ?> Disciplines</span> <i class="ri-arrow-right-line"></i>
            </a>
          </div>

        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Blog & Case Studies Mega-Dropdown (Balanced Height & 100% Dynamic Content) -->
    <div class="nav-dropdown-trigger">
      <a href="<?= url('blog') ?>" class="navbar__link <?= is_active('blog') || is_active('case-studies') ? 'active' : '' ?>">
        Blog <i class="ri-arrow-down-s-line" style="font-size: 1rem; vertical-align: middle;"></i>
      </a>
      <div class="nav-dropdown" id="blogNavDropdown">
        <div class="dropdown-mega-layout">
          
          <!-- Left Column: 2-Column Grid + Bottom Highlight Ribbon -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px 18px; align-content: space-between;">
            
            <!-- Column 1: Latest Blog Articles with Dynamic Thumbnails -->
            <div>
              <div class="nav-mega-heading">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span><i class="ri-article-line"></i> WORDORA JOURNAL</span>
                  <span class="nav-sub-tag">Blog</span>
                </div>
                <a href="<?= url('blog') ?>">All Articles →</a>
              </div>
              
              <div class="nav-blog-items-list">
                <?php foreach ($navLatestBlogs as $idx => $nb): 
                    $isNewBlog = ($idx === 0 && is_recent_new($nb['created_at'] ?? '', 7));
                ?>
                <a href="<?= url('blog/' . e($nb['slug'])) ?>" class="dropdown-item dropdown-item--compact">
                  <div class="dropdown-item__thumb" style="width: 48px; height: 48px; min-width: 48px; border-radius: 10px; overflow: hidden; background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); flex-shrink: 0; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="<?= media_url($nb['featured_img'], '/img/blog.png') ?>" alt="<?= e($nb['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 9px;">
                  </div>
                  <div class="dropdown-item__text">
                    <h4>
                      <?= e(truncate($nb['title'], 32)) ?>
                      <?php if ($isNewBlog): ?>
                        <span class="nav-new-pill">NEW</span>
                      <?php endif; ?>
                    </h4>
                    <p><i class="ri-time-line"></i> <?= (int)$nb['read_time'] ?>m read • <?= e($nb['category_name'] ?? 'Insights') ?></p>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
            </div>

            <?php $enableCs = (setting('enable_case_studies', '1') !== '0'); ?>
            <?php if ($enableCs && !empty($navLatestStudies)): ?>
            <!-- Column 2 (Active Case Studies Mode): Dynamic Case Studies with Mini Thumbnails -->
            <div>
              <div class="nav-mega-heading">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span><i class="ri-checkbox-circle-fill"></i> CASE STUDIES</span>
                  <span class="nav-sub-tag">Proof</span>
                </div>
                <a href="<?= url('case-studies') ?>">All Studies →</a>
              </div>

              <div class="nav-blog-items-list">
                <?php foreach ($navLatestStudies as $sIdx => $cs): 
                    $isNewStudy = ($sIdx === 0 && is_recent_new($cs['created_at'] ?? '', 7));
                ?>
                <a href="<?= url('case-study/' . e($cs['slug'])) ?>" class="dropdown-item dropdown-item--compact">
                  <div class="dropdown-item__thumb" style="width: 48px; height: 48px; min-width: 48px; border-radius: 10px; overflow: hidden; background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); flex-shrink: 0; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="<?= media_url($cs['image'], '/img/service treasure.png') ?>" alt="<?= e($cs['client']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 9px;">
                  </div>
                  <div class="dropdown-item__text">
                    <h4>
                      <?= e(truncate($cs['client'], 22)) ?>
                      <?php if ($isNewStudy): ?>
                        <span class="nav-new-pill">NEW</span>
                      <?php endif; ?>
                    </h4>
                    <p><strong style="color: var(--color-teal-ink);"><?= e($cs['headline_metric']) ?></strong> <?= e($cs['headline_label'] ?? $cs['badge']) ?> • <?= e($cs['industry_slug'] ?? 'B2B') ?></p>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Bottom Full-Width Highlight Ribbon (Case Studies Link) -->
            <div style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: rgba(74, 139, 140, 0.05); border: 1.5px dashed rgba(74, 139, 140, 0.3); border-radius: 12px;">
              <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--color-navy);">
                <i class="ri-verified-badge-fill" style="color: var(--color-teal-ink); font-size: 1.05rem;"></i>
                <span><span style="font-weight: 600; color: var(--color-navy);">Verified Commercial Proof:</span> <span style="color: var(--color-text-muted);">Real frameworks with quantified ROI</span></span>
              </div>
              <a href="<?= url('case-studies') ?>" style="font-size: 0.8125rem; color: var(--color-teal-ink); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Explore All Proof <i class="ri-arrow-right-line"></i>
              </a>
            </div>
            <?php else: ?>
            <!-- Column 2 (Case Studies OFF Mode): More Dynamic Blog Articles from Database -->
            <div>
              <div class="nav-mega-heading">
                <div style="display: flex; align-items: center; gap: 6px;">
                  <span><i class="ri-fire-line"></i> FEATURED INSIGHTS</span>
                  <span class="nav-sub-tag">Insights</span>
                </div>
                <a href="<?= url('blog') ?>">Explore All →</a>
              </div>

              <div class="nav-blog-items-list">
                <?php foreach ($navCol2Blogs as $idx => $nb): ?>
                <a href="<?= url('blog/' . e($nb['slug'])) ?>" class="dropdown-item dropdown-item--compact">
                  <div class="dropdown-item__thumb" style="width: 48px; height: 48px; min-width: 48px; border-radius: 10px; overflow: hidden; background: #FAF8F5; border: 1px solid rgba(74, 139, 140, 0.25); flex-shrink: 0; padding: 0; display: flex; align-items: center; justify-content: center;">
                    <img src="<?= media_url($nb['featured_img'], '/img/blog.png') ?>" alt="<?= e($nb['title']) ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; border-radius: 9px;">
                  </div>
                  <div class="dropdown-item__text">
                    <h4><?= e(truncate($nb['title'], 32)) ?></h4>
                    <p><i class="ri-time-line"></i> <?= (int)$nb['read_time'] ?>m read • <?= e($nb['category_name'] ?? 'Insights') ?></p>
                  </div>
                </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Bottom Full-Width Highlight Ribbon (Journal Focus) -->
            <div style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: rgba(74, 139, 140, 0.05); border: 1.5px dashed rgba(74, 139, 140, 0.3); border-radius: 12px;">
              <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--color-navy);">
                <i class="ri-article-line" style="color: var(--color-teal-ink); font-size: 1.05rem;"></i>
                <span><span style="font-weight: 600; color: var(--color-navy);">Wordora Journal:</span> <span style="color: var(--color-text-muted);">Fresh essays on content strategy &amp; search authority</span></span>
              </div>
              <a href="<?= url('blog') ?>" style="font-size: 0.8125rem; color: var(--color-teal-ink); font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                Read All Articles <i class="ri-arrow-right-line"></i>
              </a>
            </div>
            <?php endif; ?>
          </div>

          <!-- Right Column (Exact 0.95fr): Featured Showcase Card matching Services card -->
          <div class="dropdown-featured-card">
            <span class="dropdown-featured-card__badge"><i class="ri-sparkling-fill"></i> Wordora Journal</span>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; padding: 4px 0;">
              <img src="<?= img('blog.png') ?>" alt="WORDORA Journal" loading="lazy" style="max-height: 185px; width: auto; object-fit: contain;">
            </div>
            <a href="<?= url('blog') ?>" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
              <span>Explore Wordora Journal</span> <i class="ri-arrow-right-line"></i>
            </a>
          </div>

        </div>
      </div>
    </div>

    <a href="<?= url('careers') ?>" class="navbar__link <?= is_active('careers') ?>">Careers</a>
    <a href="<?= url('contact') ?>" class="navbar__link <?= is_active('contact') ?>">Contact Us</a>
  </div>

  <!-- CTA Button -->
  <a href="<?= url('contact') ?>" class="btn btn-primary btn-sm navbar__cta">Get a Quote</a>

  <!-- Hamburger for Mobile -->
  <button class="navbar__hamburger" id="hamburgerBtn" aria-label="Open mobile menu" aria-expanded="false">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>