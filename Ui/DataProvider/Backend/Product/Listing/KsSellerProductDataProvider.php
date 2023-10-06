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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class KsSellerProductDataProvider
 *
 */
class KsSellerProductDataProvider extends \Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing\KsMarketplaceProductDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
    * Store manager
    *
    * @var StoreManagerInterface
    */
    protected $ksStoreManager;

    /**
     * KsSellerProductDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param \Magento\Framework\App\RequestInterface $ksRequest
     * @param \Magento\Framework\App\Request\DataPersistorInterface $ksDataPersistor
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,        
        \Magento\Framework\App\RequestInterface $ksRequest,
        \Magento\Framework\App\Request\DataPersistorInterface $ksDataPersistor,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory,
        \Magento\Customer\Model\SessionFactory $ksSession,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        $this->ksRequest = $ksRequest;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksStoreManager = $ksStoreManager;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $ksCollectionFactory,
            $ksRequest,
            $ksProductFactory,
            $ksSession,
            $ksSellerProductFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data,
            $modifiersPool
        );

        // Get KsSellerID from the params
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
        // Get KsSellerId from DataPersistor
        $ksDataPersistorSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        // Terinory operator used to get seller id
        $ksCurrentSellerId = $ksSellerId ? $ksSellerId : $ksDataPersistorSellerId;
        // Filter according to seller id
        $this->collection->addFieldToFilter('ks_seller_id', $ksCurrentSellerId);

        $ksStoreId = $this->ksRequest->getParam('ks_store_id');
        if ($ksStoreId) {
            $ksStore = $this->ksStoreManager->getStore($ksStoreId);
            $this->collection->addStoreFilter($ksStore);
        }
    }
}
