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

namespace Ksolves\MultivendorMarketplace\Block\Order\Shipment\Create;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Filter\FilterManager;

/**
 * Shipment items grid
 */
class KsItems extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractItems
{
    /**
     * Disable submit button
     *
     * @var bool
     */
    protected $_disableSubmitButton = false;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $ksStockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $ksStockConfiguration;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var StoreManager
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksSerializer;
     
    /**
     * @var SalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration
     * @param \Magento\Framework\Registry $ksRegistry
     * @param KsOrderHelper $ksOrderHelper
     * @param KsProductHelper $ksProductHelper
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param FilterManager $filterManager
     * @param \Magento\Sales\Model\OrderItem $ksSalesItemRepository
     * @param \Magento\Sales\Model\Order\Item $ksSalesOrderItem
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     * @param array $ksData
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration,
        \Magento\Framework\Registry $ksRegistry,
        KsSellerHelper $ksSellerHelper,
        KsOrderHelper $ksOrderHelper,
        KsProductHelper $ksProductHelper,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        \Magento\Sales\Model\Order\Item $ksSalesOrderItem,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        Json $ksSerializer,
        FilterManager $ksFilterManager,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemRepository,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        array $ksData = []
    ) {
        $this->ksSalesData = $ksSalesData;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSalesOrderItem =$ksSalesOrderItem;
        $this->ksProductHelper = $ksProductHelper;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        parent::__construct($ksContext, $ksRegistry, $ksSalesItemRepository, $ksOrderHelper, $ksAdminHelper, $ksCatalogHelper, $ksSerializer, $ksFilterManager, $ksPurchasedFactory, $ksItemsFactory, $ksData);
    }

    /**
     * Retrieve shipment order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        return $this->getKsShipment()->getOrder();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getKsShipment()
    {
        if ($this->ksRegistry->registry('current_shipment')) {
            return $this->ksRegistry->registry('current_shipment');
        } else {
            return $this->ksRegistry->registry('current_shipment_request');
        }
    }

    /**
     * Retrieve shipment items
     *
     * @return Object
     */
    public function getKsShipmentItems()
    {
        return $this->ksRegistry->registry('current_shipment_items');
    }

    /**
     * Get update url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('sales/*/updateQty', ['order_id' => $this->getKsShipment()->getOrderId()]);
    }

    /**
     * Check the seller product item
     * @param $productId
     * @return bool
     */
    public function KsIsSellerProduct($ks_productId)
    {
        return $this->ksOrderHelper->KsIsSellerProduct($ks_productId);
    }

    /**
     * @return bool
     */
    public function ksCheckNotesAndNotifyShipment()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyShipment() && $this->ksSalesData->canSendShipmentCommentEmail(
            $this->getKsShipment()->getOrder()->getStore()->getId()
        );
    }

    /**
     * @return \Magento\Sales\Order\Shipment\Item
     */
    public function getKsSalesShipmentItem($ksShipmentIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesShipmentItem($ksShipmentIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesCreditmemoItem
     */
    public function getKsSalesShipmentReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesShipmentReqItem($ksReqId, $ksOrderItemId);
    }
    
    /**
     * Returns the data from  Sales Order Item table
     *
     * @param \Magento\Sales\Model\Order\Item $ksSalesOrderItem
     * @return  string
     */
    public function getKsOrderItem($ksItemId)
    {
        return $this->ksSalesOrderItem->load($ksItemId);
    }

    /**
     * Returns the data for particular product
     *
     * @param \Magento\Sales\Helper\Data
     * @return  string
     */
    public function getKsShipmentItemAttrValue($ksItemId)
    {
        $attrvalue = $this->getKsOrderItem($ksItemId);
        return $this->ksOrderHelper->getKsShipmentItemAttrValue($attrvalue);
    }

    /**
     * @return Boolean
     */
    public function getKsIsSellerProduct($ksProductId)
    {
        return $this->ksProductHelper->isKsSellerProduct($ksProductId);
    }
}
