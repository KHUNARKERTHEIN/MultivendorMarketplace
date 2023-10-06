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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory as KsCategoryRequestsCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Class KsCategoryRequestsDataProvider
 *
 */
class KsCategoryRequestsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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

    /**
     * @var KsSellerHelper
     */
    protected $KsSellerHelper;

    protected $collection;
    
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollectionFactory
     * @param DataPersistorInterface $ksDataPersistor
     * @param StoreManagerInterface $ksStoreManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        KsCategoryRequestsCollectionFactory $ksCategoryRequestsCollectionFactory,
        KsSellerHelper $ksSellerHelper,
        DataPersistorInterface $ksDataPersistor,
        StoreManagerInterface $ksStoreManager,
        array $meta = [],
        array $data = []
    ) {
        $ksSellerList = $ksSellerHelper->getKsSellerList();
        $this->collection = $ksCategoryRequestsCollectionFactory->create()->addFieldToFilter('ks_seller_id', ['in' => $ksSellerList])->addFieldToFilter(
            'ks_request_status',
            [
                ['eq'=>\Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_PENDING]
            ]
        );
        $joinTable = $this->collection->getTable('customer_grid_flat');
        $this->collection->getSelect()->join(
            $joinTable.' as ks_cgf',
            'main_table.ks_seller_id = ks_cgf.entity_id',
            [
                'ks_seller_name'=>'name',
            ]
        );
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
