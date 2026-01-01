<?php

namespace App\Models;

/**
 * Tech product model.
 */
class TechProduct extends Product
{
    /**
     * Get product type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'TechProduct';
    }

    /**
     * Convert to array with product-specific data.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['__typename'] = $this->getType();
        $data['specifications'] = $this->getSpecifications();
        return $data;
    }

    /**
     * Get technical specifications.
     *
     * @return array
     */
    public function getSpecifications(): array
    {
        $specs = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getType() === 'technical') {
                $specs[$attribute->getName()] = $attribute->getDefaultValue();
            }
        }
        return $specs;
    }
}
