<?php snippet('header') ?>

<?php
$page->trackHit();
?>

<main class="main-content">
  
  <section class="welcome-section" id="welcome-section">
    <?php
      $welcomeDE = $site->heading_welcome_de()->or('Willkommen');
      $welcomeEN = $site->heading_welcome_en()->exists() ? $site->heading_welcome_en() : 'Welcome';
      $welcomeChars = mb_strlen($welcomeDE);
    ?>
    <h1 style="--char-count: <?= $welcomeChars ?>">
      <h1 class="lang-de"><?= $welcomeDE ?></h1>
      <h1 class="lang-en" style="display: none"><?= $welcomeEN ?></h1>
    </h1>
    <?php if ($site->welcome_subtitle_de()->isNotEmpty() || $site->welcome_subtitle_en()->isNotEmpty()): ?>
      <h3 class="welcome-subtitle">
        <span class="lang-de"><?= $site->welcome_subtitle_de() ?></span>
        <span class="lang-en" style="display: none"><?= $site->welcome_subtitle_en()->or($site->welcome_subtitle_de()) ?></span>
      </h3>
    <?php endif ?>

   
    <div class="slideshows-container">
      <?php snippet('slideshow', ['id' => 'slideshow', 'field' => 'slideshow', 'placeholder' => 'Slideshow']) ?>
    </div>

    <!-- Collapsible Aufmacher Text -->
    <?php
      $aufmacherTextDE = $site->aufmacher_text_de();
      $aufmacherTextEN = $site->aufmacher_text_en();
      $aufmacherToggleDE = $site->aufmacher_toggle_de()->or('Entdecke mehr');
      $aufmacherToggleEN = $site->aufmacher_toggle_en()->or('Discover more');
    ?>
    <?php if ($aufmacherTextDE->isNotEmpty() || $aufmacherTextEN->isNotEmpty()): ?>
      <div class="aufmacher-text-section">
        <button class="aufmacher-toggle" aria-expanded="false">
          <span class="lang-de"><?= $aufmacherToggleDE ?></span>
          <span class="lang-en" style="display: none"><?= $aufmacherToggleEN ?></span>
          <span class="toggle-icon">+</span>
        </button>
        <div class="aufmacher-text-content">
          <div class="lang-de">
            <?= $aufmacherTextDE->kt() ?>
          </div>
          <div class="lang-en" style="display: none">
            <?= $aufmacherTextEN->isNotEmpty() ? $aufmacherTextEN->kt() : $aufmacherTextDE->kt() ?>
          </div>
        </div>
      </div>
    <?php endif ?>
  
  </section>
  
  <?php snippet('calendar') ?>

  <?php snippet('collectives') ?>

  <?php snippet('floorplan') ?>

  <?php snippet('info-section') ?>

  <?php snippet('booking-form') ?>

  <?php
    $contactDE = 'Kontakt';
    $contactEN = 'Contact';
    $contactChars = mb_strlen($contactDE);
  ?>
  <section class="content-section" id="contact-section">
    <h2 class="section-heading" style="--char-count: <?= $contactChars ?>">
      <span class="lang-de"><?= $contactDE ?></span>
      <span class="lang-en" style="display: none"><?= $contactEN ?></span>
    </h2>
    <div class="cards-container">
      <?php foreach($site->contacts()->toStructure() as $contact): ?>
        <article class="text-card">
          <h3>
            <span class="lang-de"><?= $contact->name() ?></span>
            <span class="lang-en" style="display: none;"><?= method_exists($contact, 'name_en') && $contact->name_en()->isNotEmpty() ? $contact->name_en() : $contact->name() ?></span>
          </h3>
          <?php if ($contact->role()->isNotEmpty() || (method_exists($contact, 'role_en') && $contact->role_en()->isNotEmpty())): ?>
            <p>
              <span class="lang-de"><?= $contact->role() ?></span>
              <span class="lang-en" style="display: none;"><?= method_exists($contact, 'role_en') && $contact->role_en()->isNotEmpty() ? $contact->role_en() : $contact->role() ?></span>
            </p>
          <?php endif ?>
          <?php if ($contact->email()->isNotEmpty()): ?>
            <p><a href="mailto:<?= $contact->email() ?>"><?= $contact->email() ?></a></p>
          <?php endif ?>
          <?php if ($contact->link()->isNotEmpty()): ?>
            <p>
              <a href="<?= $contact->link() ?>" target="_blank" rel="noopener">
                <?= $contact->link_label()->or($contact->link()) ?>
              </a>
            </p>
          <?php endif ?>
          <?php if ($contact->phone()->isNotEmpty()): ?>
            <p><?= $contact->phone() ?></p>
          <?php endif ?>
        </article>
      <?php endforeach ?>
    </div>
  </section>


 </main>
<?php snippet('menu-overlay') ?>
<?php snippet('cookie-banner') ?>

<?php snippet('footer') ?>
