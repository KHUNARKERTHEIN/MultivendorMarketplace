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
 * Class MassReject
 */
class MassReject extends AbstractCategory
{
    /**
     * XML Path
     */
    const XML_PATH_CATEGORY_REJECTED_MAIL = 'ks_marketplace_catalog/ks_product_categories/ks_category_request_rejection_email';

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
        $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_REJECTED;
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksCategoryRequestsCollection->create());
            $ksRejected = 0;
            $ksNotRejected = 0;
            //get collection size
            $ksCollectionSize = 0;

            $ksMassSellerIds = [];
            $ksIds = [];
            $ksStoreId = 0;

            //get category request model data
            $ksCategoryRequests = $this->ksCategoryRequestsFactory->create();
            foreach ($ksCollection as $ksRecord) {
                $ksCategoryRequests->load($ksRecord->getId());
                $ksIds[] = $ksRecord->getId();
                if ($ksRecord->getKsRequestStatus()==0) {
                    $ksMassSellerIds[] = $ksCategoryRequests->getKsSellerId();
                    $ksCategoryRequests->setKsRejectionReason("");
                    $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                    $ksCategoryRequests->save();
                    $ksRejected++;
                } else {
                    $ksNotRejected++;
                }
            }
            if ($ksRejected) {
                $this->messageManager->addSuccess(__('A total of %1 product category(s) has been rejected successfully.', $ksRejected));

                //email functionality
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_request_rejection_email', $this->ksDataHelper->getKsCurrentStoreView());
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
            if ($ksNotRejected) {
                $this->messageManager->addErrorMessage(__("A total of %1 record(s) of Product Category can't be rejected as there was no request made.", $ksNotRejected));
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
        $ksTemplateVariable['ksRejection'] = "";
        $ksTemplateVariable['ksSellerId'] = $ksSellerId;
        // Send Mail
        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
    }
}
