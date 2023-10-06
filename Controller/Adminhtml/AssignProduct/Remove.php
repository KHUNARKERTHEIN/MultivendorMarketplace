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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\AssignProduct;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Magento\Catalog\Model\ProductFactory;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Remove Controller Class
 */
class Remove extends Action
{
    /**
     * XML Path
     */
    public const XML_PATH_UNASSIGN_PRODUCT_MAIL = 'ks_marketplace_catalog/ks_assign_product_settings/ks_seller_product_unassign_mail_template';

    /**
     * @var KsProduct
     */
    protected $ksProduct;

    /**
     * @var ProductFactory
     */
    protected $ksMainProduct;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param Action\Context $ksContext
     * @param JsonFactory $ksResultJsonFactory
     */
    public function __construct(
        Action\Context $ksContext,
        KsProductFactory $ksProduct,
        ProductFactory $ksMainProduct,
        KsDataHelper $ksDataHelper,
        KsEmailHelper $ksEmailHelper,
        KsProductHelper $ksProductHelper,
        KsProductTypeHelper $ksProductTypeHelper
    ) {
        parent::__construct($ksContext);
        $this->ksMainProduct        = $ksMainProduct;
        $this->ksProduct            = $ksProduct;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksEmailHelper        = $ksEmailHelper;
        $this->ksProductTypeHelper  = $ksProductTypeHelper;
        $this->ksProductHelper      = $ksProductHelper;
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            // Get data from the Ajax call
            $ksData = (int)$this->getRequest()->getParam('entity_id');
            // Check Data
            if ($ksData) {
                $ksModel = $this->ksProduct->create()->load($ksData);
                $ksProductId = $ksModel->getKsProductId();
                $ksSellerId = $ksModel->getKsSellerId();
                $ksProductIdArray = [];
                $ksProductIdArray[] = $ksProductId;
                $ksType = true;
                $ksProductType = $this->ksProductHelper->ksCheckProductType($ksProductId);
                $this->ksProductHelper->ksRemoveProductLinks($ksProductId);
                $ksSetId = $this->ksProductHelper->getKsProductAttributeSet($ksProductId);
                $this->ksProductHelper->ksChangeQuantityAndStock($ksProductId);
                if (!$this->ksProductHelper->ksCheckAdminAttributes($ksSetId)) {
                    $this->ksProductHelper->ksChangeAttributeSet($ksProductId);
                }
                // If Product is Configurable, Bundle and Grouped
                if ($ksProductType == 'configurable' || $ksProductType == 'bundle' || $ksProductType == 'grouped') {
                    // Get Child Products
                    $ksChildProduct = (array)$this->ksProductHelper->getKsChildProductIds($ksProductId);
                    if (!empty($ksChildProduct)) {
                        foreach ($ksChildProduct as $ksChild) {
                            $ksChildProductModel = $this->ksProduct->create()->load($ksChild, 'ks_product_id');
                            // Check the Product is not assigned by admin
                            $ksNotAssigned = (int)$ksChildProductModel->getKsIsAdminAssignedProduct();
                            if (!$ksNotAssigned) {
                                $this->ksProductHelper->ksRemoveAssociatedProducts($ksChild);
                            } else {
                                $ksChildSetId = $this->ksProductHelper->getKsProductAttributeSet($ksChild);
                                if (!$this->ksProductHelper->ksCheckAdminAttributes($ksChildSetId)) {
                                    $this->ksProductHelper->ksChangeAttributeSet($ksChild);
                                }
                                $ksProductIdArray[] = $ksChild;
                                $this->ksProductHelper->ksChangeQuantityAndStock($ksChild);
                                $ksChildProductModel->delete();
                                $this->ksProductHelper->ksRemoveProductLinks($ksChild);
                                $ksType = false;
                                $this->ksProductHelper->ksRemoveAssociatedProducts($ksChild, $ksProductId);
                                $this->ksProductHelper->ksRemoveConfigurableChildProduct($ksChild);
                            }
                        }
                    }
                    if ($ksType) {
                        // Change the product type if configurable product has no child
                        $this->ksProductHelper->ksChangeProductType($ksProductId);
                    }
                } else {
                    $this->ksProductHelper->ksRemoveAssociatedProducts($ksProductId);
                }
                $ksModel->delete();
                $this->ksProductTypeHelper->disableKsProducts($ksProductIdArray);
                $this->ksRemoveProductEmail($ksProductIdArray, $ksSellerId);
                $this->messageManager->addSuccessMessage(
                    __('A Product has been removed successfully from seller')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('There is no product available')
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
    }

    /**
     * Send Mail
     * @param $ksProductId
     * @return void
     */
    private function ksRemoveProductEmail($ksProductIds, $ksSellerId)
    {
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            'ks_marketplace_catalog/ks_assign_product_settings/ks_seller_product_unassign_mail_template',
            $this->ksDataHelper->getKsCurrentStoreView()
        );

        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_assign_product_settings/ks_email_sender',
                $this->ksDataHelper->getKsCurrentStoreView()
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            //Get Receiver Info
            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);

            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductIds'] = $ksProductIds;


            // Send Mail
            $this->ksEmailHelper->ksAssignProductMail(
                self::XML_PATH_UNASSIGN_PRODUCT_MAIL,
                $ksTemplateVariable,
                $ksSenderInfo,
                ucwords($ksReceiverDetails['name']),
                $ksReceiverDetails['email']
            );
        }
    }
}
