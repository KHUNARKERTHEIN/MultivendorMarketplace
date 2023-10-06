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

use Magento\Store\Model\ScopeInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;
use Magento\Framework\App\ResourceConnection;
use Ksolves\MultivendorMarketplace\Model\KsProductCategoryBackupFactory;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;

/**
 * KsCategoryRequests Helper class
 */
class KsCategoryRequests extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const KS_XML_PATH_MULTIVENDOR = 'ks_marketplace_catalog/';
    public const KS_XML_PATH_CUSTOMER = 'customer/account_share/scope';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $ksCategoryCollection;

    /**
     * @var KsCategoryRequestsCollectionFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var KsProductCollection
     */
    protected $ksCatalogProductCollectionFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var  \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $ksCategoryLinkInterface;

    /**
     * @var  \Magento\Catalog\Model\ProductFactory
     */
    protected $ksCatalogProductFactory;

    /**
     * @var ResourceConnection
     */
    protected $ksResourceConnection;

    /**
     * @var KsProductCategoryBackupFactory
     */
    protected $ksProductCategoryBackupFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    protected $ksCategoryLinkRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollection
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryCollectionFactory
     * @param KsCategoryRequestsCollectionFactory $ksCategoryRequestsFactory
     * @param Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param KsProductCollection $ksCatalogProductCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param Magento\Catalog\Api\CategoryLinkManagementInterface $ksCategoryLinkInterface
     * @param Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory
     * @param KsProductCollection $ksCatalogProductFactory
     * @param ResourceConnection $ksResourceConnection
     * @param KsProductCategoryBackup $ksProductCategoryBackupFactory
     * @param CategoryLinkRepositoryInterface  $ksCategoryLinkRepository
     * @param \Psr\Log\LoggerInterface $ksLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollection,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryCollectionFactory,
        KsCategoryRequestsCollectionFactory $ksCategoryRequestsFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        KsProductCollection $ksCatalogProductCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $ksCategoryLinkInterface,
        \Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory,
        ResourceConnection $ksResourceConnection,
        KsProductCategoryBackupFactory $ksProductCategoryBackupFactory,
        CategoryLinkRepositoryInterface  $ksCategoryLinkRepository,
        \Psr\Log\LoggerInterface $ksLogger
    ) {
        $this->ksCategoryCollection = $ksCategoryCollection;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        $this->ksCategoryLinkInterface = $ksCategoryLinkInterface;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksCatalogProductCollectionFactory = $ksCatalogProductCollectionFactory;
        $this->ksCategoryLinkRepository = $ksCategoryLinkRepository;
        $this->ksResourceConnection = $ksResourceConnection;
        $this->ksProductCategoryBackupFactory = $ksProductCategoryBackupFactory;
        $this->ksLogger = $ksLogger;
        parent::__construct($ksContext);
    }

    /**
     * @param $ksFieldid,$ksStoreId=null
     * @return Magento\Store\Model\ScopeInterface
     */
    public function getKsConfigValue($ksField, $ksStoreId = null)
    {
        return $this->scopeConfig->getValue(
            $ksField,
            ScopeInterface::SCOPE_STORE,
            $ksStoreId
        );
    }

    /**
     * @param $ksFieldid,$ksStoreCode=null
     * @return string
     */
    public function getKsGeneralSettingConfig($ksFieldid, $ksStoreCode = null)
    {
        return $this->getKsConfigValue(self::KS_XML_PATH_MULTIVENDOR.'ks_product_categories/'.$ksFieldid, $ksStoreCode);
    }

    /**
     * @param $ksStoreId
     * @return int[]
     */
    public function getKsAllCategoriesIds($ksStoreId)
    {
        $ksEnabledStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED;
        $ksCategoryIds=[];
        //get model data
        $ksCategories = $this->ksCategoryCollection->create()
        ->addAttributeToSelect('*')
        ->setStoreId($ksStoreId);
        foreach ($ksCategories as $ksCategory) {
            $ksData = $ksCategory->getData();
            if (!isset($ksData['ks_include_in_marketplace'])) {
                $ksCategoryIds[] = $ksCategory->getId();
            } elseif ($ksCategory->getKsIncludeInMarketplace()) {
                $ksCategoryIds[] = $ksCategory->getId();
            }
        }
        return $ksCategoryIds;
    }

    /**
     * @param $ksCategoryId
     * @param $ksStoreId
     * @return boolean
     */
    public function getKsCategoryExist($ksCategoryId, $ksStoreId)
    {
        //check store id
        if ($ksStoreId != 0) {
            //get root id
            $ksRootId = $this->ksStoreManager->getStore($ksStoreId)->getRootCategoryId();
            //get model data
            $ksCategory = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCategoryId);
            $ksData = $ksCategory->getData();
            //check category status
            if (!isset($ksData['ks_include_in_marketplace']) && $this->getKsParentCategory($ksCategoryId, $ksStoreId) == $ksRootId) {
                return true;
            } elseif ($ksCategory->getKsIncludeInMarketplace() && $this->getKsParentCategory($ksCategoryId, $ksStoreId)  == $ksRootId) {
                return true;
            } else {
                return false;
            }
        } else {
            //get model data
            $ksCategory = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCategoryId);
            $ksData = $ksCategory->getData();
            //check category status
            if (!isset($ksData['ks_include_in_marketplace'])) {
                return true;
            } elseif ($ksCategory->getKsIncludeInMarketplace()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * return new seller category id
     * @param $ksStoreId
     * @return int[]
     */
    public function getKsNewSellerInitialCategories($ksStoreId)
    {
        //return categories id
        return $this->getKsAllCategoriesIds($ksStoreId);
    }

    /**
     * return parent id of category
     * @param $ksCategoryId
     * @param $ksStoreId
     * @return int
     */
    public function getKsParentCategory($ksCategoryId, $ksStoreId)
    {
        //get model data
        $ksCat = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCategoryId);
        if ($ksCat->getParentId() && $ksCat->getParentId()!= \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            return $this->getKsParentCategory($ksCat->getParentId(), $ksStoreId);
        }
        return  $ksCat->getId();
    }

    /**
     * get Current Website Id
     * @return int
     */
    public function getKsCurrentWebsiteId()
    {
        return $this->ksStoreManager->getStore()->getWebsiteId();
    }

    /**
     * get Current Website Id
     * @return int
     */
    public function getKsCurrentStoreId()
    {
        return $this->ksStoreManager->getStore()->getStoreId();
    }


    /**
     * get ROot Ids
     * @return int[]
     */
    public function getKsRootIds()
    {
        $ksRootIds = [];
        if ($this->getKsCustomerConfigScope() == 1) {
            $ksWebsite = $this->ksStoreManager->getWebsite($this->getKsCurrentWebsiteId());
            $ksStoreGroups = $ksWebsite->getGroups();
            foreach ($ksStoreGroups as $ksGroups) {
                if (!in_array($ksGroups->getRootCategoryId(), $ksRootIds)) {
                    $ksRootIds[] = $ksGroups->getRootCategoryId();
                }
            }
        } else {
            $ksWebsites = $this->ksStoreManager->getWebsites();
            foreach ($ksWebsites as $ksWebsite) {
                $ksStoreGroups = $ksWebsite->getGroups();
                foreach ($ksStoreGroups as $ksGroups) {
                    if (!in_array($ksGroups->getRootCategoryId(), $ksRootIds)) {
                        $ksRootIds[] = $ksGroups->getRootCategoryId();
                    }
                }
            }
        }
        return $ksRootIds;
    }

    /**
     * get Customer Config Scope
     * @param $ksStoreId
     * @return int[]
     */
    public function getKsCustomerConfigScope($ksStoreId = null)
    {
        return $this->scopeConfig->getValue(
            self::KS_XML_PATH_CUSTOMER,
            ScopeInterface::SCOPE_STORE,
            $ksStoreId
        );
    }

    /**
     * return pending category requests count
     * @return int
     */
    public function getKsPendingCategoryRequestsCount()
    {
        $ksPendingStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_PENDING;
        //get model data
        $ksCategoryRequestsCollection = $this->ksCategoryRequestsFactory->create()
                ->addFieldToFilter('ks_request_status', $ksPendingStatus);
        //return collection size
        return $ksCategoryRequestsCollection->getSize();
    }

    /**
     * Get category details
     *
     * @param  [int] $ksSellerId
     * @return [array]
     */
    public function getKsCategoryDetails($ksCatId, $ksSellerId)
    {
        $ksCollection = $this->ksCategoryCollectionFactory->create()->load($ksCatId);
        //get product collection from category
        $ksProductCollection = $this->ksCatalogProductCollectionFactory->create()->addAttributeToSelect('*');
        $ksProductCollection->addCategoriesFilter(['eq' => $ksCatId]);
        //get global product count
        $ksGlobalProductCount = $ksProductCollection->getSize();
        //get seller product count
        $ksSellerProductCount =  $ksProductCollection->addFieldToFilter('entity_id', ['in' => $this->getKsProductIds($ksSellerId)])->getSize();
        $ksCatDetails = [];
        $ksCatDetails['ks_category_image'] = $ksCollection->getImage();
        $ksCatDetails['ks_category_description'] = is_null($ksCollection->getDescription()) ? "" : trim(strip_tags($ksCollection->getDescription()));
        $ksCatDetails['ks_global_product_count'] = $ksGlobalProductCount + $this->getKsBackupGlobalCount($ksCatId);
        $ksCatDetails['ks_seller_product_count'] = $ksSellerProductCount + count($this->getKsBackupProductIds($ksCatId,$ksSellerId));
        if ($ksCatDetails['ks_category_description'] == "") {
            $ksCatDetails['ks_category_description'] = "N/A";
        } else {
            $ksCatDetails['ks_category_description'] = $ksCatDetails['ks_category_description'];
        }
        return $ksCatDetails;
    }

    /**
     * Get category image url
     *
     * @param  [string] $ksUrl
     * @return [string]
     */
    public function getKsCategoryImageUrl($ksUrl)
    {
        return $this->ksStoreManager->getStore()->getBaseUrl().$ksUrl;
    }

    /**
     * Get category name with parent
     *
     * @param  [int] $ksCatId
     * @param  [int] $ksStoreId
     * @return [string]
     */
    public function getKsCategoryNameWithParent($ksCatId, $ksStoreId)
    {
        $ksCategoryCollection = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCatId);
        $ksParents = array_map('intval', explode('/', $ksCategoryCollection->getPath()));
        $ksCategoryName ='';
        foreach ($ksParents as $ksParent) {
            // category id not equal to root category
            if ($ksParent!=1 && $ksParent!=$ksCategoryCollection->getId()) {
                $ksCat=$this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksParent);
                $ksCategoryName .= $ksCat->getName() . ' >> ';
            }
        }
        return $ksCategoryName .= $ksCategoryCollection->getName();
    }

    /**
     * Return product id
     *
     * @return array
     */
    public function getKsProductIds($ksSellerId)
    {
        $ksProductId =[];
        $ksProductCollection = $this->ksProductFactory->create()->getCollection()
                ->addFieldToFilter('ks_seller_id', $ksSellerId)
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
    public function getKsBackupProductIds($ksCategoryId,$ksSellerId)
    {
        $ksProductId =[];
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId)->addFieldToFilter('ks_product_id',['in' => $this->getKsProductIds($ksSellerId)]);
        foreach ($ksProductCategoryCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     * Return backup product ids count
     * 
     * @return int
     */
    public function getKsBackupGlobalCount($ksCategoryId)
    {
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId);
        return $ksProductCategoryCollection->getSize();
    }

    /**
     * assign category in products
     *
     * @return void
     */
    public function ksAssignProductInCategory($ksSellerId, $ksCategoryId)
    {
        $ksCategoryProductCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id', $ksCategoryId)->addFieldToFilter('ks_product_id', ['in' => $this->getKsSellerProductIds($ksSellerId) ]);

        if ($ksCategoryProductCollection->getSize() > 0) {
            foreach ($ksCategoryProductCollection as $ksCollection) {
                $ksCol = $this->ksCategoryCollectionFactory->create()->setStoreId(0)->load($ksCategoryId);
                $ksProducts = $ksCol->getProductsPosition();
                $ksProducts[$ksCollection->getKsProductId()] = $ksCollection->getKsPosition();
                $ksCol->setPostedProducts($ksProducts);
                //save category data
                $ksCol->save();
                //delete model data
                $ksCollection->delete();
            }
        }
    }

    /**
     * Return product id
     *
     * @return array
     */
    public function getKsSellerProductIds($ksSellerId)
    {
        $ksProductId =[];
        $ksProductCollection = $this->ksProductFactory->create()->getCollection()
                ->addFieldToFilter('ks_seller_id', $ksSellerId)
                ->addFieldToFilter('ks_parent_product_id', 0);

        foreach ($ksProductCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     *
     * save seller Requested Category unassign
     */
    public function ksUnAssignProductCategory($ksFinalSelectCategoryIds, $ksSellerId = null)
    {
        try {
            $ksAllSellerProductIds = [];
            $ksProductIds = [];
            $ksCategoryIds =[];
            $ksCategoryTableIds = [];
            $ksIndexerProductIds = [];
            $ksIndexerCategoryIds= [];

            $ksSellerProductCollection = $this->ksProductFactory->create()->getCollection();
            if ($ksSellerId) {
                $ksSellerProductCollection->addFieldToFilter('ks_seller_id', $ksSellerId);
            }

            $ksConnection = $this->ksResourceConnection->getConnection();
            $ksCategoryProductTable = $ksSellerProductCollection->getTable('catalog_category_product');
            $ksProductTable = $ksSellerProductCollection->getTable('catalog_product_entity');

            if ($ksSellerProductCollection->getSize()) {
                foreach ($ksSellerProductCollection as $ksProduct) {
                    $ksAllSellerProductIds[] = $ksProduct->getKsProductId();
                }

                $ksSelect = $ksConnection->select()
                ->from($ksCategoryProductTable)
                ->where('product_id IN (?)', $ksAllSellerProductIds)
                ->where('category_id IN (?)', $ksFinalSelectCategoryIds);

                $ksSelect->join(
                    $ksProductTable. ' as cpe',
                    'product_id = cpe.entity_id',
                    array('sku'=>'sku')
                );

                $ksSelectData = $ksConnection->fetchAll($ksSelect);

                if (!empty($ksSelectData)) {
                    $ksCount = 0;
                    foreach ($ksSelectData as $ksData) {
                        $ksCateogryId = $ksData['category_id'];
                        $ksCategoryIds[$ksCateogryId][$ksCount]['ks_product_id'] = $ksData['product_id'];
                        $ksCategoryIds[$ksCateogryId][$ksCount]['ks_position'] = $ksData['position'];
                        $ksCategoryTableIds[] = $ksData['entity_id'];
                        $ksIndexerProductIds[] = $ksData['product_id'];
                        $ksIndexerCategoryIds[] = $ksData['category_id'];
                        $ksCount++;
                        $ksCol = $this->ksCategoryCollectionFactory->create()->setStoreId(0)->load($ksCateogryId);
                        $ksProducts = $ksCol->getProductsPosition();
                        unset($ksProducts[$ksData['product_id']]);
                        $ksCol->setPostedProducts($ksProducts);
                        //save category data
                        $ksCol->save();
                    }
                    //save category and product ids in backup table
                    $this->ksSaveDataBackupTable($ksCategoryIds);
                }
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }

    /**
     * save category and product ids in backup table
     * @param array $ksProductIds
     * @param array $ksFinalSelectCategoryIds
     */
    protected function ksSaveDataBackupTable($ksCategoryIds)
    {
        try {
            foreach ($ksCategoryIds as $ksCategoryId => $ksProductIds) {
                foreach ($ksProductIds as $ksProductId) {
                    $ksCategoryBackupCollection = $this->ksProductCategoryBackupFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('ks_product_id', $ksProductId)
                    ->addFieldToFilter('ks_category_id', $ksCategoryId);

                    if ($ksCategoryBackupCollection->getSize() <= 0) {
                        $ksData = [
                        "ks_category_id" => $ksCategoryId,
                        "ks_product_id" => $ksProductId['ks_product_id'],
                        "ks_position" => $ksProductId['ks_position']
                        ];

                        $ksBackupFactory = $this->ksProductCategoryBackupFactory->create();
                        $ksBackupFactory->addData($ksData)->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }
}
