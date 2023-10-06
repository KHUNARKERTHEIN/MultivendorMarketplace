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
 * Class Reject
 */
class Reject extends AbstractCategory
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
            $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_REJECTED;
            //get id
            $ksId = $this->getRequest()->getParam('ks_id');
            //get category id
            $ksCatId = $this->getRequest()->getParam('ks_category_id');
            //get seller id
            $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
            //get store id
            $ksStoreId = $this->getRequest()->getParam('ks_store_id');
            //get rejected reason
            $ksRejectionReason = $this->getRequest()->getParam('ks_rejection_reason');
            //rejected category request
            $this->ksRejectCategoryRequest($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId);
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['success' => true,
                    'message' => $this->messageManager->addSuccess("A category has been rejected successfully.")
                ]);
        //for redirecting url
        return $ksResultRedirect;
    }

    /**
     * @param $ksCategoryStatus
     * @param $ksId
     * @param $ksCatId
     * @param $ksSellerId
     * @param $ksRejectionReason
     * @return void
     */
    public function ksRejectCategoryRequest($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId)
    {
        //check Id
        if ($ksId) {
            //get model data
            $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksId);
            //check model data
            if ($ksCategoryRequests) {
                $ksData = [
                   "ks_seller_id" => $ksCategoryRequests->getKsSellerId(),
                   "ks_category_id" => $ksCategoryRequests->getKsCategoryId()
                ];
                $ksCategoryRequests->setKsRejectionReason($ksRejectionReason);
                $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                $ksCategoryRequests->save();
                $this->messageManager->addSuccessMessage(
                    __('A product category has been rejected successfully.')
                );

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_catalog/ks_product_categories/ks_category_request_rejection_email',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                //to notify seller
                $ksNotifySeller = $this->getRequest()->getParam('ks_notify_seller');

                if ($ksEmailEnabled != "disable" && $ksNotifySeller) {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_email_sender', $this->ksDataHelper->getKsCurrentStoreView());
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    //Get Receiver Info
                    $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksData['ks_seller_id']);
                    //save category id in array
                    $ksMassCatIds = [];
                    $ksMassCatIds[] = $ksData['ks_category_id'];
                    //Template variables
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                    $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                    $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
                    $ksTemplateVariable['ksSellerId'] = $ksData['ks_seller_id'];

                    if (trim($ksCategoryRequests->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksRejection'] = "N/A";
                    } else {
                        $ksTemplateVariable['ksRejection'] = $ksCategoryRequests->getKsRejectionReason();
                    }
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while rejecting category.')
                );
            }
        } elseif ($ksCatId && $ksSellerId && $ksStoreId!=null) {
            //get model data
            $ksCategoryCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            //get model data
            $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksCategoryCollection->getId());
            //check model data
            if ($ksCategoryRequests) {
                $ksCategoryRequests->setKsRejectionReason($ksRejectionReason);
                $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                $ksCategoryRequests->save();
                $this->messageManager->addSuccessMessage(
                    __('A category has been rejected successfully.')
                );

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_request_rejection_email', $this->ksDataHelper->getKsCurrentStoreView());
                //to notify seller
                $ksNotifySeller = $this->getRequest()->getParam('ks_notify_seller');

                if ($ksEmailEnabled != "disable" && $ksNotifySeller) {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_email_sender', $this->ksDataHelper->getKsCurrentStoreView());
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

                    if (trim($ksCategoryRequests->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksRejection'] = "";
                    } else {
                        $ksTemplateVariable['ksRejection'] = $ksCategoryRequests->getKsRejectionReason();
                    }
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while rejecting category.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while rejected category.')
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
