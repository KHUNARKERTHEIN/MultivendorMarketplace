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

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context as ModelContext;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Sales\Api\Data\OrderItemInterface;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * KsSalesOrder Model Class
 */
class KsSalesOrder extends AbstractModel
{
    /**
     * Order Approval Statuses
     */
    const KS_STATUS_DISABLED     = 0;
    const KS_STATUS_ENABLED     = 1;

    /**
     * @var Order
     */
    protected $ksSalesOrder;

    /**
     * @var Item
     */
    protected $ksSalesOrderItem;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksSalesOrderItems;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $ksPriceCurrency;

    /**
     * @param ModelContext $ksContext
     * @param Registry $ksRegistry
     * @param AbstractResource|null $ksResource
     * @param AbstractDb|null $ksResourceCollection
     * @param Order $ksSalesOrder
     * @param Item $ksSalesOrderItem
     * @param array $ksData
     * @param PriceCurrencyInterface $ksPriceCurrency
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ModelContext $ksContext,
        Registry $ksRegistry,
        Order $ksSalesOrder,
        Item $ksSalesOrderItem,
        KsProductHelper $ksProductHelper,
        KsSalesOrderItem $ksSalesOrderItems,
        PriceCurrencyInterface $ksPriceCurrency,
        AbstractResource $ksResource = null,
        AbstractDb $ksResourceCollection = null,
        array $ksData = []
    ) {
        $this->ksSalesOrder = $ksSalesOrder;
        $this->ksSalesOrderItem = $ksSalesOrderItem;
        $this->ksSalesOrderItems = $ksSalesOrderItems;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksPriceCurrency = $ksPriceCurrency;
        parent::__construct($ksContext, $ksRegistry, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_order';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_order';

    /**
     * Prepare Order Approval statuses
     *
     * @return array
     */
    public function getKsApprovalStatus()
    {
        return [
          self::KS_STATUS_DISABLED => __('Disabled'),
          self::KS_STATUS_ENABLED => __('Enabled')
        ];
    }

    /**
     * Set Order Details
     *
     * @param Int    $ksSellerOrderData
     */
    public function setKsOrder($ksSellerOrderData)
    {
        $this->setData($ksSellerOrderData)->save();
    }

    /**
     * Order object
     *
     * @return \Magento\Model\Sales\Order
     */
    public function getKsOrder()
    {
        return $this->ksSalesOrder->load($this->getKsOrderId());
    }

    public function getKsOrderItems()
    {
        $ksOrderItems= $this->getKsOrder()->getAllItems();
        $ksSellerItems=[];
        
        foreach ($ksOrderItems as $ksItem) {
            if ($this->ksProductHelper->getKsSellerId($ksItem->getProductId())==$this->getKsSellerId()) {
                $ksitemid=$ksItem->getId();
                $ksItems=array('ks_sales_order_item'=>$this->ksSalesOrderItems->loadByKsOrderItemId($ksitemid)->getData(),'sales_order_item'=>$ksItem);
                array_push($ksSellerItems, $ksItems);
            }
        }
        return $ksSellerItems;
    }

    /**
     * Retrieve order unhold availability
     *
     * @return bool
     */
    public function ksCanUnhold()
    {
        if ($this->getKsOrder()->getActionFlag($this->ksSalesOrder::ACTION_FLAG_UNHOLD) === false || $this->ksIsPaymentReview()) {
            return false;
        }
        return $this->getKsOrder()->getState() === $this->ksSalesOrder::STATE_HOLDED;
    }

    /**
     * Check if item is refunded.
     *
     * @param OrderItemInterface $item
     * @param Array $ksItem
     * @return bool
     */
    private function isRefunded(OrderItemInterface $item, $ksItem)
    {
        return $ksItem['ks_qty_refunded'] == $item->getQtyOrdered();
    }

    /**
     * Check whether the payment is in payment review state
     * In this state order cannot be normally processed. Possible actions can be:
     * - accept or deny payment
     * - fetch transaction information
     *
     * @return bool
     */
    public function ksIsPaymentReview()
    {
        return $this->getKsOrder()->getState() === $this->ksSalesOrder::STATE_PAYMENT_REVIEW;
    }

