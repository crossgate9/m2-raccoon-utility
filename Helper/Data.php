<?php
namespace Raccoon\Utility\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper {

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getMailChimpListId() {
        return $this->scopeConfig->getValue(
            'repeat_general/repeat_mailchimp/repeat_listing_id', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnabled() {
        return true;
    }
}
