<footer class="footer">
  <div class="footer-inner">
    <div class="footer-left">
      <nav class="footer-nav">
        <ul>
          <?php
            $sections = [
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
            foreach ($sections as $id => $labels):
          ?>
            <li>
              <a href="<?= $site->url() ?>#<?= $id ?>" class="footer-link" data-scroll>
                <span class="lang-de"><?= $labels['de'] ?></span>
                <span class="lang-en" style="display: none"><?= $labels['en'] ?></span>
              </a>
            </li>
          <?php endforeach ?>
        </ul>
      </nav>
    </div>
    <div class="footer-center">
      <?php snippet('newsletter-form') ?>
    </div>
    <div class="footer-right">
        <details>
          <summary>
            <span class="lang-de"><?= $site->imprint_title_de()->or('Impressum') ?></span>
            <span class="lang-en" style="display: none"><?= $site->imprint_title_en()->or('Imprint') ?></span>
          </summary>
          <div class="lang-de"><?= $site->imprint_de()->kt() ?></div>
          <div class="lang-en" style="display: none"><?= $site->imprint_en()->or($site->imprint_de())->kt() ?>
</div>
        </details>
        <p>
          <a href="#" id="cookieSettingsTrigger" class="cookies-link">
            <span class="lang-de">Cookies</span>
            <span class="lang-en" style="display: none">Cookies</span>
          </a>
        </p>
        <br>
        <p class="lang-de">Website Design und Entwicklung: <a style="font-weight: bold; color: inherit; text-decoration: none;" href="https://moritzgauss.com" target="_blank">Moritz Gauss</a></p>
        <p class="lang-en" style="display: none">Website Design and Development: <a style="font-weight: bold; color: inherit; text-decoration: none;" href="https://moritzgauss.com" target="_blank">Moritz Gauss</a></p>
      </div>
    </div>
  </div>
</footer>


<?= js(['assets/js/script.js']) ?>
<?= js(['assets/js/cookies.js']) ?>
<?= js(['assets/js/menu.js']) ?>
<?= js(['assets/js/calendar.js']) ?>
<?= js(['assets/js/floorplan.js']) ?>
<?= js(['assets/js/lageplan.js']) ?>
<?= js(['assets/js/collectives.js']) ?>
<?= js(['assets/js/info-section.js']) ?>
<?= js(['assets/js/event-modal.js']) ?>
<?= js(['assets/js/slideshow.js']) ?>
<?= js(['assets/js/aufmacher.js']) ?>

</body>
</html>
