<?php

namespace Raccoon\Utility\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface {
    protected $_layout;
    protected $_cms_block;

    public function __construct(LayoutInterface $layout, $_block_id) {
        $this->_layout = $layout;
        $this->_cms_block = $this->constructBlock($_block_id);
    }

    public function constructBlock($_block_id) {
        $block = $this->_layout->createBlock('Magento\Cms\Block\Block')
                      ->setBlockId($_block_id)->toHtml();

        return $block;
    }

    public function getConfig() {
        return [
            'cms_block' => $this->_cms_block
        ];
    }
}