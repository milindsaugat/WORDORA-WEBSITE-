<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/core/helpers.php';
Auth::requireAuth();

$adminTitle = 'Dashboard Overview';

// Fetch stats
$totalPosts = 0;
$totalServices = 0;
$totalCaseStudies = 0;
$totalLeads = 0;
$unreadLeads = 0;
$activeSlides = 0;
$recentLeads = [];
$recentPosts = [];

try {
    $totalPosts = Post::countPublished();
    $totalServices = count(Service::getActive());
    $totalCaseStudies = count(CaseStudy::getAll('', true));
    $totalLeads = Contact::countAll();
    $unreadLeads = Contact::countByStatus('unread');
    $activeSlides = count(Hero::getActiveSlides());
    $recentLeads = array_slice(Contact::getAll(), 0, 5);
    $recentPosts = array_slice(Post::getAll(), 0, 5);
} catch (Exception $e) {}

include ROOT_PATH . '/admin/includes/header.php';
?>

<!-- 01. Key Metrics Stat Row (6 Balanced Cards) -->
<div class="stats-row" style="margin-bottom: 24px;">
  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase; letter-spacing: 0.05em;">Visual Media</span>
      <div class="stat-icon teal"><i class="ri-slideshow-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$activeSlides ?></div>
      <div class="stat-title">Hero Slides</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase; letter-spacing: 0.05em;">Editorial</span>
      <div class="stat-icon navy"><i class="ri-article-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$totalPosts ?></div>
      <div class="stat-title">Articles</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase; letter-spacing: 0.05em;">Offerings</span>
      <div class="stat-icon teal"><i class="ri-quill-pen-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$totalServices ?></div>
      <div class="stat-title">Services</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-muted); text-transform: uppercase; letter-spacing: 0.05em;">Proof</span>
      <div class="stat-icon navy"><i class="ri-folder-user-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$totalCaseStudies ?></div>
      <div class="stat-title">Case Studies</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-warning); text-transform: uppercase; letter-spacing: 0.05em;">Inquiries</span>
      <div class="stat-icon orange"><i class="ri-mail-unread-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$unreadLeads ?></div>
      <div class="stat-title">Unread Leads</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-card-top">
      <span style="font-size: 11px; font-weight: 700; color: var(--admin-success); text-transform: uppercase; letter-spacing: 0.05em;">Pipeline</span>
      <div class="stat-icon green"><i class="ri-contacts-line"></i></div>
    </div>
    <div class="stat-info">
      <div class="stat-value"><?= (int)$totalLeads ?></div>
      <div class="stat-title">Total Leads</div>
    </div>
  </div>
</div>

<!-- 02. Visual Page Builders & Studios (Balanced 3x2 Grid) -->
<div class="admin-card" style="margin-bottom: 24px;">
  <div style="padding: 22px 26px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
      <div>
        <h2 style="font-size: 16px; font-weight: 700; color: var(--admin-navy); margin: 0 0 2px; display: flex; align-items: center; gap: 8px;">
          <i class="ri-layout-masonry-line" style="color: var(--admin-teal);"></i> Visual Page Builders &amp; Studios
        </h2>
        <div style="font-size: 12.5px; color: var(--admin-muted);">Direct access to live sections, content editors, and SEO meta across all frontend pages</div>
      </div>
      <span style="font-size: 11.5px; font-weight: 700; color: var(--admin-teal); background: var(--admin-teal-pale); padding: 4px 12px; border-radius: 20px;">
        6 Active Builders
      </span>
    </div>
    
    <div class="studio-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <!-- 1. Home Page Studio -->
      <a href="<?= url('admin/pages/home.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon teal">
          <i class="ri-home-4-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Home Page Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">9 Live Sections &amp; Bento Grid</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 2. Who We Are Studio -->
      <a href="<?= url('admin/pages/who-we-are.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon navy">
          <i class="ri-team-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Who We Are Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Story, Values &amp; Team Members</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 3. Services Studio -->
      <a href="<?= url('admin/pages/services.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon teal">
          <i class="ri-service-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Services Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Disciplines Hub &amp; Service Pages</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 4. Contact & Consultation Studio -->
      <a href="<?= url('admin/pages/contact.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon orange">
          <i class="ri-contacts-book-2-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Contact Page Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Lead Capture &amp; FAQ Accordion</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 5. Careers Studio -->
      <a href="<?= url('admin/pages/careers.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon purple">
          <i class="ri-briefcase-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Careers Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Open Roles, Perks &amp; Culture</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 6. Case Studies Studio -->
      <a href="<?= url('admin/pages/case-studies.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon teal">
          <i class="ri-folder-shield-2-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Case Studies Studio</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Metrics, Client Proof &amp; Sectors</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>

      <!-- 7. Global SEO Engine -->
      <a href="<?= url('admin/settings/seo.php') ?>" class="studio-shortcut-card">
        <div class="studio-shortcut-icon green">
          <i class="ri-seo-line"></i>
        </div>
        <div style="flex: 1; min-width: 0;">
          <div style="font-weight: 700; font-size: 14px; color: var(--admin-navy); margin-bottom: 2px;">Global SEO Engine</div>
          <div style="font-size: 12px; color: var(--admin-muted);">Meta Tags, Social &amp; Schema</div>
        </div>
        <i class="ri-arrow-right-line studio-arrow"></i>
      </a>
    </div>
  </div>
