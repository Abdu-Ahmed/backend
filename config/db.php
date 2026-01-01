<?php

return [
    'host' => $_ENV['DB_HOST'] ?? ' 127.0.0.1 ',
    'dbname' => $_ENV['DB_NAME'] ?? 'assignment',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'port' => $_ENV['DB_PORT'] ?? '3306',
];
