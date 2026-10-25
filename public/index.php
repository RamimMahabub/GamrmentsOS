<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
    $storagePath = sys_get_temp_dir().'/garmentos-storage';

    if (PHP_OS_FAMILY === 'Windows') {
        $storagePath = '\\\\?\\'.$storagePath;
    }

    foreach (['app/private', 'app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
        $directoryPath = $storagePath.'/'.$directory;

        if (! is_dir($directoryPath) && ! mkdir($directoryPath, 0775, true) && ! is_dir($directoryPath)) {
            throw new RuntimeException('Unable to create the application storage directory.');
        }
    }

    foreach ([
        'LARAVEL_STORAGE_PATH' => $storagePath,
        'VIEW_COMPILED_PATH' => $storagePath.'/framework/views',
        'APP_PACKAGES_CACHE' => $storagePath.'/packages.php',
        'APP_SERVICES_CACHE' => $storagePath.'/services.php',
    ] as $name => $path) {
        $_ENV[$name] = $_SERVER[$name] = $path;
    }
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
