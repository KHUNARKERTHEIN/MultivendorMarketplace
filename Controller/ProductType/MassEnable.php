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

namespace Ksolves\MultivendorMarketplace\Controller\ProductType;

use Ksolves\MultivendorMarketplace\Controller\ProductType\MassAction;

/**
 * MassEnable Controller Class
 */
class MassEnable extends MassAction
{
    /**
     * Execute Mass Enable action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //Get Selected Data
                $ksCollection = $this->ksFilter->getCollection($this->ksProductTypeCollectionFactory->create());
                $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
                // Values for Enable and Disable
                $ksEnabled = 0;
                $ksDisabled = 0;
                // Check Collection has data
                if ($ksCollection->getSize()) {
                    foreach ($ksCollection as $ksRecord) {
                        //Store the important value in variable
                        $ksRequestStatus = $ksRecord->getKsRequestStatus();
                        // Check the Product Type is in Pending or Unassigned or Rejected
                        if ($ksRequestStatus == 1 || $ksRequestStatus == 4) {
                            $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                            $ksRecord->save();
                            $ksEnabled++;
                        } else {
                            $ksDisabled++;
                        }
                    }
                    if ($ksEnabled) {
                        $this->messageManager->addSuccess(__('A total of %1 product type(s) has been enabled successfully.', $ksEnabled));
                        if ($ksDisabled) {
                            $this->messageManager->addErrorMessage(
                                __("The selected product type can't be enabled.")
                            );
                        }
                    } else {
                        $this->messageManager->addErrorMessage(
                            __("The selected product type can't be enabled.")
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong.')
                    );
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
