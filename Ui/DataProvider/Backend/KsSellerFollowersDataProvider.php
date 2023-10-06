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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\Collection as KsFavouriteSellerCollection;
use Magento\Framework\App\RequestInterface;

/**
 * KsSellerFollowersDataProvider Class
 */
class KsSellerFollowersDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * favourite seller collection
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
     * @var \Magento\Framework\App\RequestInterface,
     */
    private $ksRequest;

    /**
     * Construct
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        KsFavouriteSellerCollection $ksFavouriteSellerCollection,
        RequestInterface $ksRequest,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;

        //Getting Seller Id from Grid
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
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
