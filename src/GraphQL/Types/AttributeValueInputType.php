<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * GraphQL input type for attribute values.
 */
class AttributeValueInputType extends InputObjectType
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
            'name' => 'AttributeValueInput',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'value' => Type::nonNull(Type::string())
            ]
        ]);
    }
}
