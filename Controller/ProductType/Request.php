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

use Magento\Framework\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;

/**
 * Request Controller Class
 */
class Request extends Action
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_TYPE_REQUEST_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_admin_product_type_request_email';
    const XML_PATH_PRODUCT_TYPE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_approval_email';

    /**
     * @var KsProductTypeFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Request constructor.
     * @param Context $ksContext
     * @param KsProductTypeFactory $ksProductTypeFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param KsSellerFactory $ksSellerFactory
     */
    public function __construct(
        Context $ksContext,
        KsProductTypeFactory $ksProductTypeFactory,
        KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        KsSellerFactory $ksSellerFactory
    ) {
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksSellerFactory = $ksSellerFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action for Request Product Type
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksRequestStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_PENDING;
                $ksApprovalStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED;
                $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
                $ksId = $this->getRequest()->getParam('id');
                //check data
                if ($ksId) {
                    //get model data
                    $ksModel = $this->ksProductTypeFactory->create()->load($ksId);
                    $ksSellerModel = $this->ksSellerFactory->create()->load($this->ksSellerHelper->getKsCustomerId(), 'ks_seller_id');
                    $ksModelData = $ksModel->getData();
                    if ($ksModelData) {
                        $ksSellerId = $ksModel->getKsSellerId();
                        $ksProductType = $ksModel->getKsProductType();
                        if ($ksSellerModel->getKsProducttypeAutoApprovalStatus()) {
                            $ksModel->setKsRequestStatus($ksApprovalStatus)
                                ->setKsProductTypeStatus($ksProductTypeStatus)
                                ->setKsProductTypeRejectionReason("")
                                ->save();
                            $ksMessage = 'A product type has been approved successfully.';
                            $this->ksSendApprovalMail($ksSellerId, $ksProductType);
                        } else {
                            $ksModel->setKsRequestStatus($ksRequestStatus)
                                ->save();
                            $ksMessage = 'A product type request has been sent successfully.';
                            $this->ksSendRequestMail($ksSellerId, $ksProductType);
                        }
                        $this->messageManager->addSuccessMessage(
                            __($ksMessage)
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __('There is not such product type available.')
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while requesting Product Type')
                    );
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
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
     * Send Approval Mail
     * @param  $ksSellerId
     * @param  $ksProductType
     * @return void
     */
    private function ksSendApprovalMail($ksSellerId, $ksProductType)
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
            $ksTemplateVariable['ksProductType'] = ucwords($ksProductType);
                        
            // Send Mail
            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
        }
    }

    /**
     * Send Rejection Mail
     * @param  $ksSellerId
     * @param  $ksProductType
     * @return void
     */
    private function ksSendRequestMail($ksSellerId, $ksProductType)
    {
        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            self::XML_PATH_PRODUCT_TYPE_REQUEST_MAIL,
            $ksStoreId
        );

        if ($ksEmailEnabled != "disable") {
            //Get Seller Info
            $ksSellerInfo = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
            //Get Receiver Info
            $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_type_settings/ks_product_type_admin_email_option';
            $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_type_settings/ks_product_type_admin_email';
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            $ksReceiverInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
            
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                $ksStoreId
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                           
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverInfo['name']);
            $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerInfo['name']);
            $ksTemplateVariable['ksProductType'] = ucwords($ksProductType);
            // Send Mail
            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverInfo);
        }
    }
}
