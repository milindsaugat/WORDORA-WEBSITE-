<?php
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/core/helpers.php';

$meta = $meta ?? [];

// Automatically Detect Route Key for Page-Specific SEO Configuration
$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');

$hasExplicitMeta = !empty($meta['title']);

if ($hasExplicitMeta) {
    $pageTitle    = $meta['title'];
    $pageDesc     = $meta['description'] ?? '';
    $pageKeywords = $meta['keywords'] ?? '';
    $ogImage      = $meta['og_image'] ?? '/img/wordorga logo.png';
} else {
    $seoKey = 'home';
    if (preg_match('#/(?:who-we-are|who_we_are)(?:\.php|/|$)#i', $currentUri) || $scriptName === 'who-we-are.php') {
        $seoKey = 'who_we_are';
    } elseif (preg_match('#/services(?:\.php|/|$)#i', $currentUri) || $scriptName === 'services.php') {
        $seoKey = 'services';
    } elseif (preg_match('#/case-studies(?:\.php|/|$)#i', $currentUri) || $scriptName === 'case-studies.php') {
        $seoKey = 'case_studies';
    } elseif (preg_match('#/blog(?:\.php|/|$)#i', $currentUri) || $scriptName === 'blog') {
        $seoKey = 'blog';
    } elseif (preg_match('#/careers(?:\.php|/|$)#i', $currentUri) || $scriptName === 'careers.php') {
        $seoKey = 'careers';
    } elseif (preg_match('#/contact(?:\.php|/|$)#i', $currentUri) || $scriptName === 'contact.php') {
        $seoKey = 'contact';
    }

    $dbTitle = setting("seo_{$seoKey}_title");
    $dbDesc  = setting("seo_{$seoKey}_desc");
    $dbKw    = setting("seo_{$seoKey}_keywords");
    $dbOg    = setting("seo_{$seoKey}_og_image");

    $pageTitle    = !empty($dbTitle) ? $dbTitle : (!empty($meta['title']) ? $meta['title'] : 'WORDORA — Words That Work. Stories That Sell.');
    $pageDesc     = !empty($dbDesc) ? $dbDesc : (!empty($meta['description']) ? $meta['description'] : 'Professional content writing and editorial services that convert readers into clients.');
    $pageKeywords = !empty($dbKw) ? $dbKw : (!empty($meta['keywords']) ? $meta['keywords'] : 'content writing agency, SEO content strategy, enterprise ghostwriting, B2B copywriting');
    $ogImage      = !empty($dbOg) ? $dbOg : (!empty($meta['og_image']) ? $meta['og_image'] : '/img/wordorga logo.png');
}

$ogTitle = $meta['og_title'] ?? $pageTitle;
$ogDesc  = $meta['og_desc'] ?? $pageDesc;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-M8R75ZP7');</script>
  <!-- End Google Tag Manager -->

  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-602CB9Y6GP"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-602CB9Y6GP');
  </script>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDesc) ?>">
  <?php if (!empty($pageKeywords)): ?>
    <meta name="keywords" content="<?= e($pageKeywords) ?>">
  <?php endif; ?>

  <!-- Open Graph / Social Media -->
  <meta property="og:title" content="<?= e($ogTitle) ?>">
  <meta property="og:description" content="<?= e($ogDesc) ?>">
  <meta property="og:image" content="<?= e(str_starts_with($ogImage, 'http') ? $ogImage : base_url($ogImage)) ?>">
  <meta property="og:type" content="website">
  <link rel="canonical" href="<?= e(base_url($_SERVER['REQUEST_URI'] ?? '')) ?>">

  <!-- Twitter Cards -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($ogTitle) ?>">
  <meta name="twitter:description" content="<?= e($ogDesc) ?>">
  <meta name="twitter:image" content="<?= e(str_starts_with($ogImage, 'http') ? $ogImage : base_url($ogImage)) ?>">
  <?= CSRF::meta() ?>
  <meta name="api-contact-url" content="<?= url('api/contact.php') ?>">
  <meta name="api-subscribe-url" content="<?= url('api/subscribe.php') ?>">
  <meta name="api-search-url" content="<?= url('api/blog-search.php') ?>">

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Fredoka:wght@700;800;900&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Manrope:wght@600;700;800&family=Montserrat:wght@800;900&family=Nunito:wght@800;900&family=Outfit:wght@700;800;900&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,600;1,700&family=Plus+Jakarta+Sans:wght@800;900&family=Poppins:wght@700;800;900&family=Syne:wght@700;800&display=swap" rel="stylesheet">

  <!-- Favicon -->
  <?php $siteFavicon = setting('site_favicon', '/img/logo.png'); ?>
  <link rel="icon" type="image/png" href="<?= media_url($siteFavicon) ?>">
  <link rel="apple-touch-icon" href="<?= media_url($siteFavicon) ?>">

  <!-- Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">

  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

  <!-- Main CSS -->
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>?v=<?= file_exists(ROOT_PATH . '/assets/css/main.css') ? filemtime(ROOT_PATH . '/assets/css/main.css') : time() ?>">
</head>
<body>
  <!-- Google Tag Manager (noscript) -->
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M8R75ZP7"
  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <!-- End Google Tag Manager (noscript) -->

  <?php include ROOT_PATH . '/views/partials/nav.php'; ?>
  <?php include ROOT_PATH . '/views/partials/mobile-drawer.php'; ?>

  <main>
    <?= $content ?? '' ?>
  </main>

  <?php include ROOT_PATH . '/views/partials/footer.php'; ?>

  <!-- GSAP -->
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js" defer></script>

  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

  <!-- Main JS -->
  <script src="<?= asset('js/main.js') ?>?v=<?= file_exists(ROOT_PATH . '/assets/js/main.js') ? filemtime(ROOT_PATH . '/assets/js/main.js') : time() ?>" defer></script>
</body>
</html>
