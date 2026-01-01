<?php

namespace App\Models;

/**
 * Technical attribute model.
 */
class TechnicalAttribute extends Attribute
{
    /**
     * Get attribute specific type.
     *
     * @return string
     */
    public function getAttributeType(): string
    {
        return 'TechnicalAttribute';
    }

    /**
     * Convert to array with attribute-specific data.
     *
     * @return array
     */
    public function toArray(): array
    {
        // Get the base attribute data.
        $data = parent::toArray();
// Override __typename with our specialized type.
        $data['__typename'] = $this->getAttributeType();
// Ensure the id field is set. If not, use the fallback from getId().
        if (empty($data['id'])) {
            $data['id'] = $this->getId();
        }

        return $data;
    }
}
