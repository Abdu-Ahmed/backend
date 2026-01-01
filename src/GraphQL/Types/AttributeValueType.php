<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL type for attribute values.
 */
class AttributeValueType extends ObjectType
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
            'name' => 'AttributeValue',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'value' => Type::nonNull(Type::string())
            ]
        ]);
    }
}
