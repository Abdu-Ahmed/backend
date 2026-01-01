<?php

declare(strict_types=1);

/**
 * Application bootstrap
 *
 * - Loads environment variables when a .env file exists (non-fatal when missing).
 * - Provides env() helper.
 * - Sets error reporting based on APP_ENV / DEBUG.
 * - Adds exception and shutdown handlers that log and, in dev, return detailed JSON.
 */

// Define base path early so other helpers can use it
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/../');
}

/**
 * Ensure we have a function to resolve full paths.
 */
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $p = rtrim(BASE_PATH, '/\\') . '/';
        return $p . ltrim($path, '/\\');
    }
}

/**
 * Safe dotenv load:
 * - If a .env file exists in the project root, load it.
 * - If it does not exist, skip loading (we expect real env vars to come from Railway).
 */
try {
    // Only attempt to load if .env exists
    $envFile = base_path('.env');
    if (file_exists($envFile) && is_readable($envFile)) {
        $dotenv = Dotenv\Dotenv::createImmutable(base_path());
        $dotenv->load();
    }
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // No .env found — harmless on Railway; continue
} catch (\Throwable $e) {
    // Unexpected error while loading dotenv — rethrow so our handler will show it
    throw $e;
}

/**
 * env helper: checks $_ENV, $_SERVER, and getenv()
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }
        $v = getenv($key);
        return $v === false ? $default : $v;
    }
}

/**
 * Development flags and error reporting
 */
$appEnv = (string) (env('APP_ENV', env('ENV', 'production')) ?? 'production');
$debug = filter_var(env('DEBUG', ($appEnv === 'development' ? '1' : '0')), FILTER_VALIDATE_BOOLEAN);

// Configure error display/logging
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
    // Put logs inside storage/logs (create if missing)
    $logDir = base_path('storage/logs');
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    ini_set('error_log', $logDir . '/php-error.log');
}

/**
 * Global exception handler: logs full trace and returns JSON on API requests.
 */
set_exception_handler(function (\Throwable $e) use ($debug) {
    // Always log full error and trace
    error_log("Uncaught Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Decide response format: JSON if request likely expecting JSON, otherwise plain text
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isJsonRequest = stripos($accept, 'application/json') !== false
                     || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    http_response_code(500);
    if ($isJsonRequest || php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        if ($debug) {
            echo json_encode([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ], JSON_PRETTY_PRINT);
        } else {
            echo json_encode(['error' => 'Server misconfiguration']);
        }
    } else {
        // CLI or plain text
        if ($debug) {
            echo "Uncaught Exception: " . $e->getMessage() . "\n\n" . $e->getTraceAsString();
        } else {
            echo "Server misconfiguration\n";
        }
    }
    exit(1);
});

/**
 * Shutdown handler to catch fatal errors (parse/compile/fatal)
 */
register_shutdown_function(function () use ($debug) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $message = sprintf("Fatal error: %s in %s on line %d", $err['message'], $err['file'], $err['line']);
        error_log($message);
        if ($debug) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $message, 'error_details' => $err], JSON_PRETTY_PRINT);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Server misconfiguration']);
        }
    }
});

/**
 * Helpers: dd() and abort()
 */
if (!function_exists('dd')) {
    function dd($value)
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        exit;
    }
}

if (!function_exists('abort')) {
    function abort(int $code = 404, string $message = 'Resource not found')
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        exit;
    }
}

/**
 * Validate required environment variables (fail early with helpful message in dev)
 */
$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'];
$missing = [];
foreach ($requiredVars as $v) {
    if (env($v) === null || env($v) === '') {
        $missing[] = $v;
    }
}
if (!empty($missing)) {
    $msg = 'Missing required environment variables: ' . implode(', ', $missing);
    if ($debug) {
        // Throw so our exception handler returns a full trace
        throw new RuntimeException($msg);
    } else {
        // Log and abort with generic message in production
        error_log($msg);
        // Send 500 JSON response and terminate
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Server misconfiguration']);
        exit;
    }
}
