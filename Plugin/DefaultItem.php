<?php

namespace Raccoon\Utility\Plugin;

use Magento\Quote\Model\Quote\Item;

class DefaultItem {
    public function aroundGetItemData($subject, \Closure $proceed, Item $item)
    {
        $data = $proceed($item);
        $product = $item->getProduct();

        $atts = [
            "product_color" => $product->getAttributeText('color'),
        ];

        return array_merge($data, $atts);
    }
}