<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * OrderItemInput type for creating orders with multiple items.
 */
class OrderItemInputType extends InputObjectType
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
            'name' => 'OrderItemInput',
            'description' => 'Input type for a single item in an order',
            'fields' => [
                'productId' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The product ID'
                ],
                'quantity' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'Quantity to order'
                ],
                'selectedAttributes' => [
                    'type' => Type::listOf(AttributeValueInputType::getInstance()),
                    'description' => 'Selected product attributes (size, color, etc)'
                ]
            ]
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
