<?php
// Routes for admin views and API
// Booking submission is now handled by the event-bookings plugin
return [
  // Admin events JSON (read-only)
  [
    'pattern' => 'admin/events',
    'method'  => 'GET',
    'action'  => function () {
      // only allow logged in users
      if (!kirby()->user()) {
        return ['status' => 'error', 'message' => 'Unauthorized'];
      }

      $eventsData = [];
      $events = array_merge(
        dondorf_event_items('upcoming', true),
        dondorf_event_items('regular', true)
      );

      $events = array_merge([], ...array_map("dondorf_event_occurrences", $events));
      foreach ($events as $event) {
          $images = [];
          if ($event->images()->isNotEmpty()) {
            foreach ($event->images()->toFiles() as $image) {
              $images[] = ['url' => $image->url(), 'alt' => $image->alt()->or('')];
            }
          }

          $tags = [];
          if ($event->tags()->isNotEmpty()) {
            $tags = array_map('trim', explode(',', $event->tags()->value()));
          }

          $social = [];
          if ($event->social_1_label()->isNotEmpty() && $event->social_1_url()->isNotEmpty()) {
            $social[] = ['label' => $event->social_1_label()->value(), 'url' => $event->social_1_url()->value()];
          }
          if ($event->social_2_label()->isNotEmpty() && $event->social_2_url()->isNotEmpty()) {
            $social[] = ['label' => $event->social_2_label()->value(), 'url' => $event->social_2_url()->value()];
          }

          $eventsData[] = [
            'cancelled' => $event->cancelled()->toBool(),
            'original_date' => $event->original_date()->value(),
            'title_de' => $event->title_de()->or('')->value(),
            'title_en' => $event->title_en()->or($event->title_de())->value(),
            'date' => $event->date()->or('')->value(),
            'time' => $event->time()->or('')->value(),
            'location' => $event->location()->or('')->value(),
            'description_de' => $event->description_de()->or('')->value(),
            'description_en' => $event->description_en()->or($event->description_de())->value(),
            'images' => $images,
            'tags' => $tags,
            'social_links' => $social,
            'freigabe_status' => $event->freigabe_status()->or('veröffentlicht')->value(),
            'event_type' => $event->event_type()->or('upcoming')->value(),
          ];
      }

      return ['status' => 'ok', 'events' => $eventsData];
    }
  ],

  // Admin calendar HTML view (authenticated)
  [
    'pattern' => 'admin/calendar',
    'method'  => 'GET',
    'action'  => function () {
      $user = kirby()->user();
      if (!$user) {
        return new \Kirby\Cms\Response('Unauthorized', 'text/plain', 401);
      }

      // prepare events server-side (like calendar snippet)
      $eventsData = [];
      $events = array_merge(
        dondorf_event_items('upcoming', true),
        dondorf_event_items('regular', true)
      );

      $events = array_merge([], ...array_map("dondorf_event_occurrences", $events));
      foreach ($events as $event) {
          $images = [];
          if ($event->images()->isNotEmpty()) {
            foreach ($event->images()->toFiles() as $image) {
              $images[] = ['url' => $image->url(), 'alt' => $image->alt()->or('')];
            }
          }

          $tags = [];
          if ($event->tags()->isNotEmpty()) {
            $tags = array_map('trim', explode(',', $event->tags()->value()));
          }

          $social = [];
          if ($event->social_1_label()->isNotEmpty() && $event->social_1_url()->isNotEmpty()) {
            $social[] = ['label' => $event->social_1_label()->value(), 'url' => $event->social_1_url()->value()];
          }
          if ($event->social_2_label()->isNotEmpty() && $event->social_2_url()->isNotEmpty()) {
            $social[] = ['label' => $event->social_2_label()->value(), 'url' => $event->social_2_url()->value()];
          }

          $eventsData[] = [
            'cancelled' => $event->cancelled()->toBool(),
            'original_date' => $event->original_date()->value(),
            'title_de' => $event->title_de()->or('')->value(),
            'title_en' => $event->title_en()->or($event->title_de())->value(),
            'date' => $event->date()->or('')->value(),
            'time' => $event->time()->or('')->value(),
            'location' => $event->location()->or('')->value(),
            'description_de' => $event->description_de()->or('')->value(),
            'description_en' => $event->description_en()->or($event->description_de())->value(),
            'images' => $images,
            'tags' => $tags,
            'social_links' => $social,
            'freigabe_status' => $event->freigabe_status()->or('veröffentlicht')->value(),
            'event_type' => $event->event_type()->or('upcoming')->value()
          ];
      }

      // render admin calendar snippet and return HTML
      $html = snippet('admin-calendar', ['eventsData' => $eventsData], true);
      return new \Kirby\Cms\Response($html, 'text/html');
    }
  ],

  // Admin bookings/anfragen view (authenticated)
  [
    'pattern' => 'admin/anfragen',
    'method'  => 'GET',
    'action'  => function () {
      $user = kirby()->user();
      if (!$user) {
        return new \Kirby\Cms\Response('Unauthorized', 'text/plain', 401);
      }

      // Get all booking pages (template = booking)
      $bookings = site()->index()->filterBy('template', 'booking')->sortBy('date', 'desc');

      $html = snippet('admin-bookings', ['bookings' => $bookings], true);
      return new \Kirby\Cms\Response($html, 'text/html');
    }
  ]
];
