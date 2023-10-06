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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\Collection as KsFavouriteSellerCollection;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsFollowersDataProvider Class
 */
class KsFollowersDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * collection factory
     *
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory
     */
    protected $collection;

    /**
     * Collection for getting table name
     *
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\Collection
     */
    protected $ksFavouriteSellerCollection;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        KsFavouriteSellerCollection $ksFavouriteSellerCollection,
        KsSellerHelper $ksSellerHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksSellerHelper = $ksSellerHelper;
        
        //Getting Seller Id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        $ksCollection = $ksCollectionFactory->create()
                ->addFieldToSelect('*');
        $ksCustomerGridFlat = $ksFavouriteSellerCollection->getTable('customer_grid_flat');

        // join with customer_grid_flat table
        $ksCollection->getSelect()->join(
            $ksCustomerGridFlat.' as ks_cgf',
            'main_table.ks_customer_id = ks_cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
                'gender' => 'gender'
            ]
        );
        $this->collection = $ksCollection->addFieldToFilter('ks_seller_id', $ksSellerId);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $ksItems = $this->getCollection()->toArray();
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' =>  $ksItems['items'],
        ];
    }
}
