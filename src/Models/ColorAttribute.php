<?php

namespace App\Models;

/**
 * Color attribute model.
 */
class ColorAttribute extends Attribute
{
    /**
     * Get attribute specific type.
     *
     * @return string
     */
    public function getAttributeType(): string
    {
        return 'ColorAttribute';
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
     * Check if a color is available.
     *
     * @param string $color
     * @return bool
     */
    public function hasColor(string $color): bool
    {
        foreach ($this->items as $item) {
            if (strtolower($item['value']) === strtolower($color)) {
                return true;
            }
        }

        return false;
    }
}