    /**
     * Check whether order is canceled
     *
     * @return bool
     */
    public function ksIsCanceled()
    {
        return $this->getKsOrder()->getState() === $this->ksSalesOrder::STATE_CANCELED;
    }

    /**
     * Retrieve Is Virtual
     *
     * @return bool
     */
    public function ksIsVirtual()
    {
        $isVirtual=1;
        
        if ($this->getKsOrder()->getIsVirtual()) {
            return true;
        }
        
        foreach ($this->getKsOrderItems() as $ksItem) {
            if ($ksItem['sales_order_item']->getIsVirtual()!=1) {
                return 0;
            }
        }
       
        return $isVirtual;
    }

    /**
     * Retrieve order shipment availability
     *
     * @return bool
     */
    public function ksCanShip()
    {
        if ($this->ksCanUnhold() || $this->ksIsPaymentReview()) {
            return false;
        }

        if ($this->ksIsVirtual() || $this->ksIsCanceled()) {
            return false;
        }

        if ($this->getKsOrder()->getActionFlag($this->ksSalesOrder::ACTION_FLAG_SHIP) === false) {
            return false;
        }

        if ($this->getKsOrder()->canShip() === false) {
            return false;
        }

        foreach ($this->getKsOrderItems() as $item) {
            $ksItem =$item['ks_sales_order_item'];
            $ksOrderItem = $item['sales_order_item'];
            if ($ksOrderItem->getQtyOrdered()>$ksItem['ks_qty_shipped'] && !$this->isRefunded($ksOrderItem, $ksItem)&& !$ksOrderItem->getIsVirtual() && !$ksOrderItem->isDummy(true) && $ksOrderItem->getQtyCanceled()==0 && $ksOrderItem->canShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order invoice availability
     *
     * @return bool
     */
    public function ksCanInvoice()
    {
        if ($this->ksCanUnhold() || $this->ksIsPaymentReview()) {
            return false;
        }
        $state = $this->getKsOrder()->getState();
        if ($this->ksIsCanceled() || $this->ksSalesOrder === $this->ksSalesOrder::STATE_COMPLETE || $state === $this->ksSalesOrder::STATE_CLOSED) {
            return false;
        }

        if ($this->getKsOrder()->getActionFlag($this->ksSalesOrder::ACTION_FLAG_INVOICE) === false) {
            return false;
        }

        if ($this->getKsOrder()->canInvoice() === false) {
            return false;
        }

        foreach ($this->getKsOrderItems() as $item) {
            $ksItem=[];
            $ksOrderItem = $item['sales_order_item'];
            $ksItem =$item['ks_sales_order_item'];
            if ($ksItem['ks_qty_ordered'] > $ksItem['ks_qty_invoiced'] && $ksOrderItem->getQtyCanceled()==0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order credit memo (refund) availability
     *
     * @return bool
     */
    public function ksCanCreditmemo()
    {
        if ($this->getKsOrder()->hasForcedCanCreditmemo()) {
            return $this->getKsOrder()->getForcedCanCreditmemo();
        }

        if ($this->ksCanUnhold() || $this->ksIsPaymentReview() ||
            $this->ksIsCanceled() || $this->getKsOrder()->getState() === $this->ksSalesOrder::STATE_CLOSED) {
            return false;
        }

        if ($this->getKsOrder()->canCreditmemo() === false) {
            return false;
        }

        foreach ($this->getKsOrderItems() as $item) {
            $ksItem =$item['ks_sales_order_item'];
            $ksOrderItem = $item['sales_order_item'];
            if ($ksOrderItem->getQtyInvoiced() > $ksItem['ks_qty_refunded'] && !$ksOrderItem->isDummy() && $ksItem['ks_qty_refunded']< ($ksItem['ks_qty_invoiced'] - $ksOrderItem->getQtyCanceled())) {
                return true;
            }
        }
        return false;
    }
}
