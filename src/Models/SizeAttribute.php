<?php

namespace App\Models;

/**
 * Size attribute model.
 */
class SizeAttribute extends Attribute
{
    /**
     * Get attribute specific type.
     *
     * @return string
     */
    public function getAttributeType(): string
    {
        return 'SizeAttribute';
    }

    /**
     * Convert to array with attribute-specific data.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['__typename'] = $this->getAttributeType();
        return $data;
    }

    /**
     * Check if a size is available.
     *
     * @param string $size
     * @return bool
     */
    public function hasSize(string $size): bool
    {
        foreach ($this->items as $item) {
            if (strtolower($item['value']) === strtolower($size)) {
                return true;
            }
        }

        return false;
    }
}
