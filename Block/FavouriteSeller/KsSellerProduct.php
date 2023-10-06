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

namespace Ksolves\MultivendorMarketplace\Block\FavouriteSeller;

use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsProductModel;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * KsSellerProduct Block Class
 */
class KsSellerProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $ksImageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksHelperData;

    /**
    * @var \Magento\Catalog\Model\Product
    */
    protected $ksProductlists;

    /**
     * @var KsProductModel
     */
    protected $ksProductModel;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $ksCategoryRepository;

    /**
     * @var CollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var productStatus
     */
    protected $ksProductStatus;

    /**
     * @var productVisibility
     */
    protected $ksProductVisibility;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavSellerHelper;
    
    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $ksStockInventory;

    /**
     * Constructor
     *
     * @param Magento\Backend\Block\Template\Context $ksContext
     * @param Magento\Catalog\Helper\Image $ksImageHelper
     * @param Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param Magento\Framework\Registry $ksRegistry
     * @param KsProductModel $ksProductModel
     * @param CategoryRepositoryInterface  $ksCategoryRepository
     * @param CollectionFactory $ksProductCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $ksProductStatus
     * @param \Magento\Catalog\Model\Product\Visibility $ksProductVisibility
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\CatalogInventory\Helper\Stock $ksStockInventory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $ksContext,
        \Magento\Catalog\Helper\Image $ksImageHelper,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData,
        KsProductModel $ksProductModel,
        CategoryRepositoryInterface $ksCategoryRepository,
        CollectionFactory $ksProductCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $ksProductStatus,
        \Magento\Catalog\Model\Product\Visibility $ksProductVisibility,
        \Magento\Framework\Data\Helper\PostHelper $ksPostDataHelper,
        \Magento\Framework\Url\Helper\Data $ksUrlHelper,
        \Magento\Catalog\Model\Layer\Resolver $ksLayerResolver,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper,
        \Magento\CatalogInventory\Helper\Stock $ksStockInventory,
        array $data = []
    ) {
        $this->ksImageHelper = $ksImageHelper;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksRegistry = $ksRegistry;
        $this->ksHelperData = $ksHelperData;
        $this->ksProductModel = $ksProductModel;
        $this->ksCategoryRepository = $ksCategoryRepository;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksProductStatus = $ksProductStatus;
        $this->ksProductVisibility = $ksProductVisibility;
        $this->ksFavSellerHelper = $ksFavSellerHelper;
        $this->ksStockInventory = $ksStockInventory;
        parent::__construct(
            $ksContext,
            $ksPostDataHelper,
            $ksLayerResolver,
            $ksCategoryRepository,
            $ksUrlHelper,
            $data
        );
    }

    /**
     * Get Seller Products Collection
     * @param  $ksSellerId
     * @return array
     */
    public function getKsProductCollection($ksSellerId)
    {
        // Get Product Collcection and Filter It according to product Status
        $ksQuerydata = $this->ksProductModel->create()->getCollection()
            ->addFieldToFilter('ks_seller_id', ['eq' => $ksSellerId])
            ->addFieldToFilter('ks_product_approval_status', KsProduct::KS_STATUS_APPROVED)
            ->addFieldToFilter('ks_parent_product_id', 0);

        $ksProductIds = [];
        foreach ($ksQuerydata as $ksItem) {
            $ksProductIds[] =  $ksItem->getKsProductId();
        }
            
        // Create Product Collection
        $ksCollection = $this->ksProductCollectionFactory->create();

        // Check Status
        $ksCollection->addAttributeToFilter('status', ['in' => $this->ksProductStatus->getVisibleStatusIds()]);
        // Check Visibility
        $ksCollection->addAttributeToFilter('visibility', ['in' => $this->ksProductVisibility->getVisibleInSiteIds()]);
        $ksCollection->addWebsiteFilter($this->ksStoreManager->getStore()->getWebsiteId());
        $ksCollection->addStoreFilter();
        $this->ksStockInventory->addIsInStockFilterToCollection($ksCollection);
        $ksCollection->addAttributeToSelect('*');
        $ksCollection->addFieldToFilter('entity_id', ['in' => $ksProductIds])->setOrder('created_at', 'DESC')->setPageSize(4);
            
        return $ksCollection;
    }
}
