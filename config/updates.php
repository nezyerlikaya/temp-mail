<?php

return [
    'endpoint' => env('UPDATE_SERVER_URL', 'https://www.doic.net/update'),
    'current_version' => env('APP_VERSION', '1.0.0'),
    'default_channel' => env('UPDATE_CHANNEL', 'stable'),
    'required_extensions' => ['pdo', 'openssl', 'mbstring', 'json', 'curl', 'zip'],
    'history_limit' => 50,
    'signature_public_key' => env('UPDATE_SIGNATURE_PUBLIC_KEY'),
    'max_package_bytes' => (int) env('UPDATE_MAX_PACKAGE_BYTES', 64 * 1024 * 1024),
    'manual_package_disk' => 'local',
];
