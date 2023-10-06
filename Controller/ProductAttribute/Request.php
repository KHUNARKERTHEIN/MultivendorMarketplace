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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Request Controller Class
 */
class Request extends \Magento\Framework\App\Action\Action
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_ATTRIBUTE_REQUEST_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_request_email_template';
    const XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_approval_email_template';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
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
     * Request constructor.
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsFavouriteSellerHelper $ksHelper,
     * @param ksSellerHelper $ksSellerHelper,
     * @param KsEmailHelper $ksEmailHelper,
     * @param KsDataHelper $ksDataHelper,
     * @param AttributeFactory $ksAttributeCollection
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsFavouriteSellerHelper $ksHelper,
        KsSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        AttributeFactory $ksAttributeCollection
    ) {
        $this->ksHelper = $ksHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksAttributeCollection = $ksAttributeCollection;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksPendingAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING;
                $ksApprovalAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
                $ksAutoApproval = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_attribute_status');
                $ksId = $this->getRequest()->getParam('attribute_id');
                //check Id
                if ($ksId) {
                    //get model data
                    $ksModel = $this->ksAttributeCollection->create()->load($ksId);
                    //check model data
                    if ($ksModel) {
                        // Check Admin Approval Required
                        if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                            $ksModel->setKsAttributeRejectionReason("");
                            $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                            $ksModel->save();
                            $this->ksSendApprovalMail($ksModel);
                            $this->messageManager->addSuccessMessage(
                                __('A product attribute has been approved successfully.')
                            );
                        } else {
                            $ksModel->setKsAttributeApprovalStatus($ksPendingAttributeStatus);
                            $ksModel->save();
                            $this->ksSendRequestMail($ksModel);
                            $this->messageManager->addSuccessMessage(
                                __('A product attribute request has been send successfully')
                            );
                        }
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('There is no such product attribute exists')
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while requesting product attribute.')
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Send Request Mail
     * @param $ksModel
     */
    public function ksSendRequestMail($ksModel)
    {
        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_request_email_template', $this->ksDataHelper->getKsCurrentStoreView());

        if ($ksEmailDisable != 'disable') {
            // Store Details of Email Template
            $ksSellerDetails = $this->ksHelper->getKsCustomerAccountInfo($ksModel->getKsSellerId());
            //Get Receiver Info
            $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_admin_email_option';
            $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_admin_email';
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            $ksRecieveInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_sender_email', $this->ksDataHelper->getKsCurrentStoreView());
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksAdminName'] = ucwords($ksRecieveInfo['name']);
            $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
            $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
            $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsRequired());
            $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsUserDefined());
            $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsVisible());
            $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksModel->getIsGlobal());
            $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsSearchable());
            $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksModel->getIsFilterable());
            $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsComparable());
            $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerDetails['name']);

            $this->ksEmailHelper->ksSendRequestProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REQUEST_MAIL, $ksTemplateVariable, $ksRecieveInfo, $ksSenderInfo);
        }
    }

    /**
     * Send Approval Mail
     * @param $ksModel
     */
    public function ksSendApprovalMail($ksModel)
    {
        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_approval_email_template', $this->ksDataHelper->getKsCurrentStoreView());

        if ($ksEmailDisable != 'disable') {
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            // Store Details of Email Template
            $ksSellerDetails = $this->ksHelper->getKsCustomerAccountInfo($ksModel->getKsSellerId());
            $ksRecieveInfo = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email', $ksStoreId);
            $ksTemplateVariable = [];
            $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
            $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
            $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
            $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
            $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsRequired());
            $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsUserDefined());
            $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsVisible());
            $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksModel->getIsGlobal());
            $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsSearchable());
            $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksModel->getIsFilterable());
            $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsComparable());
            $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL, $ksTemplateVariable, $ksRecieveInfo, $ksSellerDetails);
        }
    }
}
