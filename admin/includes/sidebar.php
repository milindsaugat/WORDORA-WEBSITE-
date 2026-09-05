<?php
$currUri = $_SERVER['REQUEST_URI'] ?? '';
if (!function_exists('is_adm_active')) {
    function is_adm_active(string $path): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_contains($uri, $path) ? 'active' : '';
    }
}
?>
<aside class="admin-sidebar">
  <div class="sidebar-brand">
    <div class="brand-logo-wrap">
      <img src="<?= img('wordorga logo.png') ?>" alt="WORDORA">
    </div>
    <span class="brand-badge">Control Panel</span>
  </div>

  <nav class="sidebar-nav">

    <!-- ── Overview ── -->
    <span class="nav-label">Overview</span>
    <a href="<?= url('admin/index.php') ?>" class="sidebar-link <?= ($currUri === url('admin/index.php') || $currUri === url('admin/') || $currUri === url('admin')) ? 'active' : '' ?>">
      <i class="ri-dashboard-line"></i> Dashboard
    </a>

    <!-- ── Page Builder ── -->
    <span class="nav-label">Page Builder</span>
    <a href="<?= url('admin/pages/home.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/home') || is_adm_active('/admin/hero') ?>">
      <i class="ri-home-4-line"></i> Home Page
    </a>
    <a href="<?= url('admin/pages/who-we-are.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/who-we-are') ?>">
      <i class="ri-team-line"></i> Who We Are
    </a>
    <a href="<?= url('admin/pages/services.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/services') ?>">
      <i class="ri-briefcase-4-line"></i> Services
    </a>
    <a href="<?= url('admin/pages/case-studies.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/case-studies') ?>">
      <i class="ri-folder-shield-2-line"></i> Case Studies
    </a>
    <a href="<?= url('admin/pages/contact.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/contact') && !str_contains($currUri, 'tab=leads') ? 'active' : '' ?>">
      <i class="ri-contacts-book-2-line"></i> Contact Page
    </a>
    <a href="<?= url('admin/pages/careers.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/careers') && !str_contains($currUri, 'tab=applications') ? 'active' : '' ?>">
      <i class="ri-user-search-line"></i> Careers Page
    </a>

    <!-- ── Content Studio ── -->
    <span class="nav-label">Content Studio</span>
    <a href="<?= url('admin/posts/index.php') ?>" class="sidebar-link <?= is_adm_active('/admin/posts') ?>">
      <i class="ri-quill-pen-line"></i> Blog Articles
    </a>
    <a href="<?= url('admin/pages/blog.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/blog') && !str_contains($currUri, 'tab=articles') ? 'active' : '' ?>">
      <i class="ri-article-line"></i> Blog Studio
    </a>
    <a href="<?= url('admin/services/index.php') ?>" class="sidebar-link <?= is_adm_active('/admin/services') ?>">
      <i class="ri-file-list-3-line"></i> Service Details
    </a>
    <a href="<?= url('admin/pages/case-studies.php') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/case-studies') ?>">
      <i class="ri-folder-shield-2-line"></i> Case Studies
    </a>

    <!-- ── Inquiries & Leads ── -->
    <span class="nav-label">Inquiries</span>
    <?php
    $unreadLeadsCount = 0;
    try {
        $unreadLeadsCount = (int)DB::getInstance()->query("SELECT COUNT(*) FROM contacts WHERE status = 'unread' OR status IS NULL OR status = ''")->fetchColumn();
    } catch (\Throwable $t) {}
    ?>
    <a href="<?= url('admin/pages/contact.php?tab=leads') ?>" class="sidebar-link <?= (is_adm_active('/admin/leads') || (is_adm_active('/admin/pages/contact') && str_contains($currUri, 'tab=leads'))) ? 'active' : '' ?>">
      <i class="ri-mail-line"></i> Contact Leads
      <?php if (!empty($unreadLeadsCount) && $unreadLeadsCount > 0): ?>
        <span class="sidebar-badge" style="background: #D97706;"><?= (int)$unreadLeadsCount ?></span>
      <?php endif; ?>
    </a>
    <?php
    $pendingApps = 0;
    try {
        $pendingApps = (int)DB::getInstance()->query("SELECT COUNT(*) FROM job_applications WHERE status = 'pending' OR status IS NULL OR status = ''")->fetchColumn();
    } catch (\Throwable $t) {}
    ?>
    <a href="<?= url('admin/pages/careers.php?tab=applications') ?>" class="sidebar-link <?= is_adm_active('/admin/pages/careers') && str_contains($currUri, 'tab=applications') ? 'active' : '' ?>">
      <i class="ri-user-shared-line"></i> Applications
      <?php if ($pendingApps > 0): ?>
        <span class="sidebar-badge"><?= (int)$pendingApps ?></span>
      <?php endif; ?>
    </a>

    <!-- ── Configuration ── -->
    <span class="nav-label">Configuration</span>
    <a href="<?= url('admin/settings/seo.php') ?>" class="sidebar-link <?= is_adm_active('/admin/settings/seo') ?>">
      <i class="ri-search-eye-line"></i> SEO & Meta
    </a>
    <a href="<?= url('admin/settings/index.php') ?>" class="sidebar-link <?= is_adm_active('/admin/settings') && !is_adm_active('/admin/settings/seo') ? 'active' : '' ?>">
      <i class="ri-settings-4-line"></i> Site Settings
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="user-avatar">
        <?= strtoupper(substr(Auth::user('name') ?: 'A', 0, 1)) ?>
      </div>
      <div class="user-info">
        <div class="user-name"><?= e(Auth::user('name') ?: 'Admin') ?></div>
        <div class="user-role"><?= e(ucfirst(Auth::user('role') ?: 'Admin')) ?></div>
      </div>
    </div>
    <a href="<?= url('admin/logout.php') ?>" title="Sign Out" class="sidebar-logout-btn">
      <i class="ri-logout-box-r-line"></i>
    </a>
  </div>
</aside>
