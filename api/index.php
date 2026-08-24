<?php

/*
|--------------------------------------------------------------------------
| Vercel serverless bootstrap
|--------------------------------------------------------------------------
|
| Vercel lambdas ship a read-only filesystem: only /tmp is writable. Laravel
| needs to write compiled views, cached config and session/cache files, so we
| relocate every writable path into /tmp before booting the framework.
|
*/

$storage = '/tmp/storage';
$bootstrapCache = '/tmp/bootstrap/cache';

/**
 * Écrit un diagnostic sur php://stderr : c'est le seul canal repris par les
 * Runtime Logs de Vercel, le disque de logs Laravel n'existant pas ici.
 */
$stderr = static function (string $message): void {
    file_put_contents('php://stderr', '[vercel] '.$message.PHP_EOL);
};

$directories = [
    $storage.'/app/public',
    $storage.'/framework/cache/data',
    $storage.'/framework/sessions',
    $storage.'/framework/views',
    $storage.'/logs',
    $bootstrapCache,
];

foreach ($directories as $directory) {
    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        $stderr("impossible de créer $directory");

        continue;
    }

    if (! is_writable($directory)) {
        $stderr("répertoire non inscriptible : $directory");
    }
}

$defaults = [
    // Storage + bootstrap cache locations.
    'LARAVEL_STORAGE_PATH' => $storage,
    'VIEW_COMPILED_PATH' => $storage.'/framework/views',
    'APP_SERVICES_CACHE' => $bootstrapCache.'/services.php',
    'APP_PACKAGES_CACHE' => $bootstrapCache.'/packages.php',
    'APP_CONFIG_CACHE' => $bootstrapCache.'/config.php',
    'APP_ROUTES_CACHE' => $bootstrapCache.'/routes-v7.php',
    'APP_EVENTS_CACHE' => $bootstrapCache.'/events.php',

    // Drivers that must not depend on a writable project directory.
    'LOG_CHANNEL' => 'stderr',
    'LOG_STACK' => 'stderr',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'APP_MAINTENANCE_DRIVER' => 'file',
];

foreach ($defaults as $key => $value) {
    if (getenv($key) === false || getenv($key) === '') {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

/*
|--------------------------------------------------------------------------
| Diagnostic des 500
|--------------------------------------------------------------------------
|
| Une exception levée avant que Laravel n'ait installé son propre handler
| (autoloader manquant, config illisible, driver PDO absent…) ne laisse aucune
| trace : Vercel renvoie un 500 nu. On imprime donc classe, message, fichier,
| ligne et pile sur stderr avant de laisser l'erreur suivre son cours.
|
*/

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', 'php://stderr');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e) use ($stderr): void {
    $stderr(sprintf(
        '%s: %s in %s:%d%s%s',
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        PHP_EOL,
        $e->getTraceAsString()
    ));

    http_response_code(500);
});

register_shutdown_function(static function () use ($stderr): void {
    $error = error_get_last();

    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $stderr(sprintf(
            'Fatal error: %s in %s:%d',
            $error['message'],
            $error['file'],
            $error['line']
        ));
    }
});

require __DIR__.'/../public/index.php';
