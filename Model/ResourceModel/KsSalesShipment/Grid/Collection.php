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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment\Collection as KsSalesShipmentCollection;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Class Collection
 * Collection for displaying grid of shipment
 */
class Collection extends KsSalesShipmentCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    protected $session;

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
     * @param KsSellerHelper $ksSellerHelper
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
        \Magento\Framework\Session\SessionManagerInterface $session,
        $ksModel = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $ksResource = null
    ) {
        parent::__construct(
            $ksEntityFactoryInterface,
            $ksLoggerInterface,
            $ksFetchStrategyInterface,
            $ksEventManagerInterface,
            $connection,
            $ksResource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->session = $session;
        $this->_init($ksModel, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @inheritdoc
     */
   

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
        $ksJoinTable = $this->getTable('sales_order');
        $order_id = $this->session->getOrderId();
        if ($order_id) {
            $this->getSelect()->join(
                $ksJoinTable.' as ks_so',
                'main_table.ks_order_id = ks_so.entity_id',
                ['increment_id'=>'increment_id']
            )->where("main_table.ks_order_id = '$order_id'");
        } else {
            $this->getSelect()->join(
                $ksJoinTable.' as ks_so',
                'main_table.ks_order_id = ks_so.entity_id',
                ['increment_id'=>'increment_id']
            );
        }

        $ksSalesTable = $this->getTable('sales_order_grid');
        $ks_sellerId      =  $this->ksSellerHelper->getKsCustomerId();
        if ($ks_sellerId) {
            $this->getSelect()->join(
                $ksSalesTable.' as sog',
                'main_table.ks_order_id = sog.entity_id',
                [
                    'shipping_name',
                    'created_at' => 'created_at',
                    'ks_order_status'     => 'status',
                    'ks_billing_address' => 'billing_address',
                    'ks_shipping_address' => 'shipping_address',
                    'ks_customer_name' => 'customer_name',
                    'ks_customer_email' => 'customer_email',
                    'ks_payment_method' => 'payment_method',
                    'ks_shipping_information' => 'shipping_information',
                    'ks_shipping_and_handling' => 'shipping_and_handling',
                    'store_id'
                ]
            )->where("main_table.ks_seller_id = '$ks_sellerId'");
        } else {
            $this->getSelect()->join(
                $ksSalesTable.' as sog',
                'main_table.ks_order_id = sog.entity_id',
                [
                    'ks_billing_name' => 'billing_name',
                    'ks_shipping_name' => 'shipping_name',
                    'ks_order_increment_id' => 'increment_id',
                    'ks_order_created_at' => 'created_at',
                    'ks_order_status'     => 'status',
                    'ks_billing_address' => 'billing_address',
                    'ks_shipping_address' => 'shipping_address',
                    'ks_customer_name' => 'customer_name',
                    'ks_customer_email' => 'customer_email',
                    'ks_payment_method' => 'payment_method',
                    'ks_shipping_information' => 'shipping_information',
                    'ks_shipping_and_handling' => 'shipping_and_handling',
                    'store_id'
                ]
            );
        }
        parent::_renderFiltersBefore();
    }
}
