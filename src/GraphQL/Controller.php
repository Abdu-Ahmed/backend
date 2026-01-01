<?php

namespace App\GraphQL;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Error\DebugFlag;
use Throwable;
use App\Controller\GraphQL;
use RuntimeException;

class Controller
{
    /**
     * Handles an incoming GraphQL HTTP request.
     *
     * @return string JSON encoded result of the GraphQL query execution.
     * @throws RuntimeException if input cannot be read.
     */
    public function handle(): string
    {
        try {
            // Use the central schema that includes products, product, and categories fields
            $schema = GraphQL::get();

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            error_log("GraphQL Input: " . $rawInput);

            $input = json_decode($rawInput, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON: ' . json_last_error_msg());
            }

            $query = $input['query'] ?? null;
            if (!$query) {
                throw new RuntimeException('No GraphQL query provided');
            }

            $variableValues = $input['variables'] ?? null;
            error_log("Executing GraphQL query: " . $query);

            // Add debug flags to see detailed error information during development
            // Remove or adjust for production
            $debugFlag = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray($debugFlag);

            // Ensure output contains data field even if empty
            if (!isset($output['data'])) {
                $output['data'] = null;
            }

            if (isset($output['errors'])) {
                error_log("GraphQL errors: " . json_encode($output['errors']));
            }

            error_log("GraphQL Output: " . json_encode($output));
        } catch (Throwable $e) {
            error_log("GraphQL error: " . $e->getMessage());
            error_log($e->getTraceAsString());

            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                        'locations' => null,
                        'path' => null,
                        'extensions' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => explode("\n", $e->getTraceAsString())
                        ]
                    ]
                ],
                'data' => null
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');
        return json_encode($output);
    }
}
