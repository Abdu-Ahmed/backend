<?php

return [
    'host' => getenv('DB_HOST') ?: 'mysql.railway.internal',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'railway',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: 'UlqoiYvsJzCPrwBjIwITlDtyJcCbCgSz',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];