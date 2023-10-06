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

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class KsConfigurableAssociateProductList
 */
class KsConfigurableAssociateProductList extends \Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing\KsCatalogProductDataProvider
{
    /**
     * @var RequestInterface
     * @since 101.0.0
     */
    protected $ksRequest;

    /**
     * @var ProductRepositoryInterface
     * @since 101.0.0
     */
    protected $ksProductRepository;

    /**
     * @var StoreRepositoryInterface
     * @since 101.0.0
     */
    protected $ksStoreRepository;

    /**
     * @var ProductLinkRepositoryInterface
     * @since 101.0.0
     */
    protected $ksProductLinkRepository;

    /**
     * @var ProductInterface
     */
    private $ksProduct;

    /**
     * @var StoreInterface
     */
    private $ksStore;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param ProductRepositoryInterface $ksProductRepository
     * @param StoreRepositoryInterface $ksStoreRepository
     * @param ProductLinkRepositoryInterface $ksProductLinkRepository
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param PoolInterface $modifiersPool
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        \Magento\Framework\App\RequestInterface $ksRequest,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory,
        \Magento\Customer\Model\SessionFactory $ksSession,
        ProductRepositoryInterface $ksProductRepository,
        StoreRepositoryInterface $ksStoreRepository,
        ProductLinkRepositoryInterface $ksProductLinkRepository,
        \Magento\Catalog\Model\ProductFactory $ksCatalogProductFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        $this->ksRequest                = $ksRequest;
        $this->ksProductRepository      = $ksProductRepository;
        $this->ksStoreRepository        = $ksStoreRepository;
        $this->ksProductLinkRepository  = $ksProductLinkRepository;
        $this->ksSellerProductFactory   = $ksSellerProductFactory;
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
}
