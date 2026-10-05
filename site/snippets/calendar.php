 <?php
   use Kirby\Toolkit\Str;

   $eventsDE = $site->heading_events_de()->or('Veranstaltungen');
   $eventsEN = $site->heading_events_en()->or('Events');
  $regularEventsDE = $site->heading_regular_events_de()->or('Regelmäßige Veranstaltungen');
  $regularEventsEN = $site->heading_regular_events_en()->or('Regular Events');
   $eventsChars = mb_strlen($eventsDE);
  $currentLang = $kirby->language() ? $kirby->language()->code() : 'de';
   
   // Load rooms dynamically from backend
   $roomNames = [];
   foreach (($site->rooms()->toStructure() ?? []) as $room) {
     $roomId = trim((string)$room->id());
     if ($roomId === '') {
       continue;
     }

     $roomNames[$roomId] = (string)$room->label_de()->or($room->id());
   }
    
    // Prepare events data for JavaScript
    $eventsData = [];
    $upcomingEventsData = [];
    $regularEventsData = [];

    $buildEventPayload = function ($event, $includeDate = true) use (&$roomNames) {
      $images = [];
      if ($event->images()->isNotEmpty()) {
        foreach($event->images()->toFiles() as $image) {
          $images[] = [
            'url' => $image->url(),
            'alt' => $image->alt()->or('')
          ];
        }
      }

      $tags = [];
      if ($event->tags()->isNotEmpty()) {
        $tags = array_map('trim', explode(',', $event->tags()->value()));
      }

      $initiativeName = $event->initiative_link()->isNotEmpty()
        ? $event->initiative_link()->value()
        : $event->initiative()->value();

      $initiativeSlug = null;
      if (!empty($initiativeName)) {
        $initiativeSlug = Str::slug($initiativeName, '-');
        $tags[] = $initiativeSlug;
      }

      $roomId = $event->room_link()->value();
      if ($roomId === 'none') {
        $roomId = null;
      }
      $roomLabel = ($roomId && isset($roomNames[$roomId])) ? $roomNames[$roomId] : null;

      $social = [];
      if ($event->social_1_label()->isNotEmpty() && $event->social_1_url()->isNotEmpty()) {
        $social[] = ['label' => $event->social_1_label()->value(), 'url' => $event->social_1_url()->value()];
      }
      if ($event->social_2_label()->isNotEmpty() && $event->social_2_url()->isNotEmpty()) {
        $social[] = ['label' => $event->social_2_label()->value(), 'url' => $event->social_2_url()->value()];
      }

      $timeValue = $event->time()->or('')->value();
      $timeValueEn = $event->time_en()->or($event->time())->or('')->value();

      $data = [
        'title_de' => $event->title_de()->value(),
        'title_en' => $event->title_en()->or($event->title_de())->value(),
        'title' => $event->title()->value(),
        'date' => $includeDate ? $event->date()->toDate('Y-m-d') : '',
        'time' => $timeValue,
        'time_en' => $timeValueEn,
        'location' => $event->location()->or('')->value(),
        'description_de' => $event->description_de()->or('')->value(),
        'description_en' => $event->description_en()->or($event->description_de())->or('')->value(),
        'description_de_html' => (string)$event->description_de()->or('')->kt(),
        'description_en_html' => (string)$event->description_en()->or($event->description_de())->or('')->kt(),
        'description' => $event->description()->or('')->value(),
        'images' => $images,
        'tags' => $tags,
        'initiative' => $initiativeName ?: null,
        'initiative_name' => $initiativeName ?: null,
        'initiative_slug' => $initiativeSlug,
        'room_id' => $roomId,
        'room_label' => $roomLabel,
        'social_links' => $social,
        'cancelled' => $includeDate && $event->cancelled()->toBool(),
        'is_recurring' => ($event->wiederholung()->or('none')->value() !== 'none')
      ];

      if (!$includeDate) {
        $data['date'] = '';
      }

      return $data;
    };

    foreach (['upcoming', 'regular'] as $type) {
      foreach (dondorf_event_items($type) as $event) {
        foreach (dondorf_event_occurrences($event) as $instance) {
          $payload = $buildEventPayload($instance, true);
          $eventsData[] = $payload;
          $upcomingEventsData[] = $payload;
        }
        if ($type === 'regular' || $event->wiederholung()->or('none')->value() !== 'none') {
          $regularEventsData[] = $buildEventPayload($event, false);
        }
      }
    }
  ?>
  <section class="content-section" id="events-section">
    <h2 class="section-heading" style="--char-count: <?= $eventsChars ?>">
      <span class="lang-de"><?= $eventsDE ?></span>
      <span class="lang-en" style="display: none"><?= $eventsEN ?></span>
    </h2>
    
    <div class="calendar-wrapper">
      <!-- Calendar Container -->
      <div class="calendar-container">
        <div class="calendar-header">
          <h3 id="calendar-month-year"></h3>
          <div class="calendar-nav">
            <button id="calendar-prev">←</button>
            <button id="calendar-today">
              <span class="lang-de">Heute</span>
              <span class="lang-en" style="display: none">Today</span>
            </button>
            <button id="calendar-next">→</button>
          </div>
        </div>
        <div class="calendar-grid" id="calendar-grid"></div>
      </div>

      <!-- Events Display -->
      <div class="events-display">
        <div class="selected-day-events" id="selected-day-events"></div>
        <div class="all-events-section">
          <div id="upcoming-filters" class="calendar-filters"></div>
          <div id="all-upcoming-events"></div>
          <div id="regular-events-list">
            <?php if (!empty($regularEventsData)): ?>
              <h3 class="regular-events-title"><?= esc($currentLang === 'de' ? $regularEventsDE : $regularEventsEN) ?></h3>
              <div class="upcoming-events-list regular-events-list">
                <?php foreach ($regularEventsData as $event): ?>
                  <article class="event-card regular-event-card" data-event='<?= htmlspecialchars(json_encode($event), ENT_QUOTES, 'UTF-8') ?>' style="cursor: pointer;">
                    <h3><?= esc($currentLang === 'de' ? ($event['title_de'] ?: $event['title']) : ($event['title_en'] ?: $event['title'])) ?></h3>
                    <?php
                      $cardTime = $currentLang === 'de'
                        ? ($event['time'] ?? '')
                        : ($event['time_en'] ?? ($event['time'] ?? ''));
                      $cardMeta = array_filter([$cardTime, $event['location'] ?? '']);
                    ?>
                    <?php if (!empty($cardMeta)): ?>
                      <p class="event-card-meta"><?= esc(implode(' · ', $cardMeta)) ?></p>
                    <?php endif ?>
                    <?php if (!empty($event['tags'])): ?>
                      <div class="event-tags">
                        <?php foreach ($event['tags'] as $tag): ?>
                          <span class="event-tag"><?= esc($tag) ?></span>
                        <?php endforeach ?>
                      </div>
                    <?php endif ?>
                    <?php if (!empty($currentLang === 'de' ? ($event['description_de'] ?? '') : ($event['description_en'] ?? $event['description_de'] ?? ''))): ?>
                      <p><?= esc($currentLang === 'de' ? ($event['description_de'] ?? '') : ($event['description_en'] ?? $event['description_de'] ?? '')) ?></p>
                    <?php endif ?>
                  </article>
                <?php endforeach ?>
              </div>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Pass events data to JavaScript
      window.upcomingEvents = <?= json_encode($upcomingEventsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      window.calendarEvents = <?= json_encode($eventsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      window.regularEvents = <?= json_encode($regularEventsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      window.regularEventsHeading = {
        de: <?= json_encode((string)$regularEventsDE) ?>,
        en: <?= json_encode((string)$regularEventsEN) ?>
      };
      // Pass initiatives from events (collect unique initiatives used in events)
      <?php
        $inits = [];
        $usedInitiatives = [];
        
        // Collect all unique initiatives from events
        foreach(dondorf_event_items('upcoming') as $event) {
          $initiativeName = $event->initiative_link()->isNotEmpty()
            ? $event->initiative_link()->value()
            : $event->initiative()->value();

          if (!empty($initiativeName) && !in_array($initiativeName, $usedInitiatives)) {
            $usedInitiatives[] = $initiativeName;
            $slug = Str::slug($initiativeName, '-');
            $inits[] = [
              'slug' => $slug,
              'title_de' => $initiativeName,
              'title_en' => $initiativeName,
            ];
          }
        }

        foreach(dondorf_event_items('regular') as $event) {
          $initiativeName = $event->initiative_link()->isNotEmpty()
            ? $event->initiative_link()->value()
            : $event->initiative()->value();

          if (!empty($initiativeName) && !in_array($initiativeName, $usedInitiatives)) {
            $usedInitiatives[] = $initiativeName;
            $slug = Str::slug($initiativeName, '-');
            $inits[] = [
              'slug' => $slug,
              'title_de' => $initiativeName,
              'title_en' => $initiativeName,
            ];
          }
        }
      ?>
      window.calendarInitiatives = <?= json_encode($inits) ?>;
    </script>
  </section>
