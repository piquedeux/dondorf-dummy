<?php

use Kirby\Cms\App as Kirby;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Str;

require_once __DIR__ . "/events.php";

function dondorf_site_section_fields(): array
{
    return [
        'aufmacher' => [
            'logomark',
            'logomark_colored',
            'logo_wordmark',
            'logo_wordmark_colored',
            'heading_welcome_de',
            'heading_welcome_en',
            'welcome_subtitle_de',
            'welcome_subtitle_en',
            'slideshow',
            'aufmacher_text_de',
            'aufmacher_text_en',
            'aufmacher_toggle_de',
            'aufmacher_toggle_en',
        ],
        'veranstaltungen' => [
            'heading_events_de',
            'heading_events_en',
            'heading_regular_events_de',
            'heading_regular_events_en',
            'events',
            'regular_events',
        ],
        'initiativen' => [
            'heading_initiatives_de',
            'heading_initiatives_en',
            'initiativen',
        ],
        'lageplan' => [
            'heading_floorplan_de',
            'heading_floorplan_en',
            'rooms',
            'heading_lageplan_de',
            'heading_lageplan_en',
            'lageplan_image',
            'lageplan_wegbeschreibung',
            'lageplan_googlemaps_embed',
        ],
        'kontakt' => [
            'heading_contact_de',
            'heading_contact_en',
            'contacts',
        ],
        'info' => [
            'heading_info_de',
            'heading_info_en',
            'dondorf_info_de',
            'dondorf_info_en',
            'imprint_title_de',
            'imprint_title_en',
            'imprint_de',
            'imprint_en',
        ],
        'einstellungen' => [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_image',
        ],
    ];
}

function dondorf_mirror_site_sections($site = null): void
{
    $site ??= site();
    dondorf_mirror_site_sections_from_content($site->content()->toArray());
}

function dondorf_mirror_site_sections_from_content(array $content): void
{
    $content = array_change_key_case($content, CASE_LOWER);
    $root = kirby()->root('content') . '/site-sections';

    Dir::make($root);

    foreach (dondorf_site_section_fields() as $section => $fields) {
        $data = [
            'title' => ucfirst($section),
        ];

        foreach ($fields as $field) {
            $value = $content[$field] ?? null;

            if (is_array($value) === true) {
                if ($value !== []) {
                    $data[$field] = $value;
                }
                continue;
            }

            if ($value !== null && trim((string)$value) !== '') {
                $data[$field] = $value;
            }
        }

        $sectionRoot = $root . '/' . $section;
        Dir::make($sectionRoot);
        Data::write($sectionRoot . '/' . $section . '.txt', $data, 'txt');
    }
}

function dondorf_storage_state_path(): string
{
    return kirby()->root('content') . '/site-sections/storage-state.json';
}

function dondorf_storage_state($oldSite = null): array
{
    $path = dondorf_storage_state_path();

    if (is_file($path) === true) {
        return Data::read($path, 'json');
    }

    $site = $oldSite ?? site();

    return [
        'eventBaseCounts' => [
            'events' => $site->events()->toStructure()->count(),
            'regular_events' => $site->regular_events()->toStructure()->count(),
        ],
        'eventFiles' => [
            'events' => [],
            'regular_events' => [],
        ],
    ];
}

function dondorf_write_storage_state(array $state): void
{
    Dir::make(kirby()->root('content') . '/site-sections');
    Data::write(dondorf_storage_state_path(), $state, 'json');
}

function dondorf_event_title($event): string
{
    $title = trim((string)$event->title_de()->or($event->title_en())->or($event->title())->value());
    return $title !== '' ? $title : 'Neue Veranstaltung';
}

function dondorf_event_file_data($event, string $eventType): array
{
    $title = dondorf_event_title($event);

    $fields = [
        'title' => $title,
        'event_exceptions' => $event->event_exceptions()->value(),
        'title_de' => $event->title_de()->value(),
        'title_en' => $event->title_en()->value(),
        'event_type' => $eventType,
        'freigabe_status' => $event->freigabe_status()->or('veröffentlicht')->value(),
        'date' => $event->date()->value(),
        'time' => $event->time()->value(),
        'time_en' => $event->time_en()->value(),
        'location' => $event->location()->value(),
        'room_link' => $event->room_link()->value(),
        'description_de' => $event->description_de()->value(),
        'description_en' => $event->description_en()->value(),
        'images' => $event->images()->value(),
        'tags' => $event->tags()->value(),
        'initiative_link' => $event->initiative_link()->value(),
        'initiative' => $event->initiative()->value(),
        'wiederholung' => $event->wiederholung()->or('none')->value(),
        'wiederholen_bis' => $event->wiederholen_bis()->value(),
        'kapazitaet' => $event->kapazitaet()->value(),
        'social_1_label' => $event->social_1_label()->value(),
        'social_1_url' => $event->social_1_url()->value(),
        'social_2_label' => $event->social_2_label()->value(),
        'social_2_url' => $event->social_2_url()->value(),
    ];

    return array_filter($fields, fn ($value) => $value !== null);
}

