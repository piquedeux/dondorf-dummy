<?php
// Newsletter popup snippet — content configurable in site blueprints
$title = $site->newsletter_title_de()->or('Newsletter');
$text = $site->newsletter_text_de()->or('Abonniere unseren Newsletter');
?>
<div class="newsletter-popup" role="dialog" aria-hidden="false">
  <button class="close" aria-label="Schließen">✕</button>
  <h4><?= $title ?></h4>
  <p><?= $text ?></p>
  <form action="/" method="post">
    <input type="email" name="email" placeholder="E‑Mail" required>
    <button type="submit">Abonnieren</button>
  </form>
  <div class="result" aria-live="polite"></div>
</div>

<?= js(['assets/js/newsletter.js']) ?>
