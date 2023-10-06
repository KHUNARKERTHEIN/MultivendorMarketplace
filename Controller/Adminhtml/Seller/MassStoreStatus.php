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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;

/**
 * MassStoreStatus Controller Class
 */
class MassStoreStatus extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller
{
    /**
     * Check Store Status
     * Execute Action
     */
    public function execute()
    {
        $ksCount = $ksRejectedSellerFlag = $ksPendingSellerFlag = 0;
        //for check Store Status
        if ($this->getRequest()->getParam('ks_store_status') == 1) {
            $ksStoreStatus = 'enabled';
        } else {
            $ksStoreStatus = 'disabled';
        }

        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerCollection->create());

            foreach ($ksCollection as $ksData) {
                if ($ksData->getKsSellerStatus() == 2 && $this->getRequest()->getParam('ks_store_status') == 1) {
                    $ksRejectedSellerFlag++;
                } elseif ($ksData->getKsSellerStatus() == 0 && $this->getRequest()->getParam('ks_store_status') == 1) {
                    $ksPendingSellerFlag++;
                } else {
                    $ksData->setKsStoreStatus($this->getRequest()->getParam('ks_store_status'));
                    $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksData->save();
                    $ksCount++;
                }
            }
            if ($ksCount) {
                $this->_eventManager->dispatch('ksseller_store_change_after');
                
                $this->messageManager->addSuccessMessage(
                    __("A total of 1 seller's store(s) has been %2 successfully.", $ksCount, $ksStoreStatus)
                );
            }
            // error msg for rejected seller
            if ($ksRejectedSellerFlag) {
                $this->messageManager->addErrorMessage(
                    __('Store of rejected seller can\'t be '.$ksStoreStatus.'.')
                );
            }
            // error msg for rejected seller
            if ($ksPendingSellerFlag) {
                $this->messageManager->addErrorMessage(
                    __('Store of pending seller can\'t be '.$ksStoreStatus.'.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
