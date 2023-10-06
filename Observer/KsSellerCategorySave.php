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

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * KsSellerCategorySave Observer Class
 */
class KsSellerCategorySave implements ObserverInterface
{
    /**
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
     */
    protected $ksSellerCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $ksrequest;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory
     */
    protected $ksCategoryRequestsCollection;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $ksConfigInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $ksCacheTypeList;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $ksCategoryCollectionFactory;

    /**
     * @var KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection
     * @param \Magento\Framework\App\RequestInterface $ksrequest
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollectionFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection,
        \Magento\Framework\App\RequestInterface $ksrequest,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollectionFactory,
        \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
    ) {
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksConfigInterface = $ksConfigInterface;
        $this->ksrequest = $ksrequest;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
        $this->ksCacheTypeList = $ksCacheTypeList;
        $this->ksCategoryHelper = $ksCategoryHelper;
    }

    /**
     * system config event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //ksField
        $ksField = 'ks_marketplace_catalog/ks_product_categories/ks_select_categories';
        //get category id
        $ksCategoryId = $this->ksrequest->getParam('entity_id');
        //get store id
        $ksStoreId = $this->ksrequest->getParam('store_id');
        //get seller collection
        $ksSellers = $this->ksSellerCollection->create();
        //get include in marketplace status
        $ksIncludeInMarketplace = $this->ksrequest->getParam('ks_include_in_marketplace');
        try {
            //call function
            $this->ksSellerCategorySave($ksCategoryId, $ksStoreId, $ksSellers, $ksIncludeInMarketplace, $ksField);

            //Unassign category from Product
            if ((int) $ksIncludeInMarketplace==0) {
                $this->ksCategoryHelper->ksUnAssignProductCategory(array($ksCategoryId));
            }
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
    }

    /**
     * save seller category data.
     * @param $ksCategoryId
     * @param $ksStoreId
     * @param $ksSellers
     * @param $ksIncludeInMarketplace
     * @param $ksField
     * @return void
     */
    public function ksSellerCategorySave($ksCategoryId, $ksStoreId, $ksSellers, $ksIncludeInMarketplace, $ksField)
    {
        if (!$ksIncludeInMarketplace) {
            $count = 0;
            $ksConfigData = $this->ksScopeConfig->getValue(
                $ksField,
                $scopeType = ScopeInterface::SCOPE_STORE,
                $scopeCode = null
            );
            if($ksConfigData){
                //get category id
                $ksCategoryIds = explode(',',$ksConfigData);
                foreach ($ksCategoryIds as $ksKey => $ksId) {
                    if ($ksId == $ksCategoryId) {
                        //delete data from array
                        unset($ksCategoryIds[$ksKey]);
                        $count=1;
                    }
                }
                if ($count) {
                    //get updated value
                    $ksValue = implode(',', $ksCategoryIds);
                    //save data
                    $this->ksConfigInterface->saveConfig($ksField, $ksValue, 'default', 0);
                    //clean config cache
                    $this->ksCacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                }
            }
            foreach ($ksSellers as $ksSeller) {
                //get seller category data
                $ksSellerCategories = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSeller->getKsSellerId())->addFieldToFilter('ks_category_id', $ksCategoryId);
                //get category request collection
                $ksCatReqCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_seller_id', $ksSeller->getKsSellerId())->addFieldToFilter('ks_category_id', $ksCategoryId);
                //check seller category collection data
                if (count($ksSellerCategories)!=0) {
                    foreach ($ksSellerCategories as $ksSellerCategory) {
                        //delete model data
                        $ksSellerCategory->delete();
                    }
                }
                //check category request collection data
                if (count($ksCatReqCollection)!=0) {
                    foreach ($ksCatReqCollection as $ksCatReq) {
                        $ksCategoryRequests = $this->ksCategoryRequestsFactory->create()->load($ksCatReq->getId());
                        $ksCategoryRequests->setKsRequestStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_UNASSIGNED)->save();
                    }
                }
            }
        } else {
            $this->ksAssignNewCategories($ksCategoryId, $ksStoreId, $ksSellers, $ksIncludeInMarketplace, $ksField);
        }
    }

    /**
     * assign new category .
     * @param $ksCategoryId
     * @param $ksStoreId
     * @param $ksSellers
     * @param $ksIncludeInMarketplace
     * @param $ksField
     * @return void
     */
    public function ksAssignNewCategories($ksCategoryId, $ksStoreId, $ksSellers, $ksIncludeInMarketplace, $ksField)
    {
        $ksCatIds = [];
        $ksConfigData = $this->ksScopeConfig->getValue(
            $ksField,
            $scopeType = ScopeInterface::SCOPE_STORE,
            $scopeCode = null
        );
        if($ksConfigData){
            //get category ids array
            $ksCatIds = explode(',',$ksConfigData);
        }
        if(!in_array($ksCategoryId,$ksCatIds)){
            //add id
            $ksCatIds[] = $ksCategoryId;
        }
        //get updated value
        $ksVal = implode(',', $ksCatIds);
        //save data
        $this->ksConfigInterface->saveConfig($ksField, $ksVal, 'default', 0);
        //clean config cache
        $this->ksCacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }
}