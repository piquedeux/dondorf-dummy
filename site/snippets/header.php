<!--
 __  __       ____  
|  \/  |     / ___| 
| \  / |    | | __  
| |\/| |    | |(  | 
| |  | |  _ | |_) |  _ 
(_)  (_) (_) \____| (_)
© moritzgauss.com 
-->
<!DOCTYPE html>
<html lang="<?= $kirby->language() ? $kirby->language()->code() : 'de' ?>">
<head>
  <?php
    $metaTitle = $site->meta_title()->isNotEmpty() ? $site->meta_title()->value() : $site->title()->value();
    $metaDescription = $site->meta_description()->value();
    $metaKeywords = $site->meta_keywords()->value();
    $canonicalUrl = $page->url();
    $ogImage = $site->og_image()->toFile();
  ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= esc($metaTitle) ?></title>
  <?php if (!empty($metaDescription)): ?>
    <meta name="description" content="<?= esc($metaDescription) ?>">
  <?php endif ?>
  <?php if (!empty($metaKeywords)): ?>
    <meta name="keywords" content="<?= esc($metaKeywords) ?>">
  <?php endif ?>
  <meta name="robots" content="index,follow">
  <link rel="canonical" href="<?= esc($canonicalUrl) ?>">
  <!-- Google Search Console verification (add your own token) -->
  <meta name="google-site-verification" content="" />
  <meta property="og:title" content="<?= esc($metaTitle) ?>">
  <?php if (!empty($metaDescription)): ?>
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
  <?php endif ?>
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= esc($canonicalUrl) ?>">
  <?php if ($ogImage): ?>
    <meta property="og:image" content="<?= esc($ogImage->url()) ?>">
  <?php endif ?>
  <meta name="twitter:card" content="summary_large_image">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
  <?= css(['assets/css/style.css']) ?>
  <?= css(['assets/css/header.css']) ?>
  <?= css(['assets/css/booking.css']) ?>
  <?= css(['assets/css/newsletter.css']) ?>
  <?= css(['assets/css/footer.css']) ?>
  <?= css(['assets/css/menu.css']) ?>
  <?= css(['assets/css/cookie-banner.css']) ?>
  <?= css(['assets/css/calendar.css']) ?>
  <?= css(['assets/css/floorplan.css']) ?>
  <?= css(['assets/css/collectives.css']) ?>
  <?= css(['assets/css/event-modal.css']) ?>
  <?= css(['assets/css/info-section.css']) ?>
  <?= css(['assets/css/slideshow.css']) ?>
  <link rel="icon" type="image/png" href="assets/icons/favicon.png">
</head>
<body>

  <header class="header">
    <a href="#welcome-section" class="logo-container" id="headerLogoLink">
      <?php if($logomark = $site->logomark()->toFile()): ?>
        <img src="<?= $logomark->url() ?>" alt="<?= $site->title()->esc() ?>" class="logo logo-main" id="mainLogo">
      <?php endif ?>
      <?php if($logomarkColor = $site->logomark_colored()->toFile()): ?>
        <img src="<?= $logomarkColor->url() ?>" alt="<?= $site->title()->esc() ?>" class="logo logo-main logo-colored" id="mainLogoColor" style="display: none;">
      <?php endif ?>
      <?php if($wordmark = $site->logo_wordmark()->toFile()): ?>
        <img src="<?= $wordmark->url() ?>" alt="<?= $site->title()->esc() ?>" class="logo logo-wordmark" id="logoWordmark" style="display: none;">
      <?php endif ?>
      <?php if($wordmarkColor = $site->logo_wordmark_colored()->toFile()): ?>
        <img src="<?= $wordmarkColor->url() ?>" alt="<?= $site->title()->esc() ?>" class="logo logo-wordmark logo-colored" id="logoWordmarkColored" style="display: none;">
      <?php endif ?>
    </a>
    
    <div class="header-controls">
      <button class="lang-switch" id="langSwitch">DE</button>
      <div class="theme-switch" id="themeSwitch"></div>
    </div>
  </header>



