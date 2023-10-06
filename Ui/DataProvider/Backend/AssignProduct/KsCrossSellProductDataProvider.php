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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\AssignProduct;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Class KsCrossSellProductDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend
 */
class KsCrossSellProductDataProvider extends \Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing\KsCatalogProductDataProvider
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
    * @var StoreManagerInterface
    */
    protected $ksProductHelper;

    /**
     * KsCrossSellProductDataProvider constructor.
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
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        \Magento\Framework\App\RequestInterface $ksRequest,
        KsProductHelper $ksProductHelper,
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
        $this->ksProductHelper = $ksProductHelper;
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
        // Get KsSellerId from DataPersistor
        $ksProductId = $this->ksDataPersistor->get('ks_assign_product_details');
        //Get Child Product
        $ksChildIds = $this->ksProductHelper->getKsChildProductIds($ksProductId);
        $ksChildCrossSellArray = [];
        if (!empty($ksChildIds)) {
            foreach ($ksChildIds as $ksChildId) {
                $ksCrossSell = $this->ksProductHelper->getKsCrossSellProductIds($ksChildId);
                $ksChildCrossSellArray = array_merge($ksChildCrossSellArray, $ksCrossSell);
            }
        }

        $ksCrossSellProductIds = $this->ksProductHelper->getKsCrossSellProductIds($ksProductId);
        $ksCrossSellProductIds = array_merge($ksCrossSellProductIds, $ksChildCrossSellArray);
        // Filter according to seller id
        $this->collection->addFieldToFilter('entity_id', ['in' => $ksCrossSellProductIds]);
    }
}
