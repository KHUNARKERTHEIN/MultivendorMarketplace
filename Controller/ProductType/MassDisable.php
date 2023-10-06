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
 * MassDisable Controller Class
 */
class MassDisable extends MassAction
{
    /**
     * Execute Mass Disable Function
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //Get Selected Data
                $ksCollection = $this->ksFilter->getCollection($this->ksProductTypeCollectionFactory->create());
                $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
                $ksCollectionSize = $ksCollection->getSize();
                $ksProductType = [];

                // Check Collection has data
                if ($ksCollectionSize) {
                    foreach ($ksCollection as $ksRecord) {
                        $ksSellerId = $ksRecord->getKsSellerId();
                        $ksProductType[] = $ksRecord->getKsProductType();
                        $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                        $ksRecord->save();
                    }
                    //disabled product with unassign product types
                    $this->ksProductTypeHelper->disableKsUnassignTypeProductIds($ksSellerId, $ksProductType);

                    $this->messageManager->addSuccess(__('A total of %1 product type(s) has been disabled successfully.', $ksCollectionSize));
                } else {
                    $this->messageManager->addErrorMessage(
                        __('There is no such product type to disabled.')
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
