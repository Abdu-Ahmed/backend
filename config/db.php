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
<<<<<<< HEAD
    'host' => getenv('DB_HOST') ?: getenv('RAILWAY_MYSQL_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: getenv('RAILWAY_MYSQL_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: getenv('RAILWAY_MYSQL_DATABASE'),
    'user' => getenv('DB_USER') ?: getenv('RAILWAY_MYSQL_USER'),
    'password' => getenv('DB_PASSWORD') ?: getenv('RAILWAY_MYSQL_PASSWORD'),
=======
    'host' => getenv('DB_HOST') ?: 'mysql-3e217c99-mstrtrpni-3760.g.aivencloud.com',
    'port' => getenv('DB_PORT') ?: '11062',
    'dbname' => getenv('DB_NAME') ?: 'defaultdb',
    'user' => getenv('DB_USER') ?: 'avnadmin',
    'password' => getenv('DB_PASSWORD') ?: '',
>>>>>>> 9eeaa4f (fix: use env variables for db config)
];