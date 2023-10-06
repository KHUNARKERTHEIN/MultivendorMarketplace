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

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo\Item;

/**
 * KsAbstract items renderer
 */
class KsAbstractItems extends \Magento\Framework\View\Element\Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * Renderers for other column with column name key
     * block    => the block name
     * template => the template file
     * renderer => the block object
     *
     * @var array
     */
    protected $_columnRenders = [];

    /**
     * Flag - if it is set method canEditQty will return value of it
     *
     * @var bool|null
     */
    protected $_canEditQty;

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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration
     * @param \Magento\Framework\Registry $ksRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $ksStockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $ksStockConfiguration,
        \Magento\Framework\Registry $ksRegistry,
        array $data = []
    ) {
        $this->ksStockRegistry = $ksStockRegistry;
        $this->ksStockConfiguration = $ksStockConfiguration;
        $this->ksRegistry = $ksRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Add column renderers
     *
     * @param array $blocks
     * @return $this
     */
    public function setColumnRenders(array $blocks)
    {
        foreach ($blocks as $blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block->getRenderedBlock() === null) {
                $block->setRenderedBlock($this);
            }
            $this->_columnRenders[$blockName] = $block;
        }
        return $this;
    }

    /**
     * Retrieve rendered item html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getItemHtml(\Magento\Framework\DataObject $item)
    {
        if ($item->getOrderItem()) {
            $type = $item->getOrderItem()->getProductType();
        } else {
            $type = $item->getProductType();
        }

        return $this->getItemRenderer($type)->setItem($item)->setCanEditQty($this->canEditQty())->toHtml();
    }

    /**
     * Retrieve rendered item extra info html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getItemExtraInfoHtml(\Magento\Framework\DataObject $item)
    {
        $extraInfoBlock = $this->getChildBlock('order_item_extra_info');
        if ($extraInfoBlock) {
            return $extraInfoBlock->setItem($item)->toHtml();
        }
        return '';
    }

    /**
     * Get credit memo
     *
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->ksRegistry->registry('current_creditmemo');
    }

    /**
     * ######################### SALES ##################################
     */

    /**
     * Retrieve available order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->ksRegistry->registry('current_order')) {
            return $this->ksRegistry->registry('current_order');
        }
        if ($this->ksRegistry->registry('order')) {
            return $this->ksRegistry->registry('order');
        }
        if ($this->getInvoice()) {
            return $this->getInvoice()->getOrder();
        }
        if ($this->getCreditmemo()) {
            return $this->getCreditmemo()->getOrder();
        }
        if ($this->getItem()->getOrder()) {
            return $this->getItem()->getOrder();
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    /**
     * Retrieve price data object
     *
     * @return Order
     */
    public function getPriceDataObject()
    {
        $obj = $this->getData('price_data_object');
        if ($obj === null) {
            return $this->getOrder();
        }
        return $obj;
    }

    /**
     * Retrieve price attribute html content
     *
     * @param string $code
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPriceAttribute($code, $strong = false, $separator = '<br />')
    {
        if ($code == 'tax_amount' && $this->getOrder()->getRowTaxDisplayPrecision()) {
            return $this->displayRoundedPrices(
                $this->getPriceDataObject()->getData('base_' . $code),
                $this->getPriceDataObject()->getData($code),
                $this->getOrder()->getRowTaxDisplayPrecision(),
                $strong,
                $separator
            );
        } else {
            return $this->displayPrices(
                $this->getPriceDataObject()->getData('base_' . $code),
                $this->getPriceDataObject()->getData($code),
                $strong,
                $separator
            );
        }
    }

    /**
     * Retrieve price formatted html content
     *
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br />')
    {
        return $this->displayRoundedPrices($basePrice, $price, 2, $strong, $separator);
    }

    /**
     * Display base and regular prices with specified rounding precision
     *
     * @param float $basePrice
     * @param float $price
     * @param int $precision
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayRoundedPrices($basePrice, $price, $precision = 2, $strong = false, $separator = '<br />')
    {
        if ($this->getOrder()->isCurrencyDifferent()) {
            $res = '';
            $res .= $this->getOrder()->formatBasePricePrecision($basePrice, $precision);
            $res .= $separator;
            $res .= $this->getOrder()->formatPricePrecision($price, $precision, true);
        } else {
            $res = $this->getOrder()->formatPricePrecision($price, $precision);
            if ($strong) {
                $res = '<strong>' . $res . '</strong>';
            }
        }
        return $res;
    }

    /**
     * Retrieve tax calculation html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function displayTaxCalculation(\Magento\Framework\DataObject $item)
    {
        if ($item->getTaxPercent() && $item->getTaxString() == '') {
            $percents = [$item->getTaxPercent()];
        } elseif ($item->getTaxString()) {
            $percents = explode(\Magento\Tax\Model\Config::CALCULATION_STRING_SEPARATOR, $item->getTaxString());
        } else {
            return '0%';
        }

        foreach ($percents as &$percent) {
            $percent = sprintf('%.2f%%', $percent);
        }
        return implode(' + ', $percents);
    }

    /**
     * Retrieve tax with percent html content
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function displayTaxPercent(\Magento\Framework\DataObject $item)
    {
        if ($item->getTaxPercent()) {
            return sprintf('%s%%', $item->getTaxPercent() + 0);
        } else {
            return '0%';
        }
    }

    /**
     *  INVOICES
     */

    /**
     * Check shipment availability for current invoice
     *
     * @return bool
     */
    public function canCreateShipment()
    {
        foreach ($this->getInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->getQtyToShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Setter for flag _canEditQty
     *
     * @param bool $value
     * @return $this
     * @see self::_canEditQty
     * @see self::canEditQty
     */
    public function setCanEditQty($value)
    {
        $this->_canEditQty = $value;
        return $this;
    }

    /**
     * Check availability to edit quantity of item
     *
     * @return bool
     */
    public function canEditQty()
    {
        /**
         * If parent block has set
         */
        if ($this->_canEditQty !== null) {
            return $this->_canEditQty;
        }

        /**
         * Disable editing of quantity of item if creating of shipment forced
         * and ship partially disabled for order
         */
        if ($this->getOrder()->getForcedShipmentWithInvoice()
            && ($this->canShipPartially($this->getOrder()) || $this->canShipPartiallyItem($this->getOrder()))
        ) {
            return false;
        }
        if ($this->getOrder()->getPayment()->canCapture()) {
            return $this->getOrder()->getPayment()->canCapturePartial();
        }
        return true;
    }

    /**
     * Check capture availability
     *
     * @return bool
     */
    public function canCapture()
    {
        if ($this->_authorization->isAllowed('Magento_Sales::capture')) {
            return $this->getInvoice()->canCapture();
        }
        return false;
    }

    /**
     * Retrieve formatted price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getOrder()->formatPrice($price);
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getInvoice();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->ksRegistry->registry('current_invoice');
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function canReturnToStock($store = null)
    {
        return $this->ksStockConfiguration->canSubtractQty($store);
    }

    /**
     * Whether to show 'Return to stock' checkbox for item
     *
     * @param Item $item
     * @return bool
     */
    public function canReturnItemToStock($item = null)
    {
        if (null !== $item) {
            if (!$item->hasCanReturnToStock()) {
                $stockItem = $this->ksStockRegistry->getStockItem(
                    $item->getOrderItem()->getProductId(),
                    $item->getOrderItem()->getStore()->getWebsiteId()
                );
                $item->setCanReturnToStock($stockItem->getManageStock());
            }
            return $item->getCanReturnToStock();
        }

        return $this->canReturnToStock();
    }

    /**
     * Whether to show 'Return to stock' column for item parent
     *
     * @param Item $item
     * @return bool
     */
    public function canParentReturnToStock($item = null)
    {
        if ($item !== null) {
            if ($item->getCreditmemo()->getOrder()->hasCanReturnToStock()) {
                return $item->getCreditmemo()->getOrder()->getCanReturnToStock();
            }
        } elseif ($this->getOrder()->hasCanReturnToStock()) {
            return $this->getOrder()->getCanReturnToStock();
        }
        return $this->canReturnToStock();
    }

    /**
     * Return true if can ship partially
     *
     * @param Order|null $order
     * @return bool
     */
    public function canShipPartially($order = null)
    {
        if ($order === null || !$order instanceof Order) {
            $order = $this->ksRegistry->registry('current_shipment')->getOrder();
        }
        $value = $order->getCanShipPartially();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Return true if can ship items partially
     *
     * @param Order|null $order
     * @return bool
     */
    public function canShipPartiallyItem($order = null)
    {
        if ($order === null || !$order instanceof Order) {
            $order = $this->ksRegistry->registry('current_shipment')->getOrder();
        }
        $value = $order->getCanShipPartiallyItem();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Check is shipment is regular
     *
     * @return bool
     */
    public function isShipmentRegular()
    {
        if (!$this->canShipPartiallyItem() || !$this->canShipPartially()) {
            return false;
        }
        return true;
    }
}
