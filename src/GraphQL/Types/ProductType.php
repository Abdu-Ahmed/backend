<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ProductType extends ObjectType
{
    private static $instance = null;

    public function __construct()
    {
        parent::__construct([
            'name'   => 'Product',
            'fields' => [
                '__typename' => [
                    'type'    => Type::string(),
                    'resolve' => function () {
                        return 'Product';
                    }
                ],
                'id'         => Type::string(),
                'name'       => Type::string(),
                'inStock'    => Type::boolean(),
                'gallery'    => Type::listOf(Type::string()),
                'description' => Type::string(),
                'prices'     => Type::listOf(new ObjectType([
                    'name'   => 'Price',
                    'fields' => [
                        'amount'   => Type::float(),
                        'currency' => new ObjectType([
                            'name'   => 'Currency',
                            'fields' => [
                                'label'  => Type::string(),
                                'symbol' => Type::string()
                            ]
                        ])
                    ]
                ])),
                'category'   => Type::string(),
                'attributes' => Type::listOf(new ObjectType([
                    'name'   => 'Attribute',
                    'fields' => [
                        '__typename' => [
                            'type'    => Type::string(),
                            'resolve' => function () {
                                return 'Attribute';
                            }
                        ],
                        'id'         => Type::string(),
                        'name'       => Type::string(),
                        'type'       => Type::string(),
                        'items'      => Type::listOf(new ObjectType([
                            'name'   => 'AttributeItem',
                            'fields' => [
                                'id'           => Type::string(),
                                'value'        => Type::string(),
                                'displayValue' => Type::string()
                            ]
                        ]))
                    ]
                ]))
            ]
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
