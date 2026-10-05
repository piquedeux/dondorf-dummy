<?php
/** Run from the project root: php -n site/scripts/check-calendar.php */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__, 2) . '/kirby/bootstrap.php';

$app = new Kirby\Cms\App(['roots' => ['index' => dirname(__DIR__, 2)]]);
function calendarCheck(bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException($message);
}
$siteFields = $app->site()->blueprint()->fields();
$page = new Kirby\Cms\Page([
    'slug' => 'calendar-check', 'template' => 'event',
    'content' => [
        'title_de' => 'Offener Montag', 'date' => '2026-10-05', 'time' => '19:00',
        'wiederholung' => 'weekly', 'wiederholen_bis' => '2026-10-26',
        'room_link' => 'saal', 'initiative_link' => 'Kollektiv',
        'event_exceptions' => Kirby\Data\Yaml::encode([
            ['original_date' => '2026-10-12', 'cancelled' => 'true', 'description_de' => 'Heute geschlossen'],
            ['original_date' => '2026-10-19', 'date' => '2026-10-20', 'time' => '20:00'],
        ]),
    ],
]);
// Check resolved field types AND actual Kirby form creation, not just valid YAML.
$definitions = [
    $siteFields['events']['fields']['event_exceptions'],
    $siteFields['regular_events']['fields']['event_exceptions'],
    $page->blueprint()->fields()['event_exceptions'],
];
foreach ($definitions as $definition) {
    calendarCheck($definition['type'] === 'structure', 'Exception must be a built-in structure field');
    calendarCheck(isset($definition['fields']['original_date']), 'Exception date field must resolve');
    $field = Kirby\Form\Field::factory('structure', array_merge($definition, ['model' => $page]));
    $field->fill($page->event_exceptions()->value());
    calendarCheck(count(Kirby\Data\Yaml::decode($field->toStoredValue())) === 2, 'Form must preserve both exceptions');
}
calendarCheck(!isset($siteFields['initiativen']['fields']['event_exceptions']), 'No calendar exceptions on initiatives');
$instances = dondorf_event_occurrences($page);
calendarCheck(count($instances) === 4, 'Four Mondays');
calendarCheck($instances[1]->cancelled()->toBool(), 'Chosen Monday cancelled');
calendarCheck(!$instances[0]->cancelled()->toBool() && !$instances[2]->cancelled()->toBool() && !$instances[3]->cancelled()->toBool(), 'Only chosen Monday cancelled');
calendarCheck($instances[2]->date()->value() === '2026-10-20' && $instances[2]->time()->value() === '20:00', 'One occurrence moved');
calendarCheck($instances[3]->date()->value() === '2026-10-26' && $instances[3]->time()->value() === '19:00', 'Following occurrence unchanged');
calendarCheck($instances[2]->room_link()->value() === 'saal' && $instances[2]->initiative_link()->value() === 'Kollektiv', 'Links inherited');
calendarCheck(dondorf_event_file_data($page, 'regular')['event_exceptions'] === $page->event_exceptions()->value(), 'Storage retains exceptions');
calendarCheck($page->date()->value() === '2026-10-05', 'Series not mutated');
$html = snippet('calendar', ['site' => $app->site(), 'kirby' => $app], true);
calendarCheck(str_contains($html, 'window.calendarEvents ='), 'Calendar renders without removed plugins');
calendarCheck(!function_exists('dondorf_google_snapshot'), 'Google Calendar removed');
echo "OK: Panel field definitions, form serialization, individual cancellations/edits, links, storage and calendar rendering.\n";
