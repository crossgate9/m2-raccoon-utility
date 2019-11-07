<?php

namespace Raccoon\Utility\Controller\Cms;

use Magento\Framework\Controller\ResultFactory;

class GetContent extends \Magento\Framework\App\Action\Action {
    public function execute() {
        $_params = $this->getRequest()->getParams();

        if (! isset($_params['url_key'])) {
            die();
        }

        $_url_key = $_params['url_key'];

        $_object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        $_page_factory = $_object_manager->create('\Magento\Cms\Model\PageFactory')->create();
        $_page_factory->load($_url_key, 'identifier');

        $_result_json = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $_result_json->setData([
            'success' => true, 
            'message' => '',
            'data' => [
                'content' => $_page_factory->getContent(),
            ],
        ]);
        return $_result_json;
    }
}