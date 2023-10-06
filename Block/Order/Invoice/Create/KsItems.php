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

namespace Ksolves\MultivendorMarketplace\Block\Order\Invoice\Create;

use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory;
use Magento\Catalog\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Filter\FilterManager;

/**
 * Invoice items grid
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
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $ksSalesModel;

    /**
     * @var \Magento\Sales\Model\OrderItem
     */
    protected $ksSalesItemModel;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Sales\Helper\Admin
     */
    private $ksAdminHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory
     */
    protected $ksCommentFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;
    
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksSerializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Data $ksSalesData
     * @param \Magento\Sales\Model\Order $ksSalesModel
     * @param \Magento\Sales\Model\OrderItem $ksSalesItemRepository
     * @param KsOrderHeler $ksOrderHelper
     * @param KsProductHelper $ksProductHelper
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment\CollectionFactory $ksCommentFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param FilterManager $ksFilterManager
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
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Sales\Model\Order $ksSalesModel,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemRepository,
        KsOrderHelper $ksOrderHelper,
        KsProductHelper $ksProductHelper,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        CollectionFactory $ksCommentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        Json $ksSerializer,
        FilterManager $ksFilterManager,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        array $ksData = []
    ) {
        $this->ksSalesData = $ksSalesData;
        $this->ksSalesModel = $ksSalesModel;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksCommentFactory = $ksCommentFactory;
        $this->ksScopeConfig = $ksScopeConfig;
        $ksData['ksOrderHelper'] = $ksOrderHelper;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        parent::__construct($ksContext, $ksRegistry, $ksSalesItemRepository, $ksOrderHelper, $ksAdminHelper, $ksCatalogHelper, $ksSerializer, $ksFilterManager, $ksPurchasedFactory, $ksItemsFactory, $ksData);
    }

    /**
     * Retrieve invoice model instance
     *
     * @return Object
     */
    public function getKsInvoice()
    {
        if ($this->ksRegistry->registry('current_invoice')) {
            return $this->ksRegistry->registry('current_invoice');
        } else {
            return $this->ksRegistry->registry('current_invoice_request');
        }
    }

    /**
     * Retrieve invoice items
     *
     * @return Object
     */
    public function getKsInvoiceItems()
    {
        return $this->ksRegistry->registry('current_invoice_items');
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function ksFormatPrice($price)
    {
        if (!$this->getKsInvoiceItems()) {
            return $this->getKsInvoice()->getOrder()->formatPrice($price);
        } else {
            return $this->ksSalesModel->load($this->getKsInvoice()->getKsOrderId())->formatPrice($price);
        }
    }

    /**
     * Check if qty can be edited
     *
     * @return bool
     */
    public function canKsEditQty()
    {
        if ($this->getKsInvoice()->getOrder()->getPayment()->canCapture()) {
            return $this->getKsInvoice()->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }

    /**
     * @return bool
     */
    public function ksCheckNotesAndNotifyInvoice()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyInvoice() && $this->ksSalesData->canSendInvoiceCommentEmail(
            $this->getKsOrder()->getStore()->getId()
        );
    }

    /**
     * @return object
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
    }

    /**
     * Replace links in string
     *
     * @param array|string $data
     * @param null|array $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        return $this->ksAdminHelper->escapeHtmlWithLinks($data, $allowedTags);
    }

    /**
     * @return bool
     */
    public function canKsSendCommentEmail()
    {
        return $this->ksSalesData->canSendInvoiceCommentEmail(
            $this->getKsOrder()->getStore()->getId()
        );
    }

    /**
     * Get comment collection for the Invoice
     *
     * @return Object
     */
    public function getKsComments()
    {
        if ($this->getKsInvoice()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getInvoiceId($this->getKsInvoice()->getEntityId());
            return $this->ksCommentFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->setOrder('entity_id', 'DESC');
        } else {
            return $this->getKsInvoice();
        }
    }

    /**
     * @param integer $ksIncrementId
     * @return integer
     */
    public function getKsSellerTotalOrderCommission($ksIncrementId)
    {
        $this->ksOrderHelper->getKsSellerTotalOrderCommission($ksIncrementId);
    }

    /**
     * @param integer $ksOrderId
     * @return integer
     */
    public function getKsOrderIncrementId($ksOrderId)
    {
        return $this->ksOrderHelper->getKsOrderIncrementId($ksOrderId);
    }

    /**
     * @return bool
     */
    public function checkNotesAndNotifyInvoice()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyInvoice();
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
    public function formatToOrderCurrency($price)
    {
        return $this->getKsOrder()->formatPrice($price, true);
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
     * @return \Magento\Sales\Order\Invoice\Item
     */
    public function getKsSalesInvoiceItem($ksInvoiceIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesInvoiceItem($ksInvoiceIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem
     */
    public function getKsSalesInvoiceReqItem($ksInvoiceIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesInvoiceReqItem($ksInvoiceIncrId, $ksOrderItemId);
    }

    /**
     * @return Boolean
     */
    public function getKsIsSellerProduct($ksProductId)
    {
        return $this->ksProductHelper->isKsSellerProduct($ksProductId);
    }
}
