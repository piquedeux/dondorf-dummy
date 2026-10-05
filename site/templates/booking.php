<?php
// Minimal booking template — admins can view booking details in the Panel
// Frontend visitors see a simple confirmation page
?>
<section class="content-section booking-page">
  <div class="container">
    <h2>Booking received</h2>
    <p>Thank you <?= html($page->name()) ?> — your booking for <?= html($page->date()) ?> at <?= html($page->start()) ?> has been saved.</p>
    <dl>
      <dt>Name</dt><dd><?= html($page->name()) ?></dd>
      <dt>Email</dt><dd><?= html($page->email()) ?></dd>
      <dt>Ort</dt><dd><?= html($page->location()) ?></dd>
      <dt>Dauer</dt><dd><?= html($page->duration()) ?> minutes</dd>
      <dt>Status</dt><dd><?= html($page->status()) ?></dd>
    </dl>
  </div>
</section>
