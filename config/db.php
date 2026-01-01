<?php

return [
    'host' => getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('MYSQLDATABASE') ?: getenv('DB_DATABASE') ?: 'assignment',
    'user' => getenv('MYSQLUSER') ?: getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '',
];