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
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * MassRequest Controller Class
 */
class MassRequest extends MassAction
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_TYPE_REQUEST_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_admin_product_type_request_email';
    const XML_PATH_PRODUCT_TYPE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_approval_email';

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

                $ksRequestStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_PENDING;
                $ksApprovalStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED;
                $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
                // Values for Enable and Disable
                $ksRequest = 0;
                $ksNotRequest = 0;
                $ksProductType = [];
                $ksSellerId = 0;

                // Check Collection has data
                if ($ksCollection->getSize()) {
                    $ksSellerId =$this->ksSellerHelper->getKsCustomerId();
                    if ($this->ksSellerHelper->getksProductRequestAllowed($ksSellerId)) {
                        foreach ($ksCollection as $ksRecord) {
                            //Store the important value in variable
                            $ksCheckRequestStatus = $ksRecord->getKsRequestStatus();
                            $ksSellerModel = $this->ksSellerFactory->create()->load($ksSellerId, 'ks_seller_id');
                            // Check the Product Type is in Pending or Unassigned or Rejected
                            if ($ksCheckRequestStatus == 0 || $ksCheckRequestStatus == 2 || $ksCheckRequestStatus == 3) {
                                $ksSellerId = $ksRecord->getKsSellerId();
                                $ksProductType[] = $ksRecord->getKsProductType();
                                if ($ksSellerModel->getKsProducttypeAutoApprovalStatus()) {
                                    $ksRecord->setKsRequestStatus($ksApprovalStatus)
                                        ->setKsProductTypeStatus($ksProductTypeStatus)
                                        ->setKsProductTypeRejectionReason("")
                                        ->save();
                                } else {
                                    $ksRecord->setKsRequestStatus($ksRequestStatus);
                                    $ksRecord->save();
                                }
                                $ksRequest++;
                            } else {
                                $ksNotRequest++;
                            }
                        }
                        $ksMassProduct = implode(", ", $ksProductType);

                        if ($ksRequest) {
                            if ($ksSellerModel->getKsProducttypeAutoApprovalStatus()) {
                                $this->messageManager->addSuccess(__('A total of %1 product type(s) has been approved successfully.', $ksRequest));
                                $this->ksSendApprovalMail($ksSellerId, $ksMassProduct);
                            } else {
                                $this->messageManager->addSuccess(__('A total of %1 product type(s) request has been send successfully.', $ksRequest));
                                $this->ksSendRequestMail($ksSellerId, $ksMassProduct);
                            }
                            if ($ksNotRequest) {
                                $this->messageManager->addErrorMessage(
                                    __('The other selected product type cannot be requested because either it is Assigned, Approved or Pending')
                                );
                            }
                        } else {
                            $this->messageManager->addErrorMessage(
                                __('The selected product type cannot be requested because either it is Assigned, Approved or Pending')
                            );
                        }
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('Request Approval cannot be processed, for more information please contact to Admin')
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

    /**
     * Send Request Mail
     * @param  $ksSellerId
     * @param  $ksMassProduct
     * @return void
     */
    private function ksSendRequestMail($ksSellerId, $ksMassProduct)
    {
        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            self::XML_PATH_PRODUCT_TYPE_REQUEST_MAIL,
            $ksStoreId
        );

        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSellerInfo = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
            //Get Receiver Info
            $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_type_settings/ks_product_type_admin_email_option';
            $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_type_settings/ks_product_type_admin_email';
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            $ksReceiver = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
            
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                $ksStoreId
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
            
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiver['name']);
            $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerInfo['name']);
            $ksTemplateVariable['ksProductType'] = ucwords(strtolower($ksMassProduct));

            // Send Mail
            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiver);
        }
    }

    /**
     * Send Approval Mail
     * @param  $ksSellerId
     * @param  $ksMassProduct
     * @return void
     */
    private function ksSendApprovalMail($ksSellerId, $ksMassProduct)
    {
        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            self::XML_PATH_PRODUCT_TYPE_APPROVAL_MAIL,
            $ksStoreId
        );

        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                $ksStoreId
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductType'] = $ksMassProduct;
                        
            // Send Mail
            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
        }
    }
}
