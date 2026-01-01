<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class TechProductType extends ObjectType
{
    private static $instance;
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        parent::__construct([
            'name'       => 'TechProduct',
            'interfaces' => [ProductInterface::getInstance()],
            'fields'     => function () {

                return [
                    'id' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getId();
                        }
                    ],
                    'name' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getName();
                        }
                    ],
                    'inStock' => [
                        'type'    => Type::nonNull(Type::boolean()),
                        'resolve' => function ($product) {
                            return $product->getInStock();
                        }
                    ],
                    'gallery' => [
                        'type'    => Type::listOf(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getGallery();
                        }
                    ],
                    'description' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getDescription();
                        }
                    ],
                    'category' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getCategory();
                        }
                    ],
                    'brand' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {
                            return $product->getBrand();
                        }
                    ],
                    'attributes' => [
                        'type'    => Type::listOf(AttributeInterface::getInstance()),
                        'resolve' => function ($product) {
                            return $product->getAttributes();
                        }
                    ],
                    'prices' => [
                        'type'    => Type::listOf(PriceType::getInstance()),
                        'resolve' => function ($product) {
                            return $product->getPrices();
                        }
                    ],
                    'specifications' => [
                        'type'    => Type::nonNull(Type::string()),
                        'resolve' => function ($product) {

                            // Assuming getSpecifications() returns an array of technical data;
                            // here we encode it as a JSON string.
                            return json_encode($product->getSpecifications());
                        }
                    ]
                ];
            }
        ]);
    }
}
