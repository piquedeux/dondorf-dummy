<?php

use Kirby\Content\Field;

final class DondorfEventOccurrence
{
    public function __construct(private mixed $event, private string $date, private mixed $exception = null) {}

    public function __call(string $name, array $arguments): mixed
    {
        if ($name === 'date') return new Field(null, 'date', $this->date);
        if ($name === 'cancelled') return new Field(null, $name, $this->exception?->cancelled()->toBool() ? 'true' : 'false');
        if ($name === 'original_date') return new Field(null, $name, $this->exception?->original_date()->value() ?? $this->date);
        if ($this->exception && in_array($name, [
            'title_de', 'title_en', 'time', 'time_en', 'location', 'room_link',
            'description_de', 'description_en', 'images', 'tags', 'initiative_link',
            'kapazitaet', 'social_1_label', 'social_1_url', 'social_2_label', 'social_2_url',
        ], true)) {
            $field = $this->exception->{$name}();
            if ($field->isNotEmpty()) return $field;
            // A changed German value must not leave an obsolete English fallback.
            if (str_ends_with($name, '_en')) {
                $base = $name === 'time_en' ? 'time' : substr($name, 0, -3) . '_de';
                if ($this->exception->{$base}()->isNotEmpty()) return $this->exception->{$base}();
            }
        }
        return $this->event->{$name}(...$arguments);
    }
}

/** Expand first, then overlay an exception keyed by the ORIGINAL date. */
function dondorf_event_occurrences($event): array
{
    $zone = new DateTimeZone('Europe/Berlin');
    $date = $event->date()->toDate('Y-m-d');
    if (!$date) return [new DondorfEventOccurrence($event, '')];
    $start = new DateTimeImmutable($date, $zone);
    $until = $event->wiederholen_bis()->toDate('Y-m-d');
    $rule = $event->wiederholung()->or('none')->value();
    $step = ['daily' => '+1 day', 'weekly' => '+1 week', 'monthly' => '+1 month'][$rule] ?? null;
    $end = $until && $step ? new DateTimeImmutable($until, $zone) : $start;
    $exceptions = [];
    foreach ($event->event_exceptions()->toStructure() as $exception) {
        $key = $exception->original_date()->toDate('Y-m-d');
        if ($key) $exceptions[$key] = $exception;
    }
    $items = [];
    for ($cursor = $start, $i = 0; $cursor <= $end && $i < 365; $i++) {
        $key = $cursor->format('Y-m-d');
        $exception = $exceptions[$key] ?? null;
        $actual = $exception?->date()->toDate('Y-m-d') ?: $key;
        $items[] = new DondorfEventOccurrence($event, $actual, $exception);
        if (!$step || !$until) break;
        $cursor = $cursor->modify($step);
    }
    return $items;
}

