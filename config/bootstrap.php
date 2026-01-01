<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// --- Load environment variables from .env only if present ---
// On Railway / modern hosts you typically set environment variables in the service/project settings,
// so no .env file is necessary. We attempt to load a .env only when it exists,
// and we use safeLoad() to avoid throwing an exception.
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    try {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        // safeLoad() will not throw if file missing; since we already checked it exists this is extra safety
        $dotenv->safeLoad();
    } catch (\Throwable $e) {
        // If dotenv fails for any unexpected reason, log it but continue to let getenv() provide values.
        error_log('Dotenv load error: ' . $e->getMessage());
    }
}

// --- Determine environment and set error reporting accordingly ---
$appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'production');

if (in_array(strtolower($appEnv), ['dev', 'development', 'local'], true)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    // production (or unspecified) - don't show errors to users
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

// --- Validate required environment variables ---
// We check from getenv() to be compatible with Railway / host-provided variables.
$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'];

$missing = [];
foreach ($requiredVars as $var) {
    $value = getenv($var);
    if ($value === false || $value === null || $value === '') {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    $msg = 'Missing required environment variables: ' . implode(', ', $missing) . '.';
    // In production we throw so the app fails fast and deploys surface the problem.
    if (strtolower($appEnv) === 'production') {
        throw new RuntimeException($msg);
    } else {
        // In dev/staging: log and continue (makes local debugging smoother)
        error_log('[bootstrap] ' . $msg);
    }
}

// --- Convenience helpers ---
if (!function_exists('dd')) {
    /**
     * Dump and die helper.
     *
     * @param mixed $value
     * @return void
     */
    function dd($value): void
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
        exit(1);
    }
}

if (!function_exists('base_path')) {
    /**
     * Return absolute path relative to project base.
     *
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
        }
        // normalize leading slash
        $path = ltrim($path, '/\\');
        return BASE_PATH . $path;
    }
}

if (!function_exists('abort')) {
    /**
     * Abort the request with a JSON error message.
     *
     * @param int    $code
     * @param string $message
     * @return void
     */
    function abort(int $code = 404, string $message = 'Resource not found'): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message, 'status' => $code]);
        exit(0);
    }
}

// Optional: expose a simple debug flag for other code to check
if (!defined('APP_ENV')) {
    define('APP_ENV', $appEnv);
}
