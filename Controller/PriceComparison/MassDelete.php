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
namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

/**
 * MassDelete controller class
 */
class MassDelete extends \Ksolves\MultivendorMarketplace\Controller\PriceComparison\MassAction
{
    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get data
                $ksProductCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
                //get size
                $ksCollectionSize = $ksProductCollection->getSize();

                if ($ksCollectionSize > 0) {
                    foreach ($ksProductCollection as $ksItem) {
                        $this->ksProductCollection->create()
                        ->load($ksItem->getEntityId(), 'ks_product_id')
                        ->delete();
                    }
                    $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $ksCollectionSize));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
            }
            return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
