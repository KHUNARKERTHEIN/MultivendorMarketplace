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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;

use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute\MassAction;

/**
 * MassReject Controller Class
 */
class MassReject extends MassAction
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_rejection_email_template';

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        try {
            $ksAttribute = 0;
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksAttributeCollection->create());
            //get collection size
            $ksCollectionSize = $ksCollection->getSize();

            foreach ($ksCollection as $ksData) {
                if ($this->ksProductHelper->ksUnassignAttributeFromAttributeSet($ksData->getAttributeId())) {
                    $ksModel = $this->ksAttributeFactory->create()->load($ksData->getAttributeId());
                    $ksData->setKsAttributeRejectionReason("");
                    $ksData->setKsAttributeApprovalStatus($ksAttributeStatus);
                    $ksData->save();

                    $ksSellerDetails = $this->ksSellerHelper->getKsCustomerAccountInfo($ksData->getKsSellerId());
                    $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email');

                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
                    $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                    $ksTemplateVariable['ks-product-attribute-name'] = $ksData->getFrontendLabel();
                    $ksTemplateVariable['ks-attribute-code'] = $ksData->getAttributeCode();
                    $ksTemplateVariable['ks-required'] = $this->ksHelper->getKsYesNoValue($ksData->getIsRequired());
                    $ksTemplateVariable['ks-system'] = $this->ksHelper->getKsYesNoValue($ksData->getIsUserDefined());
                    $ksTemplateVariable['ks-visible'] = $this->ksHelper->getKsYesNoValue($ksData->getIsVisible());
                    $ksTemplateVariable['ks-scope'] = $this->ksHelper->getKsStoreValues($ksData->getIsGlobal());
                    $ksTemplateVariable['ks-searchable'] = $this->ksHelper->getKsYesNoValue($ksData->getIsSearchable());
                    $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksHelper->getKsFilterableValues($ksModel->getIsFilterable());
                    $ksTemplateVariable['ks-comparable'] = $this->ksHelper->getKsYesNoValue($ksData->getIsComparable());
                    $ksTemplateVariable['ks-rejection-reason'] = "";
                    $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_rejection_email_template');
                    if ($ksEmailDisable != 'disable') {
                        $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
                    }
                } else {
                    $ksAttribute++;
                    $this->messageManager->addErrorMessage(
                        __('The "%1" attribute is used in configurable products.', $ksData->getFrontendLabel())
                    );
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 product attribute(s) has been rejected successfully.', $ksCollectionSize - $ksAttribute)
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
