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

namespace Ksolves\MultivendorMarketplace\Block\Checkout\Cart;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\LinkFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\RelatedProducts;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Cart crosssell list
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class KsCrosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    /**
     * Items quantity will be capped to this value
     *
     * @var int
     */
    protected $_maxItemCount = 4;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Visibility
     */
    protected $_productVisibility;

    /**
     * @var StockHelper
     */
    protected $stockHelper;

    /**
     * @var LinkFactory
     */
    protected $_productLinkFactory;

    /**
     * @var RelatedProducts
     */
    protected $_itemRelationsList;

    /**
     * @var CollectionFactory|null
     */
    private $productCollectionFactory;

    /**
     * @var ProductRepositoryInterface|null
     */
    private $productRepository;

    /**
     * @var Product[]
     */
    private $cartProducts;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param Context $ksContext
     * @param Session $ksCheckoutSession
     * @param Visibility $ksProductVisibility
     * @param LinkFactory $ksProductLinkFactory
     * @param RelatedProducts $ksItemRelationsList
     * @param StockHelper $ksStockHelper
     * @param array $ksData
     * @param CollectionFactory|null $ksProductCollectionFactory
     * @param ProductRepositoryInterface|null $ksProductRepository
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param KsProductHelper $ksProductHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        Context $ksContext,
        Session $ksCheckoutSession,
        Visibility $ksProductVisibility,
        LinkFactory $ksProductLinkFactory,
        RelatedProducts $ksItemRelationsList,
        StockHelper $ksStockHelper,
        KsProductHelper $ksProductHelper,
        KsDataHelper $ksDataHelper,
        array $ksData = [],
        ?CollectionFactory $ksProductCollectionFactory = null,
        ?ProductRepositoryInterface $ksProductRepository = null
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct(
            $ksContext,
            $ksCheckoutSession,
            $ksProductVisibility,
            $ksProductLinkFactory,
            $ksItemRelationsList,
            $ksStockHelper,
            $ksData,
            $ksProductCollectionFactory,
            $ksProductRepository
        );
        $this->productCollectionFactory = $ksProductCollectionFactory
            ?? ObjectManager::getInstance()->get(CollectionFactory::class);
        $this->productRepository = $ksProductRepository
            ?? ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
    }

    /**
     * Get crosssell items
     *
     * @return array
     */
    public function getItems()
    {
        $items = $this->getData('items');
        if ($items === null) {
            $items = [];
            $ninProductIds = $this->_getCartProductIds();

            $ksEnableCrossSell = $this->ksDataHelper->getKsConfigProductSetting('ks_cross_sell_product');
            if (!$ksEnableCrossSell) {
                foreach ($this->getCartProducts() as $ksCartItems) {
                    if ($this->ksProductHelper->KsIsAnySellerProduct($ksCartItems->getId())) {
                        foreach ($ksCartItems->getCrossSellProducts() as $ksCrosssell) {
                            $ninProductIds[] = $ksCrosssell->getId();
                        }
                    }
                }
            }

            if ($ninProductIds) {
                $lastAddedProduct = $this->getLastAddedProduct();
                if ($lastAddedProduct) {
                    $collection = $this->_getCollection()
                        ->addProductFilter($lastAddedProduct->getData($this->getProductLinkField()));
                    if (!empty($ninProductIds)) {
                        $collection->addExcludeProductFilter($ninProductIds);
                    }
                    $collection->setPositionOrder()->load();

                    foreach ($collection as $item) {
                        $ninProductIds[] = $item->getId();
                        $items[] = $item;
                    }
                }

                if (count($items) < $this->_maxItemCount) {
                    $filterProductIds = array_merge(
                        $this->getCartProductLinkIds(),
                        $this->getCartRelatedProductLinkIds()
                    );
                    $collection = $this->_getCollection()->addProductFilter(
                        $filterProductIds
                    )->addExcludeProductFilter(
                        $ninProductIds
                    )->setPageSize(
                        $this->_maxItemCount - count($items)
                    )->setGroupBy()->setPositionOrder()->load();
                    foreach ($collection as $item) {
                        $items[] = $item;
                    }
                }
            }

            $this->setData('items', $items);
        }
        return $items;
    }

    /**
     * Get product link ID field
     *
     * @return string
     */
    private function getProductLinkField(): string
    {
        /* @var $collection Collection */
        $collection = $this->productCollectionFactory->create();
        return $collection->getProductEntityMetadata()->getLinkField();
    }

    /**
     * Get cart products link IDs
     *
     * @return array
     */
    private function getCartProductLinkIds(): array
    {
        $linkField = $this->getProductLinkField();
        $linkIds = [];
        foreach ($this->getCartProducts() as $product) {
            /** * @var Product $product */
            $linkIds[] = $product->getData($linkField);
        }
        return $linkIds;
    }

    /**
     * Get cart related products link IDs
     *
     * @return array
     */
    private function getCartRelatedProductLinkIds(): array
    {
        $productIds = $this->_itemRelationsList->getRelatedProductIds($this->getQuote()->getAllItems());
        $linkIds = [];
        if (!empty($productIds)) {
            $linkField = $this->getProductLinkField();
            /* @var $collection Collection */
            $collection = $this->productCollectionFactory->create();
            $collection->addIdFilter($productIds);
            foreach ($collection as $product) {
                /** * @var Product $product */
                $linkIds[] = $product->getData($linkField);
            }
        }
        return $linkIds;
    }

    /**
     * Retrieve just added to cart product object
     *
     * @return ProductInterface|null
     */
    private function getLastAddedProduct(): ?ProductInterface
    {
        $product = null;
        $productId = $this->_getLastAddedProductId();
        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
        return $product;
    }

    /**
     * Retrieve Array of Product instances in Cart
     *
     * @return array
     */
    private function getCartProducts(): array
    {
        if ($this->cartProducts === null) {
            $this->cartProducts = [];
            foreach ($this->getQuote()->getAllItems() as $quoteItem) {
                /* @var $quoteItem \Magento\Quote\Model\Quote\Item */
                $product = $quoteItem->getProduct();
                if ($product) {
                    $this->cartProducts[$product->getEntityId()] = $product;
                }
            }
        }
        return $this->cartProducts;
    }
}
