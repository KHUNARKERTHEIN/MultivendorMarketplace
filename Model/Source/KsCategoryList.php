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

namespace Ksolves\MultivendorMarketplace\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class KsCategoryList
 */
class KsCategoryList implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $ksCategoryCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $ksCategoryCollectionFactory
    ) {
        $this->ksCategoryFactory = $ksCategoryFactory;
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
    }

    /**
     * @param $isActive
     * @param $level
     * @param $sortBy
     * @param $pageSize
     * @return array
     */
    public function getKsCategoryCollection($isActive = false, $level = false, $sortBy = false, $pageSize = false)
    {
        $ksCollection = $this->ksCategoryCollectionFactory->create()->setStoreId(0);
        $ksCollection->addAttributeToSelect('*');
        // select only active categories
        if ($isActive) {
            $ksCollection->addIsActiveFilter();
        }
        // select categories of certain level
        if ($level) {
            $ksCollection->addLevelFilter($level);
        }
        // sort categories by some value
        if ($sortBy) {
            $ksCollection->addOrderField($sortBy);
        }
        // select certain number of categories
        if ($pageSize) {
            $ksCollection->setPageSize($pageSize);
        }
        return $ksCollection;
    }
    
    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];
        
        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }
    
    /**
     * To array
     *
     * @return array
     */
    private function _toArray()
    {
        $ksCategories = $this->getKsCategoryCollection(false, false, false, false);
        $ksCatagoryList = [];
        foreach ($ksCategories as $ksCategory) {
            //not include root catalog category
            if ($ksCategory->getId() != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $ksCatagoryList[$ksCategory->getEntityId()] = __($this->getKsParentName($ksCategory->getPath()) . $ksCategory->getName());
            }
        }
        return $ksCatagoryList;
    }

    /**
     * Get Parent Name
     *
     * @return varchar
     */
    private function getKsParentName($path = '')
    {
        $ksParentName = '';
        //make static variable for root and default category
        $ksRootCats = [\Magento\Catalog\Model\Category::TREE_ROOT_ID];
        $ksCatTree = explode("/", $path);
        // Deleting category itself
        array_pop($ksCatTree);
        
        if ($ksCatTree && (count($ksCatTree) > count($ksRootCats))) {
            foreach ($ksCatTree as $ksCatId) {
                if (!in_array($ksCatId, $ksRootCats)) {
                    //for all store views
                    $ksCategory = $this->ksCategoryFactory->create()->setStoreId(0)->load($ksCatId);
                    $ksCategoryName = $ksCategory->getName();
                    $ksParentName .= $ksCategoryName . ' >> ';
                }
            }
        }
        return $ksParentName;
    }
}
