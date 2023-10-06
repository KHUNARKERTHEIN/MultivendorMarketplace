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

namespace Ksolves\MultivendorMarketplace\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Ksolves\MultivendorMarketplace\Model\KsCategoryRequests;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as KsCategoryCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as ksSellerCategoriesCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests as KsCategoryRequestsHelper;

/**
 * Backend for serialized array data
 */
class KsUpdateCategory extends \Magento\Framework\App\Config\Value
{
    /**
   * @var ScopeConfigInterface
   */
    protected $ksScopeConfig;

    /**
     * @var CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * @var KsCategoryCollectionFactory
     */
    protected $ksCategoryCollection;

    /**
     * @var KsSellerCategoriesCollection
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var KsCategoryRequestsCollectionFactory
     */
    protected $ksCategoryRequestsCollection;

    /**
     * @var KsCategoryRequestsHelper
     */
    protected $ksCategoryHelper;

    /**
     * @var LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $ksConfigInterface;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $ksCacheTypeList;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param ResourceConnection $ksResourceConnection
    * @param ScopeConfigInterface $ksScopeConfig
    * @param CategoryFactory $ksCategoryFactory
    * @param KsCategoryCollectionFactory $ksCategoryCollection
    * @param KsSellerCategoriesCollection $ksSellerCategoriesCollection
    * @param KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollection
    * @param KsCategoryRequestsHelper $ksCategoryHelper
    * @param \Psr\Log\LoggerInterface $ksLogger
    * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface
    * @param \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList
    * @param AbstractResource $resource = null
    * @param AbstractDb $resourceCollection = null
    * @param array $data = []
    */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ScopeConfigInterface $ksScopeConfig,
        CategoryFactory $ksCategoryFactory,
        KsCategoryCollectionFactory $ksCategoryCollection,
        KsSellerCategoriesCollection $ksSellerCategoriesCollection,
        KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollection,
        KsCategoryRequestsHelper $ksCategoryHelper,
        \Psr\Log\LoggerInterface $ksLogger,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $ksConfigInterface,
        \Magento\Framework\App\Cache\TypeListInterface $ksCacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryCollection = $ksCategoryCollection;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksLogger = $ksLogger;
        $this->ksConfigInterface = $ksConfigInterface;
        $this->ksCacheTypeList = $ksCacheTypeList;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $ksAllowedCategories = $this->getValue();
            $ksSelectCategoryIds = $this->ksScopeConfig->getValue('ks_marketplace_catalog/ks_product_categories/ks_select_categories');

            if ((int) $ksAllowedCategories !=1) {
                $this->ksAllCategoryUpdate();
            } else {
                if ($ksSelectCategoryIds !=null) {
                    $ksSelectCategoryIds = explode(",", $ksSelectCategoryIds);
                    $this->ksSelectedCategoryUpdate($ksSelectCategoryIds);
                    $this->ksSellerCategorySave($ksSelectCategoryIds);
                    $this->ksCategoryRequestsChange($ksSelectCategoryIds);
                }
            }
        }
        return parent::afterSave();
    }

    /**
     * Update All Category Include Menu
     */
    protected function ksAllCategoryUpdate()
    {
        try {
            //get model data
            $ksCategories = $this->ksCategoryCollection->create();
            $ksCategoryIds = [];
            //for all categories
            foreach ($ksCategories as $ksCategory) {
                if ($ksCategory->getId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    $ksCategoryIds[] = $ksCategory->getId();
                    $ksCategory = $this->ksCategoryFactory->create()->load($ksCategory->getId());
                    $ksCategory->setKsIncludeInMarketplace(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);
                    $ksCategory->save();
                }
            }
            //ksField
            $ksField = 'ks_marketplace_catalog/ks_product_categories/ks_select_categories';
            //get updated value
            $ksValue = implode(',', $ksCategoryIds);
            //save data
            $this->ksConfigInterface->saveConfig($ksField, $ksValue, 'default', 0);
            //clean config cache
            $this->ksCacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }

    /**
     * Update category include menu
     */
    protected function ksSelectedCategoryUpdate($ksSelectCategoryIds)
    {
        try {
            //get model data
            $ksCategories = $this->ksCategoryCollection->create();
            $ksUnAssignCategoryIds = [];

            //for all categories
            foreach ($ksCategories as $ksCategory) {
                if ($ksCategory->getId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    $ksCategory = $this->ksCategoryFactory->create()->load($ksCategory->getId());
                    //check category id is in array or not
                    if (in_array($ksCategory->getId(), $ksSelectCategoryIds)) {
                        $ksCategory->setKsIncludeInMarketplace(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);
                    } else {
                        $ksCategory->setKsIncludeInMarketplace(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_DISABLED);
                        $ksUnAssignCategoryIds[] = $ksCategory->getId();
                    }
                    $ksCategory->save();
                }
            }

            if (!empty($ksUnAssignCategoryIds)) {
                $this->ksCategoryHelper->ksUnAssignProductCategory($ksUnAssignCategoryIds);
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }

    /**
     * save seller category from configuration
     */
    protected function ksSellerCategorySave($ksSelectCategoryIds)
    {
        try {
            $ksSellerCategories = $this->ksSellerCategoriesCollection->create()
                                ->addFieldToFilter('ks_category_id', array('nin'=>$ksSelectCategoryIds));

            foreach ($ksSellerCategories as $ksSellerCategory) {
                // delete model data
                $ksSellerCategory->delete();
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }

    /**
     * save seller Requested Category unassign
     */
    protected function ksCategoryRequestsChange($ksSelectCategoryIds)
    {
        try {
            $ksCatReqCollection = $this->ksCategoryRequestsCollection->create()
                                ->addFieldToFilter('ks_category_id', array('nin'=>$ksSelectCategoryIds));

            foreach ($ksCatReqCollection as $ksCategoryReqData) {
                $ksCategoryReqData->setKsRequestStatus(KsCategoryRequests::KS_STATUS_UNASSIGNED)
                ->save();
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }
}