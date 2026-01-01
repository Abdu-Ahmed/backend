<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;
use App\GraphQL\Types\AttributeItemType;

class AttributeInterface extends InterfaceType
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
            'name' => 'Attribute',
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
            ],
            'resolveType' => function ($attribute) {

                if ($attribute->getAttributeType() === 'ColorAttribute') {
                    return ColorAttributeType::getInstance();
                } elseif ($attribute->getAttributeType() === 'SizeAttribute') {
                    return SizeAttributeType::getInstance();
                } elseif ($attribute->getAttributeType() === 'TechnicalAttribute') {
                    return TechnicalAttributeType::getInstance();
                }
                throw new \Exception("Unknown attribute type: " . $attribute->getAttributeType());
            }
        ]);
    }
}
