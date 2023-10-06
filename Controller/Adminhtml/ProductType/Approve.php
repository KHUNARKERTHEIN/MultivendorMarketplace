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

use Magento\Backend\App\Action;
use Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * Approve Controller Class for Product Type
 */
class Approve extends Action
{
    /**
     * XML Path
     */
    const XML_PATH_PRODUCT_TYPE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_approval_email';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ksProductTypeFactory
     */
    protected $ksProductTypeFactory;

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
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * ProductType Approve constructor
     *
     * @param Action\Context $ksContext
     * @param KsProductTypeFactory $ksProductTypeFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     */
    public function __construct(
        Action\Context $ksContext,
        KsProductTypeFactory $ksProductTypeFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
    ) {
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        parent::__construct($ksContext);
    }

    /**
     * Assign Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $ksAssignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED;
            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
            $ksData = $this->getRequest()->getParam('ks_id');
            $ksMessage = '';
            $ksError = false;
            //check data
            if ($ksData) {
                //get model data
                $ksModel=$this->ksProductTypeFactory->create()->load($ksData);
                //check model data
                $ksModelData = $ksModel->getData();
                if ($ksModelData) {
                    $ksSellerId = $ksModel->getKsSellerId();
                    $ksProductType = $ksModel->getKsProductType();
                    $ksModel->setKsRequestStatus($ksAssignStatus);
                    $ksModel->setKsProductTypeStatus($ksProductTypeStatus);
                    $ksModel->setKsProductTypeRejectionReason("");
                    $ksModel->save();

                    $ksStoreId = $this->getRequest()->getParam('store', 0);
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        self::XML_PATH_PRODUCT_TYPE_APPROVAL_MAIL,
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
                        $ksTemplateVariable['ksProductType'] = ucwords($ksProductType);
                        
                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                    }
                    
                    $ksMessage = __('A product type has been approved successfully.');
                } else {
                    $ksMessage = __('There is no such product type to approved.');
                    $ksError = true;
                }
            } else {
                $ksMessage = __('Something went wrong.');
                $ksError = true;
            }
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }

        //check error
        if ($ksError) {
            //message
            $this->messageManager->addErrorMessage(
                __($ksMessage)
            );
        } else {
            //message
            $this->messageManager->addSuccessMessage(
                __($ksMessage)
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
