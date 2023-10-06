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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\Collection as KsCategoryRequestsAllowedCollection;

/**
 * Class Collection
 * Collection for displaying grid of category requests allowed
 */
class Collection extends KsCategoryRequestsAllowedCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

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
     * @param mixed $ksResourceModel
     * @param string $ksModel
     * @param null $connection
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
        $ksResourceModel,
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
        $this->_init($ksModel, $ksResourceModel);
        $this->setMainTable($mainTable);
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
         $joinTable = $this->getTable('customer_grid_flat');
         $this->getSelect()->join(
             $joinTable.' as ks_cgf',
             'main_table.ks_seller_id = ks_cgf.entity_id',
             [
               'ks_seller_name'=>'name',
             ]
         );
        parent::_renderFiltersBefore();
    }
}
