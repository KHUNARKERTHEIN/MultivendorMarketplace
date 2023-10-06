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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CategoryRequests;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Unassign
 */
class Unassign extends AbstractCategory
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            //get category request status
            $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_UNASSIGNED;
            //get category id
            $ksCatId = $this->getRequest()->getParam('ks_category_id');
            //get seller id
            $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
            //get store id
            $ksStoreId = $this->getRequest()->getParam('ks_store_id');
            //rejection reason
            $ksRejectionReason = "";
            //unassigned category
            $this->ksUnassignCategory($ksCategoryStatus, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId);
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @param $ksCategoryStatus
     * @param $ksCatId
     * @param $ksSellerId
     * @param $ksRejectionReason
     * @return void
     */
    public function ksUnassignCategory($ksCategoryStatus, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId)
    {
        if ($ksCatId && $ksSellerId && $ksStoreId!=null) {
            //get seller category collection data
            $ksSellerCatCollection = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();

            if (!empty($ksSellerCatCollection->getData())) {
                //get collection data of category table
                $ksCategoryCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId);
                $ksCategoryRequests = $this->ksCategoryRequestsFactory->create();
                //get model data
                $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create()->load($ksSellerCatCollection->getId());
                //delete data
                $ksCategoriesFactory->delete();
                //check collection data
                if (count($ksCategoryCollection)!=0) {
                    $ksCat = $ksCategoryCollection->getFirstItem();
                    $ksCategoryRequests->load($ksCat->getId());
                    $ksCategoryRequests->setKsRejectionReason($ksRejectionReason);
                    $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                    $ksCategoryRequests->save();
                }
                //Unassign category from Product
                $this->ksCategoryHelper->ksUnAssignProductCategory(array($ksCatId), $ksSellerId);

                $this->messageManager->addSuccessMessage(
                    __('A product category has been unassigned successfully.')
                );
                
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_catalog/ks_product_categories/ks_category_unassign_email',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_catalog/ks_product_categories/ks_email_sender',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    //Get Receiver Info
                    $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                    //save category id in array
                    $ksMassCatIds = [];
                    $ksMassCatIds[] = $ksCatId;
                    //Template variables
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                    $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                    $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
                    $ksTemplateVariable['ksSellerId'] = $ksSellerId;
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while unassigned category.')
            );
        }
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::category_requests');
    }
}
