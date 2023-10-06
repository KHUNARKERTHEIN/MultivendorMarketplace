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

namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;

/**
 * KsProductAttributeDataSave Observer Class
 */
class KsProductAttributeDataSave implements ObserverInterface
{
    /**
     * @var AttributeFactory
     */
    protected $ksAttributeCollection;

    /**
     * @var WriterInterface
     */
    protected $ksConfigWriter;

    /**
     * @var RequestInterface
     */
    private $ksrequest;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var \Magento\Eav\Api\AttributeManagementInterface
     */
    protected $ksAttributeManagementInterface;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsProductCollection
     */
    protected $ksProductCollection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepositoryInterface;

    /**
     * @var LoggerInterface
     */
    private $ksLogger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $ksProductFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @param RequestInterface $ksrequest
     * @param KsProductAttributeDataSave $ksSellerFactory
     * @param ManagerInterface $ksMessageManager
     * @param AttributeManagementInterface $ksAttributeManagementInterface
     * @param CollectionFactory $ksSellerFactory
     * @param ksSellerHelper $ksSellerHelper
     * @param WriterInterface $ksConfigWriter
     * @param KsProductCollection $ksProductCollection
     * @param ProductRepositoryInterface $ksProductRepositoryInterface
     * @param KsProductHelper $ksProductHelper
     * @param KsProductTypeHelper $ksProductTypeHelper
     * @param LoggerInterface $ksLogger = null
     */
    public function __construct(
        RequestInterface $ksrequest,
        AttributeFactory $ksAttributeCollection,
        ManagerInterface $ksMessageManager,
        WriterInterface $ksConfigWriter,
        AttributeManagementInterface $ksAttributeManagementInterface,
        CollectionFactory $ksSellerFactory,
        KsSellerHelper $ksSellerHelper,
        KsProductCollection $ksProductCollection,
        ProductRepositoryInterface $ksProductRepositoryInterface,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        KsProductHelper $ksProductHelper,
        KsProductTypeHelper $ksProductTypeHelper,
        LoggerInterface $ksLogger = null
    ) {
        $this->ksAttributeCollection = $ksAttributeCollection;
        $this->ksrequest = $ksrequest;
        $this->ksMessageManager   = $ksMessageManager;
        $this->ksAttributeManagementInterface = $ksAttributeManagementInterface;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksConfigWriter = $ksConfigWriter;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksProductRepositoryInterface = $ksProductRepositoryInterface;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksLogger = $ksLogger;
    }

    /**
     * Getting Product Attribute Set When Configuration Page Save
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            // Get the Field Of the System.xml File
            $ksFieldData = $this->ksrequest->getParam('groups');
            $ksDefaultScope = true;

            try {
                // Get The details of Product Type Selected by the admin
                $ksProductAttribute = $ksFieldData['ks_product_attribute_settings']['fields']['ks_product_attribute_set']['value'];
            } catch (\Exception $e) {
                $ksProductAttribute = [];
            }
            // Enable Value
            $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;

            if (!empty($ksProductAttribute)) {
                // Delete the Product when Attribute set is changed
                $this->ksDisableProduct($ksProductAttribute);
                // Iterate over Default Given Attribute Id
                foreach ($ksProductAttribute as $ksValue) {
                    // Get Collection of Attribute Set
                    $ksArray = $this->ksAttributeManagementInterface->getAttributes(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $ksValue);
                    // Iterate to get all the attribute id in attribute set
                    foreach ($ksArray as $ksRecord) {
                        $ksModel = $this->ksAttributeCollection->create()->load($ksRecord['attribute_id']);
                        $ksModel->setKsIncludeInMarketplace($ksAttributeStatus);
                        $ksModel->save();
                    }
                }
            } else {
                $ksProductAttribute = [];
                // Delete the Product when Attribute set is changed
                $this->ksDisableProduct($ksProductAttribute);
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
    }

    /**
     * Disable Product
     * @return void
     */
    public function ksDisableProduct($ksAttributeSet)
    {
        try {
            $ksDefault = $this->ksProductFactory->create()->getDefaultAttributeSetId();
            $ksAdminAttributeSet = $this->ksProductHelper->getksAdminAttributes();
            // Get the Collection of Product
            $ksCollection = $this->ksProductCollection->create();
            // Join the Table of Product to get Seller Id
            $ksCollection->joinField(
                'ks_seller_id',
                'ks_product_details',
                'ks_seller_id',
                'ks_product_id=entity_id',
                [],
                'left'
            )->addFieldToFilter(
                'ks_seller_id',
                ['neq' => '']
            )->addFieldToFilter(
                'attribute_set_id',
                ['in' => $ksAdminAttributeSet]
            );
            $ksProductIds = [];
            // Iterate the Collection to get Delete the Product
            foreach ($ksCollection as $ksRecord) {
                if (!in_array($ksRecord->getAttributeSetId(), $ksAttributeSet)) {
                    if ($ksRecord->getAttributeSetId() != $ksDefault) {
                        $ksProduct = $this->ksProductRepositoryInterface->getById(
                            $ksRecord->getEntityId(),
                            true,
                            0
                        );
                        $ksProduct->setAttributeSetId($ksDefault);
                        $ksProductIds[] = $ksRecord->getEntityId();
                        $ksProduct->save();
                    }
                }
            }
            if ($ksProductIds) {
                $this->ksProductTypeHelper->disableKsProducts($ksProductIds);
            }
        } catch (LocalizedException $exception) {
            $this->ksLogger->error($exception->getLogMessage());
        }
    }
}
