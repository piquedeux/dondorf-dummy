<!-- Floating bubble menu (bottom-left) -->
<?php
  $floatSections = [
              'welcome-section' => [
                'de' => $site->heading_welcome_de()->or('Willkommen'),
                'en' => $site->heading_welcome_en()->or('Welcome')
              ],
              'events-section' => [
                'de' => $site->heading_events_de()->or('Veranstaltungen'),
                'en' => $site->heading_events_en()->or('Events')
              ],
              'initiatives-section' => [
                'de' => $site->heading_initiatives_de()->or('Initiativen'),
                'en' => $site->heading_initiatives_en()->or('Initiatives')
              ],
              'floorplan-section' => [
                'de' => $site->heading_floorplan_de()->or('Lageplan'),
                'en' => $site->heading_floorplan_en()->or('Floorplan')
              ],
              'lageplan-section' => [
                'de' => $site->heading_lageplan_de()->or('Lageplan'),
                'en' => $site->heading_lageplan_en()->or('Location Plan')
              ],
               'info-section' => [
                'de' => $site->heading_info_de()->or('Info'),
                'en' => $site->heading_info_en()->or('Info')
              ],
              'contact-section' => [
                'de' => $site->heading_contact_de()->or('Kontakt'),
                'en' => $site->heading_contact_en()->or('Contact')
              ],

  ];
?>
<button id="floatingMenuToggle" class="floating-menu-toggle" aria-expanded="false" aria-label="Open menu">+</button>
<button id="floatingLageplanToggle" class="floating-lageplan-toggle" aria-label="Zum Lageplan">
  <svg width="26" height="26" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M9 19L3.78974 20.7368C3.40122 20.8663 3 20.5771 3 20.1675L3 5.43246C3 5.1742 3.16526 4.94491 3.41026 4.86325L9 3M9 19L15 21M9 19L9 3M15 21L20.5897 19.1368C20.8347 19.0551 21 18.8258 21 18.5675L21 3.83246C21 3.42292 20.5988 3.13374 20.2103 3.26325L15 5M15 21L15 5M15 5L9 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
  </svg>
</button>
<div class="floating-menu" aria-hidden="false">
  <div class="floating-menu-panel" role="menu">
    <ul class="floating-menu-list">
      <?php foreach ($floatSections as $id => $labels): ?>
        <li>
          <a href="#<?= $id ?>" data-scroll class="floating-menu-link" role="menuitem">
            <span class="lang-de"><?= $labels['de'] ?></span>
            <span class="lang-en" style="display: none"><?= $labels['en'] ?></span>
          </a>
        </li>
      <?php endforeach ?>
    </ul>
  </div>
</div>