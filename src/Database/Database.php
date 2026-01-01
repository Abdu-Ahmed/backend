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

            // Log ALL configuration details (except password)
            error_log("=== DATABASE CONNECTION ATTEMPT ===");
            error_log("Host: " . $config['host']);
            error_log("Port: " . $config['port']);
            error_log("DB Name: " . $config['dbname']);
            error_log("User: " . $config['user']);
            error_log("Password set: " . (!empty($config['password']) ? 'YES' : 'NO'));
            
            // Also check environment variables directly
            error_log("Env MYSQLHOST: " . getenv('MYSQLHOST'));
            error_log("Env MYSQLPORT: " . getenv('MYSQLPORT'));
            error_log("Env MYSQLDATABASE: " . getenv('MYSQLDATABASE'));
            error_log("Env MYSQLUSER: " . getenv('MYSQLUSER'));

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                $config['host'],
                $config['port'],
                $config['dbname']
            );

            error_log("Full DSN: " . $dsn);

            // Try with connection timeout
            self::$instance = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5, // 5 second timeout
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );

            error_log("✓ Database connection successful");
            
        } catch (PDOException $e) {
            error_log("✗ Connection failed: " . $e->getMessage());
            error_log("Error Code: " . $e->getCode());
            error_log("Full Error: " . print_r($e, true));
            
            throw new RuntimeException(
                "Database connection failed: " . $e->getMessage() . 
                "\nCheck Railway logs for more details."
            );
        }
    }

    return self::$instance;
}
}
