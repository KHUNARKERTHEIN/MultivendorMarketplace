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
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;

/**
 * Class KsOrderList
 */
class KsOrderList extends AbstractDataProvider
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
        $this->ksRequest = $ksRequest;
        $this->ksFulltextFilter = $ksFulltextFilter;
        $this->collection = $collectionFactory->create();
        $ks_sellerId = $ksSellerHelper->getKsCustomerId();
        $ks_joinTable = $this->collection->getTable('sales_order_grid');
        $ksCustomerTable = $this->collection->getTable('customer_grid_flat');
        $this->collection->getSelect()->join(
            $ksCustomerTable.' as cgf',
            'main_table.ks_seller_id = cgf.entity_id',
            ['ks_seller' => 'cgf.name']
        );
        $this->collection->getSelect()->join(
            $ks_joinTable.' as sog',
            'main_table.ks_order_id = sog.entity_id',
            [
                'entity_id'       =>'main_table.id',
                'status',
                'base_grand_total' =>'sog.base_grand_total',
                'subtotal',
                'ks_order_date'=>'sog.created_at',
                'grand_total' => 'sog.grand_total',
                'billing_name',
                'shipping_name',
                'store_id',
                'billing_address',
                'shipping_address',
                'order_currency_code',
                'base_currency_code'
            ]
        );
        $this->collection->getSelect()->group('sog.entity_id')->limit(5);
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
        $ksAppliedFilters = $this->ksRequest->getParam('filters');
        $this->getCollection()->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
        
        if (isset($ksAppliedFilters['ks_order_date'])) {
            if (isset($ksAppliedFilters['ks_order_date']['from'])) {
                $ksDate = new \DateTime($ksAppliedFilters['ks_order_date']['from']);
                $ksDate->setTime(00, 00, 00);
                $this->getCollection()->addFieldToFilter(
                    'sog.created_at',
                    ['gteq' => $ksDate]
                );
            }

            if (isset($ksAppliedFilters['ks_order_date']['to'])) {
                $ksDate = new \DateTime($ksAppliedFilters['ks_order_date']['to']);
                $ksDate->setTime(23, 59, 59);
                $this->getCollection()->addFieldToFilter('sog.created_at', ['lteq' => $ksDate]);
            }
        }

        if (isset($ksAppliedFilters['ks_order_increment_id'])) {
            if (isset($ksAppliedFilters['ks_order_increment_id']['from'])) {
                $this->getCollection()->addFieldToFilter(
                    'ks_order_increment_id',
                    ['gteq' => $ksAppliedFilters['ks_order_increment_id']['from']]
                );
            }

            if (isset($ksAppliedFilters['ks_order_increment_id']['to'])) {
                $this->getCollection()->addFieldToFilter(
                    'ks_order_increment_id',
                    ['lteq' => $ksAppliedFilters['ks_order_increment_id']['to']]
                );
            }
        }

        if (isset($ksAppliedFilters['base_grand_total'])) {
            if (isset($ksAppliedFilters['base_grand_total']['from'])) {
                $this->getCollection()->addFieldToFilter(
                    'base_grand_total',
                    ['gteq' => $ksAppliedFilters['base_grand_total']['from']]
                );
            }

            if (isset($ksAppliedFilters['base_grand_total']['to'])) {
                $this->getCollection()->addFieldToFilter(
                    'base_grand_total',
                    ['lteq' => $ksAppliedFilters['base_grand_total']['to']]
                );
            }
        }

        if (isset($ksAppliedFilters['grand_total'])) {
            if (isset($ksAppliedFilters['grand_total']['from'])) {
                $this->getCollection()->addFieldToFilter(
                    'grand_total',
                    ['gteq' => $ksAppliedFilters['grand_total']['from']]
                );
            }

            if (isset($ksAppliedFilters['grand_total']['to'])) {
                $this->getCollection()->addFieldToFilter(
                    'grand_total',
                    ['lteq' => $ksAppliedFilters['grand_total']['to']]
                );
            }
        }

        if (isset($ksAppliedFilters['billing_address'])) {
            $this->getCollection()->addFieldToFilter(
                'billing_address',
                ['like' => '%'.$ksAppliedFilters['billing_address'].'%']
            );
        }

        if (isset($ksAppliedFilters['billing_name'])) {
            $this->getCollection()->addFieldToFilter(
                'billing_name',
                ['like' => '%'.$ksAppliedFilters['billing_name'].'%']
            );
        }

        if (isset($ksAppliedFilters['shipping_address'])) {
            $this->getCollection()->addFieldToFilter(
                'shipping_address',
                ['like' => '%'.$ksAppliedFilters['shipping_address'].'%']
            );
        }

        if (isset($ksAppliedFilters['shipping_name'])) {
            $this->getCollection()->addFieldToFilter(
                'shipping_name',
                ['like' => '%'.$ksAppliedFilters['shipping_name'].'%']
            );
        }

        if (isset($ksAppliedFilters['status'])) {
            $this->getCollection()->addFieldToFilter(
                'status',
                ['eq' => $ksAppliedFilters['status']]
            );
        }

        if (isset($ksAppliedFilters['store_id'])) {
            $this->getCollection()->addFieldToFilter(
                'store_id',
                ['eq' => $ksAppliedFilters['store_id']]
            );
        }

        if ($searchKey) {
            $this->getCollection()->addFieldToFilter(
                [ 'increment_id',
                'billing_name',
                'shipping_name',
                'billing_address',
                'shipping_address'],
                [
                    ['eq'=>$searchKey],
                    ['like'=> '%'.$searchKey.'%'],
                    ['like'=> '%'.$searchKey.'%'],
                    ['like'=> '%'.$searchKey.'%'],
                    ['like'=> '%'.$searchKey.'%']
                ]
            );
        }

        return $this->getCollection()->load()->toArray();
    }
}
