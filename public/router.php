<?php
// Development router for PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    
    if (is_file($file)) {
        return false; // Serve static files
    }
}

require_once __DIR__ . '/index.php';