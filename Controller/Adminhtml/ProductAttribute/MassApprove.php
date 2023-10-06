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
 * MassApprove Controller Class
 */
class MassApprove extends MassAction
{

    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_approval_email_template';

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksAttributeCollection->create());
            //get collection size
            $ksCollectionSize = $ksCollection->getSize();
            // Iterate the Collection
            foreach ($ksCollection as $ksData) {
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
                $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_approval_email_template');
                if ($ksEmailDisable != 'disable') {
                    // Send Mail
                    $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 product attribute(s) has been approved successfully.', $ksCollectionSize)
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
