<?php
// Fonctions pour interagir avec l’API Nichesss
function nichesss_api_generate_content($prompt, $type = 'article') {
    $api_key = get_option('nichesss_ai_api_key');
    if (!$api_key) return false;
    $endpoint = 'https://api.nichesss.com/v1/generate';
    $args = [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
            'prompt' => $prompt,
            'type' => $type
        ]),
        'timeout' => 60
    ];
    $response = wp_remote_post($endpoint, $args);
    if (is_wp_error($response)) return false;
    $body = wp_remote_retrieve_body($response);
    return json_decode($body, true);
}
