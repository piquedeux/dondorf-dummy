<?php
// Newsletter form snippet using Kirby Uniform plugin
// Expects $newsletterForm variable from controller
?>

<?php if ($newsletterForm->hasErrors()): ?>
  <div class="newsletter-errors" role="alert">
    <?php snippet('uniform/errors', ['form' => $newsletterForm]) ?>
  </div>
<?php endif ?>

<form class="newsletter-form" method="POST">
  <label for="newsletter-email">
    <span class="lang-de">Newsletter abonnieren</span>
    <span class="lang-en" style="display: none">Subscribe to newsletter</span>
  </label>
  <div class="newsletter-input-group">
    <input 
      id="newsletter-email" 
      type="email" 
      name="newsletter_email" 
      value="<?= $newsletterForm->old('newsletter_email') ?>" 
      placeholder="E-Mail"
      required
    >
    <button type="submit" name="newsletter_submit" value="1">
      <span class="lang-de">Abonnieren</span>
      <span class="lang-en" style="display: none">Subscribe</span>
    </button>
  </div>
  
  <?= csrf_field() ?>
  <?= honeypot_field() ?>
</form>

<?php if ($newsletterForm->success()): ?>
  <script>
    (function() {
      const popup = document.createElement('div');
      popup.className = 'success-popup';
      popup.innerHTML = '<p>Thank you!</p>';
      document.body.appendChild(popup);
      
      setTimeout(() => popup.classList.add('show'), 10);
      setTimeout(() => {
        popup.classList.remove('show');
        setTimeout(() => popup.remove(), 300);
        document.getElementById('newsletter-form').reset();
      }, 3000);
    })();
  </script>
<?php endif ?>