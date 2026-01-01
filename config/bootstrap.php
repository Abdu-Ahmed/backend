<?php
// config/bootstrap.php

// Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Define BASE_PATH constant if not set
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);
}

/**
 * Helper to read environment variables from getenv() or $_ENV (Railway supplies env vars)
 */
function env(string $key, $default = null) {
    $val = getenv($key);
    if ($val === false) {
        // fallback to $_ENV if getenv returned false
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        return $default;
    }
    return $val;
}

/**
 * Attempt to load .env using vlucas/phpdotenv only if a .env file exists.
 * This prevents an InvalidPathException on platforms (like Railway) where env
 * vars are set in the environment and no .env file is present.
 */
$envPath = __DIR__ . '/..';
$envFile = $envPath . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($envPath);
        $dotenv->load();
    } catch (Throwable $e) {
        // If dotenv fails for any reason, log and continue — environment may still be provided
        error_log("Dotenv load warning: " . $e->getMessage());
    }
}

// Determine debug mode from env `APP_DEBUG` (accepts "1", "true", "on")
$appDebug = strtolower((string) (env('APP_DEBUG', '0'))) === '1' ||
            strtolower((string) (env('APP_DEBUG', '0'))) === 'true' ||
            strtolower((string) (env('APP_ENV', ''))) === 'development';

// Configure PHP error reporting and display depending on debug mode
error_reporting(E_ALL);
if ($appDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

// Set a friendly timezone if not already set (optional)
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/**
 * Error and exception handlers: in debug mode return/echo detailed info;
 * in production log to error_log() (captured by Railway logs) and keep responses minimal.
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($appDebug) {
    $msg = sprintf("PHP ERROR [%d] %s in %s on line %d", $errno, $errstr, $errfile, $errline);
    if ($appDebug) {
        // display and log
        header('Content-Type: text/plain', true, 500);
        echo $msg . PHP_EOL . PHP_EOL;
        debug_print_backtrace();
    } else {
        error_log($msg);
        // don't reveal internals to clients
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Internal Server Error']);
    }
    exit(1);
});

set_exception_handler(function ($e) use ($appDebug) {
    $msg = sprintf("Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
                   $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    if ($appDebug) {
        header('Content-Type: text/plain', true, 500);
        echo $msg;
    } else {
        error_log($msg);
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Internal Server Error']);
    }
    exit(1);
});

// Catch fatal errors on shutdown
register_shutdown_function(function () use ($appDebug) {
    $err = error_get_last();
    if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = sprintf("Fatal error: %s in %s on line %d", $err['message'], $err['file'], $err['line']);
        if ($appDebug) {
            header('Content-Type: text/plain', true, 500);
            echo $msg;
        } else {
            error_log($msg);
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Internal Server Error']);
        }
    }
});

/**
 * Validate required environment variables (lookups use env() above so Railway vars work).
 * If a required variable is missing, show detailed message in debug; otherwise log & abort.
 */
$required_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'];
$missing = [];
foreach ($required_vars as $var) {
    $value = env($var);
    if ($value === null || $value === false || $value === '') {
        $missing[] = $var;
    }
}
if (!empty($missing)) {
    $msg = "Required environment variable(s) missing: " . implode(', ', $missing);
    if ($appDebug) {
        // throw an exception so it shows full trace (debug mode)
        throw new RuntimeException($msg);
    } else {
        error_log($msg);
        // graceful JSON response for API clients
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server misconfiguration']);
        exit(1);
    }
}

/**
 * Convenience helpers (unchanged semantics from your previous file).
 */
if (!function_exists('dd')) {
    function dd($value)
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        die();
    }
}

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return BASE_PATH . ltrim($path, '/\\');
    }
}

if (!function_exists('abort')) {
    function abort($code = 404, $message = 'Resource not found')
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        die();
    }
}
