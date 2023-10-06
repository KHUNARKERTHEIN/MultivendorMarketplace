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
 * Unassign Controller Class for Product Type
 */
class Unassign extends Action
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_TYPE_UNASSIGNED_MAIL = 'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_unassign_email';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ksProductTypeFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * ProductType Unassign constructor
     *
     * @param Action\Context $ksContext
     * @param KsProductTypeFactory $ksProductTypeFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        Action\Context $ksContext,
        KsProductTypeFactory $ksProductTypeFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper
    ) {
        parent::__construct($ksContext);
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
    }

    /**
     * Unassign Product Type
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $ksUnassignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_UNASSIGNED;
            $ksProductTypeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::DISABLE_VALUE;
            $ksData=$this->getRequest()->getParam('id');
            $ksMessage = '';
            $ksError = false;
            //check data
            if ($ksData) {
                //get model data
                $ksModel = $this->ksProductTypeFactory->create()->load($ksData);
                //check model data
                $ksModelData = $ksModel->getData();
                if ($ksModelData) {
                    $ksSellerId = $ksModel->getKsSellerId();
                    $ksProductType = $ksModel->getKsProductType();
                    $ksModel->setKsRequestStatus($ksUnassignStatus);
                    $ksModel->setKsProductTypeStatus($ksProductTypeStatus);
                    $ksModel->save();

                    //disabled product with unassign product types
                    $ksProductTypes = array($ksProductType);
                    $this->ksProductTypeHelper->disableKsUnassignTypeProductIds($ksSellerId, $ksProductTypes);

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
                        $ksTemplateVariable['ksProductType'] = ucwords($ksProductType);
                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                    }

                    $ksMessage = __('A product type has been unassigned successfully.');
                } else {
                    $ksMessage = __('There is no such product type to unassigned.');
                    $ksError = true;
                }
            } else {
                $ksMessage = __('Something went wrong.');
                $ksError = true;
            }
        } catch (Exception $e) {
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
