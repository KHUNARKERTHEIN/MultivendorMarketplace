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
 * MassDisable Controller Class
 */
class MassDisable extends MassAction
{
  
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_UNASSIGNED_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_unassign_email_template';
    
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksAttributeCollection->create());
            //get collection size
            $ksCollectionSize = $ksCollection->getSize();
            $ksSellerList = $this->ksHelper->getKsSellerList();

            foreach ($ksCollection as $ksData) {
                $ksModel = $this->ksAttributeFactory->create()->load($ksData->getAttributeId());
                $ksData->setKsIncludeInMarketplace($ksAttributeStatus);
                $ksData->save();

                $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_unassign_email_template');
                if ($ksEmailDisable != 'disable') {
                    foreach ($ksSellerList as $ksSellerId) {
                        $ksSellerDetails = $this->ksSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                        $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email');
                        $ksTemplateVariable = [];
                        $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails['name'];
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
                        $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_UNASSIGNED_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
                    }
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 product attribute status(s) has been disabled successfully.', $ksCollectionSize)
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
