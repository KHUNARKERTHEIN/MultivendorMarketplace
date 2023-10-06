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
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductFactory;

/**
 * Assign Controller Class for Assign Product
 */
class Assign extends Action
{
    /**
     * XML Path
     */
    public const XML_PATH_ASSIGN_PRODUCT_MAIL = 'ks_marketplace_catalog/ks_assign_product_settings/ks_seller_product_assign_mail_template';

    /**
     * @var ProductFactory
     */
    protected $ksProductFactory;

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
     * @var ResourceConnection
     */
    protected $ksResource;

    /**
     * @var ProductFactory
     */
    protected $ksMainProduct;

    /**
     * Assign constructor
     *
     * @param Action\Context $ksContext,
     * @param KsDataHelper $ksDataHelper,
     * @param KsEmailHelper $ksEmailHelper,
     * @param KsProductTypeHelper $ksProductTypeHelper,
     * @param KsProductFactory $ksProductFactory,
     * @param KsProductHelper $ksProductHelper,
     * @param ResourceConnection $ksResource
     */
    public function __construct(
        Action\Context $ksContext,
        KsDataHelper $ksDataHelper,
        KsEmailHelper $ksEmailHelper,
        KsProductTypeHelper $ksProductTypeHelper,
        KsProductFactory $ksProductFactory,
        KsProductHelper $ksProductHelper,
        ProductFactory $ksMainProduct,
        ResourceConnection $ksResource
    ) {
        parent::__construct($ksContext);
        $this->ksProductFactory     = $ksProductFactory;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksEmailHelper        = $ksEmailHelper;
        $this->ksProductTypeHelper  = $ksProductTypeHelper;
        $this->ksProductHelper      = $ksProductHelper;
        $this->ksResource           = $ksResource;
        $this->ksMainProduct        = $ksMainProduct;
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
            $ksProductId = (int)$this->getRequest()->getParam('entity_id');
            $ksSellerId = (int)$this->getRequest()->getParam('seller_id');
            $ksNotIncludedChild = (array)$this->getRequest()->getParam('child_ids');
            // Check Data
            if ($ksProductId && $ksSellerId) {
                // Create Model
                $ksProductIdArray = [];
                $ksProductIdArray[] = $ksProductId;
                $this->ksSaveProduct($ksProductId, $ksSellerId);
                $this->ksProductHelper->ksGetProductCategory($ksProductId, $ksSellerId);
                $this->ksProductHelper->ksRemoveProductLinks($ksProductId);
                $this->ksAssignChildProducts($ksProductId, $ksNotIncludedChild, $ksSellerId);
                $this->ksProductHelper->ksRemoveAssociatedProducts($ksProductId);
                $this->ksProductHelper->ksChangeQuantityAndStock($ksProductId);
                if (!empty($ksNotIncludedChild)) {
                    $this->ksProductHelper->ksRemoveChildProducts($ksNotIncludedChild);
                }
                $ksProductArray = [$ksProductId];
                $this->ksProductTypeHelper->disableKsProducts($ksProductArray);
                try {
                    $this->ksSendEmailProductAssign($ksProductIdArray, $ksSellerId);
                } catch (\Exception $e) {
                    $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                            'message' => $this->messageManager->addErrorMessage($e->getMessage())
                        ]);
                }
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['success' => true,
                    'message' => $this->messageManager->addSuccess("A Product has been assigned successfully to seller.")
                ]);
            } else {
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("There is no such product to be assigned.")
                    ]);
            }
        } catch (Exception $e) {
            $ksResponse = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData(['error' => true,
                            'message' => $this->messageManager->addErrorMessage($e->getMessage())
                        ]);
        }
        return $ksResponse;
    }

    /**
     * Send Mail to Seller when Admin Assigns Product to Seller
     *
     * @param int $ksProductIds
     * @param int $ksSellerId
     * @return void
     */
    private function ksSendEmailProductAssign($ksProductIds, $ksSellerId)
    {
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            'ks_marketplace_catalog/ks_assign_product_settings/ks_seller_product_assign_mail_template',
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
                self::XML_PATH_ASSIGN_PRODUCT_MAIL,
                $ksTemplateVariable,
                $ksSenderInfo,
                ucwords($ksReceiverDetails['name']),
                $ksReceiverDetails['email']
            );
        }
    }

    /**
     * Save Product
     *
     * @param int $ksProductId
     * @param int $ksSellerId
     * @return void
     */
    private function ksSaveProduct($ksProductId, $ksSellerId)
    {
        $ksProductStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
        $ksProductCondition = \Ksolves\MultivendorMarketplace\Model\Source\Product\KsProductCondition::KS_NEW_CONDITION;
        $ksModel = $this->ksProductFactory->create();
        $ksModel->setKsProductId($ksProductId)
                        ->setKsSellerId($ksSellerId)
                        ->setKsProductStage($ksProductCondition)
                        ->setKsProductApprovalStatus($ksProductStatus)
                        ->setKsIsAdminAssignedProduct(1)
                        ->save();
    }

    /**
     * Assign Child of Configurable, Virtual and Bundle Product to Seller
     *
     * @param int $ksProductId
     * @return void
     */
    private function ksAssignChildProducts($ksProductId, $ksNotIncluded, $ksSellerId)
    {
        $ksProductType = $this->ksProductHelper->ksCheckProductType($ksProductId);
        $ksProductIds = [];
        $ksType = true;
        if ($ksProductType == 'configurable' || $ksProductType == 'bundle' || $ksProductType == 'grouped') {
            $ksProductIds = (array)$this->ksProductHelper->getKsChildProductIds($ksProductId);
            if (!empty($ksProductIds)) {
                foreach ($ksProductIds as $ksEntityId) {
                    if (!in_array($ksEntityId, $ksNotIncluded)) {
                        $this->ksSaveProduct($ksEntityId, $ksSellerId);
                        $ksType = false;
                        $this->ksProductHelper->ksChangeQuantityAndStock($ksEntityId);
                        $this->ksProductHelper->ksRemoveAssociatedProducts($ksEntityId, $ksProductId);
                        $this->ksProductHelper->ksRemoveProductLinks($ksEntityId);
                        $this->ksProductHelper->ksGetProductCategory($ksEntityId, $ksSellerId);
                        $this->ksProductHelper->ksRemoveConfigurableChildProduct($ksEntityId);
                    }
                }
                $this->ksProductTypeHelper->disableKsProducts($ksProductIds);
            }
            if ($ksType) {
                $this->ksProductHelper->ksChangeProductType($ksProductId);
            }
        }
    }
}
