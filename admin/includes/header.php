<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';
Auth::requireAuth();

if (!headers_sent()) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("X-Robots-Tag: noindex, nofollow, noarchive, nosnippet", false);
}

$unreadLeadsCount = 0;
try {
    $unreadLeadsCount = Contact::countByStatus('unread');
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <!-- Search Engine Blocking for Admin Panel (Never verify or index in Search Console) -->
  <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp, notranslate, noimageindex">
  <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
  <meta name="bingbot" content="noindex, nofollow, noarchive, nosnippet">

  <title><?= e($adminTitle ?? 'Admin Dashboard') ?> — WORDORA Control Panel</title>

  <!-- Favicon -->
  <?php $siteFavicon = setting('site_favicon', '/img/logo.png'); ?>
  <link rel="icon" type="image/png" href="<?= media_url($siteFavicon) ?>">
  <link rel="apple-touch-icon" href="<?= media_url($siteFavicon) ?>">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- RemixIcon -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=<?= filemtime(ROOT_PATH . '/assets/css/admin.css') ?>">
</head>
<body>

<div class="admin-wrapper">
  <?php include ROOT_PATH . '/admin/includes/sidebar.php'; ?>

  <div class="admin-main">
    <header class="admin-topbar">
      <div class="topbar-left">
        <h1 class="page-title"><?= e($adminTitle ?? 'Dashboard') ?></h1>
      </div>
      <div class="topbar-right">
        <a href="<?= url('/') ?>" target="_blank" class="topbar-btn">
          <i class="ri-external-link-line"></i> View Live Site
        </a>
        <a href="<?= url('admin/logout.php') ?>" class="topbar-btn" style="color: #EF4444;">
          <i class="ri-logout-box-r-line"></i> Logout
        </a>
      </div>
    </header>

    <div class="admin-content">
      <?php
      $flash = flash_get();
      if ($flash): ?>
        <div style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; <?= $flash['type'] === 'success' ? 'background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0;' : 'background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA;' ?>">
          <i class="ri-<?= $flash['type'] === 'success' ? 'checkbox-circle-line' : 'error-warning-line' ?>" style="font-size: 16px;"></i>
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
