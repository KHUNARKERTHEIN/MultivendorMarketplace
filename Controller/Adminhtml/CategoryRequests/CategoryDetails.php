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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CategoryRequests;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\CategoryFactory as KsCategoryCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoriesCollection;
use Magento\Framework\Controller\Result\JsonFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollection;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;

/**
 * class CategoryDetails
 */
class CategoryDetails extends Action
{
    /**
     * @var KsCategoryCollection
     */
    protected $ksCategoryCollectionFactory;

    /**
     * @var JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var KsSellerCategoriesCollection
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var KsCategoryRequestsCollection
     */
    protected $ksCategoryRequestsCollection;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
      * @var DataPersistorInterface
      */
    protected $ksDataPersistor;

    /**
      * @var KsDataHelper
      */
    protected $ksDataHelper;

    /**
     * @var KsProductCollection
     */
    protected $ksCatalogProductFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @param Context $ksContext
     * @param KsCategoryCollection $ksCategoryCollectionFactory
     * @param KsSellerCategoriesCollection $ksSellerCategoriesCollection
     * @param KsCategoryRequestsCollection $ksCategoryRequestsCollection
     * @param Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param JsonFactory $ksResultJsonFactory
     * @param KsDataHelper $ksDataHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param KsProductCollection $ksCatalogProductFactory
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        KsCategoryCollection $ksCategoryCollectionFactory,
        KsSellerCategoriesCollection $ksSellerCategoriesCollection,
        KsCategoryRequestsCollection $ksCategoryRequestsCollection,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        JsonFactory $ksResultJsonFactory,
        KsDataHelper $ksDataHelper,
        DataPersistorInterface $ksDataPersistor,
        KsProductCollection $ksCatalogProductFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
    ) {
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        //get category id
        $ksCategoryId = $this->getRequest()->getPost('ks_category_id');
        //get seller id
        $ksSellerId = (int)$this->getRequest()->getPost('ks_seller_id');
        //get store id
        $ksStoreId = (int)$this->getRequest()->getPost('ks_store_id');
        // set seller id in datapersistor
        $this->ksDataPersistor->set('ks_current_admin_category_id', $ksCategoryId);
        //intialize variable
        $ksRequestStatus = '';
        //get collection data
        $ksSellerCategory = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_category_id', $ksCategoryId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        //get collection data
        $ksItem = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCategoryId)->addFieldToFilter('ks_seller_id', $ksSellerId);
        //check count
        if (count($ksItem) != 0) {
            $ksCategoryRequest = $ksItem->getFirstItem();
            if ($ksCategoryRequest->getKsRequestStatus() == 0) {
                $ksRequestStatus = '<span class="ks-action ks-primary ks-req-pending font-weight-light">Pending</span>';
            } elseif ($ksCategoryRequest->getKsRequestStatus() == 2) {
                $ksRequestStatus = '<span class="ks-action ks-primary ks-req-rejected font-weight-light">Rejected</span>';
            }
        }
        //get model data
        $ksCategoryCollection = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCategoryId);
        
        //get product collection from category
        $ksProductCollection = $this->ksCatalogProductFactory->create()->addAttributeToSelect('*');
        $ksProductCollection->addCategoriesFilter(['eq' => $ksCategoryId]);
        //get global product count 
        $ksGlobalProductCount = $ksProductCollection->getSize();   
        //get seller product count
        $ksSellerProductCount =  $ksProductCollection->addFieldToFilter('entity_id', ['in' => $this->getKsProductIds()])->getSize();
        //update count from backup tables
        $ksCount = count($this->getKsBackupProductIds($ksCategoryId));
        $ksGlobalProductCount += $this->getKsBackupGlobalCount($ksCategoryId);
        $ksSellerProductCount += $ksCount; 

        $ksImageUrl = "";
        if($ksCategoryCollection->getImageUrl()){
            //set image url
            $ksImageArray = explode('/',$ksCategoryCollection->getImageUrl());
            if($ksImageArray[1] != 'media'){
                unset($ksImageArray[1]);
            }
            unset($ksImageArray[0]);
            $ksImageUrl = implode('/',$ksImageArray);
        }
        //init array
        $ksCategoryDetails=[
          "ks_category_name" => $ksCategoryCollection->getName(),
          "ks_category_image" => $ksImageUrl,
          "ks_category_description" => $ksCategoryCollection->getDescription(),
          "ks_category_product_count" => $ksGlobalProductCount,
          "ks_seller_product_count" => $ksSellerProductCount,
          "ks_display_mode" => $ksCategoryCollection->getDisplayMode(),
          "ks_is_anchor" => $ksCategoryCollection->getIsAnchor(),
          "ks_default_sort_by" => $ksCategoryCollection->getDefaultSortBy(),
          "ks_available_sort_by" => $ksCategoryCollection->getAvailableSortBy(),
          "ks_filter_price_range" => $ksCategoryCollection->getFilterPriceRange(),
          "ks_currency_symbol" => $this->ksDataHelper->ksGetCurrencyAccordingToStore($ksStoreId) ? $this->ksDataHelper->ksGetCurrencyAccordingToStore($ksStoreId) : "",
          "ks_request_status" => $ksRequestStatus,
          "ks_category_status" => $ksSellerCategory->getKsCategoryStatus()
        ];
        //create resultjson factory
        $result = $this->ksResultJsonFactory->create();
        $result->setData($ksCategoryDetails);
        //return json data
        return $result;
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
                            ->addFieldToFilter('ks_seller_id', (int)$this->getRequest()->getPost('ks_seller_id'))
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
     * Return backup product ids count
     * 
     * @return int
     */
    public function getKsBackupGlobalCount($ksCategoryId)
    {
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId);
        return $ksProductCategoryCollection->getSize();
    }
}
