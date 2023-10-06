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
use Magento\Sales\Model\Order\Item;
use Magento\Catalog\Model\Product\Type\AbstractType;

/**
 * KsSalesOrderItem Model Class
 */
class KsSalesOrderItem extends AbstractModel
{

    /**
     * @var Item
     */
    protected $ksOrderItem;

    /**
     * @param ModelContext $ksContext
     * @param Registry $ksRegistry
     * @param AbstractResource|null $ksResource
     * @param AbstractDb|null $ksResourceCollection
     * @param Item $ksOrderItem
     * @param array $ksData
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ModelContext $ksContext,
        Registry $ksRegistry,
        Item $ksOrderItem,
        AbstractResource $ksResource = null,
        AbstractDb $ksResourceCollection = null,
        array $ksData = []
    ) {
        $this->ksOrderItem = $ksOrderItem;
        parent::__construct($ksContext, $ksRegistry, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrderItem');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_order_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_order_item';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_order_item';

    /**
     * Set Order Item details
     *
     * @param Array $ksData
     */
    public function setKsOrderItem($ksData)
    {
        $this->setData($ksData)->save();
    }

    /**
     * Get order item object by order item id
     *
     * @param Object
     */
    public function loadByKsOrderItemId($ksOrderItemId)
    {
        return $this->loadByAttribute($ksOrderItemId, 'ks_sales_order_item_id');
    }

    /**
     * Get order item object by attribute
     *
     * @param Object
     */
    public function loadByAttribute($ksAttrVal, $ksAttr)
    {
        return $this->load($ksAttrVal, $ksAttr);
    }

    /**
     * Get Quantity to Ship
     *
     * @param float|integer
     */
    public function getKsQtyToShip()
    {
        $ksQty = $this->getKsQtyOrdered() - max($this->getKsQtyShipped(), $this->getKsQtyRefunded()) - $this->getKsQtyCanceled();
        return max(round($ksQty, 8), 0);
    }

    /**
     * Set Quantity Shipped
     *
     */
    public function setKsQuantityShipped($ksOrderItemId, $ksQty)
    {
        $ksItem = $this->loadByKsOrderItemId($ksOrderItemId);
        $ksShippedQty = $ksItem->getKsQtyShipped();
        $this->setKsQtyShipped($ksShippedQty+$ksQty)->save();
    }

    /**
     * Get Quantity to Invoice
     *
     * @param float|integer
     */
    public function getKsQtyToInvoice()
    {
        $ksQty = $this->getKsQtyOrdered() - $this->getKsQtyInvoiced() - $this->getKsQtyCanceled();
        return max(round($ksQty, 8), 0);
    }

    /**
     * Set Quantity Invoiced
     *
     */
    public function setKsQuantityInvoiced($ksOrderItemId, $ksQty)
    {
        $ksItem = $this->loadByKsOrderItemId($ksOrderItemId);
        $ksInvoicedQty = $ksItem->getKsQtyInvoiced();
        $this->setKsQtyInvoiced($ksInvoicedQty+$ksQty)->save();
    }

    /**
     * Get Quantity to Refund
     *
     * @return float|integer
     */
    public function getKsQtyToRefund()
    {
        $ksItem = $this->ksOrderItem->load($this->getKsSalesOrderItemId());
        if ($ksItem->getProductType()=='bundle' && !$this->isKsChildCalculated($ksItem)) {
            return $ksItem->getQtyOrdered();
        } else {
            return max($ksItem->getQtyInvoiced() - $this->getKsQtyRefunded(), 0);
        }
    }

    /**
     * Set Quantity Refunded
     *
     */
    public function setKsQuantityRefunded($ksOrderItemId, $ksQty)
    {
        $ksItem = $this->loadByKsOrderItemId($ksOrderItemId);
        $ksRefundedQty = $ksItem->getKsQtyRefunded();
        $this->setKsQtyRefunded($ksRefundedQty+$ksQty)->save();
    }

    /**
     * Get Quantity to Cancel
     *
     * @return float|integer
     */
    public function getKsQtyToCancel()
    {
        $ksQtyToCancel = min($this->getKsQtyToInvoice(), $this->getKsQtyToShip());
        return max($ksQtyToCancel, 0);
    }

    /**
     * Check if child items calculated
     *
     * @param mixed $ksItem
     * @return bool
     */
    public function isKsChildCalculated($ksItem = null)
    {
        $options = $ksItem->getProductOptions();
        if ($options) {
            if (isset($options['product_calculations'])
                && $options['product_calculations'] == AbstractType::CALCULATE_PARENT
            ) {
                return true;
            }
        }
        return false;
    }
}
