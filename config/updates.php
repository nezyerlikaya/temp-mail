<?php

return [
    'endpoint' => env('UPDATE_SERVER_URL', 'https://www.doic.net/update'),
    'current_version' => env('APP_VERSION', '1.0.0'),
    'default_channel' => env('UPDATE_CHANNEL', 'stable'),
    'required_extensions' => ['pdo', 'openssl', 'mbstring', 'json', 'curl', 'zip'],
    'history_limit' => 50,
];
