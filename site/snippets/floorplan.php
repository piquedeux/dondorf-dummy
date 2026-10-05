<?php
  // Load rooms dynamically from backend
  $roomLabels = [];
  foreach (($site->rooms()->toStructure() ?? []) as $room) {
    $roomId = trim((string)$room->id());
    if ($roomId === '') {
      continue;
    }

    $roomLabels[$roomId] = [
      'de' => (string)$room->label_de()->or($room->id()),
      'en' => (string)$room->label_en()->or($room->label_de())->or($room->id()),
    ];
  }
  
  // Helper function to get room label with fallback
  function getRoomLabel($roomLabels, $roomId, $lang = 'de') {
    if (isset($roomLabels[$roomId][$lang])) {
      return $roomLabels[$roomId][$lang];
    }
    // Fallback: use room ID and make it readable
    return ucwords(str_replace('-', ' ', $roomId));
  }

  $floorplanDE = $site->heading_floorplan_de()->or('Lageplan');
  $floorplanEN = $site->heading_floorplan_en()->or('Floorplan');
  $floorplanChars = mb_strlen($floorplanDE);
?>
<section class="content-section floorplan-section" id="floorplan-section">
  <h2 class="section-heading" style="--char-count: <?= $floorplanChars ?>">
    <span class="lang-de"><?= $floorplanDE ?></span>
    <span class="lang-en" style="display: none"><?= $floorplanEN ?></span>
  </h2>

  <div class="floorplan-container">
    <div class="floorplan-grid">
      <div class="floorplan-rooms">
        <!-- Left side - Gemeinschaftsraum (160m²) -->
        <div class="room beratungsraum" data-room-id="gemeinschaftsraum" style="grid-column: 1 / 10; grid-row: 1 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'gemeinschaftsraum', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'gemeinschaftsraum', 'en') ?></span>
          </span>
          <span class="label-mobile">G</span>
          <br><span class="label-size" style="font-size: 0.5rem;">160m²</span>
        </div>
        
        <!-- Center top - Studio 08 (90,6m²) -->
        <div class="room studio" data-room-id="studio-08" style="grid-column: 10 / 16; grid-row: 1 / 3;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-08', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-08', 'en') ?></span>
          </span>
          <span class="label-mobile">S8</span>
          <br><span class="label-size" style="font-size: 0.5rem;">90,6m²</span>
        </div>
        
        <!-- Center - Verwaltung (23m²) -->
        <div class="room vorraum" data-room-id="verwaltung" style="grid-column: 10 / 11; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'verwaltung', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'verwaltung', 'en') ?></span>
          </span>
          <span class="label-mobile">V</span>
          <br><span class="label-size" style="font-size: 0.5rem;">23m²</span>
        </div>
        
        <!-- Right side top row - Studios 08, 07, 06 -->
        <div class="room studio" data-room-id="studio-07" style="grid-column: 16 / 17; grid-row: 1 / 3;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-07', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-07', 'en') ?></span>
          </span>
          <span class="label-mobile">S7</span>
          <br><span class="label-size" style="font-size: 0.5rem;">22,4m²</span>
        </div>
        <div class="room studio" data-room-id="studio-06" style="grid-column: 17 / 19; grid-row: 1 / 3;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-06', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-06', 'en') ?></span>
          </span>
          <span class="label-mobile">S6</span>
          <br><span class="label-size" style="font-size: 0.5rem;">84,3m²</span>
        </div>
        
        <!-- Right side bottom row - Studios 01-05 -->
        <div class="room studio" data-room-id="studio-01" style="grid-column: 11 / 13; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-01', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-01', 'en') ?></span>
          </span>
          <span class="label-mobile">S1</span>
          <br><span class="label-size" style="font-size: 0.5rem;">41,2m²</span>
        </div>
        <div class="room studio" data-room-id="studio-02" style="grid-column: 13 / 14; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-02', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-02', 'en') ?></span>
          </span>
          <span class="label-mobile">S2</span>
          <br><span class="label-size" style="font-size: 0.5rem;">20,6m²</span>
        </div>
        <div class="room studio" data-room-id="studio-03" style="grid-column: 14 / 15; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-03', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-03', 'en') ?></span>
          </span>
          <span class="label-mobile">S3</span>
          <br><span class="label-size" style="font-size: 0.5rem;">19,3m²</span>
        </div>
        <div class="room studio" data-room-id="studio-04" style="grid-column: 15 / 16; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-04', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-04', 'en') ?></span>
          </span>
          <span class="label-mobile">S4</span>
          <br><span class="label-size" style="font-size: 0.5rem;">18,2m²</span>
        </div>
        <div class="room studio" data-room-id="studio-05" style="grid-column: 16 / 19; grid-row: 3 / 8;">
          <span class="label-full">
            <span class="lang-de"><?= getRoomLabel($roomLabels, 'studio-05', 'de') ?></span>
            <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'studio-05', 'en') ?></span>
          </span>
          <span class="label-mobile">S5</span>
          <br><span class="label-size" style="font-size: 0.5rem;">27,9m²</span>
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="floorplan-legend">
      <div class="legend-item">
        <div class="legend-color beratungsraum"></div>
        <span>
          <span class="lang-de">Gemeinschaftsräume</span>
          <span class="lang-en" style="display: none">Community Spaces</span>
        </span>
      </div>
      <div class="legend-item">
        <div class="legend-color studio"></div>
        <span>Studios</span>
      </div>
      <div class="legend-item">
        <div class="legend-color vorraum"></div>
        <span>
          <span class="lang-de"><?= getRoomLabel($roomLabels, 'verwaltung', 'de') ?></span>
          <span class="lang-en" style="display: none"><?= getRoomLabel($roomLabels, 'verwaltung', 'en') ?></span>
        </span>
      </div>
    </div>
  </div>
