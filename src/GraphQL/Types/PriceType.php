<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PriceType extends ObjectType
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
            'name'   => 'Price',
            'fields' => [
                'amount' => [
                    'type'    => Type::float(),
                    'resolve' => function ($price) {
                        return isset($price['amount']) ? (float)$price['amount'] : null;
                    }
                ],
                'currency' => [
                    'type'    => CurrencyType::getInstance(),
                    'resolve' => function ($price) {
                        // Build the currency object from the separate columns.
                        return [
                            'label'  => $price['currency_label'] ?? null,
                            'symbol' => $price['currency_symbol'] ?? null,
                        ];
                    }
                ]
            ]
        ]);
    }
}
