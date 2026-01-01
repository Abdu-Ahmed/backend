<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CategoryType extends ObjectType
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
            'name'   => 'Category',
            'fields' => [
                'id' => [
                    'type'    => Type::nonNull(Type::string()),
                    'resolve' => function ($category) {
                        return $category->getId();
                    }
                ],
                'name' => [
                    'type'    => Type::nonNull(Type::string()),
                    'resolve' => function ($category) {
                        return $category->getName();
                    }
                ],
                'description' => [
                    'type'    => Type::string(),
                    'resolve' => function ($category) {
                        return $category->getDescription();
                    }
                ],
                '__typename' => [
                    'type'    => Type::string(),
                    'resolve' => function () {
                        return 'Category';
                    }
                ],
                'products' => [
                    'type'    => Type::listOf(ProductInterface::getInstance()),
                    'resolve' => function ($category) {

                        return $category->getProducts();
                    }
                ]
            ]
        ]);
    }
}
