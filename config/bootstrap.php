<?php

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validate required environment variables
$required_vars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER'];
foreach ($required_vars as $var) {
    if (!isset($_ENV[$var])) {
        throw new RuntimeException("Environment variable {$var} is not set.");
    }
}

/**
 * Dumps a value and terminates the script.
 *
 * @param mixed $value The value to dump.
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

/**
 * Returns a full path based on the provided relative path.
 *
 * @param string $path The relative path.
 * @return string The full path.
 */
if (!function_exists('base_path')) {
    function base_path($path)
    {
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', __DIR__ . '/../');
        }
        return BASE_PATH . $path;
    }
}

/**
 * Aborts the current request with a given HTTP status code and error message.
 *
 * @param int    $code    The HTTP status code (default is 404).
 * @param string $message The error message (default is "Resource not found").
 */
if (!function_exists('abort')) {
    function abort($code = 404, $message = 'Resource not found')
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        die();
    }
}
