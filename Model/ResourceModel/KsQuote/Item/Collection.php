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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsQuote\Item;

use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as KsQuoteCollection;

/**
 * Collection Class
 */
class Collection extends KsQuoteCollection
{
    /**
     * @var bool $ksRecollectQuote
     */
    private $ksRecollectQuote = false;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var Magento\Quote\Model\ResourceModel\Quote
     */
    protected $ksQuoteResource;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param Option\CollectionFactory $itemOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Quote\Model\Quote\Config $quoteConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param \Magento\Quote\Model\ResourceModel\Quote $ksQuoteResource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $ksEntityFactory,
        \Psr\Log\LoggerInterface $ksLogger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $ksFetchStrategy,
        \Magento\Framework\Event\ManagerInterface $ksEventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $ksEntitySnapshot,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $ksItemOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksProductCollectionFactory,
        \Magento\Quote\Model\Quote\Config $ksQuoteConfig,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Magento\Quote\Model\ResourceModel\Quote $ksQuoteResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $ksConnection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $ksResource = null,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksQuoteResource = $ksQuoteResource;
        parent::__construct(
            $ksEntityFactory,
            $ksLogger,
            $ksFetchStrategy,
            $ksEventManager,
            $ksEntitySnapshot,
            $ksItemOptionCollectionFactory,
            $ksProductCollectionFactory,
            $ksQuoteConfig
        );
    }

    /**
     * Add condition to hide seller product if not approved
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _assignProducts(): KsQuoteCollection
    {
        parent::_assignProducts();
        $ksProductIds = [];

        $productCollection = $this->_productCollectionFactory->create()->setStoreId(
            $this->getStoreId()
        )->addIdFilter(
            $this->_productIds
        )->addAttributeToSelect(
            $this->_quoteConfig->getProductAttributes()
        )->setFlag('has_stock_status_filter', true);
        $productCollection->addOptionsToResult()->addStoreFilter()->addUrlRewrite();
        
        foreach ($this as $ksItem) {
            /** @var ProductInterface $product */
            $ksProduct = $productCollection->getItemById($ksItem->getProductId());
            $ksProductId = $ksItem->getProductId();
            try {
                /** @var QuoteItem $item */
                $ksParentItem = $ksItem->getParentItem();
                $ksParentProductId = $ksParentItem ? $ksParentItem->getProductId() : null;
            } catch (NoSuchEntityException $exception) {
                $ksParentItem = null;
                $ksParentProductId = null;
            }
            
            if (!$this->ksCheckSellerValidProduct($ksProductId) || ($ksParentItem && !$this->ksCheckSellerValidProduct($ksParentProductId))) {
                $ksProductIds[] = $ksProductId;
                $ksItem->isDeleted(true);
                $this->ksRecollectQuote = true;
            }

            foreach ($ksItem->getOptions() as $ksOption) {
                $ksProduct->getTypeInstance()->assignProductToOption(
                    $productCollection->getItemById($ksOption->getProductId()),
                    $ksOption,
                    $ksProduct
                );

                if (is_object($ksOption->getProduct()) && $ksOption->getProduct()->getId() != $ksProduct->getId()) {
                    $isValidProduct = $this->ksCheckSellerValidProduct($ksOption->getProduct()->getId());
                    if (!$isValidProduct && !$ksItem->isDeleted()) {
                        $ksProductIds[] = $ksProductId;
                        $ksItem->isDeleted(true);
                        $this->ksRecollectQuote = true;
                        continue;
                    }
                }
            }
        }
        if ($this->ksRecollectQuote && $this->_quote) {
            $this->ksQuoteResource->markQuotesRecollect($ksProductIds);
            $this->_quote->setTotalsCollectedFlag(false);
        }
         
        \Magento\Framework\Profiler::stop('QUOTE:' . __METHOD__);

        return $this;
    }

    /**
     * Add products to items and item options.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function ksCheckSellerValidProduct($ksProductId)
    {
        return $this->ksProductHelper->ksSellerProductFilter($ksProductId);
    }
}
