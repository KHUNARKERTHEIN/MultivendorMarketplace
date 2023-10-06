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
 * MassDelete Controller Class
 */
class MassDelete extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller
{
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerCollection->create());
            // get collection size
            $ksCollectionSize = $ksCollection->getSize();
            
            foreach ($ksCollection as $ksData) {
                // get seller store collection
                $ksSellerStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData->getKsSellerId());
                if ($ksSellerStoreCollection->getSize() > 0) {
                    foreach ($ksSellerStoreCollection as $ksValue) {
                        $ksSellerStoreModel = $this->ksSellerStoreFactory->create()->load($ksValue->getId());
                        // delete seller store
                        $ksSellerStoreModel->delete();
                    }
                }
                $ksEmail = $this->ksSellerHelper->getKsCustomerEmail($ksData->getKsSellerId());
                //change product status
                $this->ksProductHelper->ksChangeProductStatus($ksData->getKsSellerId(), $ksEmail);
                // delete seller
                $ksData->delete();

                //delete seller store url rewrite
                $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksData->getKsSellerId().'/';
                $this->ksSellerHelper->ksRedirectUrlDelete($ksTargetPathUrl);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 seller(s) has been deleted successfully.', $ksCollectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
