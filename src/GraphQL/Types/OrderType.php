<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * Order GraphQL type.
 */
class OrderType extends ObjectType
{
    /**
     * @var self|null Singleton instance.
     */
    private static ?self $instance = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $config = [
            'name' => 'Order',
            'description' => 'An order containing multiple items',
            'fields' => function () {
                return [
                    'id' => [
                        'type' => Type::string(),
                        'description' => 'Order ID',
                        'resolve' => fn($order) => $order->getId()
                    ],
                    'items' => [
                    'type' => Type::listOf(new ObjectType([
                    'name' => 'OrderItem',
                    'fields' => [
                    'productId' => Type::string(),
                    'quantity' => Type::int(),
                    'price' => Type::float(),
            // selectedAttributes should be a list of name/value objects
                    'selectedAttributes' => Type::listOf(new ObjectType([
                    'name' => 'SelectedAttribute',
                    'fields' => [
                    'name' => Type::string(),
                    'value' => Type::string()
                    ]
                    ]))
                    ]
                    ])),
                'description' => 'Order items',
                'resolve' => fn($order) => $order->getItems()
                    ],
                    'totalAmount' => [
                        'type' => Type::float(),
                        'description' => 'Total order amount',
                        'resolve' => fn($order) => $order->getTotalAmount()
                    ],
                    'currencyCode' => [
                        'type' => Type::string(),
                        'description' => 'Currency code',
                        'resolve' => fn($order) => $order->getCurrencyCode()
                    ],
                    'status' => [
                        'type' => Type::string(),
                        'description' => 'Order status',
                        'resolve' => fn($order) => $order->getStatus()
                    ]
                ];
            }
        ];

        parent::__construct($config);
    }

    /**
     * Get singleton instance.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
