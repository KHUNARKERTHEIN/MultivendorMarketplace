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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\Grid;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\Collection as KsProductCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;

/**
 * Class PriceComparisonProductCollection
 * @package Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\Grid
 */
class PriceComparisonProductCollection extends KsProductCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    protected $_eventPrefix;
    protected $_eventObject;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $ksEntityFactoryInterface
     * @param \Psr\Log\LoggerInterface $ksLoggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $ksFetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface $ksEventManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManagerInterface
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $ksModel
     * @param null $connection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $ksResource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $ksEntityFactoryInterface,
        \Psr\Log\LoggerInterface $ksLoggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $ksFetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $ksEventManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        $ksModel = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $ksResource = null
    ) {
        parent::__construct(
            $ksEntityFactoryInterface,
            $ksLoggerInterface,
            $ksFetchStrategyInterface,
            $ksEventManagerInterface,
            $ksStoreManagerInterface,
            $connection,
            $ksResource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_eavAttribute = $eavAttribute;
        $this->_init($ksModel, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        $this->addFilterToMap("ks_product_name", "ks_cpev.value");
        $this->addFilterToMap("ks_product_price", "ks_cped.value");
        $this->addFilterToMap("ks_qty", "ks_csi.qty");
        $this->addFilterToMap("entity_id", "ks_cpe.entity_id");
        $this->addFilterToMap("ks_type_id", "ks_cpe.type_id");
        $this->addFilterToMap("ks_sku", "ks_cpe.sku");
        $this->addFilterToMap("thumbnail", "ks_cpemg.value");

        parent::_initSelect();
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $ksProductNameAttrId = $this->_eavAttribute->getIdByCode("catalog_product", "name");
        $ksProStatusAttrId = $this->_eavAttribute->getIdByCode("catalog_product", "status");
        $ksProPriceAttrId = $this->_eavAttribute->getIdByCode("catalog_product", "price");

        /* product data */
        $this->getSelect()->join(
            'catalog_product_entity as ks_cpe',
            'main_table.ks_product_id = ks_cpe.entity_id',
            ["entity_id" => "entity_id", "ks_type_id" => "type_id","ks_sku" => "sku"]
        );

        /* product name */
        $this->getSelect()->join(
            'catalog_product_entity_varchar as ks_cpev',
            'main_table.ks_product_id = ks_cpev.entity_id',
            ["ks_product_name" => "value", "ks_mage_store_id" => "store_id"]
        )->where("ks_cpev.store_id = 0 AND ks_cpev.attribute_id = " . $ksProductNameAttrId);

        /* product status */
        $this->getSelect()->join(
            'catalog_product_entity_int as ks_cpei',
            'main_table.ks_product_id = ks_cpei.entity_id',
            ["ks_product_status" => "value"]
        )->where("ks_cpei.store_id = 0 AND ks_cpei.attribute_id = " . $ksProStatusAttrId);

        /* product price */
        $this->getSelect()->join(
            'catalog_product_entity_decimal as ks_cped',
            'main_table.ks_product_id = ks_cped.entity_id and ks_cped.store_id = 0 AND ks_cped.attribute_id = ' . $ksProPriceAttrId,
            ["ks_product_price" => "value"]
        );

        /* product qty */
        $this->getSelect()->join(
            'cataloginventory_stock_item as ks_csi',
            'main_table.ks_product_id = ks_csi.product_id',
            ["ks_qty" => "qty"]
        )->where("ks_csi.website_id = 0 OR ks_csi.website_id = 1");

        $this->getSelect()->where("ks_parent_product_id !=0");

        parent::_renderFiltersBefore();
    }
}
