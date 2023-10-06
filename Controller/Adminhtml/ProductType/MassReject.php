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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductType;

use Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductType\MassAction;
use Magento\Framework\Controller\ResultFactory;

/**
 * MassReject Controller Class
 */
class MassReject extends MassAction
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_TYPE_REJECT_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_rejection_email';
    
    /**
     * MassReject for the Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        try {
            /** @var \Ksolves\MultivendorMarketPlace\Model\ResourceModel\KsProductType\Collection $ksCollection */
            $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
            $ksRejectedStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_REJECTED;
            $ksReject = 0;
            $ksNotReject = 0;
            $ksNotRejectMessage = '';
            $ksMessage = '';
            $ksError = false;
            $ksProductType = [];
            $ksSellerId = 0;
            if ($ksCollection) {
                //Loop for Reject the product type
                foreach ($ksCollection as $ksRecord) {
                    if ($ksRecord->getKsRequestStatus() == 2) {
                        $ksSellerId = $ksRecord->getKsSellerId();
                        $ksProductType[] = $ksRecord->getKsProductType();
                        $ksRecord->setKsRequestStatus($ksRejectedStatus);
                        $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                        $ksRecord->setKsProductTypeRejectionReason("");
                        $ksRecord->save();
                        $ksReject++;
                    } else {
                        $ksNotReject++;
                    }
                }
                $ksMassProduct = implode(", ", $ksProductType);

                if ($ksReject) {
                    $ksMessage = __('A total of %1 product type(s) has been rejected successfully.', $ksReject);
                    
                    $ksStoreId = $this->getRequest()->getParam('store', 0);
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        self::XML_PATH_PRODUCT_TYPE_REJECT_MAIL,
                        $ksStoreId
                    );
                    if ($ksEmailEnabled != "disable") {
                        //Get Sender Info
                        $ksSender = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                            $this->ksDataHelper->getKsCurrentStoreView()
                        );
                        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                        $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
                        $ksTemplateVariable = [];
                        $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                        $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                        $ksTemplateVariable['ksProductType'] = $ksMassProduct;
                        $ksTemplateVariable['ksReason'] = "";

                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                    }

                    $this->messageManager->addSuccessMessage(
                        __($ksMessage)
                    );
                    if ($ksNotReject) {
                        $ksNotRejectMessage = __("A total of %1 record(s) of Product Type can't be rejected as there was no request made.", $ksNotReject);
                        $ksNotReject = 0;
                        $this->messageManager->addErrorMessage(
                            __($ksNotRejectMessage)
                        );
                    }
                }

                if ($ksNotReject && ($ksReject == 0)) {
                    $ksNotRejectMessage = __("A total of %1 record(s) of Product Type can't be rejected as there was no request made.", $ksNotReject);
                    $this->messageManager->addErrorMessage(
                        __($ksNotRejectMessage)
                    );
                }
            } else {
                $ksMessage = __('Something went wrong');
                $this->messageManager->addErrorMessage(
                    __($ksMessage)
                );
            }
        } catch (NoSuchEntityException $e) {
            $ksMessage = __('There is no such product type to rejected.');
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        } catch (LocalizedException $e) {
            $ksMessage = __($e->getMessage());
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        } catch (\Exception $e) {
            $ksMessage = __('We can\'t mass disable the product type right now.');
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
            $this->ksLogger->critical($e);
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
