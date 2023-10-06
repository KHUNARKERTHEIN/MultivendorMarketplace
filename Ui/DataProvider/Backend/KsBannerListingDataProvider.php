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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBanners\CollectionFactory as KsBannersCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Class KsBannerListingDataProvider
 *
 */
class KsBannerListingDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var ksDataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var array
     */
    protected $ksLoadedData;

    protected $collection;
    
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param KsBannersCollectionFactory $ksBannersCollectionFactory
     * @param DataPersistorInterface $ksDataPersistor
     * @param StoreManagerInterface $ksStoreManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        KsBannersCollectionFactory $ksBannersCollectionFactory,
        DataPersistorInterface $ksDataPersistor,
        StoreManagerInterface $ksStoreManager,
        array $meta = [],
        array $data = []
    ) {
        $this->ksDataPersistor = $ksDataPersistor;
        $ksSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        $this->collection = $ksBannersCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $joinTable = $this->collection->getTable('customer_grid_flat');
        $this->collection->getSelect()->join(
            $joinTable.' as ks_cgf',
            'main_table.ks_seller_id = ks_cgf.entity_id',
            [
                'ks_seller_name'=>'name',
            ]
        );
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
