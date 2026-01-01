<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class CurrencyType extends ObjectType
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
            'name'   => 'Currency',
            'fields' => [
                'label' => [
                    'type'    => Type::string(),
                    'resolve' => function ($currency) {

                        return $currency['label'] ?? null;
                    }
                ],
                'symbol' => [
                    'type'    => Type::string(),
                    'resolve' => function ($currency) {

                        return $currency['symbol'] ?? null;
                    }
                ]
            ]
        ]);
    }
}
