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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as KsCategoryCollectionFactory;
use Magento\Catalog\Ui\Component\Product\Form\Categories\Options as KsCategoryOption;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsCategory block class
 */
class KsCategory extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $ksSession;

    /**
     * @var KsCategoryCollectionFactory
     */
    protected $ksCategoryCollection;

    /**
     *
     * @var KsCategoryRequests
     */
    protected $ksCategoryRequests;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsCategoryOption
     */
    protected $ksCategoryOption;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsCategoryOption $ksCategoryOption
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param KsCategoryCollectionFactory $ksCategoryCollection
     * @param KsCategoryRequests $ksCategoryRequests
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsCategoryOption $ksCategoryOption,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection,
        \Magento\Customer\Model\SessionFactory $ksSession,
        KsCategoryCollectionFactory $ksCategoryCollection,
        KsCategoryRequests $ksCategoryRequests,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        array $ksData = []
    ) {
        $this->ksCategoryOption           = $ksCategoryOption;
        $this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
        $this->ksSession                  = $ksSession;
        $this->ksCategoryCollection       = $ksCategoryCollection;
        $this->ksCategoryRequests         = $ksCategoryRequests;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Return category tree
     * @return json
     */
    public function getKsCategoriesTree()
    {
        if (!empty($this->getKsSellerAllowedProdCategories())) {
            $ksCategories = $this->getKsSellerCategoriesTree();
            return json_encode($ksCategories);
        } else {
            return json_encode(array());
        }
    }

    /**
     * @return array
     */
    protected function getKsSellerAllowedProdCategories()
    {
        $ksSellerCollection = $this->ksSellerCategoryCollection->create()
            ->addFieldToFilter('ks_seller_id', $this->ksSession->create()->getId())
            ->addFieldToFilter('ks_category_status', 1);

        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$this->getKsProduct()->getId());    
        $ksCategory = [];
        foreach ($ksSellerCollection as $ksItem) {
            if ($ksItem->getId()) {
                $ksCategory[] = $ksItem->getKsCategoryId();
            }
        }
        foreach ($ksProductCategoryCollection as $ksItem) {
            $ksCollection = $this->ksSellerCategoryCollection->create()
            ->addFieldToFilter('ks_seller_id', $this->ksSession->create()->getId())
            ->addFieldToFilter('ks_category_id',$ksItem->getKsCategoryId());
            if (!in_array($ksItem->getKsCategoryId(),$ksCategory) && $ksCollection->getSize() > 0) {
                $ksCategory[] = $ksItem->getKsCategoryId();
            }
        }
        return $ksCategory;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getKsSellerCategoriesTree()
    {
        /* @var $ksMatchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $ksMatchingNamesCollection = $this->ksCategoryCollection->create();

        $ksMatchingNamesCollection->addAttributeToSelect('path');

        if (!empty($this->getKsSellerAllowedProdCategories())) {
            $ksMatchingNamesCollection->addAttributeToFilter('entity_id', ['in' => $this->getKsSellerAllowedProdCategories()]);
        }

        $ksShownCategoriesIds = [];

        /** @var \Magento\Catalog\Model\Category $ksCategory */
        foreach ($ksMatchingNamesCollection as $ksCategory) {
            foreach (explode('/', $ksCategory->getPath()) as $ksParentId) {
                $ksShownCategoriesIds[$ksParentId] = 1;
            }
        }

        /* @var $ksCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $ksCollection = $this->ksCategoryCollection->create();

        $ksCollection->addAttributeToFilter('entity_id', ['in' => array_keys($ksShownCategoriesIds)])
        ->addAttributeToSelect(['name', 'is_active', 'parent_id']);

        $ksCategoryById = [
        CategoryModel::TREE_ROOT_ID => [
            'value' => CategoryModel::TREE_ROOT_ID
        ],
        ];

        foreach ($ksCollection as $ksCategory) {
            foreach ([$ksCategory->getId(), $ksCategory->getParentId()] as $ksCategoryId) {
                if (!isset($ksCategoryById[$ksCategoryId])) {
                    $ksCategoryById[$ksCategoryId] = ['value' => $ksCategoryId];
                }
            }
            // condition for assigned categories to seller
            if (in_array($ksCategory->getId(), $this->getKsSellerAllowedProdCategories())) {
                $ksCategoryById[$ksCategory->getId()]['show_checkbox'] = true;
            } else {
                $ksCategoryById[$ksCategory->getId()]['show_checkbox'] = false;
            }
            //$ksCategoryById[$ksCategory->getId()]['show_checkbox'] = true;
            $ksCategoryById[$ksCategory->getId()]['is_active'] = $ksCategory->getIsActive();
            $ksCategoryById[$ksCategory->getId()]['label'] = $ksCategory->getName();
            $ksCategoryById[$ksCategory->getParentId()]['optgroup'][] = &$ksCategoryById[$ksCategory->getId()];
        }

        $this->ksCategoriesTree = $ksCategoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];

        return $this->ksCategoriesTree;
    }

    /**
     * Return category value
     * @return json
     */
    public function getKsCategoryValue()
    {
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$this->getKsProduct()->getId());

        $ksArray = $this->getKsProduct()->getCategoryIds();
        foreach ($ksProductCategoryCollection as $ksItem) {
            $ksCollection = $this->ksSellerCategoryCollection->create()
            ->addFieldToFilter('ks_seller_id', $this->ksSession->create()->getId())
            ->addFieldToFilter('ks_category_id',$ksItem->getKsCategoryId());
            if (!in_array($ksItem->getKsCategoryId(),$ksArray) && $ksCollection->getSize() > 0) {
                $ksArray[] = $ksItem->getKsCategoryId();
            }
        }
        return json_encode($ksArray);
    }
}