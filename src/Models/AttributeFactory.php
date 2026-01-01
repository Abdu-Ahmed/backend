<?php

namespace App\Models;

use InvalidArgumentException;

/**
 * Factory for creating attribute instances.
 */
class AttributeFactory
{
    /**
     * Create an attribute instance based on the data.
     *
     * @param array $data
     * @return Attribute
     */
    public static function create(array $data): Attribute
    {
        if (!isset($data['type'])) {
            throw new InvalidArgumentException("Attribute type is required");
        }

        return match (strtolower($data['type'])) {
            'color' => new ColorAttribute($data),
            'size'  => new SizeAttribute($data),
            default => new TechnicalAttribute($data),
        };
    }
}
