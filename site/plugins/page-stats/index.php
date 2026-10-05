<?php

use Kirby\Filesystem\F;

Kirby::plugin('custom/page-stats', [

    // 1. Methoden für die gesamte Website ($site)
    'siteMethods' => [
        'hitsCount' => function () {
            $logFile = kirby()->root('logs') . '/page_hits.json';
            if (!F::exists($logFile)) return 0;
            
            $raw = F::read($logFile);
            $hits = json_decode($raw, true);
            if (!is_array($hits)) return 0;

            return count($hits);
        },

        'statsOverview' => function () {
            $logFile = kirby()->root('logs') . '/page_hits.json';
            if (!F::exists($logFile)) return 'Keine Statistikdaten vorhanden.';

            $raw = F::read($logFile);
            $hits = json_decode($raw, true);
            if (!is_array($hits) || empty($hits)) return 'Keine Aufrufe in den letzten 6 Monaten.';

            $daysGerman = [
                'Monday'    => 'Montag',
                'Tuesday'   => 'Dienstag',
                'Wednesday' => 'Mittwoch',
                'Thursday'  => 'Donnerstag',
                'Friday'    => 'Freitag',
                'Saturday'  => 'Samstag',
                'Sunday'    => 'Sonntag'
            ];

            $weeks = [];
            $desktopTotal = 0;
            $mobileTotal = 0;

            foreach ($hits as $hit) {
                if (!is_array($hit) || !isset($hit['timestamp'])) continue;

                $ts = $hit['timestamp'];
                $weekKey = 'KW ' . date('W (Y)', $ts);
                
                $englishDay = date('l', $ts);
                $germanDay  = $daysGerman[$englishDay] ?? $englishDay;
                $dayKey     = date('d.m.Y', $ts) . ' (' . $germanDay . ')';

                $ua = $hit['user_agent'] ?? '';
                $isMobile = preg_match('/(iPhone|iPod|iPad|Android|BlackBerry|Mobile)/i', $ua);

                if ($isMobile) {
                    $mobileTotal++;
                } else {
                    $desktopTotal++;
                }

                if (!isset($weeks[$weekKey][$dayKey])) {
                    $weeks[$weekKey][$dayKey] = ['total' => 0, 'desktop' => 0, 'mobile' => 0];
                }

                $weeks[$weekKey][$dayKey]['total']++;
                if ($isMobile) {
                    $weeks[$weekKey][$dayKey]['mobile']++;
                } else {
                    $weeks[$weekKey][$dayKey]['desktop']++;
                }
            }

            $lines = [];
            $lines[] = "**Gesamt:** " . count($hits) . " Aufrufe (Desktop: {$desktopTotal} | Mobile: {$mobileTotal})";
            $lines[] = "";

            foreach ($weeks as $week => $days) {
                $lines[] = "### " . $week;
                foreach ($days as $day => $data) {
                    $lines[] = "- **" . $day . ":** " . $data['total'] . " Aufrufe (Desktop: " . $data['desktop'] . " | Mobile: " . $data['mobile'] . ")";
                }
                $lines[] = "";
            }

            return implode("\n", $lines);
        }
    ],

    // 2. Methoden für einzelne Seiten ($page)
    'pageMethods' => [
        'trackHit' => function () {
            $logFile = kirby()->root('logs') . '/page_hits.json';
            
            $raw = F::exists($logFile) ? F::read($logFile) : '[]';
            $hits = json_decode($raw, true);
            if (!is_array($hits)) {
                $hits = [];
            }

            $now = time();
            $retentionLimit = $now - (180 * 86400); // 180 Tage (6 Monate)

            // Einträge älter als 6 Monate filtern
            $hits = array_filter($hits, function ($hit) use ($retentionLimit) {
                return is_array($hit) && isset($hit['timestamp']) && $hit['timestamp'] >= $retentionLimit;
            });

            // Neuen Aufruf oben anfügen
            array_unshift($hits, [
                'timestamp'  => $now,
                'datetime'   => date('d.m.Y H:i:s'),
                'page'       => $this->id(),
                'url'        => $this->url(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);

            F::write($logFile, json_encode(array_values($hits), JSON_PRETTY_PRINT));
        },

        'hitsCount' => function () {
            $logFile = kirby()->root('logs') . '/page_hits.json';
            if (!F::exists($logFile)) return 0;
            
            $raw = F::read($logFile);
            $hits = json_decode($raw, true);
            if (!is_array($hits)) return 0;

            $pageId = $this->id();
            return count(array_filter($hits, fn($h) => is_array($h) && ($h['page'] ?? '') === $pageId));
        }
    ]
]);