</div>

<!-- 03. Two-Column Grid: Recent Inquiries & Recent Articles -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(420px, 1fr)); gap: 24px;">

  <!-- Recent Scope Inquiries -->
  <div class="admin-card" style="margin-bottom: 0;">
    <div class="card-header" style="padding: 16px 22px; border-bottom: 1px solid var(--admin-border-subtle); display: flex; align-items: center; justify-content: space-between;">
      <h2 class="card-title" style="font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="ri-mail-line" style="color: var(--admin-teal);"></i> Recent Scope Inquiries
      </h2>
      <a href="<?= url('admin/leads/index.php') ?>" class="btn-adm btn-adm-outline btn-adm-sm" style="font-size: 12px; padding: 5px 12px;">View All Leads</a>
    </div>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Client Name</th>
            <th>Service Scope</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentLeads)): ?>
            <?php foreach ($recentLeads as $lead): ?>
            <tr>
              <td>
                <div style="font-weight: 700; color: var(--admin-navy);"><?= e($lead['name']) ?></div>
                <div style="font-size: 11.5px; color: var(--admin-muted);"><?= e($lead['email']) ?></div>
              </td>
              <td><span style="font-size: 12px; font-weight: 600; color: var(--admin-teal);"><?= e($lead['service'] ?: 'General Scope') ?></span></td>
              <td>
                <span class="badge-status badge-<?= e($lead['status']) ?>">
                  <?= ucfirst(e($lead['status'])) ?>
                </span>
              </td>
              <td style="font-size: 12px; color: var(--admin-muted); white-space: nowrap;">
                <?= date('M d, H:i', strtotime($lead['submitted_at'])) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align: center; color: var(--admin-muted); padding: 36px;">
                No inquiries received yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Blog Articles -->
  <div class="admin-card" style="margin-bottom: 0;">
    <div class="card-header" style="padding: 16px 22px; border-bottom: 1px solid var(--admin-border-subtle); display: flex; align-items: center; justify-content: space-between;">
      <h2 class="card-title" style="font-size: 15px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="ri-article-line" style="color: var(--admin-navy);"></i> Recent Blog Articles
      </h2>
      <a href="<?= url('admin/posts/edit.php') ?>" class="btn-adm btn-adm-primary btn-adm-sm" style="font-size: 12px; padding: 5px 12px;">
        <i class="ri-add-line"></i> New Article
      </a>
    </div>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Article Title</th>
            <th>Category</th>
            <th>Status</th>
            <th>Views</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($recentPosts)): ?>
            <?php foreach ($recentPosts as $post): ?>
            <tr>
              <td>
                <a href="<?= url('admin/posts/edit.php?id=' . $post['id']) ?>" style="font-weight: 700; color: var(--admin-navy); text-decoration: none;">
                  <?= e(truncate($post['title'], 36)) ?>
                </a>
              </td>
              <td><span style="font-size: 12px; color: var(--admin-muted);"><?= e($post['category_name'] ?? 'General') ?></span></td>
              <td>
                <span class="badge-status badge-<?= e($post['status']) ?>">
                  <?= ucfirst(e($post['status'])) ?>
                </span>
              </td>
              <td><strong style="color: var(--admin-navy);"><?= (int)$post['views'] ?></strong></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" style="text-align: center; color: var(--admin-muted); padding: 36px;">
                No articles created yet.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include ROOT_PATH . '/admin/includes/footer.php'; ?>
