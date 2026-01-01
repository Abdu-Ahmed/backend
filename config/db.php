<?php

// First try DATABASE_URL (Railway often provides this)
if ($url = getenv('DATABASE_URL')) {
    $parts = parse_url($url);
    return [
        'host' => $parts['host'],
        'port' => $parts['port'] ?? '3306',
        'dbname' => ltrim($parts['path'], '/'),
        'user' => $parts['user'],
        'password' => $parts['pass'],
    ];
}

// Fall back to individual variables
return [
    'host' => getenv('MYSQLHOST') ?: getenv('RAILWAY_MYSQL_HOST') ?: '127.0.0.1',
    'port' => getenv('MYSQLPORT') ?: getenv('RAILWAY_MYSQL_PORT') ?: '3306',
    'dbname' => getenv('MYSQLDATABASE') ?: getenv('RAILWAY_MYSQL_DATABASE'),
    'user' => getenv('MYSQLUSER') ?: getenv('RAILWAY_MYSQL_USER'),
    'password' => getenv('MYSQLPASSWORD') ?: getenv('RAILWAY_MYSQL_PASSWORD'),
];