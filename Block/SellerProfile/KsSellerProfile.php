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

namespace Ksolves\MultivendorMarketplace\Block\SellerProfile;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Magento\Framework\App\ObjectManager;
use Ksolves\MultivendorMarketplace\Block\Report\KsReportSeller;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * KsSellerProfile Block class
*/
class KsSellerProfile extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var \Magento\Catalog\Model\ProductRepositorys
     */
    protected $ksProductRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    protected $ksResourceFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper
     */
    protected $ksProfileHelper;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /**
     * @var KsProductCollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var KsProductCollectionFactory
     */
    protected $ksReportSellerBlock;

    /**
     * @var $ksCatData
     */
    protected $ksCatData;

    /**
     * @var $ksCatLevel
     */
    protected $ksCatLevel;

    /**
     * @var $ksCatParentId
     */
    protected $ksCatParentId;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $ksCatalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\Category\Attribute\Source\Sortby
     */
    protected $ksSortBy;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $ksStockInventory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var\Ksolves\MultivendorMarketplace\Model\SellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;

    /**
     * @var\Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory
     */
    protected $ksCategoryRequestsAllowedCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory
     */
    protected $ksCategoryRequestsAllowedFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param PostHelper $ksPostDataHelper
     * @param Resolver $ksLayerResolver
     * @param CategoryRepositoryInterface $ksCategoryRepository
     * @param Data $ksUrlHelper
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \Magento\Catalog\Model\ProductRepository $ksProductRepository
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $ksResourceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $ksProfileHelper
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param \Magento\Catalog\Model\Product\Visibility $ksCatalogProductVisibility
     * @param KsProductCollectionFactory $ksProductCollectionFactory
     * @param \Magento\Catalog\Model\Category\Attribute\Source\Sortby $ksSortBy
     * @param \Magento\CatalogInventory\Helper\Stock $ksStockInventory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param array $data = []
     * @param ?OutputHelper $ksOutputHelper = null
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $ksContext,
        PostHelper $ksPostDataHelper,
        Resolver $ksLayerResolver,
        CategoryRepositoryInterface $ksCategoryRepository,
        Data $ksUrlHelper,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Magento\Catalog\Model\ProductRepository $ksProductRepository,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $ksResourceFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $ksProfileHelper,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        \Magento\Catalog\Model\Product\Visibility $ksCatalogProductVisibility,
        KsProductCollectionFactory $ksProductCollectionFactory,
        \Magento\Catalog\Model\Category\Attribute\Source\Sortby $ksSortBy,
        \Magento\CatalogInventory\Helper\Stock $ksStockInventory,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        KsReportSeller $ksReportSellerBlock,
        array $data = [],
        ?OutputHelper $ksOutputHelper = null
    ) {
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksResourceFactory = $ksResourceFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksProfileHelper = $ksProfileHelper;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        $this->ksCatalogProductVisibility = $ksCatalogProductVisibility;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksSortBy = $ksSortBy;
        $this->ksStockInventory = $ksStockInventory;
        $this->ksReportSellerBlock = $ksReportSellerBlock;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsAllowedCollection = $ksCategoryRequestsAllowedCollection;
        $this->ksCategoryRequestsAllowedFactory = $ksCategoryRequestsAllowedFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $ksPostDataHelper, $ksLayerResolver, $ksCategoryRepository, $ksUrlHelper, $data, $ksOutputHelper);
    }

    /**
     * Return product collection by product id
     *
     * @return array
     */
    public function getKsProductById($id)
    {
        return $this->ksProductRepository->getById($id);
    }

    /**
     * Return product collection by seller id
     *
     * @return array
     */
    public function getKsProductCollection()
    {
        return $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->setOrder('ks_created_at', 'DESC');
    }


    /**
     * Get Sort by
     * @return array
     */
    public function getKsSortBy()
    {
        $ksAllSortBy = $this->ksSortBy->getAllOptions();
        $ksSortArray = [];
        if ($this->getRequest()->getParam('category_id')) {
            $ksAvailableSortBy = $this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($this->getRequest()->getParam('category_id'))->getAvailableSortBy();
            if ($ksAvailableSortBy) {
                foreach ($ksAllSortBy as $ksKey => $ksValue) {
                    if (in_array($ksValue['value'], $ksAvailableSortBy)) {
                        $ksData=[
                            "ks-value"=> $ksValue['value'],
                            "ks-label"=> $ksValue['label']->getText()
                        ];
                        $ksSortArray[] = $ksData;
                    }
                }
            } else {
                foreach ($ksAllSortBy as $ksKey => $ksValue) {
                    $ksData=[
                        "ks-value"=> $ksValue['value'],
                        "ks-label"=> $ksValue['label']->getText()
                    ];
                    $ksSortArray[] = $ksData;
                }
            }
        } else {
            foreach ($ksAllSortBy as $ksKey => $ksValue) {
                $ksData=[
                    "ks-value"=> $ksValue['value'],
                    "ks-label"=> $ksValue['label']->getText()
                ];
                $ksSortArray[] = $ksData;
            }
        }
        return $ksSortArray;
    }

    /**
     * Get Current Sort by
     * @return array
     */
    public function getKsCurrentSortBy()
    {
        return $this->getRequest()->getParam('sortby') ? $this->getRequest()->getParam('sortby') : $this->getKsSortBy()[0]['ks-value'];
    }

    /** Get Current Direction
     * @return array
     */
    public function getKsCurrentDirection()
    {
        return $this->getRequest()->getParam('dirby') ? $this->getRequest()->getParam('dirby') : 'asc';
    }

    /**
     * Return store id
     *
     * @return int
     */
    public function getKsStoreId()
    {
        return $this->ksStoreManager->getStore()->getId();
    }

    /**
     * Return customer id
     *
     * @return int
     */
    public function getKsSellerId()
    {
        return (int)$this->getRequest()->getParam('seller_id');
    }

    /**
     * Return seller category collection
     *
     * @return string
     */
    public function getSellerCategoriesCollection()
    {
        $ksCollection = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        $ksCatIds = [];
        foreach ($ksCollection as $ksSellerCategory) {
            $ksCatIds[] = $ksSellerCategory->getKsCategoryId();
        }
        return $ksCatIds;
    }

    /**
     * Return root category id of a store
     *
     * @return int
     */
    public function getKsRootCategoryId()
    {
        return $this->ksStoreManager->getStore()->getRootCategoryId();
    }

    /**
     * prepare layout
     *
     * @return this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Seller Profile'));
        $ksArray = explode(",", $this->ksProfileHelper->getKsCatalogConfigData());
        $ksPagination = [];
        foreach ($ksArray as $ksValue) {
            $ksPagination[$ksValue] = $ksValue;
        }
        if ($this->getLoadedProductCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager'
            )->setAvailableLimit($ksPagination)
                ->setShowPerPage(true)->setCollection(
                    $this->getLoadedProductCollection()
                );
            $this->setChild('pager', $pager);
            $this->getLoadedProductCollection()->load();
        }
        return $this;
    }

    /**
     * Return pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Return loaded category collection
     *
     * @return AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        $ksArray = explode(",", $this->ksProfileHelper->getKsCatalogConfigData());
        $ksPage = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $ksPageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
        )->getParam('limit') : $ksArray[0];

        if ($this->getRequest()->getParam('category_id')) {
            $ksCategory=$this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($this->getRequest()->getParam('category_id'));
            $ksCollection = $ksCategory->getProductCollection();
        } else {
            $ksCollection = $this->ksProductCollectionFactory->create();
        }
        $ksCollection->setVisibility($this->ksCatalogProductVisibility->getVisibleInSiteIds());
        $ksCollection->addAttributeToSelect('*');
        $ksCollection->addWebsiteFilter($this->ksStoreManager->getStore()->getWebsiteId());
        $ksCollection->addStoreFilter();
        $this->ksStockInventory->addIsInStockFilterToCollection($ksCollection);

        $ksProductTable = $ksCollection->getTable('ks_product_details');
        $ksCollection->getSelect()->join(
            $ksProductTable. ' as ks_pro',
            'e.entity_id = ks_pro.ks_product_id',
            [
                'ks_seller_id' => 'ks_seller_id',
                'ks_created_at'    =>'ks_created_at',
                'ks_product_approval_status' => 'ks_product_approval_status',
            ]
        )->where('ks_product_approval_status =? ', KsProduct::KS_STATUS_APPROVED)
            ->where('ks_seller_id = ?', $this->getKsSellerId())
            ->where('ks_parent_product_id = ?', 0);

        if ($this->getPostValue()) {
            $ksCollection->addAttributeToFilter('name', ['like' => '%'.$this->getPostValue().'%']);
        }

        $ksCollection->setOrder($this->getKsCurrentSortBy(), $this->getKsCurrentDirection());
        $ksCollection->setPageSize($ksPageSize);
        $ksCollection->setCurPage($ksPage);
        return $ksCollection;
    }

    /**
     * Return category name
     *
     * @return string
     */
    public function getCategoryName($ksCatId)
    {
        $ksCategory=$this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($ksCatId);
        $ksParents = array_map('intval', explode('/', $ksCategory->getPath()));
        $ksText ='';
        foreach ($ksParents as $ksParent) {
            // category id not equal to root category
            if ($ksParent!= \Magento\Catalog\Model\Category::TREE_ROOT_ID && $ksParent!=$ksCategory->getId()) {
                $ksCat=$this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($ksParent);
                $ksText .= $ksCat->getName() . ' >> ';
            }
        }
        $ksText .= $ksCategory->getName();
        return $ksText;
    }

    /**
     * Return category data
     *
     * @return array
     */
    public function getCategoryData($ksCatId)
    {
        $ksCategory=$this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($ksCatId);
        return $ksCategory;
    }

    /**
     * Return tab id
     *
     * @return int
     */
    public function getKsTabId()
    {
        $ksSellerConfigData = $this->getKsSellerConfigData();
        $ksHomePageEnabled = 1;
        if (!$ksSellerConfigData->getKsShowBanner() && !$ksSellerConfigData->getKsShowRecentlyProducts() && !$ksSellerConfigData->getKsShowBestProducts() && !$ksSellerConfigData->getKsShowDiscountProducts()) {
            $ksHomePageEnabled = 0;
        }
        $ksContactPageEnabled = 0;
        if ($this->ksProfileHelper->getKsSellerConfigData('ks_show_contact_us_details') || $this->ksProfileHelper->getKsSellerConfigData('ks_show_contact_us_form')) {
            $ksContactPageEnabled = 1;
        }
        $ksTabId = $this->getRequest()->getParam('tab_id');
        if ($ksTabId) {
            if ($this->ksProfileHelper->getKsSellerConfigData('ks_homepage') && $ksTabId == "tab-1") {
                if ($ksHomePageEnabled) {
                    return $ksTabId;
                } else {
                    return "tab-2";
                }
            } elseif ($ksTabId == "tab-4") {
                if ($ksContactPageEnabled) {
                    return $ksTabId;
                } else {
                    return "tab-2";
                }
            } else {
                return $ksTabId;
            }
        } else {
            if ($this->ksProfileHelper->getKsSellerConfigData('ks_homepage') && $ksHomePageEnabled) {
                return "tab-1";
            } else {
                return "tab-2";
            }
        }
    }

    /**
     * Return search product name
     *
     * @return string
     */
    public function getPostValue()
    {
        return $this->getRequest()->getParam('s');
    }

    /**
     * Return seller config data
     *
     * @return string
     */
    public function getKsSellerConfigData()
    {
        $ksCollection = $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        if (count($ksCollection) == 0) {
            $ksModel = $this->ksSellerConfigFactory->create();
            $ksData=[
                "ks_seller_id"               => $this->getKsSellerId(),
                "ks_show_banner"             => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_show_recently_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_recently_products_text"  => $this->escapeHtml('Recently Added Products'),
                "ks_recently_products_count" => 10,
                "ks_show_best_products"      => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_best_products_text"      => $this->escapeHtml('Best Selling Products'),
                "ks_best_products_count"     => 10,
                "ks_show_discount_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_discount_products_text"  => $this->escapeHtml('Most Discounted Products'),
                "ks_discount_products_count" => 10
            ];
            $ksModel->addData($ksData)->save();
        }
        return $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
    }

    /**
     * Get delete action
     *
     * @return url
     */
    public function getKsDeleteAction()
    {
        return $this->getUrl('multivendor/favouriteSeller/delete', ['_secure' => true]);
    }

    /**
     * Get root category collection
     *
     * @return array
     */
    public function getKsRootCategoryCollection()
    {
        return $this->ksCategoryFactory->create()->load($this->getKsRootCategoryId())->setStoreId($this->getKsStoreId());
    }

    /**
     * Get category array of seller
     *
     * @return array
     */
    public function getKsCategoryArray($ksCategoryHelper)
    {
        $ksCatArray = [];
        foreach ($this->getSellerCategoriesCollection() as $ksCatId) {
            if ($ksCatId != $this->getKsRootCategoryId() && $ksCategoryHelper->getKsParentCategory($ksCatId, $this->getKsStoreId()) == $this->getKsRootCategoryId() && !in_array($ksCatId, $ksCatArray)) {
                $ksCatArray[] = $ksCatId;
                $ksParentCategory = explode("/", $this->ksCategoryFactory->create()->load($ksCatId)->getPath());
                foreach ($ksParentCategory as $ksParentId) {
                    if ($ksParentId != $this->getKsRootCategoryId() && !in_array($ksParentId, $ksCatArray) && $ksParentId  != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                        $ksCatArray[] = $ksParentId;
                    }
                }
            }
        }
        return $ksCatArray;
    }

    /**
     * recursive function to create multilevel menu list, $parentId 0 is the Root
     *
     * @return html
     */
    public function ksMultilevelMenu($ksParentId, $ksCtgLists, $ksCtgData, $block)
    {
        $ksHtml = '';
        $ksStoreUrl = $this->getKsSellerProfileUrl($this->getKsSellerId());
        if ($ksStoreUrl) {
            $ksActionUrl= $block->getBaseUrl().$ksStoreUrl;
        } else {
            $ksActionUrl = $block->getBaseUrl().'multivendor/sellerprofile/sellerprofile/seller_id/'.$block->getKsSellerId();
        }
        if (isset($ksCtgLists[$ksParentId])) {
            $ksHtml = '<ul id="ks-seller-product-catrgory"> ';
            foreach ($ksCtgLists[$ksParentId] as $ksChildId) {
                if ($ksParentId == 0) {
                    $ksClsa = ' class="firsrli"';
                } elseif (isset($ksCtgLists[$ksChildId])) {
                    $ksClsa = ' class="litems"';
                } else {
                    $ksClsa = '';
                }
                if ($ksChildId == $this->getRequest()->getParam('category_id')) {
                    $ksHtml .= '<li><span class="ks-category-caret ks-category-caret-down"><a href="'. $ksActionUrl.'?category_id='.$ksChildId.'&tab_id=tab-2'.'" title="'. $ksCtgData[$ksChildId]['lname'] .'"'. $ksClsa .'>'. $ksCtgData[$ksChildId]['lname'] .'</a></span>';
                } else {
                    if (!in_array($ksChildId, $this->getSellerCategoriesCollection())) {
                        $ksHtml .= '<li><span class="ks-category-caret ks-category-unclickable"><a href="#" title="'. $ksCtgData[$ksChildId]['lname'] .'"'. $ksClsa .'>'. $ksCtgData[$ksChildId]['lname'] .'</a></span>';
                    } else {
                        $ksHtml .= '<li><span class="ks-category-caret"><a href="'. $ksActionUrl.'?category_id='.$ksChildId.'&tab_id=tab-2'.'" title="'. $ksCtgData[$ksChildId]['lname'] .'"'. $ksClsa .'>'. $ksCtgData[$ksChildId]['lname'] .'</a></span>';
                    }
                }
                $ksHtml .= $this->ksMultilevelMenu($ksChildId, $ksCtgLists, $ksCtgData, $block);
                $ksHtml .= '</li>';
            }
            $ksHtml .= '</ul>';
        }
        return $ksHtml;
    }

    /**
     * recursive function to create ksRenderFlatNav
     *
     * @return array
     */
    public function ksRenderFlatNav($ksCategories, $ksCatArray, $ksRootId)
    {
        $ksChildrenCategories = $ksCategories->getChildrenCategories();
        if (count($ksChildrenCategories) > 0) {
            $ksCat = [];
            foreach ($ksChildrenCategories as $ksChild) {
                //pass children node
                if (in_array($ksChild->getId(), $ksCatArray)) {
                    $ksItem = [];
                    $ksItem['id'] = $ksChild->getId();
                    $ksItem['name'] = $ksChild->getName();
                    $ksItem['parent_id'] = $ksChild->getParentId();
                    $ksItem['level'] = $ksChild->getLevel();
                    $ksItem['path'] = $ksChild->getRequestPath();
                    $ksCat[] = $ksItem;

                    if (!$this->ksCatParentId) {
                        $this->ksCatParentId = $ksChild->getParentId();
                    }
                    $this->ksCatData[$ksItem['id']] = array(
                        'lname'=> $ksItem['name'],
                        'lurl'=> $ksItem['path'],
                    );
                    if (!isset($this->ksCatLevel[$ksChild->getParentId()])) {
                        $this->ksCatLevel[$ksChild->getParentId()] = array();
                    }
                    array_push($this->ksCatLevel[$ksChild->getParentId()], $ksItem['id']);
                    if ($ksChild->hasChildren()) {
                        $ksCat[] = $this->ksRenderFlatNav($ksChild, $ksCatArray, $ksRootId);
                    }
                }
            }
        }
        return [$this->ksCatLevel, $this->ksCatData, $this->ksCatParentId];
    }

    /**
     * @return Ksolves\MultivendorMarketplace\Block\Report\KsReportSeller
     */
    public function getKsReportBlock()
    {
        return $this->ksReportSellerBlock;
    }

    /**
     * @return category name
     */
    public function getKsCategoryName()
    {
        return $this->ksCategoryFactory->create()->setStoreId($this->getKsStoreId())->load($this->getRequest()->getParam('category_id'))->getName();
    }

    /**
     * save seller store categories
     * @return void
     */
    public function ksSaveStoreCategories()
    {
        //intialize variable
        $ksIsRequestAllowed = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED;
        //intialize variable
        $ksIsAutoApproved = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED;
        //get requests allowed collection data
        $ksRequestsAllowed = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->addFieldToFilter('ks_is_init', 1);
        //get requests allowed collection data
        $ksRequestsAllowedCollection = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        //check count
        if (count($ksRequestsAllowedCollection) != 0) {
            $ksIsAllowed = $ksRequestsAllowedCollection->getFirstItem();
            $ksIsRequestAllowed = $ksIsAllowed->getKsIsRequestsAllowed();
            $ksIsAutoApproved = $ksIsAllowed->getKsIsAutoApproved();
        } else {
            $ksIsRequestAllowed = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED;
            if ($this->ksCategoryHelper->getKsGeneralSettingConfig('ks_requires_admin_approval')) {
                $ksIsAutoApproved = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_DISABLED;
            }
        }
        //get model data
        $ksSellerCategories = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        //check collection data
        if (count($ksRequestsAllowed) == 0 && $this->getKsSellerId()) {
            //get model data
            $ksRequestsAllowedFactory = $this->ksCategoryRequestsAllowedFactory->create();
            $ksData=[
                "ks_seller_id" => $this->getKsSellerId(),
                "ks_is_requests_allowed" => $ksIsRequestAllowed,
                "ks_is_init" =>  \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED,
                "ks_is_auto_approved" => $ksIsAutoApproved
            ];
            //add data
            $ksRequestsAllowedFactory->addData($ksData)->save();
            $this->ksSaveSellerCategories();
        }
    }

    /**
     * save seller categories
     * @return void
     */
    public function ksSaveSellerCategories()
    {
        //get category id
        $ksCategoryIds = $this->ksCategoryHelper->getKsAllCategoriesIds($this->getKsStoreId());
        foreach ($ksCategoryIds as $ksCategoryId) {
            //get collection data
            $ksSellerCategory = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->addFieldToFilter('ks_category_id', $ksCategoryId);
            //get model data
            $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
            //check collection data
            if (count($ksSellerCategory)==0 && $this->ksCategoryHelper->getKsCategoryExist($ksCategoryId, $this->getKsStoreId())) {
                $ksData = [
                       "ks_seller_id" => $this->getKsSellerId(),
                       "ks_category_id" => $ksCategoryId,
                       "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
                      ];
                //add data
                $ksCategoriesFactory->addData($ksData)->save();
            }
        }
    }

    /**
     * Get seller profile redirect url
     * @param $ksSellerId
     * @return url
     */
    public function getKsSellerProfileUrl($ksSellerId)
    {
        return $this->ksSellerHelper->getKsSellerProfileUrl($ksSellerId);
    }
}
