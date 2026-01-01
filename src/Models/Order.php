<?php

declare(strict_types=1);

namespace App\Models;

use RuntimeException;
use PDOException;

/**
 * Order model - handles orders with multiple items.
 */
class Order extends Model
{
    /**
     * @var string|null The order ID.
     */
    protected ?string $id = null;

    /**
     * @var array The order items.
     */
    protected array $items = [];

    /**
     * @var float Total amount.
     */
    protected float $totalAmount = 0.0;

    /**
     * @var string Currency code.
     */
    protected string $currencyCode = 'USD';

    /**
     * @var string Order status.
     */
    protected string $status = 'pending';

    /**
     * Constructor.
     *
     * @param array $data Order data.
     */
    public function __construct(array $data = [])
    {
        parent::__construct();

        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->items = $data['items'] ?? [];
            $this->totalAmount = $data['totalAmount'] ?? 0.0;
            $this->currencyCode = $data['currencyCode'] ?? 'USD';
            $this->status = $data['status'] ?? 'pending';
        }
    }

    /**
     * Create a new order with multiple items.
     *
     * @return bool Whether the order was created successfully.
     */
    public function create(): bool
    {
        if (empty($this->items)) {
            throw new RuntimeException("Order must contain at least one item");
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Generate unique order ID
            $this->id = $this->generateOrderId();

            // Calculate total amount
            $this->calculateTotal();

            // Insert order
            $stmt = $this->db->prepare(
                "INSERT INTO orders (id, total_amount, currency_code, status) 
                 VALUES (:id, :total_amount, :currency_code, :status)"
            );

            $stmt->execute([
                ':id' => $this->id,
                ':total_amount' => $this->totalAmount,
                ':currency_code' => $this->currencyCode,
                ':status' => $this->status,
            ]);

            // Insert order items
            $stmtItem = $this->db->prepare(
                "INSERT INTO order_items (order_id, product_id, quantity, price, selected_attributes) 
                 VALUES (:order_id, :product_id, :quantity, :price, :selected_attributes)"
            );

            foreach ($this->items as $item) {
                $stmtItem->execute([
                    ':order_id' => $this->id,
                    ':product_id' => $item['productId'],
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price'],
                    ':selected_attributes' => json_encode($item['selectedAttributes'] ?? []),
                ]);
            }

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback on error
            $this->db->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            throw new RuntimeException("Failed to create order: " . $e->getMessage());
        }
    }

    /**
     * Calculate total amount from items.
     */
    protected function calculateTotal(): void
    {
        $total = 0.0;

        foreach ($this->items as $idx => $item) {
            // Get product price from database
            $stmt = $this->db->prepare(
                "SELECT amount FROM prices WHERE product_id = :product_id LIMIT 1"
            );
            $stmt->execute([':product_id' => $item['productId']]);
            $priceRow = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($priceRow && isset($priceRow['amount'])) {
                $itemPrice = (float)$priceRow['amount'];
            } else {
                // fallback price for missing product price
                $itemPrice = 0.0;
            }

            // update item price back into the items array so inserts get the correct price
            $this->items[$idx]['price'] = $itemPrice;

            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
            $total += $itemPrice * $quantity;
        }

        $this->totalAmount = $total;
    }


    /**
     * Generate unique order ID.
     *
     * @return string
     */
    protected function generateOrderId(): string
    {
        return 'order_' . uniqid() . '_' . time();
    }

    /**
     * Get the order ID.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get order items.
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Set order items.
     *
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Get total amount.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * Get currency code.
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Convert order to array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'items' => $this->getItems(),
            'totalAmount' => $this->getTotalAmount(),
            'currencyCode' => $this->getCurrencyCode(),
            'status' => $this->getStatus(),
            '__typename' => 'Order'
        ];
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    protected static function tableName(): string
    {
        return 'orders';
    }

    /**
     * Hydrate the model from array data.
     *
     * @param array $data
     * @return self
     */
    protected static function hydrate(array $data): self
    {
        return new self([
            'id' => $data['id'] ?? null,
            'items' => $data['items'] ?? [],
            'totalAmount' => $data['totalAmount'] ?? 0.0,
            'currencyCode' => $data['currencyCode'] ?? 'USD',
            'status' => $data['status'] ?? 'pending',
        ]);
    }
}
