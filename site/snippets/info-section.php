<section class="info-section" id="info-section">
    <?php
      $infoDE = $site->heading_info_de()->or('Info');
      $infoEN = $site->heading_info_en()->exists() ? $site->heading_info_en() : 'Info';
      $infoChars = mb_strlen($infoDE);
    ?>
    <h2 class="section-heading" style="--char-count: <?= $infoChars ?>">
      <span class="lang-de"><?= $infoDE ?></span>
      <span class="lang-en" style="display: none"><?= $infoEN ?></span>
    </h2>
  <?php if ($site->dondorf_info_de()->isNotEmpty() || $site->dondorf_info_en()->isNotEmpty()): ?>
  <div class="dondorf-info info-content" id="info-content">
    <div class="lang-de">
      <?php 
        $infoDEText = $site->dondorf_info_de()->kt();
        // Split text into paragraphs for stagger animation
        $paragraphsDE = explode('</p>', $infoDEText);
        foreach ($paragraphsDE as $index => $paragraph) {
          if (trim(strip_tags($paragraph))) {
            echo '<div class="info-item" style="--item-index: ' . $index . '">' . $paragraph . '</p></div>';
          }
        }
      ?>
    </div>
    <div class="lang-en" style="display: none">
      <?php 
        $infoENText = $site->dondorf_info_en()->exists() ? $site->dondorf_info_en()->kt() : $site->dondorf_info_de()->kt();
        // Split text into paragraphs for stagger animation
        $paragraphsEN = explode('</p>', $infoENText);
        foreach ($paragraphsEN as $index => $paragraph) {
          if (trim(strip_tags($paragraph))) {
            echo '<div class="info-item" style="--item-index: ' . $index . '">' . $paragraph . '</p></div>';
          }
        }
      ?>
    </div>
  </div>
<?php endif ?>
</section>