</section>

<?php
  $lageplanDE = $site->heading_lageplan_de()->or('Lageplan');
  $lageplanEN = $site->heading_lageplan_en()->or('Location Plan');
  $lageplanChars = mb_strlen($lageplanDE);
  $lageplanImage = $site->lageplan_image()->toFile();
  $lageplanWegbeschreibungHtml = trim((string)$site->lageplan_wegbeschreibung()->value());
  $lageplanMapsEmbedHtml = trim((string)$site->lageplan_googlemaps_embed()->value());
?>

<?php if ($lageplanImage || $lageplanWegbeschreibungHtml !== '' || $lageplanMapsEmbedHtml !== ''): ?>
  <section class="content-section lageplan-section" id="lageplan-section">
    <h2 class="section-heading" style="--char-count: <?= $lageplanChars ?>">
      <span class="lang-de"><?= $lageplanDE ?></span>
      <span class="lang-en" style="display: none"><?= $lageplanEN ?></span>
    </h2>

    <div class="lageplan-container">
      <?php if ($lageplanImage): ?>
        <button class="lageplan-image-trigger" id="lageplanImageTrigger" type="button" aria-label="Lageplan in Lightbox öffnen">
          <img src="<?= $lageplanImage->url() ?>" alt="<?= $lageplanDE ?>" class="lageplan-image" loading="lazy">
        </button>
      <?php endif ?>

      <?php if ($lageplanWegbeschreibungHtml !== ''): ?>
        <div class="lageplan-wegbeschreibung"><?= $lageplanWegbeschreibungHtml ?></div>
      <?php endif ?>

      <?php if ($lageplanMapsEmbedHtml !== ''): ?>
        <div class="lageplan-maps-embed"><?= $lageplanMapsEmbedHtml ?></div>
      <?php endif ?>
    </div>
  </section>

  <?php if ($lageplanImage): ?>
    <div class="lageplan-lightbox" id="lageplanLightbox" aria-hidden="true">
      <div class="lageplan-lightbox-inner" role="dialog" aria-modal="true" aria-label="Lageplan Lightbox">
        <button class="lageplan-lightbox-close" id="lageplanLightboxClose" type="button" aria-label="Schließen">×</button>
        <div class="lageplan-lightbox-stage" id="lageplanLightboxStage">
          <img src="<?= $lageplanImage->url() ?>" alt="<?= $lageplanDE ?>" class="lageplan-lightbox-image" id="lageplanLightboxImage">
        </div>
        <div class="lageplan-lightbox-controls">
          <button type="button" id="lageplanZoomOut" aria-label="Verkleinern">−</button>
          <button type="button" id="lageplanZoomIn" aria-label="Vergrößern">+</button>
        </div>
      </div>
    </div>
  <?php endif ?>
<?php endif ?>