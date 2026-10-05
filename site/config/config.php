<?php

/**
 * The config file is optional. It accepts a return array with config options
 * Note: Never include more than one return statement, all options go within this single return array
 * In this example, we set debugging to true, so that errors are displayed onscreen. 
 * This setting must be set to false in production.
 * All config options: https://getkirby.com/docs/reference/system/options
 */

// Load environment variables from .env file
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

$cookieKey = getenv('KIRBY_COOKIE_KEY') ?: null;

if ($cookieKey !== null) {
    \Kirby\Http\Cookie::$key = $cookieKey;
}

return [
    'debug' => false,
    'content.salt' => getenv('KIRBY_CONTENT_SALT') ?: null,
    'cookie.key' => $cookieKey,
    'yaml.handler' => 'symfony',
    'panel.kirbytext' => true,
    'panel.vueDevtools' => false,
    'panel.vue.compiler' => false,
    'locale' => 'de_DE',
    'date.format' => 'd.m.Y',

    // Pageviewcounter options (ohne SQLite)
    'bnomei.pageviewcounter.sqlite' => false,
    'bnomei.pageviewcounter.driver' => 'file',
    
    // Uniform plugin settings
    'uniform' => [
        'required' => [
            'message' => 'Bitte füllen Sie das Feld "{field}" aus / Please fill in the "{field}" field'
        ],
        'email' => [
            'message' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein / Please enter a valid email address'
        ]
    ],
    
    // Email settings (SMTP)
    'email' => [
        'transport' => [
            'type' => 'smtp',
            'host' => getenv('SMTP_HOST') ?: 'smtp.example.com',
            'port' => getenv('SMTP_PORT') ?: 587,
            'security' => getenv('SMTP_SECURITY') ?: 'tls',
            'auth' => true,
            'username' => getenv('SMTP_USERNAME'),
            'password' => getenv('SMTP_PASSWORD')
        ]
    ]
];