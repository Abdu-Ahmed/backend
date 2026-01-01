<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class AttributeItemType extends ObjectType
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
            'name' => 'AttributeItem',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($item) {

                        return $item['id'];
                    }
                ],
                'displayValue' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($item) {

                        return $item['displayValue'];
                    }
                ],
                'value' => [
                    'type' => Type::nonNull(Type::string()),
                    'resolve' => function ($item) {

                        return $item['value'];
                    }
                ],
                '__typename' => [
                    'type' => Type::string(),
                    'resolve' => function ($item) {

                        return isset($item['__typename']) ? $item['__typename'] : null;
                    }
                ]
            ]
        ]);
    }
}
