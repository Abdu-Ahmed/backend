<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Order;
use App\GraphQL\Types\OrderType;
use App\GraphQL\Types\OrderItemInputType;
use GraphQL\Type\Definition\Type;
use RuntimeException;

/**
 * CreateOrder mutation - creates an order with multiple items in a single request.
 */
class CreateOrderMutation
{
    /**
     * Returns the configuration array for the createOrder mutation.
     *
     * @return array The mutation configuration.
     */
    public static function get(): array
    {
        return [
            'type' => OrderType::getInstance(),
            'args' => [
                'items' => [
                    'type' => Type::nonNull(Type::listOf(Type::nonNull(OrderItemInputType::getInstance()))),
                    'description' => 'List of items to order'
                ]
            ],
            'resolve' => function ($rootValue, array $args): Order {
                // Validate items
                if (empty($args['items'])) {
                    throw new RuntimeException("Order must contain at least one item");
                }

                // Process items
                $orderItems = [];
                foreach ($args['items'] as $item) {
                    if (!isset($item['productId']) || !isset($item['quantity'])) {
                        throw new RuntimeException("Each item must have productId and quantity");
                    }

                    // Normalize selected attributes as an array of {name, value}
                    $selectedAttributes = [];
                    if (isset($item['selectedAttributes']) && is_array($item['selectedAttributes'])) {
                        foreach ($item['selectedAttributes'] as $attr) {
                            if (isset($attr['name']) && isset($attr['value'])) {
                                $selectedAttributes[] = [
                                    'name' => (string)$attr['name'],
                                    'value' => (string)$attr['value'],
                                ];
                            }
                        }
                    }

                    $orderItems[] = [
                        'productId' => (string)$item['productId'],
                        'quantity' => (int)$item['quantity'],
                        'selectedAttributes' => $selectedAttributes,
                        'price' => 0.0, // will be filled in Order::calculateTotal()
                    ];
                }

                // Create order with all items
                $order = new Order([
                    'items' => $orderItems,
                    'currencyCode' => 'USD',
                ]);

                if (!$order->create()) {
                    throw new RuntimeException("Failed to create order");
                }

                return $order;
            },
        ];
    }
}
