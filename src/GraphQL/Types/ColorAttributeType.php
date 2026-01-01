<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ObjectType;

class ColorAttributeType extends ObjectType
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
            'name' => 'ColorAttribute',
            'interfaces' => [AttributeInterface::getInstance()],
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($attribute) {

                        return $attribute->getId();
                    }
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($attribute) {

                        return $attribute->getName();
                    }
                ],
                'type' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($attribute) {

                        return $attribute->getType();
                    }
                ],
                'items' => [
                    'type' => Type::listOf(AttributeItemType::getInstance()),
                    'resolve' => function ($attribute) {

                        return $attribute->getItems();
                    }
                ]
            ]
        ]);
    }
}
