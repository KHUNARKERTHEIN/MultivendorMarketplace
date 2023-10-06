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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;

/**
 * Class KsShipmentList
 */
class KsShipmentList extends AbstractDataProvider
{
    /**
     * ks_sales_shipment collection
     *
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipment\Collection
     */
    protected $collection;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var FulltextFilter
     */
    protected $ksFulltextFilter;

    /**
     * @var RequestInterface
     */
    protected $ksRequest;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param FulltextFilter $ksFulltextFilter
     * @param RequestInterface $ksRequest
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        KsSellerHelper $ksSellerHelper,
        FulltextFilter $ksFulltextFilter,
        \Magento\Framework\App\RequestInterface $ksRequest,
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
        $this->ksRequest    =  $ksRequest;
        $this->ksFulltextFilter = $ksFulltextFilter;
        $this->collection = $collectionFactory->create();
        $ks_joinTable = $this->collection->getTable('sales_order_grid');
        $ksCustomerTable= $this->collection->getTable('customer_grid_flat');
        $this->collection->getSelect()->join(
            $ksCustomerTable.' as cgf',
            'main_table.ks_seller_id = cgf.entity_id',
            ['ks_seller' => 'cgf.name']
        );
        $this->collection->getSelect()->join(
            $ks_joinTable.' as sog',
            'main_table.ks_order_id = sog.entity_id',
            [
                'entity_id'                        =>'main_table.entity_id',
                'ks_shipment_increment_id'       =>'main_table.ks_shipment_increment_id',
                'status',
                'base_grand_total',
                'subtotal',
                'created_at',
                'grand_total',
                'billing_name',
                'shipping_name',
                'store_id',
                'order_currency_code',
                'base_currency_code'
            ]
        );
    }

    /**
     * Add processing fulltext query
     *
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ('fulltext' == $filter->getField()) {
            $this->ksFulltextFilter->apply($this->collection, $filter);
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $searchKey = $this->ksRequest->getParam('search');
        if($searchKey) {
            return $this->getCollection()->addFieldToFilter(['ks_order_increment_id',
                'ks_shipment_increment_id',
                'shipping_name'],
                [['eq'=>$searchKey],
                ['eq'=>$searchKey],
                ['like'=> '%'.$searchKey.'%']
            ])->load()->toArray();
        }   else    {
            return $this->getCollection()->load()->toArray();
        }
        
    }
}