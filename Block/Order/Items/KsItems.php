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

namespace Ksolves\MultivendorMarketplace\Block\Order\Items;

use Magento\Directory\Model\CurrencyFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper as KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper as KsOrderHeler;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use \Magento\Sales\Model\Order\Invoice;
use \Magento\Sales\Model\Order\Shipment;
use Magento\Framework\Registry;
use Magento\Directory\Model\Currency;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Helper\Data;
use Magento\Downloadable\Model\Link;
use Magento\Store\Model\ScopeInterface;

/**
 * KsItems block
 */
class KsItems extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Invoice
     */
    protected $ksInvoiceCollection;

    /**
     * @var Shipment
     */
    protected $ksShipmentCollection;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsOrderHeler
     */
    protected $ksOrderHelper;

    /**
     * @var KsDataHeler
     */
    protected $ksDataHelper;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
    * @var \Ksolves\MultivendorMarketplace\Model\KsSalesOrderFactory
    */
    protected $ksSalesOrderFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory
     */
    protected $ksSalesOrderItemFactory;
    
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $ksAdminHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProduct;

    /**
     * @var \Magento\GiftMessage\Api\OrderItemRepositoryInterface
     */
    protected $orderItemGiftRepo;

    /**
     * @var OrderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksSerializer;

    /**
     * @var \Magento\Downloadable\Model\Link\PurchasedFactory
     */
    protected $ksPurchasedFactory;

    /**
     * @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory
     */
    protected $ksItemsFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory $ksSalesOrderItemFactory
     * @param Registry $registry
     * @param CurrencyFactory $currencyFactory
     * @param Invoice $invoiceCollection
     * @param ksSeller $ksSeller
     * @param KsOrderHeler $ksOrderHelper
     * @param KsDataHelper $ksDataHelper
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param Currency $currency
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct
     * @param \Magento\GiftMessage\Api\OrderItemRepositoryInterface $orderItemGiftRepo
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        Registry $registry,
        Invoice $ksInvoiceCollection,
        Shipment $ksShipmentCollection,
        KsSellerHelper $ksSellerHelper,
        KsOrderHeler $ksOrderHelper,
        KsDataHelper $ksDataHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Ksolves\MultivendorMarketplace\Model\KsSalesOrderFactory $ksSalesOrderFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory $ksSalesOrderItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProduct,
        \Magento\GiftMessage\Api\OrderItemRepositoryInterface $orderItemGiftRepo,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        Json $ksSerializer,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        Currency $currency = null,
        array $ksData = []
    ) {
        $this->coreRegistry = $registry;
        $this->ksInvoiceCollection = $ksInvoiceCollection;
        $this->ksShipmentCollection= $ksShipmentCollection;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->pricingHelper = $pricingHelper;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->ksSalesOrderFactory = $ksSalesOrderFactory->create();
        $this->ksSalesOrderItemFactory  = $ksSalesOrderItemFactory;
        $this->orderCollectionFactory =$orderCollectionFactory;
        $this->ksProduct =$ksProduct;
        $this->ksAdminHelper =$ksAdminHelper;
        $this->orderItemGiftRepo = $orderItemGiftRepo;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSerializer = $ksSerializer;
        $this->ksPurchasedFactory = $ksPurchasedFactory;
        $this->ksItemsFactory = $ksItemsFactory;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        $ksData['ksOrderHelper'] = $ksOrderHelper;
        $this->currency = $currency ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(Currency::class);
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        $ksOrderId = (int)$this->getData('ks_order_id');
        if (!$this->coreRegistry->registry('current_order')) {
            $this->coreRegistry->register('current_order', $this->ksOrderRepository->get($ksOrderId));
        }
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Retrieve marketplace order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsMarketplaceOrder()
    {
        $ksOrderId = (int)$this->getRequest()->getParam('ks_order_id');
        if (!$this->coreRegistry->registry('current_marketplace_order')) {
            $this->coreRegistry->register('current_marketplace_order', $this->ksSalesOrderFactory->getCollection()->addFieldToFilter('ks_order_id', $ksOrderId)->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId())->getFirstItem());
        }
        return $this->coreRegistry->registry('current_marketplace_order');
    }

    /**
     * get ordered items
     * @return object
     */
    public function getOrderedItems()
    {
        $ks_order = $this->getKsOrder();
        return $ks_order;
    }
    
    /**
     * Get ordered product attributes
     *
     * @return array
     */
    public function getKsOrderItemAttrValues($ks_item)
    {
        return $this->ksOrderHelper->getKsOrderItemAttrValues($ks_item);
    }


    /**
     * Convert and format price value for current application store
     *
     * @return  float|string
     */
    public function getFormattedPrice($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    /**
     * Format total value based on base currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($price, $precision = 2)
    {
        return $this->getKsOrder()->getBaseCurrency()->formatPrecision($price, $precision);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatToOrderCurrency($price, $bracket=true)
    {
        return $this->getKsOrder()->formatPrice($price, $bracket);
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function canKsShowOrderCurrencyValue()
    {
        return !($this->getKsOrder()->getOrderCurrencyCode() == $this->getKsOrder()->getStoreCurrencyCode());
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalBaseOrderCommission($ksIncrementId)
    {
        return $this->ksOrderHelper->getKsSellerTotalBaseOrderCommission($ksIncrementId);
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalOrderCommission($ksIncrementId)
    {
        return $this->ksOrderHelper->getKsSellerTotalOrderCommission($ksIncrementId);
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        return $this->getKsOrder();
    }

    /**
     * Return order converted price
     *
     * @return price
     */
    public function getConvertedPrice($price)
    {
        $ksOrder = $this->getKsOrder();
        $ksRate =  $ksOrder->getBaseToOrderRate();
        $currencyCode =  $ksOrder->getOrderCurrencyCode();
        $orderPurchaseCurrency = $this->currency->load($currencyCode);
        return $orderPurchaseCurrency->format($price*$ksRate, [], false);
    }

    /**
    * Return order collection
    *
    * @return order
    */
    public function getOrderCollection()
    {
        $collection = $this->orderCollectionFactory->create()
        ->addAttributeToSelect('*')
        ->setOrder('created_at', 'desc');
        return $collection;
    }

    /**
     * Display price attribute
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br/>')
    {
        return $this->ksAdminHelper->displayPriceAttribute($this->getPriceDataObject(), $code, $strong, $separator);
    }

    /**
     * Get price data object
     *
     * @return Order|mixed
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if ($obj === null) {
            return $this->getKsOrder();
        }
        return $obj;
    }

    /**
     * Check the seller product item
     * @param $productId
     * @return bool
     */
    public function KsIsSellerProduct($ks_productId)
    {
        $ks_collection = $this->ksProduct->create()->getCollection()
        ->addFieldToFilter('ks_product_id', $ks_productId)
        ->addFieldToFilter('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        if ($ks_collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Get the invoice item
     * @param $productId
     * @return bool
     */
    public function KsGetInvoice()
    {
        $ks_collection = $this->ksInvoiceCollection->create()->getCollection();
        if ($ks_collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Get the gift messages
     * @param $orderId
     * @param $orderItemId
     * @return \Magento\GiftMessage\Api\OrderItemRepositoryInterface
     */
    public function getGiftMessage($orderId, $orderItemId)
    {
        $ksGiftMessage = $this->orderItemGiftRepo->get($orderId, $orderItemId);
        return $ksGiftMessage;
    }

    /**
     * Check if seller is allowed to edit gift messages
     *
     * @return \Magento\GiftMessage\Api\OrderItemRepositoryInterface
     */
    public function getKsCanEditGiftMessage()
    {
        return $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_order_settings/ks_edit_gift_messages');
    }

    /**
     * Get html info for item
     *
     * @param mixed $ksItem
     * @param OrderItem $ksOrderItem
     * @return string
     */
    public function getKsValueHtml($ksItem, $ksOrderItem)
    {
        $result = $this->escapeHtml($ksItem->getName());
        if (!$this->isKsShipmentSeparately($ksItem, $ksOrderItem)) {
            $attributes = $this->getKsSelectionAttributes($ksItem, $ksOrderItem);
            if ($attributes) {
                $result = sprintf('%d', $attributes['qty']) . ' x ' . $result;
            }
        }
        if (!$this->isKsChildCalculated($ksItem, $ksOrderItem)) {
            $attributes = $this->getKsSelectionAttributes($ksItem, $ksOrderItem);
            if ($attributes) {
                $result .= " " . $ksOrderItem->getOrder()->getBaseCurrency()->formatPrecision($attributes['price'], 2);
            }
        }
        return $result;
    }

    /**
     * Check if item can be shipped separately
     *
     * @param mixed $ksItem
     * @param OrderItem $ksOrderItem
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isKsShipmentSeparately($ksItem, $ksOrderItem)
    {
        if ($ksItem) {
            if ($ksItem->getOrderItem()) {
                $ksItem = $ksItem->getOrderItem();
            }
            $parentItem = $ksItem->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            } else {
                $options = $ksItem->getProductOptions();
                if ($options) {
                    return !(isset($options['shipment_type'])
                        && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY);
                }
            }
        }

        $options = $ksOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['shipment_type']) && $options['shipment_type'] == AbstractType::SHIPMENT_SEPARATELY) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve selection attributes values
     *
     * @param mixed $ksItem
     * @param OrderItem $ksOrderItem
     * @return mixed|null
     */
    public function getKsSelectionAttributes($ksItem, $ksOrderItem)
    {
        if ($ksItem instanceof \Magento\Sales\Model\Order\Item) {
            $options = $ksItem->getProductOptions();
        } else {
            $options = $ksOrderItem->getProductOptions();
        }
        if (isset($options['bundle_selection_attributes'])) {
            return $this->ksSerializer->unserialize($options['bundle_selection_attributes']);
        }
        return null;
    }

    /**
     * Check if child items calculated
     *
     * @param mixed $ksItem
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isKsChildCalculated($ksItem = null)
    {
        if ($ksItem) {
            if ($ksItem->getOrderItem()) {
                $ksItem = $ksItem->getOrderItem();
            }
            $parentItem = $ksItem->getParentItem();
            if ($parentItem) {
                $options = $parentItem->getProductOptions();
                if ($options) {
                    return (isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            } else {
                $options = $ksItem->getProductOptions();
                if ($options) {
                    return !(isset($options['product_calculations'])
                        && $options['product_calculations'] == AbstractType::CALCULATE_CHILD);
                }
            }
        }

        $options = $this->getKsBundleItem()->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_CHILD
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if child items calculated
     *
     * @param mixed $ksItem
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isKsBundleChildCalculated($ksItem)
    {
        return $this->ksOrderHelper->isKsChildCalculated($ksItem);
    }

    /**
     * Check if we can show price info for this item
     *
     * @param object $item
     * @return bool
     */
    public function canShowPriceInfo($item)
    {
        if ($item->getParentItem() && $this->isKsChildCalculated()
            || !$item->getParentItem() && !$this->isKsChildCalculated()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get ordered product attribute
     *
     * @return array
     */
    public function getKsOrderItemAttrValue($ks_item)
    {
        return $this->ksOrderHelper->getKsOrderItemAttrValue($ks_item);
    }

    /**
     * Calculate base item tax
     *
     * @param Object $ksItem
     */
    public function ksCalcItemBaseTaxAmt($ksItem)
    {
        return $this->ksOrderHelper->ksCalcItemBaseTaxAmt($ksItem);
    }

    /**
     * Calculate item tax
     *
     * @param Object $ksItem
     */
    public function ksCalcItemTaxAmt($ksItem)
    {
        return $this->ksOrderHelper->ksCalcItemTaxAmt($ksItem);
    }

    /**
     * Return purchased links.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return Object
     */
    public function getKsDownloadableItemData($ksItem)
    {
        $this->ksPurchased = $this->ksPurchasedFactory->create()->load(
            $ksItem->getId(),
            'order_item_id'
        );
        $ksPurchasedItem = $this->ksItemsFactory->create()->addFieldToFilter('order_item_id', $ksItem->getId());
        $this->ksPurchased->setPurchasedItems($ksPurchasedItem);
        return $this->ksPurchased;
    }

    /**
     * Retunrn links title.
     *
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return null|string
     */
    public function getKsDownloadableLinkTitle($ksItem)
    {
        return $this->getKsDownloadableItemData($ksItem)->getLinkSectionTitle() ?: $this->_scopeConfig->getValue(
            Link::XML_PATH_LINKS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $ksItem
     * @return string
     */
    public function getPurchasedLinkUrl($item, $ksOrderItem)
    {
        $url = $this->getUrl(
            'downloadable/download/link',
            [
                'id' => $item->getLinkHash(),
                '_scope' => $ksOrderItem->getOrder()->getStore(),
                '_secure' => true,
                '_nosid' => true
            ]
        );
        return $url;
    }
}
