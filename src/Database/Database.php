<?php

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Class Connection
 *
 * Provides a singleton PDO instance with robust error handling.
 *
 * @package App\Database
 */
class Database
{
    /**
     * @var PDO|null Singleton PDO instance.
     */
    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Returns the singleton PDO instance.
     *
     * Reads database configuration from a PHP file, logs connection details,
     * and applies robust error handling. If connection fails, logs error details
     * and throws a RuntimeException.
     *
     * @return PDO
     * @throws RuntimeException if the database connection fails.
     */
public static function getInstance(): PDO
{
    if (self::$instance === null) {
        try {
            // Load database configuration
            $config = include __DIR__ . '/../../config/db.php';

            // Log configuration details
            error_log("Attempting database connection with following config:");
            error_log("Host: " . $config['host']);
            error_log("DB Name: " . $config['dbname']);
            error_log("User: " . $config['user']);
            error_log("Port: " . $config['port']);

            // Use the host from config, NOT hardcoded 127.0.0.1
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                $config['host'],  // ← Changed from '127.0.0.1' to $config['host']
                $config['port'],
                $config['dbname']
            );

            error_log("DSN: " . $dsn);

            // Create the PDO instance with error and fetch mode options
            self::$instance = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false, // Important for Railway
                    PDO::MYSQL_ATTR_SSL_CA => getenv('MYSQL_SSL_CA_PATH') ?: null,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            error_log("Database connection successful");
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new RuntimeException(
                "Connection failed: " . $e->getMessage() .
                "\nDSN: " . ($dsn ?? 'N/A') .
                "\nUser: " . $config['user'] .
                "\nHost: " . $config['host']
            );
        }
    }

    return self::$instance;
}
}
