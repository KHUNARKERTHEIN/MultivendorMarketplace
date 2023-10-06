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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsOrdersListing\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsOrdersListing\Collection as KsOrdersListingCollection;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Class Collection
 * Collection for displaying grid of orderlisting
 */
class Collection extends KsOrdersListingCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

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
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $ksResource
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
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
        KsSellerHelper $ksSellerHelper,
        $ksModel = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface  $connection = null,
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
        $this->ksSellerHelper = $ksSellerHelper;
        $this->_init($ksModel, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('status', 'status');
        $this->addFilterToMap('base_grand_total', 'base_grand_total');
        $this->addFilterToMap('subtotal', 'subtotal');
        $this->addFilterToMap('created_at', 'created_at');
        $this->addFilterToMap('grand_total', 'grand_total');
        $this->addFilterToMap('billing_name', 'billing_name');
        $this->addFilterToMap('shipping_name', 'shipping_name');
        $this->addFilterToMap('store_id', 'store_id');

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
        $ksSellerId      =  $this->ksSellerHelper->getKsCustomerId();
        $ksSellerName = $this->ksSellerHelper->getKsSellerName($ksSellerId);
        $ksCustomerTable= $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $ksCustomerTable.' as cgf',
            'main_table.ks_seller_id = cgf.entity_id',
            ['ks_seller' => 'cgf.name']
        );
        $ks_joinTable = $this->getTable('sales_order_grid');
        $this->getSelect()->join(
            $ks_joinTable.' as sog',
            'main_table.ks_order_id = sog.entity_id',
            [
                'entity_id'       =>'main_table.ks_order_id',
                'status',
                'base_grand_total',
                'subtotal',
                'ks_created_at'=>'main_table.ks_created_at',
                'grand_total',
                'billing_name',
                'shipping_name',
                'store_id',
                'billing_address',
                'shipping_address'
            ]
        )->group("main_table.ks_order_id");
        parent::_renderFiltersBefore();
    }
}
