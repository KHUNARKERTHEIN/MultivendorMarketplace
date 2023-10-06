<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
 */
class MassDelete extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
{

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            //get data
            $ksProductCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
            //get size
            $ksCollectionSize = $ksProductCollection->getSize();

            foreach ($ksProductCollection as $ksItem) {
                $this->ksProductFactory->create()
                    ->load($ksItem->getEntityId())
                    ->delete();
            }
            $this->messageManager->addSuccess(__('A total of %1 product(s) has been deleted.', $ksCollectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
