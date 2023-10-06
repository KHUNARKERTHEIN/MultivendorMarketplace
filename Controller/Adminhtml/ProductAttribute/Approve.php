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
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Approve Controller Class
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_approval_email_template';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * Approve constructor
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsFavouriteSellerHelper $ksSellerHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerHelper $ksHelper
     * @param KsProductHelper $ksProductHelper
     * @param AttributeFactory $ksAttributeCollection
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        KsFavouriteSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        KsSellerHelper $ksHelper,
        KsProductHelper $ksProductHelper,
        AttributeFactory $ksAttributeCollection
    ) {
        
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksHelper = $ksHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksAttributeCollection = $ksAttributeCollection;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        try {
            $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
            $ksId = $this->getRequest()->getParam('attribute_id');
            //check Id
            if ($ksId) {
                //get model data
                $ksModel = $this->ksAttributeCollection->create()->load($ksId);
                // Store Details of Email Template
                $ksSellerDetails = $this->ksSellerHelper->getKsCustomerAccountInfo($ksModel->getKsSellerId());
                $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email');

                $ksTemplateVariable = [];
                $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
                $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
                $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
                $ksTemplateVariable['ks-required'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsRequired());
                $ksTemplateVariable['ks-system'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsUserDefined());
                $ksTemplateVariable['ks-visible'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsVisible());
                $ksTemplateVariable['ks-scope'] = $this->ksHelper->getKsStoreValues($ksModel->getIsGlobal());
                $ksTemplateVariable['ks-searchable'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsSearchable());
                $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksHelper->getKsFilterableValues($ksModel->getIsFilterable());
                $ksTemplateVariable['ks-comparable'] = $this->ksHelper->getKsYesNoValue($ksModel->getIsComparable());
                //check model data
                if ($ksModel) {
                    $ksModel->setKsAttributeRejectionReason("");
                    $ksModel->setKsAttributeApprovalStatus($ksAttributeStatus);
                    $ksModel->save();
                    $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_approval_email_template');
                    if ($ksEmailDisable != 'disable') {
                        // Send Mail
                        $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails);
                    }
                    $this->messageManager->addSuccessMessage(
                        __(' A product attribute has been approved successfully.')
                    );
                } else {
                    $this->messageManager->addErrorMessage(
                        __('There is no such product type exists')
                    );
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while approving product attribute.')
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
