<?php

namespace App\Models;

/**
 * Clothing product model.
 */
class ClothingProduct extends Product
{
    /**
     * Get product type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'ClothingProduct';
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
        $data['sizes'] = $this->getSizes();
        return $data;
    }

    /**
     * Get available sizes for the clothing product.
     *
     * @return array
     */
    public function getSizes(): array
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getName() === 'Size') {
                return array_map(function ($item) {
                    return $item['value'];
                }, $attribute->getItems());
            }
        }
        return [];
    }
}
