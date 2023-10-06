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
 * MassUnassign Controller Class
 */
class MassUnassign extends MassAction
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_TYPE_UNASSIGNED_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_unassign_email';

    /**
     * MassUnassign for the Product Type
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
            $ksUnassignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_UNASSIGNED;
            $ksUnassign = 0;
            $ksNotUnassign = 0;
            $ksMessage = '';
            $ksNotUnassignMessage = '';
            $ksError = false;
            $ksProductType = [];
            $ksSellerId = 0;

            if ($ksCollection) {
                //Loop for Unassign the product type
                foreach ($ksCollection as $ksRecord) {
                    if ($ksRecord->getKsRequestStatus() == 1 || $ksRecord->getKsRequestStatus() == 4) {
                        $ksSellerId = $ksRecord->getKsSellerId();
                        $ksProductType[] = $ksRecord->getKsProductType();
                        $ksRecord->setKsRequestStatus($ksUnassignStatus);
                        $ksRecord->setKsProductTypeStatus($ksProductTypeStatus);
                        $ksRecord->save();
                        $ksUnassign++;
                    } else {
                        $ksNotUnassign++;
                    }
                }
                $ksMassProduct = implode(", ", $ksProductType);
                if ($ksUnassign) {
                    //disabled product with unassign product types
                    $this->ksProductTypeHelper->disableKsUnassignTypeProductIds($ksSellerId, $ksProductType);

                    $ksMessage = __('A total of %1 product type(s) has been unassigned successfully.', $ksUnassign);

                    $ksStoreId = $this->getRequest()->getParam('store', 0);
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        self::XML_PATH_PRODUCT_TYPE_UNASSIGNED_MAIL,
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

                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                    }
                    $this->messageManager->addSuccessMessage(
                        __($ksMessage)
                    );
                    if ($ksNotUnassign) {
                        $ksNotUnassignMessage = __("A total of %1 record(s) of Product Type can't be unassigned.", $ksNotUnassign);
                        $ksNotUnassign = 0;
                        $this->messageManager->addErrorMessage(
                            __($ksNotUnassignMessage)
                        );
                    }
                }

                if ($ksNotUnassign) {
                    $ksNotUnassignMessage = __("A total of %1 record(s) of Product Type can't be unassigned.", $ksNotUnassign);
                    $this->messageManager->addErrorMessage(
                        __($ksNotUnassignMessage)
                    );
                }
            } else {
                $ksMessage = __('Something went wrong');
                $this->messageManager->addErrorMessage(
                    __($ksMessage)
                );
            }
        } catch (NoSuchEntityException $e) {
            $ksMessage = __('There is no such product type to Unassigned.');
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
            $ksMessage = __('We can\'t mass Unassigned the product type right now.');
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
