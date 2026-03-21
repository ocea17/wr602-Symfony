<?php

// Polyfill pour curl_version si la fonction n'existe pas
if (!function_exists('curl_version')) {
    function curl_version($age = null) {
        return [
            'version_number' => 0,
            'version' => '0.0.0',
            'ssl_version_number' => 0,
            'ssl_version' => 'none',
            'libz_version' => '1.0',
            'host' => 'unknown',
            'features' => 0,
            'protocols' => ['http', 'https'],
        ];
    }
}