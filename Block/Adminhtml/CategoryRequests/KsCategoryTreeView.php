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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CategoryRequests;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;

/**
 * KsCategoryTreeView block class
 */
class KsCategoryTreeView extends \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlInterface;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory
     */
    protected $ksCategoryCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory
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
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\Source\KsCategoryList
     */
    protected $ksCatList;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeInterface;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksDisplayMode;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksSortBy;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $ksBackendSession;
    
    /**
     * @var Visibility
     */
    private $ksProductVisibilty;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;
    
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
      * @var DataPersistorInterface
      */
    protected $ksDataPersistor;

    /**
     * @var KsProductCollection
     */
    protected $ksCatalogProductFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory
     */
    protected $ksCategoryRequestCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $ksCategoryTree
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Magento\Framework\DB\Helper $ksResourceHelper
     * @param \Magento\Backend\Model\Auth\Session $ksBackendSession
     * @param \Magento\Framework\UrlInterface $ksUrlInterface
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\Source\KsCategoryList $ksCatList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     * @param \Magento\Catalog\Model\Category\Attribute\Source\Mode $ksDisplayMode
     * @param \Magento\Catalog\Model\Category\Attribute\Source\Sortby $ksSortBy
     * @param DataPersistorInterface $ksDataPersistor
     * @param Visibility $ksProductVisibilty = null
     * @param KsProductCollection $ksCatalogProductFactory
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $ksCategoryTree,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\Framework\DB\Helper $ksResourceHelper,
        \Magento\Backend\Model\Auth\Session $ksBackendSession,
        \Magento\Framework\UrlInterface $ksUrlInterface,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestCollection,
        \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Model\Source\KsCategoryList $ksCatList,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface,
        \Magento\Catalog\Model\Category\Attribute\Source\Mode $ksDisplayMode,
        \Magento\Catalog\Model\Category\Attribute\Source\Sortby $ksSortBy,
        DataPersistorInterface $ksDataPersistor,
        KsProductCollection $ksCatalogProductFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        Visibility $ksProductVisibilty = null,
        array $ksData = []
    ) {
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksCategoryRequestCollection = $ksCategoryRequestCollection;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksUrlInterface = $ksUrlInterface;
        $this->ksCategoryRequestsAllowedCollection = $ksCategoryRequestsAllowedCollection;
        $this->ksCategoryRequestsAllowedFactory = $ksCategoryRequestsAllowedFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksCatList = $ksCatList;
        $this->ksScopeInterface = $ksScopeInterface;
        $this->ksDisplayMode = $ksDisplayMode;
        $this->ksSortBy = $ksSortBy;
        $this->ksBackendSession = $ksBackendSession;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksProductVisibilty = $ksProductVisibilty ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        parent::__construct($ksContext, $ksCategoryTree, $ksRegistry, $ksCategoryFactory, $ksJsonEncoder, $ksResourceHelper, $ksBackendSession, $ksData);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Ksolves_MultivendorMarketplace::category/ks_category_view.phtml');
    }
    
    /**
     * Get Seller Id
     * @return int
     */
    public function getKsSellerId()
    {
        //return seller id
        if ($this->getRequest()->getParam('ks_seller_id')) {
            return $this->getRequest()->getParam('ks_seller_id');
          
        } else {
            return $this->ksRegistry->registry('ks_seller_id');
        }
    }

    /**
     * Get Display mode
     * @return array
     */
    public function getKsDisplayMode()
    {
        return $this->ksDisplayMode->toOptionArray();
    }
    
    /**
     * Get Product Visibility
     * @return array
     */
    public function getKsProductVisibility()
    {
        return $this->ksProductVisibilty->toOptionArray();
    }
    
    /**
     * Get Sort by
     * @return array
     */
    public function getKsSortBy()
    {
        return $this->ksSortBy->getAllOptions();
    }
    
    /**
     * Get current category id for admin
     * @return int
     */
    public function getKsAdminCurrentCategoryId()
    {
        return $this->ksDataPersistor->get('ks_current_admin_category_id');
    }

    /**
     * Get current category id for frontend
     * @return int
     */
    public function getKsFrontendCurrentCategoryId()
    {
        return $this->ksDataPersistor->get('ks_current_frontend_category_id');
    }

    /**
     * Get Root Id
     * @return int
     */
    public function getKsRootIds()
    {
        //get category id array
        $ksIds = $this->ksCategoryHelper->getKsRootIds();
        $ksRootIds = implode(",", $ksIds);
        return $ksRootIds;
    }

    /**
     * Get Store Id
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->getRequest()->getParam('store') ? (int)$this->getRequest()->getParam('store') : 0;
    }
    
    /**
     * Get Base Url
     * @return string
     */
    public function getKsBaseUrl()
    {
        return $this->ksScopeInterface->getValue("web/secure/base_url");
    }

    /**
     * Get Base Url
     * @return string
     */
    public function getKsRequiresAdminApproval()
    {
        return $this->ksScopeInterface->getValue("ks_marketplace_catalog/ks_product_categories/ks_requires_admin_approval");
    }
    
    /**
     * Get Seller Categories Id
     * @return string
     */
    public function getKsSellerCategoriesIds()
    {
        return $this->getKsAllCategoriesIds();
    }

    /**
     * Get Seller Categories Id
     * @return string
     */
    public function getKsAllCategoriesIds()
    {
        //get category id array
        $ksCategoryIds = $this->ksCategoryHelper->getKsAllCategoriesIds($this->getKsStoreId());
        $ksSellerCatIds = implode(",", $ksCategoryIds);
        return $ksSellerCatIds;
    }

    /**
     * Get category Request status
     * @return int
     */
    public function getKsCategoryRequestAllowedStatus()
    {
        //get model data
        $ksRequestsAllowed = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
        //return status
        return $ksRequestsAllowed->getKsIsRequestsAllowed();
    }

    /**
     * Get category auto approval status
     * @return int
     */
    public function getKsIsAutoApproved()
    {
        //get model data
        $ksRequestsAllowed = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
        //return status
        return $ksRequestsAllowed->getKsIsAutoApproved();
    }
    
    /**
     * Get seller category collection
     * @return int
     */
    public function getKsSellerCategoriesCollection()
    {
        //get model data
        $ksSellerCategories = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        return $ksSellerCategories;
    }

    /**
     * Get categories
     * @return array
     */
    public function getKsSellerAssignedCategories()
    {
        //get model data
        $ksCollections = $this->getKsSellerCategoriesCollection();
        $ksCategoryIds=[];
        foreach ($ksCollections as $ksCollection) {
            $ksCategoryIds[] = $ksCollection->getKsCategoryId();
        }
        return $ksCategoryIds;
    }

    /**
     * return category id
     * @return int[]
     */
    public function getCategoryIds()
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
            if($this->getKsRequiresAdminApproval()){
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
        $ksCategoryIds=[];
        foreach ($ksSellerCategories as $ksSellerCategory) {
            $ksCategoryIds[] = $ksSellerCategory->getKsCategoryId();
        }
        //return selected category ids
        return array_merge($this->_selectedIds, $ksCategoryIds);
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
     * @return int[]
     */
    public function getKsCategoryProductCollection($ksCategoryId)
    {
        $ksCategoryCollection = $this->ksCategoryFactory->create()->load($ksCategoryId)->setStoreId(0);
        $ksProductCollection = $ksCategoryCollection->getProductCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('entity_id', ['in' => $this->getKsProductIds()]);      
        $ksSellerProductIds =  array_column($ksProductCollection->getData(),'entity_id');
        $ksSellerProductCount = 0;
        //check seller disabled category collection size
        if(!$ksCategoryCollection->getIsAnchor()){
            //get seller disabled category collection data
            $ksSellerCatCol = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_category_id',$ksCategoryId)->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->addFieldToFilter('ks_category_status',0);
            if($ksSellerCatCol->getSize() > 0){
                $ksAllProductIds = array_merge($ksSellerProductIds,$this->getKsBackupProductIds($ksCategoryId));
                $ksSellerProductCount = count(array_unique($ksAllProductIds));
            }else{
                $ksSellerProductCount = $ksProductCollection->getSize();
            }
        } else {
            $ksAllChildrenCatIds = $ksCategoryCollection->getAllChildren(true);
            //get seller disabled category collection data
            $ksSellerCatCol = $this->ksSellerCategoriesCollection->create()->addFieldToSelect('ks_category_id')->addFieldToFilter('ks_category_id',['in' => $ksAllChildrenCatIds])->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->addFieldToFilter('ks_category_status',0);  
            $ksSellerCatIds = array_column($ksSellerCatCol->getData(),'ks_category_id');
            $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToSelect('ks_product_id')->addFieldToFilter('ks_category_id',['in' => $ksSellerCatIds])->addFieldToFilter('ks_product_id',['in' => $this->getKsProductIds()]);
            $ksUpdatedProductIds = array_column($ksProductCategoryCollection->getData(),'ks_product_id');
            $ksAllProductIds = array_merge($ksSellerProductIds,$ksUpdatedProductIds);
            $ksSellerProductCount = count(array_unique($ksAllProductIds));
        }
        return $ksSellerProductCount;
    }

    /**
     * @return array
     */
    public function getKsRequestedCategory()
    {
        $ksDisabledStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_DISABLED;
        //get collection data
        $ksCategories = $this->ksCategoryRequestCollection->create()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->addFieldToFilter('ks_request_status', $ksDisabledStatus);
        //init category array
        $ksCategoryIds=[];
        foreach ($ksCategories as $ksCategory) {
            $ksCategoryIds[] = $ksCategory->getKsCategoryId();
        }
        //return category ids array
        return $ksCategoryIds;
    }

    /**
     * Return product id
     *
     * @return array
     */
    public function getKsProductIds()
    {
        $ksProductId =[];
        $ksProductCollection = $this->ksProductFactory->create()->getCollection()
                            ->addFieldToFilter('ks_seller_id',$this->getKsSellerId())
                            ->addFieldToFilter('ks_product_approval_status', KsProduct::KS_STATUS_APPROVED)
                            ->addFieldToFilter('ks_parent_product_id', 0);

        foreach ($ksProductCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     * Return backup product ids
     * 
     * @return array
     */
    public function getKsBackupProductIds($ksCategoryId)
    {
        $ksProductId =[];
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId)->addFieldToFilter('ks_product_id',['in' => $this->getKsProductIds()]);
        foreach ($ksProductCategoryCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     * @param array|Node $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $ksItem = [];
        $ksItem['text'] = $this->escapeHtml($node->getName());
        $ksItem['text'] .= ' (' . $this->getKsCategoryProductCollection($node->getId()) . ')';
        if (in_array($node->getId(), $this->getKsRequestedCategory())) {
            $ksItem['text'] .= '<span class="ks-requested-small">Requested</span>';
            $this->setExpandedPath($node->getData('path'));
        }

        $ksItem['id'] = $node->getId();
        $ksItem['path'] = $node->getData('path');
        $ksItem['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $ksItem['allowDrop'] = false;
        $ksItem['allowDrag'] = false;
       
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData('path'));
            $ksItem['checked'] = true;
        }
        if ($node->getLevel() < 2) {
            $this->setExpandedPath($node->getData('path'));
        }

        if ($node->hasChildren()) {
            $ksItem['children'] = [];
            foreach ($node->getChildren() as $child) {
                $ksItem['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($ksItem['children']) && (int)$node->getChildrenCount() > 0) {
            $ksItem['children'] = [];
        }
        $ksItem['expanded'] = in_array($node->getId(), $this->getExpandedPath());
        $this->setExpandedPath($node->getData('path'));
        return $ksItem;
    }

    /**
     * Get root category for tree
     *
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory);
        }
        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }

            $tree = $this->_categoryTree->load(null);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root) {
                $root->setIsVisible(true);
                if ($root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    $root->setName(__('Root'));
                }
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * Get category node for tree
     *
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->_categoryTree->loadNode($nodeId);
        $node->loadChildren();

        if ($node && $nodeId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }
}