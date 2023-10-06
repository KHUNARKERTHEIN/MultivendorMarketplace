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

namespace Ksolves\MultivendorMarketplace\Block\Order\Creditmemo\Create;

use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Sales\Api\Data\CreditmemoCommentInterface;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Filter\FilterManager;

/**
 * KsItems Credit Memo Block
 */
class KsItems extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractItems
{
    /**
     * @var bool
     */
    protected $_canReturnToStock;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $ksSalesData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $ksCreditmemoRepository;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData = null;

    /**
     * @var KsOrderHeler
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory
     */
    protected $ksCommentCollectionFactory;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

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
     * @var SalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $ksStockRegistry;

    /**
     *@var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $ksStockConfiguration;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration
     * @param \Magento\Sales\Helper\Data $salesData
     * @param array $data
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $ksCreditmemoRepository
     * @param \Magento\Sales\Model\OrderItem $ksSalesItemRepository
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param \Magento\Sales\Model\Order\Item $ksSalesOrderItem
     * @param \Magento\Framework\Serialize\Serializer\Json $ksSerializer
     * @param FilterManager $ksFilterManager
     * @param \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory
     * @param \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration,
        \Magento\Sales\Helper\Data $ksSalesData,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $ksCreditmemoRepository,
        \Magento\Sales\Api\OrderItemRepositoryInterface $ksSalesItemRepository,
        \Magento\Sales\Helper\Data $salesData,
        KsOrderHelper $ksOrderHelper,
        KsProductHelper $ksProductHelper,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $ksCommentCollectionFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        \Magento\Sales\Model\Order\Item $ksSalesOrderItem,
        \Magento\Catalog\Helper\Data $ksCatalogHelper,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        Json $ksSerializer,
        FilterManager $ksFilterManager,
        \Magento\Downloadable\Model\Link\PurchasedFactory $ksPurchasedFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory $ksItemsFactory,
        array $ksData = []
    ) {
        $this->ksStockRegistry = $ksStockRegistry;
        $this->ksStockConfiguration = $ksStockConfiguration;
        $this->ksSalesData = $ksSalesData;
        $this->ksCreditmemoRepository = $ksCreditmemoRepository;
        $this->_salesData = $salesData;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderHelper      = $ksOrderHelper;
        $this->ksCommentCollectionFactory = $ksCommentCollectionFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSalesOrderItem = $ksSalesOrderItem;
        $ksData['ksProductHelper'] = $ksProductHelper;
        $ksData['ksOrderHelper'] = $ksOrderHelper;
        $ksData['ksCatalogHelper'] = $ksCatalogHelper;
        parent::__construct($ksContext, $ksRegistry, $ksSalesItemRepository, $ksOrderHelper, $ksAdminHelper, $ksCatalogHelper, $ksSerializer, $ksFilterManager, $ksPurchasedFactory, $ksItemsFactory, $ksData);
    }

    /**
     * Retrieve credit memo order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
    }

    /**
     * Retrieve credit memo model instance
     *
     * @return Object
     */
    public function getKsCreditmemo()
    {
        if ($this->ksRegistry->registry('current_creditmemo')) {
            return $this->ksRegistry->registry('current_creditmemo');
        } else {
            return $this->ksRegistry->registry('current_creditmemo_request');
        }
    }

    /**
     * Retrieve credit memo items
     *
     * @return Object
     */
    public function getKsCreditmemoItems()
    {
        return $this->ksRegistry->registry('current_creditmemo_items');
    }

    /**
     * Check if allow to edit qty
     *
     * @return bool
     */
    public function canKsEditQty()
    {
        if ($this->getKsOrder()->getPayment()->canRefund()) {
            return $this->getKsOrder()->getPayment()->canRefundPartialPerInvoice();
        }
        return true;
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function canKsReturnToStock($store = null)
    {
        return $this->ksStockConfiguration->canSubtractQty($store);
    }

    /**
     * Whether to show 'Return to stock' column in creditmemo grid
     *
     * @return bool
     */
    public function canKsReturnItemsToStock($ksItem)
    {
        if (null !== $ksItem) {
            if (!$ksItem->hasCanReturnToStock()) {
                $stockItem = $this->ksStockRegistry->getStockItem(
                    $ksItem->getOrderItem()->getProductId(),
                    $ksItem->getOrderItem()->getStore()->getWebsiteId()
                );
                $ksItem->setCanReturnToStock($stockItem->getManageStock());
            }
            return $ksItem->getCanReturnToStock();
        }

        return $this->canReturnToStock();
    }

    /**
     * Whether to show 'Return to stock' column for item parent
     *
     * @param Item $item
     * @return bool
     */
    public function canKsParentReturnToStock($ksItem = null)
    {
        if ($ksItem !== null) {
            if ($ksItem->getCreditmemo()->getOrder()->hasCanReturnToStock()) {
                return $ksItem->getCreditmemo()->getOrder()->getCanReturnToStock();
            }
        } elseif ($this->getOrder()->hasCanReturnToStock()) {
            return $this->getOrder()->getCanReturnToStock();
        }
        return $this->canKsReturnToStock();
    }

    /**
     * Check allow to send new credit memo email
     *
     * @return bool
     */
    public function canSendKsCreditmemoEmail()
    {
        return $this->ksSalesData->canSendNewCreditmemoEmail($this->getKsOrder()->getStore()->getId());
    }


    /**
     * @return bool
     */
    public function canSendKsCommentEmail()
    {
        return $this->_salesData->canSendCreditmemoCommentEmail(
            $this->getKsOrder()->getStore()->getId()
        );
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
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(CreditmemoCommentInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function ksFormatPrice($price)
    {
        return $this->getKsOrder()->formatPrice($price);
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('multivendor/order_creditmemo/addComment');
    }

    /**
     * Get comment collection for the creditmemo
     *
     * @return Object
     */
    public function getKsComments()
    {
        if ($this->getKsCreditMemo()->getKsApprovalStatus()) {
            $ksEntityId = $this->ksOrderHelper->getCreditMemoId($this->getKsCreditMemo()->getEntityId());
            return $this->ksCommentCollectionFactory->create()->addFieldToFilter('parent_id', $ksEntityId)->addAttributeToSort('entity_id', 'DESC');
            ;
        } else {
            return $this->getKsCreditmemo();
        }
    }

    /**
     * @return bool
     */
    public function ksCheckNotesAndNotifyCreditMemo()
    {
        return $this->ksOrderHelper->checkNotesAndNotifyCreditMemo()&& $this->ksSalesData->canSendCreditmemoCommentEmail(
            $this->getKsOrder()->getStore()->getId()
        );
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
     * @return \Magento\Sales\Order\Creditmemo\Item
     */
    public function getKsSalesMemoItem($ksMemoIncrId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesMemoItem($ksMemoIncrId, $ksOrderItemId);
    }

    /**
     * @return \Ksolves\MultivendorMarketplace\Model\KsSalesCreditmemoItem
     */
    public function getKsSalesMemoReqItem($ksReqId, $ksOrderItemId)
    {
        return $this->ksOrderHelper->getKsSalesMemoReqItem($ksReqId, $ksOrderItemId);
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
     * Get ordered product attribute
     *
     * @return array
    */
    public function getKsCreditMemoItemAttrValue($ksItemId)
    {
        $attrvalue = $this->getKsOrderItem($ksItemId);
        return $this->ksOrderHelper->getKsCreditMemoItemAttrValue($attrvalue);
    }

    /**
     * @return Boolean
     */
    public function getKsIsSellerProduct($ksProductId)
    {
        return $this->ksProductHelper->isKsSellerProduct($ksProductId);
    }
}
