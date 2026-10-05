<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('dondorf/mailchimp', [
    'options' => [
        'api_key' => '',
        'datacenter' => 'us13',
        'audience_id' => '',
    ]
]);

/**
 * Subscribe an email to Mailchimp list
 * 
 * @param string $email The email address to subscribe
 * @param array $mergeFields Optional merge fields (FNAME, LNAME, etc.)
 * @param string $status Subscription status (subscribed, pending, etc.)
 * @return array Response from Mailchimp API
 */
function mailchimpSubscribe(string $email, array $mergeFields = [], string $status = 'subscribed'): array
{
    $config = kirby()->option('mailchimp');
    $apiKey = $config['api_key'] ?? '';
    $datacenter = $config['datacenter'] ?? 'us13';
    $audienceId = $config['audience_id'] ?? '';

    // Validate configuration
    if (empty($apiKey)) {
        return [
            'success' => false,
            'error' => 'Mailchimp API key is not configured'
        ];
    }

    if (empty($audienceId)) {
        return [
            'success' => false,
            'error' => 'Mailchimp Audience ID is not configured'
        ];
    }

    // Prepare the API endpoint
    $memberHash = md5(strtolower($email));
    $url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$audienceId}/members/{$memberHash}";

    // Prepare the data
    $data = [
        'email_address' => $email,
        'status' => $status,
    ];

    if (!empty($mergeFields)) {
        $data['merge_fields'] = $mergeFields;
    }

    // Make the API request using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // Handle errors
    if ($error) {
        error_log('Mailchimp cURL error: ' . $error);
        return [
            'success' => false,
            'error' => 'Failed to connect to Mailchimp: ' . $error
        ];
    }

    $responseData = json_decode($response, true);

    // Check if request was successful
    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success' => true,
            'data' => $responseData
        ];
    } else {
        $errorMessage = $responseData['detail'] ?? $responseData['title'] ?? 'Unknown error';
        error_log('Mailchimp API error: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        return [
            'success' => false,
            'error' => $errorMessage,
            'http_code' => $httpCode
        ];
    }
}

/**
 * Check if an email exists in Mailchimp list
 * 
 * @param string $email The email address to check
 * @return array Response with exists status
 */
function mailchimpCheckMember(string $email): array
{
    $config = kirby()->option('mailchimp');
    $apiKey = $config['api_key'] ?? '';
    $datacenter = $config['datacenter'] ?? 'us13';
    $audienceId = $config['audience_id'] ?? '';

    if (empty($apiKey) || empty($audienceId)) {
        return [
            'exists' => false,
            'error' => 'Mailchimp is not configured'
        ];
    }

    $memberHash = md5(strtolower($email));
    $url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$audienceId}/members/{$memberHash}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return [
            'exists' => true,
            'status' => $data['status'] ?? 'unknown',
            'data' => $data
        ];
    }

    return [
        'exists' => false
    ];
}
