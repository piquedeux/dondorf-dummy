<?php
// Ensure required heading variables exist when snippet is included without params
use Kirby\Toolkit\Str;

$initiativesDE = $initiativesDE ?? $site->heading_initiatives_de()->or('Kollektive');
$initiativesEN = $initiativesEN ?? $site->heading_initiatives_en()->or('Collectives');
$initiativesChars = $initiativesChars ?? mb_strlen($initiativesDE);

// Load rooms dynamically from backend
$studioNames = [];
foreach (($site->rooms()->toStructure() ?? []) as $room) {
  $roomId = trim((string)$room->id());
  if ($roomId === '') {
    continue;
  }

  $studioNames[$roomId] = [
    'de' => (string)$room->label_de()->or($room->id()),
    'en' => (string)$room->label_en()->or($room->label_de())->or($room->id()),
  ];
}
?>
<section class="content-section" id="initiatives-section">
    <h2 class="section-heading" style="--char-count: <?= $initiativesChars ?>">
      <span class="lang-de"><?= $initiativesDE ?></span>
      <span class="lang-en" style="display: none"><?= $initiativesEN ?></span>
    </h2>
    <div id="collectives-filters" class="calendar-filters"></div>
    
    <div class="collectives-grid">
      <?php foreach($site->initiativen()->toStructure() as $index => $collective): ?>
        <?php
          $tagsDe = trim($collective->tags()->or('')->value());
          $tagsEn = trim($collective->tags_en()->or($collective->tags())->value());
          $studioValue = $collective->studio()->value();
          $studioLabelDe = isset($studioNames[$studioValue]) ? $studioNames[$studioValue]['de'] : strtoupper(str_replace('-', ' ', $studioValue));
          $studioLabelEn = isset($studioNames[$studioValue]) ? $studioNames[$studioValue]['en'] : strtoupper(str_replace('-', ' ', $studioValue));
          $collectiveSlug = Str::slug($collective->title_de()->value(), '-');
        ?>
        <article id="collective-<?= $collectiveSlug ?>" class="collective-card" data-collective-id="collective-<?= $index ?>" data-collective-slug="<?= $collectiveSlug ?>" data-studio="<?= $studioValue ?>" data-room-de="<?= htmlspecialchars($studioLabelDe, ENT_QUOTES, 'UTF-8') ?>" data-room-en="<?= htmlspecialchars($studioLabelEn, ENT_QUOTES, 'UTF-8') ?>" data-tags-de="<?= htmlspecialchars($tagsDe, ENT_QUOTES, 'UTF-8') ?>" data-tags-en="<?= htmlspecialchars($tagsEn, ENT_QUOTES, 'UTF-8') ?>">
          <div class="collective-header">
            <div class="collective-title-group">
              <h3 class="collective-title">
                <span class="lang-de"><?= $collective->title_de() ?></span>
                <span class="lang-en" style="display: none"><?= $collective->title_en()->or($collective->title_de()) ?></span>
              </h3>
              <?php if ($collective->subtitle_de()->isNotEmpty() || $collective->subtitle_en()->isNotEmpty()): ?>
                <p class="collective-subtitle">
                  <span class="lang-de"><?= $collective->subtitle_de() ?></span>
                  <span class="lang-en" style="display: none"><?= $collective->subtitle_en()->or($collective->subtitle_de()) ?></span>
                </p>
              <?php endif ?>

              <?php if ($collective->tags()->isNotEmpty() || $collective->tags_en()->isNotEmpty()): ?>
                <div class="collective-tags">
                  <span class="lang-de">
                    <?php foreach(array_map('trim', explode(',', $collective->tags()->value())) as $tag): if(!$tag) continue; ?>
                      <span class="event-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach ?>
                  </span>
                  <span class="lang-en" style="display: none">
                    <?php foreach(array_map('trim', explode(',', $collective->tags_en()->or($collective->tags())->value())) as $tag): if(!$tag) continue; ?>
                      <span class="event-tag"><?= htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach ?>
                  </span>
                </div>
              <?php endif ?>
            </div>
            <button class="collective-toggle" aria-label="Toggle details">+</button>
          </div>

          <div class="collective-content">
            <!-- Gallery -->
            <?php if ($collective->images()->isNotEmpty()): ?>
              <div class="collective-gallery">
                <div class="collective-gallery-scroll">
                  <?php foreach($collective->images()->toFiles() as $image): ?>
                    <img src="<?= $image->url() ?>" alt="<?= $image->alt() ?>" class="collective-gallery-image" loading="lazy" decoding="async">
                  <?php endforeach ?>
                </div>
              </div>
            <?php endif ?>

            <!-- Details Grid -->
            <div class="collective-details">
              <?php if ($collective->studio()->isNotEmpty() && $collective->studio()->value() != 'none'): ?>
                <div class="collective-detail-item">
                  <span class="collective-detail-label">
                    <span class="lang-de">Raum:</span>
                    <span class="lang-en" style="display: none">Room:</span>
                  </span>
                  <span class="collective-detail-value">
                    <a href="#floorplan-section" class="collective-studio-link" data-studio="<?= $collective->studio()->value() ?>">
                      <span class="lang-de"><?= $studioLabelDe ?></span>
                      <span class="lang-en" style="display: none"><?= $studioLabelEn ?></span>
                    </a>
                  </span>
                </div>
              <?php endif ?>

              <?php if ($collective->oeffnungszeiten()->isNotEmpty()): ?>
                <div class="collective-detail-item">
                  <span class="collective-detail-label">
                    <span class="lang-de">Öffnungszeiten:</span>
                    <span class="lang-en" style="display: none">Opening Hours:</span>
                  </span>
                  <ul class="collective-hours-list">
                    <?php foreach($collective->oeffnungszeiten()->toStructure() as $hours): ?>
                      <li>
                        <span class="lang-de"><?= $hours->tag() ?>:</span>
                        <span class="lang-en" style="display: none"><?= $hours->tag_en()->or($hours->tag()) ?>:</span>
                        <?= $hours->zeiten() ?>
                      </li>
                    <?php endforeach ?>
                  </ul>
                </div>
              <?php endif ?>

              <?php if ($collective->webseiten()->isNotEmpty() || $collective->webseite()->isNotEmpty()): ?>
                <div class="collective-detail-item">
                  <span class="collective-detail-label">
                    <span class="lang-de">Website:</span>
                    <span class="lang-en" style="display: none">Website:</span>
                  </span>
                  <span class="collective-detail-value">
                    <?php if ($collective->webseiten()->isNotEmpty()): ?>
                      <?php $websiteLinks = []; ?>
                      <?php foreach ($collective->webseiten()->toStructure() as $website): ?>
                        <?php if ($website->url()->isNotEmpty()): ?>
                          <?php
                            $websiteUrl = $website->url()->value();
                            $websiteLabel = $website->label()->or($websiteUrl)->value();
                            $websiteLinks[] = '<a href="' . $websiteUrl . '" target="_blank" rel="noopener">' . $websiteLabel . '</a>';
                          ?>
                        <?php endif ?>
                      <?php endforeach ?>
                      <?= implode(' · ', $websiteLinks) ?>
                    <?php else: ?>
                      <a href="<?= $collective->webseite() ?>" target="_blank" rel="noopener"><?= $collective->webseite() ?></a>
                    <?php endif ?>
                  </span>
                </div>
              <?php endif ?>

              <?php if ($collective->email()->isNotEmpty()): ?>
                <div class="collective-detail-item">
                  <span class="collective-detail-label">
                    <span class="lang-de">Kontakt:</span>
                    <span class="lang-en" style="display: none">Contact:</span>
                  </span>
                  <span class="collective-detail-value">
                    <a href="mailto:<?= $collective->email() ?>"><?= $collective->email() ?></a>
                  </span>
                </div>
              <?php endif ?>

              <?php if ($collective->social_1_url()->isNotEmpty() || $collective->social_2_url()->isNotEmpty() || $collective->social_3_url()->isNotEmpty()): ?>
                <div class="collective-detail-item collective-socials">
                  <span class="collective-detail-label">
                    <span class="lang-de">Social</span>
                    <span class="lang-en" style="display: none">Social</span>
                  </span>
                  <span class="collective-detail-value">
                    <?php if ($collective->social_1_url()->isNotEmpty()): ?>
                      <a href="<?= $collective->social_1_url() ?>" target="_blank" rel="noopener"><?= $collective->social_1_label()->or('Link') ?></a>
                    <?php endif ?>
                    <?php if ($collective->social_2_url()->isNotEmpty()): ?>
                      &nbsp;<a href="<?= $collective->social_2_url() ?>" target="_blank" rel="noopener"><?= $collective->social_2_label()->or('Link') ?></a>
                    <?php endif ?>
                    <?php if ($collective->social_3_url()->isNotEmpty()): ?>
                      &nbsp;<a href="<?= $collective->social_3_url() ?>" target="_blank" rel="noopener"><?= $collective->social_3_label()->or('Link') ?></a>
                    <?php endif ?>
                  </span>
                </div>
              <?php endif ?>
            </div>

            <!-- Description -->
            <?php if ($collective->description_de()->isNotEmpty() || $collective->description_en()->isNotEmpty()): ?>
              <div class="collective-description">
                <div class="lang-de"><?= $collective->description_de()->kt() ?></div>
                <div class="lang-en" style="display: none"><?= $collective->description_en()->or($collective->description_de())->kt() ?></div>
              </div>
            <?php endif ?>
          </div>
        </article>
      <?php endforeach ?>
    </div>
  </section>