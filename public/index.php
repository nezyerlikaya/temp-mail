<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$basePath = dirname(__DIR__);
$environmentPath = $basePath.'/.env';
$environmentExamplePath = $basePath.'/.env.example';
$installerRecoveryPath = $basePath.'/storage/app/installer-recovery.flag';
$environmentRecovered = false;

if (! file_exists($environmentPath) && file_exists($environmentExamplePath)) {
    copy($environmentExamplePath, $environmentPath);
    $environmentRecovered = true;
}

if (file_exists($environmentPath)) {
    $environmentContents = file_get_contents($environmentPath);

    if ($environmentContents !== false && preg_match('/^APP_KEY=\s*$/m', $environmentContents)) {
        $environmentContents = preg_replace(
            '/^APP_KEY=\s*$/m',
            'APP_KEY=base64:'.base64_encode(random_bytes(32)),
            $environmentContents
        ) ?? $environmentContents;

        file_put_contents($environmentPath, $environmentContents, LOCK_EX);
        $environmentRecovered = true;
    }
}

if ($environmentRecovered) {
    if (! is_dir(dirname($installerRecoveryPath))) {
        mkdir(dirname($installerRecoveryPath), 0755, true);
    }

    file_put_contents($installerRecoveryPath, 'recovered_at='.date(DATE_ATOM).PHP_EOL, LOCK_EX);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