function dondorf_event_status_value($event): string
{
    $status = trim((string)$event->freigabe_status()->or('veröffentlicht')->value());

    return mb_strtolower($status);
}

function dondorf_event_timestamp_value(string $date): int|null
{
    $date = trim($date);

    if ($date === '') {
        return null;
    }

    foreach (['d.m.Y', 'Y-m-d', 'd/m/Y'] as $format) {
        $parsed = DateTimeImmutable::createFromFormat('!' . $format, $date);

        if ($parsed instanceof DateTimeImmutable) {
            return $parsed->getTimestamp();
        }
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? null : $timestamp;
}

function dondorf_event_timestamp($event): int|null
{
    return dondorf_event_timestamp_value((string)$event->date()->value());
}

function dondorf_event_is_published($event): bool
{
    return in_array(
        dondorf_event_status_value($event),
        ['', 'veröffentlicht', 'published'],
        true
    );
}

function dondorf_event_is_expired($event, int $cutoffTimestamp): bool
{
    if (dondorf_event_is_published($event) === false) {
        return false;
    }

    $timestamp = dondorf_event_timestamp($event);

    return $timestamp !== null && $timestamp < $cutoffTimestamp;
}

function dondorf_event_array_timestamp(array $event): int|null
{
    $event = array_change_key_case($event, CASE_LOWER);

    return dondorf_event_timestamp_value((string)($event['date'] ?? ''));
}

function dondorf_event_array_is_published(array $event): bool
{
    $event = array_change_key_case($event, CASE_LOWER);

    $status = mb_strtolower(trim((string)($event['freigabe_status'] ?? 'veröffentlicht')));

    return in_array($status, ['', 'veröffentlicht', 'published'], true);
}

function dondorf_filter_expired_events(array $events): array
{
    $cutoffTimestamp = (new DateTimeImmutable('now'))->modify('-3 months')->getTimestamp();
    $filtered = [];

    foreach ($events as $event) {
        if (
            is_array($event) === true &&
            empty(array_change_key_case($event, CASE_LOWER)['event_exceptions']) &&
            dondorf_event_array_is_published($event) === true &&
            dondorf_event_array_timestamp($event) !== null &&
            dondorf_event_array_timestamp($event) < $cutoffTimestamp
        ) {
            continue;
        }

        $filtered[] = $event;
    }

    return $filtered;
}

function dondorf_prune_expired_events(array $content, $oldSite = null): array
{
    $lookup = array_change_key_case($content, CASE_LOWER);

    if (isset($lookup['events']) !== true || is_array($lookup['events']) !== true) {
        return $content;
    }

    $filteredEvents = dondorf_filter_expired_events($lookup['events']);

    if ($filteredEvents === $lookup['events']) {
        return $content;
    }

    foreach ($content as $key => $value) {
        if (mb_strtolower((string)$key) === 'events') {
            $content[$key] = $filteredEvents;
            break;
        }
    }

    if (isset($content['events']) !== true) {
        $content['events'] = $filteredEvents;
    }

    $state = dondorf_storage_state($oldSite);
    $state['eventBaseCounts']['events'] = count($filteredEvents);
    $state['eventFiles']['events'] = [];
    dondorf_write_storage_state($state);

    return $content;
}

function dondorf_cleanup_event_directories(): void
{
    $eventsRoot = kirby()->root('content') . '/events';
    $cutoffTimestamp = (new DateTimeImmutable('now'))->modify('-3 months')->getTimestamp();

    if (is_dir($eventsRoot) === false) {
        return;
    }

    $directories = [
        ...Dir::dirs($eventsRoot, null, true),
    ];

    $draftsRoot = $eventsRoot . '/_drafts';

    if (is_dir($draftsRoot) === true) {
        $directories = [
            ...$directories,
            ...Dir::dirs($draftsRoot, null, true),
        ];
    }

    foreach ($directories as $directory) {
        if (is_dir($directory) === false) {
            continue;
        }

        $contentFile = $directory . '/event.txt';

        if (is_file($contentFile) === false) {
            continue;
        }

        $eventData = Data::read($contentFile, 'txt');
        $eventData = is_array($eventData) === true ? array_change_key_case($eventData, CASE_LOWER) : $eventData;

        if (
            is_array($eventData) !== true ||
            dondorf_event_array_is_published($eventData) === false ||
            dondorf_event_array_timestamp($eventData) === null ||
            dondorf_event_array_timestamp($eventData) >= $cutoffTimestamp
        ) {
            continue;
        }

        Dir::remove($directory);
    }
}

function dondorf_unique_event_slug(string $title, string|null $existingSlug = null): string
{
    if ($existingSlug !== null && $existingSlug !== '') {
        return $existingSlug;
    }

    $base = Str::slug($title, '-');

    if ($base === '') {
        $base = 'veranstaltung';
    }

    $slug = $base;
    $i = 2;

    while (is_dir(kirby()->root('content') . '/events/_drafts/' . $slug) === true) {
        $slug = $base . '-' . $i;
        $i++;
    }

    return $slug;
}

function dondorf_sync_new_structure_events($newSite, $oldSite = null): void
{
    $state = dondorf_storage_state($oldSite);

    foreach (['events' => 'upcoming', 'regular_events' => 'regular'] as $field => $eventType) {
        $items = $newSite->{$field}()->toStructure()->values();
        $baseCount = $state['eventBaseCounts'][$field] ?? ($oldSite ? $oldSite->{$field}()->toStructure()->count() : count($items));
        $state['eventFiles'][$field] ??= [];

        foreach ($items as $index => $event) {
            if ($index < $baseCount || dondorf_event_is_empty($event) === true) {
                continue;
            }

            $existingSlug = $state['eventFiles'][$field][(string)$index] ?? null;
            $slug = dondorf_unique_event_slug(dondorf_event_title($event), $existingSlug);
            $eventRoot = kirby()->root('content') . '/events/_drafts/' . $slug;

            Dir::make($eventRoot);
            Data::write($eventRoot . '/event.txt', dondorf_event_file_data($event, $eventType), 'txt');

            $state['eventFiles'][$field][(string)$index] = $slug;
        }
    }

    dondorf_write_storage_state($state);
}

function dondorf_event_field($event, string $field)
{
    return $event->{$field}();
}

function dondorf_event_is_empty($event): bool
{
    $fields = ['title_de', 'title_en', 'date', 'time', 'description_de', 'description_en'];

    foreach ($fields as $field) {
        if (dondorf_event_field($event, $field)->isNotEmpty()) {
            return false;
        }
    }

    return true;
}

function dondorf_event_key($event): string
{
    return dondorf_event_keys($event)[0];
}

function dondorf_event_keys($event): array
{
    $keys = [];
    $legacyKey = trim((string)dondorf_event_field($event, 'legacy_key')->value());

    if ($legacyKey !== '') {
        $keys[] = 'legacy:' . $legacyKey;
    }

    $keys[] = 'event:' . md5(implode('|', [
        dondorf_event_field($event, 'title_de')->or(dondorf_event_field($event, 'title_en'))->value(),
        dondorf_event_field($event, 'date')->value(),
        dondorf_event_field($event, 'time')->value(),
    ]));

    return array_values(array_unique($keys));
}

function dondorf_event_page_items(bool $includeDrafts = false): array
{
    $eventsPage = site()->find('events');

    if ($eventsPage === null) {
        return [];
    }

    $pages = $includeDrafts === true ? $eventsPage->childrenAndDrafts() : $eventsPage->children();
    $items = [];

    foreach ($pages as $page) {
        if ((string)$page->intendedTemplate()->name() !== 'event' || dondorf_event_is_empty($page) === true) {
            continue;
        }

        $items[] = $page;
    }

    return $items;
}

function dondorf_event_items(string $type = 'upcoming', bool $includeDrafts = false): array
{
    $items = [];
    $seen = [];

    foreach (dondorf_event_page_items($includeDrafts) as $page) {
        $eventType = $page->event_type()->or('upcoming')->value();

        if ($type === 'regular' && $eventType !== 'regular') {
            continue;
        }

        if ($type === 'upcoming' && $eventType === 'regular') {
            continue;
        }

        if ($includeDrafts === false && dondorf_event_is_published($page) === false) {
            continue;
        }

        foreach (dondorf_event_keys($page) as $key) {
            $seen[$key] = true;
        }

        $items[] = $page;
    }

    $fallbackField = $type === 'regular' ? 'regular_events' : 'events';

    foreach (site()->{$fallbackField}()->toStructure() as $event) {
        if (dondorf_event_is_empty($event) === true) {
            continue;
        }

        if ($includeDrafts === false && dondorf_event_is_published($event) === false) {
            continue;
        }

        $keys = dondorf_event_keys($event);
        $isDuplicate = false;

        foreach ($keys as $key) {
            if (isset($seen[$key]) === true) {
                $isDuplicate = true;
                break;
            }
        }

        if ($isDuplicate === true) {
            continue;
        }

        foreach ($keys as $key) {
            $seen[$key] = true;
        }

        $items[] = $event;
    }

    return $items;
}

Kirby::plugin('dondorf/content-storage', [
    'hooks' => [
        'site.update:after' => function ($newSite, $oldSite): void {
            $contentPath = kirby()->root('content') . '/site.txt';
            $content = Data::read($contentPath, 'txt');

            if (is_array($content) === true) {
                $content = dondorf_prune_expired_events($content, $oldSite);
                Data::write($contentPath, $content, 'txt');
                dondorf_mirror_site_sections_from_content($content);
            } else {
                dondorf_mirror_site_sections($newSite);
            }

            dondorf_cleanup_event_directories();
            dondorf_sync_new_structure_events($newSite, $oldSite);
        },
    ],
]);
