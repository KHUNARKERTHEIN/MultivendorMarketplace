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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule\Ajax;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Api\StoreWebsiteRelationInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as ksCategoryCollectionFactory;

/**
 * AnalyserData class
 */
class AnalyserData extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $ksCategoryCollectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $ksObjectManager;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $ksStoreWebsiteRelation;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    public $ksWebsiteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     */
    public $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCommissionRule\Source\KsSellerList
     */
    protected $ksSellerList;

    /**
     * @var website id
     */
    protected $ksWebsiteId;

    /**
     * @var product collection
     */
    protected $ksProductCollectionFactory;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Magento\Catalog\Ui\Component\Product\Form\Categories\Options
     */
    protected $ksCategoryOptions;

    /**
     * @var StoreWebsiteRelationInterface
     */
    protected $storeWebsiteRelation;

    /**
     * Constructor
     *
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param FormFactory $ksFormFactory
     * @param \Magento\Framework\ObjectManagerInterface $ksObjectManager
     * @param \Magento\Store\Model\WebsiteFactory $ksWebsiteFactory
     * @param ksCategoryCollectionFactory $ksCategoryCollectionFactory,
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRule\Source\KsSellerList $ksSellerList
     * @param \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $ksCategoryOptions
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param DataPersistorInterface $ksDataPersistor
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory
     * @param StoreWebsiteRelationInterface $ksStoreWebsiteRelation
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\Data\FormFactory $ksFormFactory,
        \Magento\Framework\ObjectManagerInterface $ksObjectManager,
        \Magento\Store\Model\WebsiteFactory $ksWebsiteFactory,
        ksCategoryCollectionFactory $ksCategoryCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\Source\CommissionRule\KsSellerList $ksSellerList,
        \Magento\Catalog\Ui\Component\Product\Form\Categories\Options $ksCategoryOptions,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory,
        StoreWebsiteRelationInterface $ksStoreWebsiteRelation
    ) {
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksObjectManager = $ksObjectManager;
        $this->storeWebsiteRelation = $ksStoreWebsiteRelation;
        $this->ksWebsiteFactory = $ksWebsiteFactory;
        $this->ksSellerList = $ksSellerList;
        $this->ksCategoryOptions = $ksCategoryOptions;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        $ksWebsiteId = $this->getRequest()->getParam('ksWebsiteId');
        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $this->ksGetSellerCategoriesTree($ksWebsiteId),
        ]);
        return $ksResponse;
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function ksGetSellerCategoriesTree($ksWebsiteId)
    {
        $matchingNamesCollection = $this->ksCategoryCollectionFactory->create();
        $matchingNamesCollection->addAttributeToSelect('path')
        ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
        ->setStore($this->ksStoreManager->getStore());

        $ksWebsite = $this->ksDataPersistor->get('ksItem');
        $ksRootCategoryIds = [];
        $ksAllRootIds = [];
        $ksAbsentRootIds = [];
        $ksStoreIds = '';

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->ksCategoryCollectionFactory->create();

        foreach ($collection as $record) {
            if ($record->getData('level') == 1) {
                array_push($ksAllRootIds, $record->getId());
            }
        }

        if ($ksWebsiteId != null) {
            $ksStoreIds = $this->storeWebsiteRelation->getStoreByWebsiteId($ksWebsiteId);
            foreach ($ksStoreIds as $ksStore) {
                $ksRootCategoryIds[] = $this->ksStoreManager->getStore($ksStore)->getRootCategoryId();
            }
            $ksAbsentRootIds = array_diff($ksAllRootIds, $ksRootCategoryIds);
        }
        $shownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->ksCategoryCollectionFactory->create();
        $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
        ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
        ->setStore($this->ksStoreManager->getStore());

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['level'] = $category->getLevel();
            $categoryById[$category->getId()]['label'] = $category->getName();
            if (!in_array($category->getId(), $ksAbsentRootIds)) {
                $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
            }
        }
        if (!isset($categoryById[1]['optgroup'])) {
            $categoryById[1]['optgroup'] = [];
        }
        $this->categoriesTree = $categoryById[1]['optgroup'];
        return $this->categoriesTree;
    }

    /**
     * Retrieve Products
     *
     * @return array
     */
    public function ksGetSellerProducts()
    {
        $ksCollection = $this->ksProductCollectionFactory->addAttributeToSelect('*');
        return $ksCollection;
    }

    /**
     * Retrieve Products
     *
     * @return array
     */
    public function ksGetSeller()
    {
        return $this->ksSellerList;
    }
}
