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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsVisitor\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Customer\Model\Visitor;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;

/**
 * Class KsCustomerOnline
 */
class KsCustomerOnline extends AbstractDataProvider
{
    /**
     * ks_sales_order collection
     *
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\Collection
     */
    protected $collection;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var Visitor
     */
    protected $visitorModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    protected $ksFulltextFilter;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param Visitor $visitorModel
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        KsSellerHelper $ksSellerHelper,
        Visitor $visitorModel,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        FulltextFilter $ksFulltextFilter,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->ksFulltextFilter = $ksFulltextFilter;
        $this->date = $date;
        $this->collection = $collectionFactory->create();
        $this->visitorModel = $visitorModel;
        $this->resource =$resource;
        $connection = $this->resource->getConnection();
        $lastDate = $this->date->gmtTimestamp() - $this->visitorModel->getOnlineInterval() * self::SECONDS_IN_MINUTE;
        $ks_sellerId      =  $ksSellerHelper->getKsCustomerId();

        $ks_joinTable = $this->collection->getTable('sales_order');
        $this->collection->getSelect()->join(
            $ks_joinTable.' as so',
            'main_table.customer_id = so.customer_id'
        );
        $ksSalesTable = $this->collection->getTable('ks_sales_order');
        $this->collection->getSelect()->join(
            $ksSalesTable.' as ks_sog',
            'so.entity_id = ks_sog.ks_order_id'
        )->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('main_table.*')->group('main_table.visitor_id')->where("ks_sog.ks_seller_id = '$ks_sellerId' AND main_table.customer_id != '$ks_sellerId'");
        $ksJoinCustomerEntity=$this->collection->getTable('customer_entity');
        $this->collection->getSelect()->join(
            $ksJoinCustomerEntity.' as ce',
            'main_table.customer_id= ce.entity_id',
            ['email','lastname','firstname']
        )->where(
            'main_table.last_visit_at >= ?',
            $connection->formatDate($lastDate)
        );
        $expression = $connection->getCheckSql(
            'main_table.customer_id IS NOT NULL AND main_table.customer_id != 0',
            $connection->quote(Visitor::VISITOR_TYPE_CUSTOMER),
            $connection->quote(Visitor::VISITOR_TYPE_VISITOR)
        );
        $this->collection->getSelect()->columns(['visitor_type' => $expression]);
    }
    
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ('fulltext' == $filter->getField()) {
            $this->ksFulltextFilter->apply($this->collection, $filter);
        } else {
            parent::addFilter($filter);
        }
    }
}
