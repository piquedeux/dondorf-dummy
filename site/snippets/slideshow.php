<?php
/**
 * Slideshow Snippet
 * 
 * @param string $id - Slideshow ID (e.g., 'slideshow-1')
 * @param string $field - Field name from site (e.g., 'slideshow_1')
 * @param string $placeholder - Placeholder text (e.g., 'Slideshow 1')
 */

$slideshowId = $id ?? 'slideshow';
$fieldName = $field ?? 'slideshow';
$placeholderText = $placeholder ?? 'Slideshow';

$images = $site->$fieldName()->toStructure();
$imageCount = $images->isNotEmpty() ? $images->count() : 0;
?>

<div class="slideshow" id="<?= $slideshowId ?>">
  <div class="slideshow-wrapper">
    <div class="slideshow-track">
      <?php if ($imageCount > 0): ?>
        <?php foreach ($images as $index => $slide): ?>
          <?php if ($slideImage = $slide->image()->toFile()): ?>
          <?php
            $slideUrl = $slideImage->thumb(['width' => 1920, 'quality' => 82])->url();
            $isFirstSlide = $index === 0;
          ?>
          <div class="slide" data-slide-index="<?= $index ?>">
            <img
              src="<?= $isFirstSlide ? $slideUrl : 'data:image/gif;base64,R0lGODlhAQABAAAAACw=' ?>"
              data-src="<?= $isFirstSlide ? '' : $slideUrl ?>"
              alt="<?= $placeholderText ?> - Slide <?= intval($index) + 1 ?>"
              width="<?= $slideImage->width() ?>"
              height="<?= $slideImage->height() ?>"
              loading="<?= $isFirstSlide ? 'eager' : 'lazy' ?>"
              fetchpriority="<?= $isFirstSlide ? 'high' : 'low' ?>"
              decoding="async"
            >
          </div>
          <?php endif ?>
        <?php endforeach ?>
      <?php else: ?>
        <div class="slide" data-slide-index="0">
          <div class="placeholder"><?= $placeholderText ?></div>
        </div>
      <?php endif ?>
    </div>
  </div>
  <div class="slideshow-indicators">
    <?php 
    $count = $imageCount > 0 ? $imageCount : 1;
    for ($i = 0; $i < $count; $i++): 
    ?>
      <button class="indicator" data-slide="<?= $i ?>" <?= $i === 0 ? 'aria-current="true"' : '' ?>></button>
    <?php endfor ?>
  </div>
</div>
