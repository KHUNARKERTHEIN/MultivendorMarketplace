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

namespace Ksolves\MultivendorMarketplace\Helper;

use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsProductCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory as KsProductTypeCollection;
use Magento\Catalog\Model\ProductFactory as KsCatalogProductFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsCategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Link\CollectionFactory as KsLinkCollectionFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as KsAttributeSetCollectionFactory;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as KsAttributeFactory;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Class KsProductHelper
 * @package Ksolves\MultivendorMarketplace\Helper
 */
class KsProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var KsProductCollection
     */
    protected $ksProductCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $ksMainProductCollection;

    /**
     * @var KsProductTypeCollection
     */
    protected $ksProductTypeFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsCategoryCollection
     */
    protected $ksSellerCategoryFactory;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $ksIsSingleSourceMode;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $ksIsSourceItemManagementAllowedForProductType;

    /**
     * @var KsCatalogProductFactory
     */
    protected $ksCatalogProductFactory;

    /**
     * @var KsLinkCollectionFactory
     */
    protected $ksLinkCollectionFactory;

    /**
     * @var KsAttributeSetCollectionFactory
     */
    protected $ksAttributeSetCollectionFactory;

    /**
     * @var SessionFactory
     */
    protected $ksSession;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepoInterface;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @var AttributeManagementInterface
     */
    protected $ksAttributeInterface;

    /**
     * @var ProductFactory
     */
    protected $ksMainProductFactory;

    /**
     * @var CategoryLinkRepository
     */
    protected $ksCategoryLinkRepository;

    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $ksStockRegistry;

    /**
     * KsProductHelper constructor.
     * @param Context $ksContext
     * @param KsProductCollection $ksProductCollectionFactory
     * @param KsProductTypeCollection $ksProductTypeFactory
     * @param KsCategoryCollection $ksSellerCategoryFactory
     * @param IsSingleSourceModeInterface $ksIsSingleSourceMode
     * @param IsSourceItemManagementAllowedForProductTypeInterface $ksIsSourceItemManagementAllowedForProductType
     * @param KsCatalogProductFactory $ksCatalogProductFactory
     * @param KsAttributeSetCollectionFactory $ksAttributeSetCollectionFactory
     * @param SessionFactory $ksSession
     * @param ProductRepositoryInterface $ksProductRepoInterface
     * @param KsSellerHelper $ksSellerHelper
     * @param StoreManagerInterface $ksStoreManager
     * @param CurrencyFactory $ksCurrencyFactory
     * @param KsAttributeFactory $ksAttributeFactory
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     * @param AttributeManagementInterface $ksAttributeInterface
     * @param ProductFactory $ksMainProductFactory
     * @param CategoryLinkRepository $ksCategoryLinkRepository
     * @param Magento\CatalogInventory\Api\StockRegistryInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        KsDataHelper $ksDataHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksMainProductCollection,
        KsProductCollection $ksProductCollectionFactory,
        KsProductTypeCollection $ksProductTypeFactory,
        KsCategoryCollection $ksSellerCategoryFactory,
        IsSingleSourceModeInterface $ksIsSingleSourceMode,
        IsSourceItemManagementAllowedForProductTypeInterface $ksIsSourceItemManagementAllowedForProductType,
        KsCatalogProductFactory $ksCatalogProductFactory,
        KsLinkCollectionFactory $ksLinkCollectionFactory,
        KsAttributeSetCollectionFactory $ksAttributeSetCollectionFactory,
        SessionFactory $ksSession,
        KsSellerHelper $ksSellerHelper,
        ProductRepositoryInterface $ksProductRepoInterface,
        KsProductFactory $ksSellerProductFactory,
        StoreManagerInterface $ksStoreManager,
        CurrencyFactory $ksCurrencyFactory,
        KsAttributeFactory $ksAttributeFactory,
        \Magento\Framework\App\ResourceConnection $ksResource,
        AttributeManagementInterface $ksAttributeInterface,
        ProductFactory $ksMainProductFactory,
        CategoryLinkRepository $ksCategoryLinkRepository,
        StockRegistryInterface $ksStockRegistry
    ) {
        $this->ksDataHelper = $ksDataHelper;
        $this->ksMainProductCollection = $ksMainProductCollection;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksSellerCategoryFactory = $ksSellerCategoryFactory;
        $this->ksIsSingleSourceMode = $ksIsSingleSourceMode;
        $this->ksIsSourceItemManagementAllowedForProductType = $ksIsSourceItemManagementAllowedForProductType;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksLinkCollectionFactory = $ksLinkCollectionFactory;
        $this->ksAttributeSetCollectionFactory = $ksAttributeSetCollectionFactory;
        $this->ksSession = $ksSession;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksProductRepoInterface = $ksProductRepoInterface;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksResource = $ksResource;
        $this->ksAttributeInterface = $ksAttributeInterface;
        $this->ksMainProductFactory = $ksMainProductFactory;
        $this->ksCategoryLinkRepository = $ksCategoryLinkRepository;
        $this->ksStockRegistry = $ksStockRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Get Count of Pending Product
     * @return int
     */
    public function getKsPendingProductCount()
    {
        $ksSellerCollection = $this->ksProductCollectionFactory->create()
            ->addFieldToFilter(
                'ks_product_approval_status',
                [
                    ['eq' => KsProduct::KS_STATUS_PENDING],
                    ['eq' => KsProduct::KS_STATUS_PENDING_UPDATE]
                ]
            )->addFieldToFilter('ks_parent_product_id', 0);

        return $ksSellerCollection->getSize();
    }

    /**
     * @param $ksProductId
     * @return int
     */
    public function getKsSellerId($ksProductId)
    {
        $ksSellerDetails = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksProductId);
        if ($ksSellerDetails->getSize()>0) {
            return $ksSellerDetails->getFirstItem()->getKsSellerId();
        } else {
            return 0;
        }
    }

    /**
     * @param $ksProductId
     * @param $ksProductSellerId
     * @return Boolean
     */
    public function isKsSellerProduct($ksProductId, $ksProductSellerId = 0)
    {
        $ksSellerId = $this->getKsSellerId($ksProductId);
        if (!$ksProductSellerId) {
            $ksProductSellerId = $this->ksDataHelper->getKsCustomerId();
        }
        return $ksSellerId == $ksProductSellerId ? true : false;
    }

    /**
     * Check the seller product item
     * @param $ksProductId
     * @return bool
     */
    public function KsIsAnySellerProduct($ksProductId)
    {
        $ksCollection = $this->ksSellerProductFactory->create()->getCollection()
        ->addFieldToFilter('ks_product_id', $ksProductId);

        if ($ksCollection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Get Count of Pending Price Comarison Product
     * @return int
     */
    public function getKsPendingPriceComparisonProductCount()
    {
        $ksSellerCollection = $this->ksProductCollectionFactory->create()
            ->addFieldToFilter(
                'ks_product_approval_status',
                [
                    ['eq' => KsProduct::KS_STATUS_PENDING],
                    ['eq' => KsProduct::KS_STATUS_PENDING_UPDATE]
                ]
            )->addFieldToFilter('ks_parent_product_id', ['neq' => 0]);

        return $ksSellerCollection->getSize();
    }

    /**
     * @param $ksSellerId
     * @return array
     */
    public function ksAllowedProductType($ksSellerId)
    {
        $ksProductType = [];

        $ksAllowedType = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_product_type_status', 1)
            ->addFieldToFilter('ks_seller_id', $ksSellerId);

        if (!$ksAllowedType->getSize()) {
            $ksAllowedType = $this->ksProductTypeFactory->create()
                ->addFieldToFilter('ks_product_type_status', 1)
                ->addFieldToFilter('ks_seller_id', 0);
        }

        $ksDatas = $ksAllowedType->getData();

        foreach ($ksDatas as $ksData) {
            $ksProductType[] = $ksData['ks_product_type'];
        }

        return $ksProductType;
    }

    /**
     * @param $ksProductType
     * @param $ksSellerId
     * @return bool
     */
    public function ksIsProductTypeAllowed($ksProductType, $ksSellerId)
    {
        $ksSellerAllowedProductTypes = [];

        $ksAllowedType = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_product_type_status', 1)
            ->addFieldToFilter('ks_seller_id', $ksSellerId)
            ->addFieldToFilter(
                'ks_request_status',
                ['in' => [
                \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED,
                \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED]]
            );

        if (!$ksAllowedType->getSize()) {
            $ksAllowedType = $this->ksProductTypeFactory->create()
                ->addFieldToFilter('ks_product_type_status', 1)
                ->addFieldToFilter('ks_seller_id', 0);
        }

        $ksDatas = $ksAllowedType->getData();

        foreach ($ksDatas as $ksData) {
            $ksSellerAllowedProductTypes[] = $ksData['ks_product_type'];
        }

        foreach ($ksSellerAllowedProductTypes as $ksItem) {
            if ($ksItem == $ksProductType) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $ksProductWebsite
     * @param $ksSellerWebsite
     * @return bool
     */
    public function ksCheckSellerProductWebsite($ksProductWebsite, $ksSellerWebsite)
    {
        $ksFlag = true;
        foreach ($ksProductWebsite as $ksItem) {
            if ($ksItem != $ksSellerWebsite) {
                $ksFlag = false;
                break;
            }
        }
        return $ksFlag;
    }

    /**
     * @param $ksSellerId
     * @param $ksStoreId
     * @return array
     */
    public function ksGetCategoryIds($ksSellerId)
    {
        $ksCategory = [];
        $ksSellerCategory = $this->ksSellerCategoryFactory->create()
            ->addFieldToFilter('ks_seller_id', $ksSellerId)
            ->addFieldToFilter('ks_category_status', 1)
            ->getData();

        foreach ($ksSellerCategory as $ksItem) {
            $ksCategory[] = $ksItem['ks_category_id'];
        }
        return $ksCategory;
    }

    /**
     * Get Seller Product Listing array
     * @param $ksSellerId
     * @return array
     */
    public function GetKsSellerPriceComparisonProduct($ksSellerId)
    {
        // Get Seller Product
        $ksCollection = $this->ksProductCollectionFactory->create();
        $ksCollection->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Get Seller Product data
        $ksSellerProduct = $ksCollection->getData();
        // Declare Empty Error
        $ksSellerProductId = [];
        // Take Seller Product Id in the Array
        foreach ($ksSellerProduct as $ksValue) {
            $ksSellerProductId[] = $ksValue['ks_parent_product_id'];
            $ksSellerProductId[] = $ksValue['ks_product_id'];
        }
        return $ksSellerProductId;
    }

    /**
     * Get Price Comparison Listing
     * @return array
     */
    public function GetKsPriceComparisonProduct()
    {
        // Get Seller Product
        $ksCollection = $this->ksProductCollectionFactory->create();
        // Get Seller Product data
        $ksSellerProduct = $ksCollection->getData();
        // Declare Empty Error
        $ksSellerProductId = [];
        // Take Seller Product Id in the Array
        foreach ($ksSellerProduct as $ksValue) {
            if ($ksValue['ks_parent_product_id'] != '0') {
                $ksSellerProductId[] = $ksValue['ks_product_id'];
            }
        }
        return $ksSellerProductId;
    }

    /**
     * Check Product who exceed limit of Price Comparison Product
     * @return array
     */
    public function CheckKsPriceComparisonLimit()
    {
        $ksLimit = $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_limit_to_price_comparison_per_product');
        $ksProductId = [];
        $ksCollection = $this->ksProductCollectionFactory->create();
        foreach ($ksCollection as $ksProduct) {
            $ksProductCollection = $this->ksProductCollectionFactory->create()
                ->addFieldToFilter('ks_parent_product_id', ['neq' => '0'])
                ->addFieldToFilter('ks_parent_product_id', $ksProduct->getKsParentProductId());
            if ($ksProductCollection->getSize() >= $ksLimit) {
                $ksProductId[] = $ksProduct->getKsParentProductId();
            }
        }
        return $ksProductId;
    }

    /**
     * Remove Admin Product From Price Comparison List
     * @param $ksCollection
     * @return object
     */
    public function ksRemoveAdminProductFromPriceComparison($ksCollection)
    {
        if (!($this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_allow_admin_products_in_comparison'))) {
            $ksCollection->joinField(
                'ks_seller_id',
                'ks_product_details',
                'ks_seller_id',
                'ks_product_id=entity_id',
                [],
                'left'
            )->addFieldToFilter('ks_seller_id', ['neq' => '']);
        }
        return $ksCollection;
    }

    /**
     * Remove Restricted Product Type from Price Comparison List
     * @param $ksCollection
     * @return object
     */
    public function ksRemoveRestrictProductTypeFromPriceComparison($ksCollection)
    {
        $ksRestrictedProductType = $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_type_restrictions');
        $ksGloballyRestrictedProductType = 'grouped,bundle';
        $ksRestrictedProductType = $ksRestrictedProductType.','.$ksGloballyRestrictedProductType;

        if (!empty($ksRestrictedProductType)) {
            $ksCollection->addFieldToFilter('type_id', ['nin' => $ksRestrictedProductType]);
        }
        return $ksCollection;
    }

    /**
     * Check Seller is disable or enable
     * @param $ksCollection
     * @return object
     */
    public function ksCheckSellerStatus($ksCollection)
    {
        $ksCollection->joinField(
            'ks_product_approval_status',
            'ks_product_details',
            'ks_product_approval_status',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        $ksCollection->joinField(
            'ks_seller_id',
            'ks_product_details',
            'ks_seller_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );
        $ksCollection->joinField(
            'ks_seller_status',
            'ks_seller_details',
            'ks_seller_status',
            'ks_seller_id=ks_seller_id',
            [],
            'left'
        );

        $ksCollection->joinField(
            'ks_store_status',
            'ks_seller_details',
            'ks_store_status',
            'ks_seller_id=ks_seller_id',
            [],
            'left'
        );
        return $ksCollection;
    }

    /**
     * Get Product of Disabled or Rejected Seller and his Store
     * @param $ksCollection
     * @return array
     */
    public function ksDisabledSellerProduct()
    {
        $ksCollection = $this->ksMainProductCollection->create();
        $ksCollection = $this->ksCheckSellerStatus($ksCollection);
        $ksSellerRejectProductId = [];
        foreach ($ksCollection->getData() as $ksKey => $ksValue) {
            if ($ksValue['ks_seller_id']) {
                if ($ksValue['ks_seller_status'] != 1 || $ksValue['ks_store_status'] != 1 || $ksValue['ks_product_approval_status'] != 1) {
                    $ksSellerRejectProductId[] = $ksValue['entity_id'];
                }
            }
        }
        return $ksSellerRejectProductId;
    }

    /**
     * check inventory single source mode
     * @param $ksProductType
     * @return boolean
     */
    public function ksIsSingleSourceMode($ksProductType)
    {
        if (!$this->ksIsSingleSourceMode->execute() &&
            $this->ksIsSourceItemManagementAllowedForProductType->execute($ksProductType)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $ksTypes
     * @param $ksSellerId
     * @return array
     */
    public function ksSellerProductList($ksTypes, $ksSellerId)
    {
        $ksSellerAllowedProductTypes = [];
        $ksIsSellerInTable = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        if ($ksIsSellerInTable->getSize()) {
            $ksAllowedType = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_product_type_status', 1)
                ->addFieldToFilter('ks_seller_id', $ksSellerId)
                ->addFieldToFilter(
                    'ks_request_status',
                    ['in' => [\Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED, \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED]]
                );
        } else {
            $ksAllowedType = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_product_type_status', 1)
                ->addFieldToFilter('ks_seller_id', 0)
                ->addFieldToFilter(
                    'ks_request_status',
                    ['in' => [\Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED, \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED]]
                );
        }

        $ksDatas = $ksAllowedType->getData();

        foreach ($ksDatas as $ksData) {
            $ksSellerAllowedProductTypes[] = $ksData['ks_product_type'];
        }

        $ksSellerProductType = [];
        foreach ($ksSellerAllowedProductTypes as $ksSellerAllowedProductType) {
            foreach ($ksTypes as $ksType) {
                if ($ksSellerAllowedProductType == $ksType['name']) {
                    $ksSellerProductType[$ksType['name']] = $ksType;
                }
            }
        }
        return $ksSellerProductType;
    }

    /**
     * check for approved products
     * @param $ksProductId
     * @return bool
     */
    public function ksSellerProductFilter($ksProductId)
    {
        $ksSellerProductCollection = $this->ksProductCollectionFactory->create()
            ->addFieldToFilter('ks_product_id', $ksProductId);

        //seller product
        if ($ksSellerProductCollection->getSize()) {
            $ksProduct = $ksSellerProductCollection->getFirstItem();
            $ksSellerId = $ksProduct->getKsSellerId();
            $ksApprovalStatus = $ksProduct->getKsProductApprovalStatus();
            if ($ksApprovalStatus == KsProduct::KS_STATUS_APPROVED
                && $this->ksSellerHelper->getKsSellerStoreStatus($ksSellerId)) {
                $ksStatus = 1;
            } else {
                $ksStatus = 0;
            }
        } else {
            //admin product
            $ksStatus = 1;
        }

        return $ksStatus;
    }

    /**
     * check for seller products conditions
     * @param $ksProductCollection
     * @return collection
     */
    public function ksFilterSellerProductCollection($ksProductCollection, $ksFilterPriceProduct = false)
    {
        $ksSellerProductTable = $ksProductCollection->getTable('ks_product_details');
        $ksSellerTable        = $ksProductCollection->getTable('ks_seller_details');

        $ksProductCollection->getSelect()->joinLeft(
            ['ks_cgf' => $ksSellerProductTable],
            ('ks_cgf.ks_product_id = e.entity_id'),
            [
                'ks_product_approval_status'=>'ks_product_approval_status',
                'ks_parent_product_id'=>'ks_parent_product_id',
                'ks_product_stage'=>'ks_product_stage',
                'ks_seller_id'=>'ks_seller_id'
            ]
        );

        $ksProductCollection->getSelect()->joinLeft(
            ['ks_seller' => $ksSellerTable],
            ('ks_seller.ks_seller_id = ks_cgf.ks_seller_id'),
            [
                'ks_store_status'=>'ks_store_status'
            ]
        );

        $ksProductCollection->getSelect()
        ->where('ks_cgf.ks_product_approval_status= '.KsProduct::KS_STATUS_APPROVED.'
        or ks_cgf.ks_product_approval_status IS NULL')
        ->where('ks_seller.ks_store_status= 1
          or ks_seller.ks_store_status IS NULL');

        if ($ksFilterPriceProduct) {
            $ksProductCollection->getSelect()
            ->where('ks_cgf.ks_parent_product_id= 0
            or ks_cgf.ks_parent_product_id IS NULL');
        }

        return $ksProductCollection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Collection
     */
    public function getKsAssociateLinkCollection()
    {
        $ksProductLinkCollection = $this->ksLinkCollectionFactory->create();

        $ksLinkTypeTable = $ksProductLinkCollection->getTable('catalog_product_link_type');

        $ksLinkAttributeIntTable = $ksProductLinkCollection->getTable('catalog_product_link_attribute_int');

        $ksLinkAttributeDecimalTable = $ksProductLinkCollection->getTable('catalog_product_link_attribute_decimal');

        $ksProductLinkCollection->getSelect()->join(
            $ksLinkTypeTable . ' as ks_lt',
            'main_table.link_type_id = ks_lt.link_type_id',
            [
                'link_type' => 'code',
            ]
        );

        $ksProductLinkCollection->getSelect()->join(
            $ksLinkAttributeDecimalTable . ' as ks_lattrdecimal',
            'main_table.link_id = ks_lattrdecimal.link_id',
            [
                'qty' => 'value',
            ]
        );

        $ksProductLinkCollection->getSelect()->join(
            $ksLinkAttributeIntTable . ' as ks_lattr',
            'main_table.link_id = ks_lattr.link_id',
            [
                'position' => 'value',
            ]
        )->where('ks_lt.link_type_id = ' . \Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED);

        return $ksProductLinkCollection;
    }

    /**
     * Get Seller Allowed for the Selected Attribute Set
     * @param $ksSellerId
     * @param $ksProductTypeIdCode
     * @param $ksSellerAttributeId
     * @return bool
     */
    public function ksCheckForSellerAttributeSet($ksSellerId, $ksProductTypeIdCode, $ksSellerAttributeId)
    {
        $ksArray = $this->getKsAttributeSet($ksSellerId, $ksProductTypeIdCode);
        $ksFlag = false;
        foreach ($ksArray as $ksItem) {
            if ($ksItem == $ksSellerAttributeId) {
                $ksFlag = true;
                break;
            }
        }
        return $ksFlag;
    }

    /**
     * Get Seller Allowed for the Selected Attribute Set
     * @param $ksSellerId
     * @param $ksProductTypeIdCode
     * @param $ksSellerAttributeId
     * @return array
     */
    public function getKsAttributeSet($ksSellerId, $ksProductTypeIdCode)
    {
        // Create Collection
        $ksCollection = $this->ksAttributeSetCollectionFactory->create();
        // Array for storing value of attribute
        $ksArray = [];
        // Filter Collection
        $ksSellerAttributeArray = $ksCollection->setEntityTypeFilter($ksProductTypeIdCode)
            ->addFieldToFilter('ks_seller_id', $ksSellerId);

        // Iterate collection
        foreach ($ksSellerAttributeArray as $ksValue) {
            $ksArray[] = $ksValue->getAttributeSetId();
        }
        // Get Default array
        $ksDefaultArray = $this->ksDataHelper->getKsDefaultAttributes();
        if (!empty($ksDefaultArray)) {
            foreach ($ksDefaultArray as $ksValue) {
                $ksArray[] = $ksValue;
            }
        }
        return $ksArray;
    }

    /**
     * @return array
     */
    public function ksSellerAllowedProductType()
    {
        $ksSellerId = $this->ksSession->create()->getId();
        $ksProductType = [];

        $ksAllowedType = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_product_type_status', 1)
            ->addFieldToFilter('ks_seller_id', $ksSellerId);

        if (!$ksAllowedType->getSize()) {
            $ksAllowedType = $this->ksProductTypeFactory->create()
                ->addFieldToFilter('ks_product_type_status', 1)
                ->addFieldToFilter('ks_seller_id', 0);
        }

        $ksDatas = $ksAllowedType->getData();

        foreach ($ksDatas as $ksData) {
            $ksProductType[] = $ksData['ks_product_type'];
        }

        return $ksProductType;
    }

    /**
     * @param $ksSellerId
     * @param $ksProduct
     * @param $ksData
     */
    private function ksCheckForWebsiteAndProductType($ksSellerId, $ksProduct, $ksData)
    {
        if ($ksSellerId !=0) {
            $ksSellerWebsite = $this->ksSellerHelper->getksSellerWebsiteId($ksSellerId);

            $ksCheckSellerWebsite = $this->ksCheckSellerProductWebsite($ksProduct->getWebsiteIds(), $ksSellerWebsite);

            $ksCustomerScope = $this->ksDataHelper->getKsConfigCustomerScopeField("scope");

            if ($ksCheckSellerWebsite || !$ksCustomerScope) {
                if (!$this->ksIsProductTypeAllowed($ksData['type'], $ksSellerId)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This product type is not allowed to the seller.')
                    );
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This website product is not allowed for the seller.')
                );
            }
        }
    }

    /**
     * @param $ksGrouped
     * @param $ksSellerId
     */
    private function ksCheckForGroupedProductType($ksGrouped, $ksSellerId)
    {
        $ksFlag = false;
        foreach ($ksGrouped as $ksGroupedProduct) {
            $ksSellerProduct = $this->ksSellerProductFactory->create();
            if ($ksSellerProduct->load($ksGroupedProduct['id'], 'ks_product_id')->getKsSellerId() != $ksSellerId) {
                $ksFlag = true;
                break;
            }
        }
        return $ksFlag;
    }

    /**
     * @param $ksBundleOptions
     * @param $ksSellerId
     */
    private function ksCheckForBundleProductType($ksBundleOptions, $ksSellerId)
    {
        $ksFlag = false;

        foreach ($ksBundleOptions as $bundleOption) {
            if (isset($bundleOption['bundle_selections'])) {
                $ksBundleSelections = $bundleOption['bundle_selections'];
                foreach ($ksBundleSelections as $ksBundleSelection) {
                    $ksSellerProduct = $this->ksSellerProductFactory->create();
                    if ($ksSellerProduct->load($ksBundleSelection['product_id'], 'ks_product_id')
                            ->getKsSellerId() != $ksSellerId) {
                        $ksFlag = true;
                        break;
                    }
                }
            }
            if ($ksFlag) {
                break;
            }
        }
        return $ksFlag;
    }

    /**
     * @param $ksSellerId
     * @param $ksData
     * @param $ksProduct
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function ksCheckForBundleAndGroupedProduct($ksSellerId, $ksData, $ksProduct)
    {
        $this->ksCheckForWebsiteAndProductType($ksSellerId, $ksProduct, $ksData);

        if ($ksData['type'] == 'grouped') {
            if (isset($ksData['links']['associated'])) {
                if ($this->ksCheckForGroupedProductType($ksData['links']['associated'], $ksSellerId)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You have not selected same seller/admin product for grouped product.')
                    );
                }
            }
        }
        if ($ksData['type'] == 'bundle') {
            if (isset($ksData['bundle_options']['bundle_options'])) {
                if ($this->ksCheckForBundleProductType($ksData['bundle_options']['bundle_options'], $ksSellerId)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You have not selected same seller/admin product for bundle product.')
                    );
                }
            }
        }
    }

    /**
     * @param $ksSellerId
     * @param $ksData
     * @return bool
     */
    private function ksCheckRelatedProducts($ksSellerId, $ksData)
    {
        $ksFlag = false;
        foreach ($ksData as $ksItem) {
            $ksSellerProduct = $this->ksSellerProductFactory->create();
            if ($ksSellerProduct->load($ksItem['id'], 'ks_product_id')->getKsSellerId() != $ksSellerId) {
                $ksFlag = true;
                break;
            }
        }
        return $ksFlag;
    }

    /**
     * @param $ksData
     * @param $ksSellerId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function ksCheckForRelatedUpSellCrossSellProduct($ksData, $ksSellerId)
    {
        if (isset($ksData['links']['related'])) {
            if ($this->ksCheckRelatedProducts($ksSellerId, $ksData['links']['related'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You have not selected same seller/admin product for related product.')
                );
            }
        }

        if (isset($ksData['links']['crosssell'])) {
            if ($this->ksCheckRelatedProducts($ksSellerId, $ksData['links']['crosssell'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You have not selected same seller/admin product for cross sell product.')
                );
            }
        }

        if (isset($ksData['links']['upsell'])) {
            if ($this->ksCheckRelatedProducts($ksSellerId, $ksData['links']['upsell'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You have not selected same seller/admin product for up sell product.')
                );
            }
        }
    }

    /**
     * Change Product Status
     * @param  int $ksSellerId
     * @return void
     */
    public function ksChangeProductStatus($ksSellerId, $ksEmail)
    {
        // Get Product Collection
        $ksProductCollection  = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        // Get Not Submiited Status
        $ksNotSubmittedStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED;
        if ($ksProductCollection->getSize()) {
            // Iterate over seller product
            foreach ($ksProductCollection as $ksRecord) {
                // Create New Collection
                $ksSellerProduct = $this->ksSellerProductFactory->create()->load($ksRecord->getId());
                $ksSellerProduct->setKsProductApprovalStatus($ksNotSubmittedStatus);
                $ksSellerProduct->setKsEmail($ksEmail);
                $ksSellerProduct->save();
                $this->ksDisbleProduct($ksRecord->getKsProductId());
            }
        }
    }

    /**
     * Get Store Currency Symbol
     * @return string
     */
    public function getKsCurrentCurrencySymbol()
    {
        $ksCurrencyCode = $this->ksStoreManager->getStore()->getCurrentCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }

    /**
     * Disable Product
     * @param $ksProductId
     * @return void
     */
    public function ksDisbleProduct($ksProductId)
    {
        $ksProduct = $this->ksProductRepoInterface->getById(
            $ksProductId,
            true,
            0
        );
        $ksProduct->setStoreId(0);
        $ksProduct->setStatus(2);
        $ksProduct->save();
    }

    /**
     * @param $KsProductId
     * @return void
     */
    public function ksChangeProductOwnerSetting($ksProductId)
    {
        $ksProductCollection = $this->ksProductCollectionFactory->create()->addFieldtoFilter('ks_parent_product_id', $ksProductId);
        //Check the Product has price comparison product or not
        if ($ksProductCollection->getSize() == 1) {
            // If only one price comparison product is available then make it main product
            $ksProductCollection->getFirstItem()->setKsParentProductId(0)->save();

        // If Two Product Available Then Check allow the conditions
        } elseif ($ksProductCollection->getSize() > 1) {
            // Get the Condition of the System Configuration from the Backend
            $ksOwnerCondition = $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_secondary_product_owner');
            $this->ksSaveProductAccordingToCondition($ksOwnerCondition);
        }
    }

    /**
     * Save According to Configuration Given
     * @param $ksOwnerCondition
     * @return void
     */
    public function ksSaveProductAccordingToCondition($ksOwnerCondition)
    {
        // Check all the Condition and Execute accordingly to that
        switch ($ksOwnerCondition) {
            case "creation_date_asc":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'created_at', 'ASC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "creation_date_desc":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'created_at', 'DESC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "min_price":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'price', 'ASC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "max_price":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'price', 'DESC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "min_qty":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'qty', 'ASC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "max_qty":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'qty', 'DESC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;
        }
    }

    /**
     * Save Configurable Product According to Condition
     * @param $ksOwnerCondition
     * @return void
     */
    public function ksSaveConfigProductAccordingToCondition($ksOwnerCondition)
    {
        // Check all the Condition and Execute accordingly to that
        switch ($ksOwnerCondition) {
            case "creation_date_asc":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'created_at', 'ASC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "creation_date_desc":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'created_at', 'DESC');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "min_variants":
                // Get the Product Id according to condition
                $ksProductId = $this->getKsConfigurableProductAccordingToCondition($ksPriceComparisonProductId, 'min');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;

            case "max_variants":
               // Get the Product Id according to condition
                $ksProductId = $this->getKsProductAccordingToCondition($ksPriceComparisonProductId, 'max');
                // Set the Product Id in the Ks Product Details Table
                $this->setKsSecondaryOwnerProduct($ksProductCollection, $ksProductId);
                break;
        }
    }

    /**
     * To get the product id according to conditions from the product collection.
     * @param ksProductIdArray
     * @param ksAttribute
     * @param ksCondition
     * @return int
     */
    private function getKsConfigurableProductAccordingToCondition($ksProductIdArray, $ksCondition)
    {
        // Create the collection of product
        $ksMainCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');
        // Get the collection according to price comparison product
        $ksMainCollection->addFieldtoFilter('entity_id', ['in' => $ksProductIdArray]);
        // Join Column 'qty'
        $ksMainCollection->joinField(
            'parent_id',
            'catalog_product_relation',
            'parent_id',
            'child_id = entity_id',
            [],
            'left'
        )->addAttributeToFilter(array(
            array(
                'attribute' => 'parent_id',
                'null' => null
            )
        ));
        foreach ($ksMainCollection as $ksData) {
            $ksChildProduct = $ksData->getTypeInstance()->getUsedProducts($ksData);
            $ksVariants[$ksData->getEntityId()] = count($ksChildProduct);
        }
        // return the product id according to satisfying condition
        return array_search($ksCondition($ksVariants), $ksVariants);
    }

    /**
     * To get the product id according to conditions from the product collection.
     * @param ksProductIdArray
     * @param ksAttribute
     * @param ksCondition
     * @return int
     */
    private function getKsProductAccordingToCondition($ksProductIdArray, $ksAttribute, $ksCondition)
    {
        // Create the collection of product
        $ksMainCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');

        // If Quantity is required
        if ($ksAttribute == 'qty') {
            $ksMainCollection = $this->ksJoinProductForQty($ksMainCollection);
        }
        // Get the collection according to price comparison product
        $ksMainCollection->addFieldtoFilter('entity_id', ['in' => $ksProductIdArray]);
        // Sort the collection according to condition
        $ksMainCollection->setOrder($ksAttribute, $ksCondition);
        // return the product id according to satisfying condition
        return $ksMainCollection->getFirstItem()->getEntityId();
    }

    /**
     * Make the Product Collection according to the configuration
     * @param ksCollection
     * @param ksProductId
     */
    private function setKsSecondaryOwnerProduct($ksCollection, $ksProductId)
    {
        // Iterate over the product collection
        foreach ($ksCollection as $ksRecord) {
            // If Product Id is equal make that parent product
            if ($ksRecord->getKsProductId() == $ksProductId) {
                // Get the Model according to product id
                $ksModel = $this->ksSellerProductFactory->create()->load($ksRecord->getId());
                // Save the Model
                $ksModel->setKsParentProductId(0)->save();
            } else {
                // get the model according to product id
                $ksModel = $this->ksSellerProductFactory->create()->load($ksRecord->getId());
                // Save the model
                $ksModel->setKsParentProductId($ksProductId)->save();
            }
        }
    }

    /**
     * Join Product Collection for Qty
     * @param ksCollection
     * @return ksCollection
     */
    private function ksJoinProductForQty($ksCollection)
    {
        // Join Column 'qty'
        $ksCollection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            [],
            'left'
        );
        return $ksCollection;
    }

    /**
     * Get Product Condition
     */
    public function getKsProductCondition($ksProductId)
    {
        $ksCondition = 1;
        $ksProductCollection = $this->ksProductCollectionFactory->create()->addFieldtoFilter('ks_product_id', $ksProductId);

        if ($ksProductCollection->getSize()) {
            $ksCondition = $ksProductCollection->getFirstItem()->getKsProductStage();
        }
        return $ksCondition;
    }

    /*
     * Unassign Attribute from Attribute Set
     * @param  $ksAttributeId
     * @return void
     */
    public function ksUnassignAttributeFromAttributeSet($ksAttributeId)
    {
        try {
            $ksAttributeModel = $this->ksAttributeFactory->create();
            // Get the Table in which attribute set and attribute are stored
            $ksTable = $this->ksResource->getTableName('eav_entity_attribute');
            // join the table
            $ksAttributeModel->getSelect()->join(
                $ksTable.' as ks_att',
                'main_table.attribute_id = ks_att.attribute_id'
            )->where('main_table.attribute_id = '.$ksAttributeId);
            // Check the Size
            if ($ksAttributeModel->getSize()) {
                foreach ($ksAttributeModel as $ksAttribute) {
                    // Unassign that attribute from the AttributeSet
                    $this->ksAttributeInterface->unassign($ksAttribute->getAttributeSetId(), $ksAttribute->getAttributeCode());
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Product Type According to the Seller
     * @param  $ksSellerId
     * @return array
     */
    public function getKsSellerProductType($ksSellerId)
    {
        $ksModel = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksAllowedType = [];
        if ($ksModel->getSize()) {
            $ksAllowedType = $ksModel->addFieldToFilter(
                'ks_request_status',
                [
                    'in' =>
                    [
                        \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED,
                        \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_APPROVED
                    ]
                ]
            )->getColumnValues('ks_product_type');
        }
        return $ksAllowedType;
    }

    /**
     * Get Allowed Categorys of Seller
     * @param $ksSellerId
     * @return void
     */
    public function getKsSellerCategories($ksSellerId)
    {
        $ksModel = $this->ksSellerCategoryFactory->create()
                        ->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksCategories = [];
        if ($ksModel->getSize()) {
            $ksCategories = $ksModel->getColumnValues('ks_category_id');
        }
        return $ksCategories;
    }

    /**
     * Get Associated Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsParentProductIds($ksProductId)
    {
        // Create the collection of product
        $ksMainCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');
        // Join Column 'qty'
        $ksMainCollection->joinField(
            'parent_id',
            'catalog_product_relation',
            'parent_id',
            'child_id = entity_id',
            [],
            'left'
        );

        $ksCollection = $ksMainCollection->addFieldToFilter('entity_id', $ksProductId)->getData();
        $ksProductIds = [];
        foreach ($ksCollection as $ksRecord) {
            // Check Index is not Null
            if ($ksRecord['parent_id']) {
                $ksProductIds[] = $ksRecord['parent_id'];
            }
        }
        if (empty($ksProductIds)) {
            return $this->getKsBundleParentProductIds($ksProductId);
        }
        return array_unique($ksProductIds);
    }

    /**
     * Get ChildProducts Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsChildProductIds($ksProductId)
    {
        $ksProductType = $this->ksCheckProductType($ksProductId);

        if ($ksProductType == 'bundle') {
            return $this->getKsBundleChildProductIds($ksProductId);
        }
        // Create the collection of product
        $ksMainCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');
        // Join Column 'qty'
        $ksMainCollection->joinField(
            'child_id',
            'catalog_product_relation',
            'child_id',
            'parent_id = entity_id',
            [],
            'left'
        );

        $ksCollection = $ksMainCollection->addFieldToFilter('entity_id', $ksProductId)->getData();
        $ksProductIds = [];
        foreach ($ksCollection as $ksRecord) {
            // Check Index is not Null
            if ($ksRecord['child_id']) {
                $ksProductIds[] = $ksRecord['child_id'];
            }
        }
        return array_unique($ksProductIds);
    }

    /**
     * Get Bundle Child Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsBundleChildProductIds($ksProductId)
    {
        // Create the collection of product
        $ksBundleCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');
        // Join Column 'product_id'
        $ksBundleCollection->joinField(
            'product_id',
            'catalog_product_bundle_selection',
            'product_id',
            'parent_product_id = entity_id',
            [],
            'left'
        );

        $ksCollection = $ksBundleCollection->addFieldToFilter('entity_id', $ksProductId)->getData();
        $ksProductIds = [];
        foreach ($ksCollection as $ksRecord) {
            // Check Index is not Null
            if ($ksRecord['product_id']) {
                $ksProductIds[] = $ksRecord['product_id'];
            }
        }
        return array_unique($ksProductIds);
    }

    /**
     * Get Bundle Parent Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsBundleParentProductIds($ksProductId)
    {
        // Create the collection of product
        $ksParentCollection = $this->ksMainProductCollection->create()->addAttributeToSelect('*');
        // Join Column 'parent_product_id'
        $ksParentCollection->joinField(
            'parent_product_id',
            'catalog_product_bundle_selection',
            'parent_product_id',
            'product_id = entity_id',
            [],
            'left'
        );

        $ksCollection = $ksParentCollection->addFieldToFilter('entity_id', $ksProductId)->getData();
        $ksProductIds = [];
        foreach ($ksCollection as $ksRecord) {
            // Check Index is not Null
            if ($ksRecord['parent_product_id']) {
                $ksProductIds[] = $ksRecord['parent_product_id'];
            }
        }
        return array_unique($ksProductIds);
    }

    /**
     * Get Upsell Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsUpsellProductIds($ksProductId)
    {
        $ksProductIds = $this->ksMainProductFactory->create()->load($ksProductId)->getUpSellProductIds();
        return $ksProductIds;
    }

    /**
     * Get CrossSell Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsCrossSellProductIds($ksProductId)
    {
        $ksProductIds = $this->ksMainProductFactory->create()->load($ksProductId)->getCrossSellProductIds();
        return $ksProductIds;
    }

    /**
     * Get Related Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function getKsRelatedProductIds($ksProductId)
    {
        $ksProductIds = $this->ksMainProductFactory->create()->load($ksProductId)->getRelatedProductIds();
        return $ksProductIds;
    }

    /**
     * Get Related Product Ids
     * @param  $ksProductId
     * @return array $ksProductIds
     */
    public function ksCheckProductType($ksProductId)
    {
        return $this->ksMainProductFactory->create()->load($ksProductId)->getTypeId();
    }


    /**
     * Remove Related, Upsell and Cross Sell Product Link from the Product
     * @param $ksProductId
     * @return void
     */
    public function ksRemoveProductLinks($ksProductId)
    {
        $ksTable = $this->ksResource->getTableName("catalog_product_link");
        $ksConnection = $this->ksResource->getConnection();
        $ksSQL = "DELETE FROM " . $ksTable." WHERE product_id = ".$ksProductId." AND link_type_id IN (1 ,4, 5)";
        $ksConnection->query($ksSQL);
    }

    /**
     * Delete Child of Configurable, Bundle and Grouped Product
     * @param array $ksDeleteChild
     * @return void
     */
    public function ksRemoveChildProducts($ksDeleteChild)
    {
        $ksTable = $this->ksResource->getTableName("catalog_product_relation");
        $ksConfigurationTable = $this->ksResource->getTableName("catalog_product_super_link");
        $ksRelationTable = $this->ksResource->getTableName("catalog_product_link");
        $ksBundleProduct = $this->ksResource->getTableName("catalog_product_bundle_selection");
        foreach ($ksDeleteChild as $ksChild) {
            $this->ksResource->getConnection()->delete($ksConfigurationTable, ["product_id = $ksChild"]);
            $this->ksResource->getConnection()->delete($ksTable, ["child_id = $ksChild"]);
            $this->ksResource->getConnection()->delete($ksRelationTable, ["linked_product_id = $ksChild"]);
            $this->ksResource->getConnection()->delete($ksBundleProduct, ["product_id = $ksChild"]);
        }
    }

    /**
     * Delete Associated of Simple, Virtual and Downloadable Product
     * @param int $ksProductId
     * @return void
     */
    public function ksRemoveAssociatedProducts($ksProductId, $ksCurrentProductId = null)
    {
        $ksProductType = $this->ksCheckProductType($ksProductId);
        if ($ksProductType == 'simple' || $ksProductType == 'virtual' || $ksProductType == 'downloadable') {
            $ksTable = $this->ksResource->getTableName("catalog_product_relation");
            $ksRelationTable = $this->ksResource->getTableName("catalog_product_link");
            $ksBundleProduct = $this->ksResource->getTableName("catalog_product_bundle_selection");
            $ksConfigurationTable = $this->ksResource->getTableName("catalog_product_super_link");

            if ($ksCurrentProductId!=null) {
                $this->ksResource->getConnection()->delete($ksTable, ["child_id = $ksProductId && parent_id!=$ksCurrentProductId"]);
                $this->ksResource->getConnection()->delete($ksConfigurationTable, ["product_id = $ksProductId && parent_id!=$ksCurrentProductId"]);
                $this->ksResource->getConnection()->delete($ksBundleProduct, ["product_id = $ksProductId && parent_product_id!=$ksCurrentProductId"]);
                $this->ksResource->getConnection()->delete($ksRelationTable, ["linked_product_id = $ksProductId && product_id!=$ksCurrentProductId"]);
            } else {
                $this->ksResource->getConnection()->delete($ksTable, ["child_id = $ksProductId"]);
                $this->ksResource->getConnection()->delete($ksConfigurationTable, ["product_id = $ksProductId"]);
                $this->ksResource->getConnection()->delete($ksBundleProduct, ["product_id = $ksProductId"]);
                $this->ksResource->getConnection()->delete($ksRelationTable, ["linked_product_id = $ksProductId"]);
            }
        }
    }

    /**
     * Get Category from Product
     */
    public function ksGetProductCategory($ksProductId, $ksSellerId)
    {
        $ksModel = $this->ksMainProductFactory->create()->load($ksProductId);
        $ksCategories = (array)$ksModel->getCategoryIds();
        $ksSellerCategoryType = $this->getKsSellerCategories($ksSellerId);
        if (!empty($ksSellerCategoryType)) {
            foreach ($ksCategories as $ksValue) {
                if (!in_array($ksValue, $ksSellerCategoryType)) {
                    $this->ksCategoryLinkRepository->deleteByIds($ksValue, $ksModel->getSku());
                }
            }
        }
    }

    /**
     * Get product details by product id
     *
     * @param  [int] $ksId
     * @return [mixed] array
     */
    public function getKsProductDetails($ksId)
    {
        $ksProductCollection = $this->ksCatalogProductFactory->create()->load($ksId);
        $ksProDetails = [];
        $ksProDetails['name'] = $ksProductCollection->getName();
        $ksProDetails['price'] = $ksProductCollection->getPrice();
        $ksProDetails['description'] = is_null($ksProductCollection->getDescription()) ? "" : trim(strip_tags($ksProductCollection->getDescription()));
        if ($ksProDetails['description'] == "") {
            $ksProDetails['description'] = "N/A";
        }
        $ksProDetails['sku'] = $ksProductCollection->getSku();
        $ksProDetails['store_id'] = $ksProductCollection->getStoreId();

        return $ksProDetails;
    }

    /**
     * Attribute from Attribute Set
     * @param  int $ksSellerId
     * @return array
     */
    public function ksGetAttribute($ksSellerId)
    {
        // Attribute Id Array
        $ksAttributeIdArray = [];
        $ksAttributeSetId = $this->ksDataHelper->getKsDefaultAttributes();
        $ksAttributeModel = $this->ksAttributeFactory->create()
                            ->addFieldToFilter(
                                'ks_seller_id',
                                $ksSellerId
                            )->addFieldtoFilter(
                                'ks_attribute_approval_status',
                                \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED
                            );
        if ($ksAttributeModel->getSize()) {
            $ksSellerAttArr = $ksAttributeModel->getColumnValues('attribute_id');
            $ksAttributeIdArray = array_merge($ksAttributeIdArray, $ksSellerAttArr);
        }

        $ksAttributeSetCollection = $this->ksAttributeSetCollectionFactory->create()
            ->addFieldToSelect('attribute_set_id');
        $ksAttributeSetArray = array_column($ksAttributeSetCollection->getData(), 'attribute_set_id');

        // Iterate over Default Given Attribute Id
        foreach ($ksAttributeSetId as $ksValue) {
            if (in_array($ksValue, $ksAttributeSetArray)) {
                // Get Collection of Attribute Set
                $ksArray = $this->ksAttributeInterface->getAttributes(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $ksValue);
                // Iterate to get all the attribute id in attribute set
                foreach ($ksArray as $ksRecord) {
                    if ($ksRecord['ks_include_in_marketplace'] && $ksRecord['ks_seller_id'] == 0) {
                        $ksAttributeIdArray[] = $ksRecord['attribute_id'];
                    }
                }
            }
        }
        return array_unique($ksAttributeIdArray);
    }

    /*
     * Check Admin Attributes
     * @param  int $ksSetId
     * @return bool 0|1
     */
    public function ksCheckAdminAttributes($ksSetId)
    {
        $ksSize = $this->ksAttributeSetCollectionFactory->create()
                        ->addFieldToFilter('attribute_set_id', $ksSetId)
                        ->addFieldToFilter('ks_seller_id', 0)->getSize();
        return $ksSize;
    }

    /**
     * Get Product Attribute Set
     *
     * @param  int $ksSetId
     * @return int
     */
    public function getKsProductAttributeSet($ksProductId)
    {
        $ksModel = $this->ksCatalogProductFactory->create()->load($ksProductId);
        return $ksModel->getAttributeSetId();
    }

    /**
     * Change product attribute set to default
     * @param  int $ksProductId
     * @return void
     */
    public function ksChangeAttributeSet($ksProductId)
    {
        $ksDefault = $this->ksCatalogProductFactory->create()->getDefaultAttributeSetId();
        $ksProduct = $this->ksProductRepoInterface->getById(
            $ksProductId,
            true,
            0
        );
        $ksProduct->setAttributeSetId($ksDefault);
        $ksProduct->save();
    }

    /**
     * Change Product Quantity and Stock Status
     * @param $ksProductId
     * @return void
     */
    public function ksChangeQuantityAndStock($ksProductId)
    {
        $ksQty = 0;
        $ksStockItem = $this->ksStockRegistry->getStockItem($ksProductId);
        $ksStockItem->setQty($ksQty);
        $ksStockItem->setIsInStock((bool)$ksQty);
        $ksStockItem->save();
    }

    /**
     * Change product type configurable to simple
     * @param  int $ksProductId
     * @return void
     */
    public function ksChangeProductType($ksProductId)
    {
        $ksType = $this->ksCheckProductType($ksProductId);
        if ($ksType == 'configurable') {
            $ksProduct = $this->ksProductRepoInterface->getById(
                $ksProductId,
                true,
                0
            );
            $ksProduct->setTypeId('simple');
            $ksProduct->save();
        }
    }

    /**
     * Remove Child of Configurable Product
     * @param $ksProductId
     * @return void
     */
    public function ksRemoveConfigurableChildProduct($ksProductId)
    {
        $ksProductType = $this->ksCheckProductType($ksProductId);
        if ($ksProductType == 'configurable') {
            $ksProductIds = (array)$this->getKsChildProductIds($ksProductId);
            if (!empty($ksProductIds)) {
                $this->ksRemoveChildProducts($ksProductIds);
            }
            $this->ksChangeProductType($ksProductId);
        }
    }


    /**
     * Check condition that product can be assigned or not
     * @param $ksProductId
     * @param $ksSellerId
     * @return bool
     */
    public function ksCheckProductCondition($ksProductId, $ksSellerId)
    {
        // Get All the neccessary conditions in variable
        $ksCondition = true;
        $ksType = $this->ksCheckProductType($ksProductId);
        $ksSet = $this->getKsProductAttributeSet($ksProductId);
        $ksSellerProductType = $this->getKsSellerProductType($ksSellerId);
        $ksAssignedSet = $this->ksDataHelper->getKsDefaultAttributes();
        $ksSellerWebsite = $this->ksSellerHelper->getksSellerWebsiteId($ksSellerId);
        $ksProductWebsite = $this->ksMainProductFactory->create()->load($ksProductId)->getWebsiteIds();
        // Check Website is present in product or not
        if ($ksProductWebsite) {
            // Check Customer per website is on or off
            $ksSharing = $this->ksDataHelper->getKsConfigValue('customer/account_share/scope');
            if (!in_array($ksSellerWebsite, $ksProductWebsite) && $ksSharing) {
                $ksCondition = false;
            }
        }
        if (!(in_array($ksType, $ksSellerProductType)) || !(in_array($ksSet, $ksAssignedSet))) {
            $ksCondition = false;
        }
        return $ksCondition;
    }

    /**
     * Get Admin Attribute Set
     * @return array
     */
    public function getksAdminAttributes()
    {
        return $this->ksAttributeSetCollectionFactory->create()
                    ->addFieldToFilter('ks_seller_id', 0)->getColumnValues('attribute_set_id');
    }
}
