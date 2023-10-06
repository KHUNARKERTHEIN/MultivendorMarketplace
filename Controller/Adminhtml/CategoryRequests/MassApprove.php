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
 * Class MassApprove
 */
class MassApprove extends AbstractCategory
{
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        //get category request status
        $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_APPROVED;
        try {
            //get collection data
            $ksCollection = $this->ksFilter->getCollection($this->ksCategoryRequestsCollection->create());
            $ksApproved = 0;
            $ksNotApproved = 0;
            //get category request collection data
            $ksCategoryRequests = $this->ksCategoryRequestsFactory->create();
      
            $ksMassSellerIds = [];
            $ksIds = [];
            $ksStoreId = 0;
            foreach ($ksCollection as $ksRecord) {
                $ksCategoryRequests->load($ksRecord->getId());
                $ksIds[] = $ksRecord->getId();
                if ($ksRecord->getKsRequestStatus()==0) {
                    $ksCategoryRequests->setKsRejectionReason("");
                    $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                    $ksCategoryRequests->save();
                    $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
                    $ksMassSellerIds[] = $ksCategoryRequests->getKsSellerId();
                    $ksData = [
                       "ks_seller_id" => $ksCategoryRequests->getKsSellerId(),
                       "ks_category_id" => $ksCategoryRequests->getKsCategoryId(),
                       "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
                    ];
                    $ksCategoriesFactory->addData($ksData)->save();
                    //assign category to product
                    $this->ksCategoryHelper->ksAssignProductInCategory($ksData['ks_seller_id'], $ksData['ks_category_id']);
                    $ksApproved++;
                } else {
                    $ksNotApproved++;
                }
            }
      
            if ($ksApproved) {
                $this->messageManager->addSuccess(__('A total of %1 product category(s) has been approved successfully.', $ksApproved));

                //email functionality
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_request_approval_email', $this->ksDataHelper->getKsCurrentStoreView());
                if ($ksEmailEnabled != "disable") {
                    $ksMassSellerIds = array_unique($ksMassSellerIds);
          
                    foreach ($ksMassSellerIds as $ksSellerId) {
                        $ksCategoryCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('id', array('in' => $ksIds))->addFieldToFilter('ks_seller_id', $ksSellerId);
                        $ksCatIds = [];
                        foreach ($ksCategoryCollection as $ksCategory) {
                            $ksCatIds[] = $ksCategory->getKsCategoryId();
                        }
                        $this->ksSendEmail($ksEmailEnabled, $ksCatIds, $ksStoreId, $ksSellerId);
                    }
                }
            }
            if ($ksNotApproved) {
                $this->messageManager->addErrorMessage(__("A total of %1 record(s) of Product Category can't be approved as there was no request made.", $ksNotApproved));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
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

    /**
     * Send Email to sellers when categories are approved
     *
     * @param  [string] $ksEmailEnabled
     * @param  [int] $ksMassCatIds
     * @param  [int] $ksStoreId
     * @param  [int] $ksSellerId
     */
    public function ksSendEmail($ksEmailEnabled, $ksMassCatIds, $ksStoreId, $ksSellerId)
    {
        //Get Sender Info
        $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_email_sender', $this->ksDataHelper->getKsCurrentStoreView());
        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
        //Get Receiver Info
        $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
        //Template variables
        $ksTemplateVariable = [];
        $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
        $ksTemplateVariable['ks_seller_email'] =$ksReceiverDetails['email'];
        $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
        $ksTemplateVariable['ksSellerId'] = $ksSellerId;
        // Send Mail
        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
    }
}
