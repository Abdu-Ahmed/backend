<?php

namespace App\Models;

use RuntimeException;

/**
 * Category model.
 */
class Category extends Model
{
    /**
     * @var string The category ID.
     */
    protected string $id;
/**
     * @var string The category name.
     */
    protected string $name;
/**
     * @var string The category description.
     */
    protected string $description;
/**
     * Constructor.
     *
     * @param array $data Category data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->description = $data['description'] ?? '';
        }
    }

    // Explicit getter methods:

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Find a category by name.
     *
     * @param string $name
     * @return Category|null
     */
    public static function findByName(string $name): ?Category
    {
        $model = new static();
        try {
            $stmt = $model->db->prepare("SELECT * FROM categories WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$data) {
                return null;
            }

            return new static($data);
        } catch (\PDOException $e) {
            error_log("Database error in findByName(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch category: " . $e->getMessage());
        }
    }

    /**
     * Get products for this category.
     *
     * @return array
     */
    public function getProducts(): array
    {
        return Product::findByCategory($this->getName());
    }

    /**
     * Convert category to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            '__typename' => 'Category'
        ];
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected static function tableName(): string
    {
        return 'categories';
    }

    /**
     * Create a category from database data.
     *
     * @param array $data
     * @return self
     */
    protected static function hydrate(array $data): self
    {
        return new static($data);
    }
}
