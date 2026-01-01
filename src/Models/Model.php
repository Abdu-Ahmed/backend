<?php

namespace App\Models;

use App\Database\Database;
use PDO;
use RuntimeException;

/**
 * Abstract base model class that provides common functionality for all models.
 */
abstract class Model
{
    /**
     * @var PDO Database connection.
     */
    protected PDO $db;
/**
     * Constructor.
     *
     * Initializes the database connection.
     *
     * @throws RuntimeException if connection fails.
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Convert model to array representation.
     *
     * @return array
     */
    abstract public function toArray(): array;
/**
     * Get the table name for this model.
     *
     * @return string
     */
    abstract protected static function tableName(): string;
/**
     * Find a record by ID.
     *
     * @param string $id
     * @return static|null
     */
    public static function find(string $id): ?self
    {
        $model = new static();
        try {
            $stmt = $model->db->prepare("SELECT * FROM " . static::tableName() . " WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return static::hydrate($data);
        } catch (\PDOException $e) {
            error_log("Database error in find(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch record: " . $e->getMessage());
        }
    }

    /**
     * Find all records.
     *
     * @return array
     */
    public static function findAll(): array
    {
        $model = new static();
        try {
            $stmt = $model->db->query("SELECT * FROM " . static::tableName());
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function ($data) {

                return static::hydrate($data);
            }, $records);
        } catch (\PDOException $e) {
            error_log("Database error in findAll(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch records: " . $e->getMessage());
        }
    }

    /**
     * Find records by a field value.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function findBy(string $field, $value): array
    {
        $model = new static();
        try {
            $stmt = $model->db->prepare("SELECT * FROM " . static::tableName() . " WHERE $field = :value");
            $stmt->execute([':value' => $value]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map(function ($data) {

                return static::hydrate($data);
            }, $records);
        } catch (\PDOException $e) {
            error_log("Database error in findBy(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch records: " . $e->getMessage());
        }
    }

    /**
     * Create a model instance from database data.
     *
     * @param array $data
     * @return static
     */
    abstract protected static function hydrate(array $data): self;
/**
 * Get database connection.
 *
 * @return PDO
 */
    protected static function getDB(): PDO
    {
        return Database::getInstance();
    }
}
