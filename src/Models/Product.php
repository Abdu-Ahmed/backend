<?php

namespace App\Models;

use InvalidArgumentException;
use RuntimeException;
use App\Models\AttributeFactory;

/**
 * Abstract Product model that provides common functionality for all product types.
 */
abstract class Product extends Model
{
    /**
     * @var string The product ID.
     */
    protected string $id;

    /**
     * @var string The product name.
     */
    protected string $name;

    /**
     * @var bool Whether the product is in stock.
     */
    protected bool $inStock;

    /**
     * @var array The product gallery images.
     */
    protected array $gallery;

    /**
     * @var string The product description.
     */
    protected string $description;

    /**
     * @var string The product category.
     */
    protected string $category;

    /**
     * @var string The product brand.
     */
    protected string $brand;

    /**
     * @var array The product attributes.
     */
    protected array $attributes = [];

    /**
     * @var array The product prices.
     */
    protected array $prices = [];

    /**
     * Constructor.
     *
     * @param array $data Product data.
     * @throws InvalidArgumentException If required data is missing.
     */
    public function __construct(array $data = [])
    {
        parent::__construct();

        if (!empty($data)) {
            $this->id = $data['id'];
            $this->name = $data['name'];
            $this->inStock = $data['inStock'];
            $this->gallery = $data['gallery'];
            $this->description = $data['description'];
            $this->category = $data['category'];
            $this->brand = $data['brand'];
            $this->attributes = $data['attributes'] ?? [];
            $this->prices = $data['prices'] ?? [];
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

    public function getInStock(): bool
    {
        return $this->inStock;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getPrices(): array
    {
        return $this->prices;
    }

    /**
     * Get product by ID.
     *
     * @param string $id
     * @return Product|null
     */
    public static function find(string $id): ?Product
    {
        try {
            $db = self::getDB();
            $stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            // Fetch related data
            $attributes = self::getAttributesForProduct($data['id']);
            $prices = self::getPricesForProduct($data['id']);

            // Parse gallery JSON
            $gallery = json_decode($data['gallery'], true);
            if (!is_array($gallery)) {
                $gallery = [];
            }

            $productData = [
                'id' => $data['id'],
                'name' => $data['name'],
                'inStock' => (bool)$data['inStock'],
                'gallery' => $gallery,
                'description' => $data['description'],
                'category' => $data['category'],
                'brand' => $data['brand'],
                'attributes' => $attributes,
                'prices' => $prices
            ];

            // Create the appropriate product type
            return self::createProductInstance($productData);
        } catch (\PDOException $e) {
            error_log("Database error in find(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch product: " . $e->getMessage());
        }
    }

    /**
     * Find all products.
     *
     * @return array
     */
    public static function findAll(): array
    {
        try {
            $db = self::getDB();
            $stmt = $db->query("SELECT * FROM products");
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($products as $data) {
                // Fetch related data
                $attributes = self::getAttributesForProduct($data['id']);
                $prices = self::getPricesForProduct($data['id']);

                // Parse gallery JSON
                $gallery = json_decode($data['gallery'], true);
                if (!is_array($gallery)) {
                    $gallery = [];
                }

                $productData = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'inStock' => (bool)$data['inStock'],
                    'gallery' => $gallery,
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'brand' => $data['brand'],
                    'attributes' => $attributes,
                    'prices' => $prices
                ];

                // Create the appropriate product type
                $result[] = self::createProductInstance($productData);
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Database error in findAll(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch products: " . $e->getMessage());
        }
    }

    /**
     * Find products by category.
     *
     * @param string $category
     * @return array
     */
    public static function findByCategory(string $category): array
    {
        try {
            $db = self::getDB();
            $stmt = $db->prepare("SELECT * FROM products WHERE category = :category");
            $stmt->execute([':category' => $category]);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $result = [];
            foreach ($products as $data) {
                // Fetch related data
                $attributes = self::getAttributesForProduct($data['id']);
                $prices = self::getPricesForProduct($data['id']);

                // Parse gallery JSON
                $gallery = json_decode($data['gallery'], true);
                if (!is_array($gallery)) {
                    $gallery = [];
                }

                $productData = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'inStock' => (bool)$data['inStock'],
                    'gallery' => $gallery,
                    'description' => $data['description'],
                    'category' => $data['category'],
                    'brand' => $data['brand'],
                    'attributes' => $attributes,
                    'prices' => $prices
                ];

                // Create the appropriate product type
                $result[] = self::createProductInstance($productData);
            }

            return $result;
        } catch (\PDOException $e) {
            error_log("Database error in findByCategory(): " . $e->getMessage());
            throw new RuntimeException("Failed to fetch products by category: " . $e->getMessage());
        }
    }

    /**
     * Create a product instance based on the category.
     *
     * @param array $data
     * @return Product
     * @throws InvalidArgumentException if category is unknown.
     */
    protected static function createProductInstance(array $data): Product
    {
        return match ($data['category']) {
            'clothes' => new ClothingProduct($data),
            'tech' => new TechProduct($data),
            default => throw new InvalidArgumentException("Unknown product category: {$data['category']}")
        };
    }

    /**
     * Get attributes for a product.
     *
     * @param string $productId
     * @return array
     */
    protected static function getAttributesForProduct(string $productId): array
    {
        try {
            $db = self::getDB();
            $stmt = $db->prepare("SELECT * FROM attributes WHERE product_id = :product_id");
            $stmt->execute([':product_id' => $productId]);
            $attributesData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $attributes = [];
            foreach ($attributesData as $attrData) {
                // Fetch items for this attribute
                $stmtItems = $db->prepare("SELECT id, displayValue, value, __typename FROM attribute_items WHERE attribute_id = :attribute_id");
                $stmtItems->execute([':attribute_id' => $attrData['id']]);
                $items = $stmtItems->fetchAll(\PDO::FETCH_ASSOC);

                // Create the appropriate attribute type
                $attrData['items'] = $items;
                $attributes[] = AttributeFactory::create($attrData);
            }

            return $attributes;
        } catch (\PDOException $e) {
            error_log("Error getting attributes for product $productId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get prices for a product.
     *
     * @param string $productId
     * @return array
     */
    protected static function getPricesForProduct(string $productId): array
    {
        try {
            $db = self::getDB();
            $stmt = $db->prepare("SELECT * FROM prices WHERE product_id = :product_id");
            $stmt->execute([':product_id' => $productId]);
            $prices = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $prices;
        } catch (\PDOException $e) {
            error_log("Error getting prices for product $productId: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected static function tableName(): string
    {
        return 'products';
    }

    /**
     * Convert product to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'inStock' => $this->getInStock(),
            'gallery' => $this->getGallery(),
            'description' => $this->getDescription(),
            'category' => $this->getCategory(),
            'brand' => $this->getBrand(),
            'attributes' => array_map(function ($attribute) {
                return $attribute->toArray();
            }, $this->getAttributes()),
            'prices' => $this->getPrices()
        ];
    }

    /**
     * Create a product from database data.
     *
     * @param array $data
     * @return self
     */
    protected static function hydrate(array $data): self
    {
        // Get related data
        $attributes = self::getAttributesForProduct($data['id']);
        $prices = self::getPricesForProduct($data['id']);

        // Parse gallery JSON
        $gallery = json_decode($data['gallery'], true);
        if (!is_array($gallery)) {
            $gallery = [];
        }

        $productData = [
            'id' => $data['id'],
            'name' => $data['name'],
            'inStock' => (bool)$data['inStock'],
            'gallery' => $gallery,
            'description' => $data['description'],
            'category' => $data['category'],
            'brand' => $data['brand'],
            'attributes' => $attributes,
            'prices' => $prices
        ];

        return self::createProductInstance($productData);
    }
}
