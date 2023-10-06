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
 * Class Approve
 */
class Approve extends AbstractCategory
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
            $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_APPROVED;
            //get id
            $ksId = $this->getRequest()->getParam('id');
            //get category id
            $ksCatId = $this->getRequest()->getParam('ks_category_id');
            //get seller id
            $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
            //get store id
            $ksStoreId = $this->getRequest()->getParam('ks_store_id');
            //rejection reason
            $ksRejectionReason = "";
            //approved category request
            $this->ksApproveCategoryRequest($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId);
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
     * @param $ksId
     * @param $ksCatId
     * @param $ksSellerId
     * @param $ksRejectionReason
     * @return void
     */
    public function ksApproveCategoryRequest($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId)
    {
        //check Id
        if ($ksId) {
            //get model data
            $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksId);
            //check model data
            if ($ksCategoryRequests) {
                $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
                $ksData = [
                   "ks_seller_id" => $ksCategoryRequests->getKsSellerId(),
                   "ks_category_id" => $ksCategoryRequests->getKsCategoryId(),
                   "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
                ];
                $ksCategoriesFactory->addData($ksData)->save();
                $ksCategoryRequests->setKsRejectionReason($ksRejectionReason);
                $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
                $ksCategoryRequests->save();
                //assign category to product
                $this->ksCategoryHelper->ksAssignProductInCategory($ksData['ks_seller_id'], $ksData['ks_category_id']);

                $this->messageManager->addSuccessMessage(
                    __('A product category has been approved successfully.')
                );

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_catalog/ks_product_categories/ks_category_request_approval_email',
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
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while approving category.')
                );
            }
            //check category id and seller id
        } elseif ($ksCatId && $ksSellerId && $ksStoreId!=null) {
            $this->ksApproveCategory($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId);
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while approved category.')
            );
        }
    }

    /**
     * @param $ksCategoryStatus
     * @param $ksId
     * @param $ksCatId
     * @param $ksSellerId
     * @param $ksRejectionReason
     * @return void
     */
    public function ksApproveCategory($ksCategoryStatus, $ksId, $ksCatId, $ksSellerId, $ksRejectionReason, $ksStoreId)
    {
        //get collecion data
        $ksCategoryCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        //get model data
        $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksCategoryCollection->getId());
        //check model data
        if ($ksCategoryRequests) {
            $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
            $ksData = [
               "ks_seller_id" => $ksCategoryRequests->getKsSellerId(),
               "ks_category_id" => $ksCategoryRequests->getKsCategoryId(),
               "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
            ];
            $ksCategoriesFactory->addData($ksData)->save();
            $ksCategoryRequests->setKsRejectionReason($ksRejectionReason);
            $ksCategoryRequests->setKsRequestStatus($ksCategoryStatus);
            $ksCategoryRequests->save();
            //assign category to product
            $this->ksCategoryHelper->ksAssignProductInCategory($ksData['ks_seller_id'], $ksData['ks_category_id']);

            $this->messageManager->addSuccessMessage(
                __('A category has been approved successfully.')
            );

            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_categories/ks_category_request_approval_email',
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
                $ksMassCatIds[] = $ksData['ks_category_id'];
                //Template variables
                $ksTemplateVariable = [];
                $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
                $ksTemplateVariable['ksSellerId'] = $ksSellerId;
                // Send Mail
                $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while approving category.')
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
