<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/bootstrap.php';

use GraphQL\GraphQL;
use App\Controller\GraphQL as AppGraphQL;

$tests = [
    'allProducts' => [
        'name' => 'All Products Query',
        'query' => 'query {
            products {
                id
                name
                __typename
                prices {
                    amount
                    currency {
                        label
                        symbol
                    }
                }
            }
        }',
        'variables' => []
    ],
    'singleProduct' => [
        'name' => 'Single Product Query',
        'query' => 'query ($id: String!) {
            product(id: $id) {
                id
                name
                __typename
                prices {
                    amount
                    currency {
                        label
                        symbol
                    }
                }
            }
        }',
        // Replace "ps-5" with a valid product ID from your test database.
        'variables' => ['id' => 'ps-5']
    ],
    'allCategories' => [
        'name' => 'All Categories Query',
        'query' => 'query {
            categories {
                id
                name
                __typename
            }
        }',
        'variables' => []
    ],
    'singleCategory' => [
        'name' => 'Single Category Query',
        'query' => 'query ($name: String!) {
            category(name: $name) {
                id
                name
                __typename
                products {
                    id
                    name
                    __typename
                }
            }
        }',
        // Replace "tech" with a valid category name from your test database.
        'variables' => ['name' => 'tech']
    ],
    'addToCart' => [
        'name' => 'Add to Cart Mutation',
        'query' => 'mutation ($productId: String!, $quantity: Int!, $attributes: [AttributeValueInput]) {
  addToCart(productId: $productId, quantity: $quantity, attributes: $attributes) {
    id
    productId
    quantity
    attributes {
      name
      value
    }
  }
}
',
        // Adjust values as needed to match valid test data.
        'variables' => [
            'productId'  => 'ps-5',
            'quantity'   => 1,
            'attributes' => []
        ]
    ]
];

echo "=== Final Integration Testing GraphQL Schema ===\n\n";

// Build the complete schema (which internally uses all of your types and resolvers)
$schema = AppGraphQL::get();

foreach ($tests as $testName => $test) {
    echo "Testing: {$test['name']}\n";
    echo "------------------------------\n";
    
    try {
        $result = \GraphQL\GraphQL::executeQuery(
            $schema,
            $test['query'],
            null,
            null,
            $test['variables']
        );
        $output = $result->toArray();
        
        if (isset($output['errors'])) {
            echo "❌ {$test['name']} FAILED\n";
            echo "Query:\n" . $test['query'] . "\n";
            echo "Variables:\n" . json_encode($test['variables'], JSON_PRETTY_PRINT) . "\n";
            echo "Errors:\n" . json_encode($output['errors'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "✅ {$test['name']} PASSED\n";
            echo "Result:\n" . json_encode($output, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (Throwable $e) {
        echo "❌ {$test['name']} CRASHED\n";
        echo "Query:\n" . $test['query'] . "\n";
        echo "Variables:\n" . json_encode($test['variables'], JSON_PRETTY_PRINT) . "\n";
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n------------------------------\n\n";
}

echo "Final integration testing complete.\n";
