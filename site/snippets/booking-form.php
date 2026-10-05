<?php
// Booking form snippet using Kirby Uniform plugin
// Expects $bookingForm variable from controller
?>
<div class="booking-form-container">
  <h3>
    <span class="lang-de">Veranstaltung anfragen</span>
    <span class="lang-en" style="display: none">Event request</span>
  </h3>

  <?php if ($bookingForm->hasErrors()): ?>
    <div class="booking-errors" role="alert">
      <?php snippet('uniform/errors', ['form' => $bookingForm]) ?>
    </div>
  <?php endif ?>

  <form id="booking-form" class="booking-form" method="POST">
    <label for="bf-name">
      <span class="lang-de">Name <span aria-hidden="true">*</span></span>
      <span class="lang-en" style="display: none">Name <span aria-hidden="true">*</span></span>
      <input id="bf-name" type="text" name="name" value="<?= $bookingForm->old('name') ?>" required>
    </label>
    
    <label for="bf-email">
      <span class="lang-de">E‑Mail <span aria-hidden="true">*</span></span>
      <span class="lang-en" style="display: none">Email <span aria-hidden="true">*</span></span>
      <input id="bf-email" type="email" name="email" value="<?= $bookingForm->old('email') ?>" required>
    </label>
    
    <label for="bf-phone">
      <span class="lang-de">Telefon</span>
      <span class="lang-en" style="display: none">Phone</span>
      <input id="bf-phone" type="text" name="phone" value="<?= $bookingForm->old('phone') ?>">
    </label>
    
    <label for="bf-date">
      <span class="lang-de">Datum <span aria-hidden="true">*</span></span>
      <span class="lang-en" style="display: none">Date <span aria-hidden="true">*</span></span>
      <input id="bf-date" type="date" name="date" value="<?= $bookingForm->old('date') ?>" required>
    </label>
    
    <label for="bf-guests">
      <span class="lang-de">Anzahl Gäste</span>
      <span class="lang-en" style="display: none">Number of Guests</span>
      <input id="bf-guests" type="number" name="guests" min="1" value="<?= $bookingForm->old('guests') ?>">
    </label>
    
    <label for="bf-message">
      <span class="lang-de">Nachricht</span>
      <span class="lang-en" style="display: none">Message</span>
      <textarea id="bf-message" name="message"><?= $bookingForm->old('message') ?></textarea>
    </label>
    
    <?= csrf_field() ?>
    <?= honeypot_field() ?>
    
    <button type="submit">
      <span class="lang-de">Absenden</span>
      <span class="lang-en" style="display: none">Send</span>
    </button>
  </form>

  <?php if ($bookingForm->success()): ?>
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
          document.getElementById('booking-form').reset();
        }, 3000);
      })();
    </script>
  <?php endif ?>
</div>
