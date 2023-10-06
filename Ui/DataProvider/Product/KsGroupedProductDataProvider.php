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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Product;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class KsGroupedProductDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing
 */
class KsGroupedProductDataProvider extends \Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing\KsCatalogProductDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequest;

    /**
     * @var ConfigInterface
     */
    protected $ksConfig;

    /**
     * @var StoreRepositoryInterface
     */
    protected $ksStoreRepository;


    /**
     * KsGroupedProductDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param ConfigInterface $ksConfig
     * @param StoreRepositoryInterface $ksStoreRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param \Magento\Framework\App\RequestInterface $ksRequest
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        ConfigInterface $ksConfig,
        StoreRepositoryInterface $ksStoreRepository,
        \Magento\Framework\App\RequestInterface $ksRequest,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory,
        \Magento\Customer\Model\SessionFactory $ksSession,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        $this->ksRequest         = $ksRequest;
        $this->ksStoreRepository = $ksStoreRepository;
        $this->ksConfig = $ksConfig;
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

        if ($ksSession->create()->getId()) {
            $this->collection->addFieldToFilter(
                'ks_product_approval_status',
                ['eq'=>\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED]
            );
        } else {
            $ksSellerId = $this->ksSellerProductFactory->create()
                        ->load($this->ksRequest->getParam('current_product_id'), 'ks_product_id')
                        ->getKsSellerId();

            if ($ksSellerId) {
                $this->collection->addFieldToFilter(
                    'ks_product_approval_status',
                    ['eq'=>\Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED]
                );
            }
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->addAttributeToFilter(
                'type_id',
                $this->ksConfig->getComposableTypes()
            );
            if ($ksStoreId = $this->ksRequest->getParam('current_store_id')) {
                /** @var StoreInterface $store */
                $ksStore = $this->ksStoreRepository->getById($ksStoreId);
                $this->getCollection()->setStore($ksStore);
            }
            $this->getCollection()->load();
        }

        $ksItems = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($ksItems),
        ];
    }
}
