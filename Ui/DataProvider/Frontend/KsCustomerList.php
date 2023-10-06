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
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCustomerListing\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter;

/**
 * Class KsCustomerList
 */
class KsCustomerList extends AbstractDataProvider
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

    protected $ksFulltextFilter;

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
        $this->collection = $collectionFactory->create();
        $ks_sellerId      =  $ksSellerHelper->getKsCustomerId();
        $ks_joinTable = $this->collection->getTable('sales_order');
        $this->collection->getSelect()->join(
            $ks_joinTable.' as so',
            'main_table.entity_id = so.customer_id',
            ['so_entity_id'=>'so.entity_id']
        );

        $ksSalesTable = $this->collection->getTable('ks_sales_order');
        $this->collection->getSelect()->join(
            $ksSalesTable.' as ks_sog',
            'so.entity_id = ks_sog.ks_order_id',
            ['sog_entity_id'=>'ks_sog.id']
        )->reset(\Magento\Framework\DB\Select::COLUMNS)->columns('main_table.*')->group('main_table.entity_id')->where("ks_sog.ks_seller_id = '$ks_sellerId' AND main_table.entity_id != '$ks_sellerId'");
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
