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
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as ksSellerCategoriesCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as KsCategoryCollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests as KsCategoryRequestsHelper;

/**
 * Backend for serialized array data
 */
class KsSelectCategoryChangeAfter extends \Magento\Framework\App\Config\Value
{
    /**
     * @var KsSellerCategoriesCollection
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var KsCategoryRequestsCollectionFactory
     */
    protected $ksCategoryRequestsCollection;

    /**
     * @var CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * @var KsCategoryCollectionFactory
     */
    protected $ksCategoryCollection;

    /**
     * @var KsCategoryRequestsHelper
     */
    protected $ksCategoryHelper;

    /**
     * @var LoggerInterface
     */
    protected $ksLogger;

    /**
    * @param Context $context,
    * @param Registry $registry
    * @param ScopeConfigInterface $config
    * @param TypeListInterface $cacheTypeList
    * @param KsSellerCategoriesCollection $ksSellerCategoriesCollection
    * @param KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollection
    * @param CategoryFactory $ksCategoryFactory
    * @param KsCategoryCollectionFactory $ksCategoryCollection
    * @param KsCategoryRequestsHelper $ksCategoryHelper
    * @param \Psr\Log\LoggerInterface $ksLogger
    * @param AbstractResource $resource = null
    * @param AbstractDb $resourceCollection = null
    * @param array $data = []
    */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        KsSellerCategoriesCollection $ksSellerCategoriesCollection,
        KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollection,
        CategoryFactory $ksCategoryFactory,
        KsCategoryCollectionFactory $ksCategoryCollection,
        KsCategoryRequestsHelper $ksCategoryHelper,
        \Psr\Log\LoggerInterface $ksLogger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryCollection = $ksCategoryCollection;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksLogger = $ksLogger;
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
            $ksSelectCategories = $this->getValue();
            $ksOldSelectCategories = $this->getOldValue();

            $ksOldSelectCategoryIds = [];

            //call function
            if ($ksOldSelectCategories !=null) {
                $ksOldSelectCategoryIds = explode(",", $ksOldSelectCategories);
            }
            if ($ksSelectCategories !=null) {
                $ksSelectCategoryIds = explode(",", $ksSelectCategories);

                $ksFinalSelectCategoryIds = array_diff($ksOldSelectCategoryIds, $ksSelectCategoryIds);
                $this->ksSelectedCategoryUpdate($ksSelectCategoryIds);

                if (!empty($ksFinalSelectCategoryIds)) {
                    $this->ksSellerCategorySave($ksFinalSelectCategoryIds);
                    $this->KsCategoryRequestsChange($ksFinalSelectCategoryIds);
                    $this->ksCategoryHelper->ksUnAssignProductCategory($ksFinalSelectCategoryIds);
                }
            }
        }
        return parent::afterSave();
    }

    /**
     * Update category include menu
     */
    protected function ksSelectedCategoryUpdate($ksSelectCategoryIds)
    {
        try {
            //get model data
            $ksCategories = $this->ksCategoryCollection->create();

            //for all categories
            foreach ($ksCategories as $ksCategory) {
                if ($ksCategory->getId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                    $ksCategory = $this->ksCategoryFactory->create()->load($ksCategory->getId());
                    //check category id is in array or not
                    if (in_array($ksCategory->getId(), $ksSelectCategoryIds)) {
                        $ksCategory->setKsIncludeInMarketplace(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);
                    } else {
                        $ksCategory->setKsIncludeInMarketplace(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_DISABLED);
                    }
                    $ksCategory->save();
                }
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }

    /**
     * save seller category from configuration
     */
    protected function ksSellerCategorySave($ksFinalSelectCategoryIds)
    {
        try {
            $ksSellerCategories = $this->ksSellerCategoriesCollection->create()
                                ->addFieldToFilter('ks_category_id', array('in'=>$ksFinalSelectCategoryIds));

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
    protected function KsCategoryRequestsChange($ksFinalSelectCategoryIds)
    {
        try {
            $ksCatReqCollection = $this->ksCategoryRequestsCollection->create()
                                ->addFieldToFilter('ks_category_id', array('in'=>$ksFinalSelectCategoryIds));

            foreach ($ksCatReqCollection as $ksCategoryReqData) {
                $ksCategoryReqData->setKsRequestStatus(KsCategoryRequests::KS_STATUS_UNASSIGNED)
                ->save();
            }
        } catch (\Exception $e) {
            $this->ksLogger->critical($e->getMessage());
        }
    }
}