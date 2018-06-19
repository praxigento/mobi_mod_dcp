<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Dcp\Controller\Index;

class Index
    extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $type = \Magento\Framework\Controller\ResultFactory::TYPE_PAGE;
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create($type);
        $resultPage->getConfig()->getTitle()->set(__('Business Center'));
        return $resultPage;
    }

}