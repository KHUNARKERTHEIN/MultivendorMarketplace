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

namespace Ksolves\MultivendorMarketplace\Block\Order;

use Magento\Sales\Model\Order;
use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * KsAbstractOrder block
 */
class KsAbstractOrder extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * Admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $ksAdminHelper;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $ksOrderRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Sales\Model\OrderRepository $ksOrderRepository
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Sales\Model\OrderRepository $ksOrderRepository,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        $this->ksAdminHelper = $ksAdminHelper;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository;
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        parent::__construct($ksContext, $data);
    }

    /**
     * Retrieve available order
     *
     * @return Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getKsOrder()
    {
        return $this->ksRegistry->registry('current_order');
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
            return $this->getOrder();
        }
        return $obj;
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
     * Display prices
     *
     * @param float $basePrice
     * @param float $price
     * @param bool $strong
     * @param string $separator
     * @return string
     */
    public function displayPrices($basePrice, $price, $strong = false, $separator = '<br/>')
    {
        return $this->ksAdminHelper->displayPrices(
            $this->getPriceDataObject(),
            $basePrice,
            $price,
            $strong,
            $separator
        );
    }

    /**
     * Retrieve order totals block settings
     *
     * @return array
     */
    public function getOrderTotalData()
    {
        return [];
    }

    /**
     * Retrieve order info block settings
     *
     * @return array
     */
    public function getOrderInfoData()
    {
        return [];
    }

    /**
     * Retrieve subtotal price include tax html formatted content
     *
     * @param \Magento\Framework\DataObject $order
     * @return string
     */
    public function displayShippingPriceInclTax($order)
    {
        $shipping = $order->getShippingInclTax();
        if ($shipping) {
            $baseShipping = $order->getBaseShippingInclTax();
        } else {
            $shipping = $order->getShippingAmount() + $order->getShippingTaxAmount();
            $baseShipping = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
        }
        return $this->displayPrices($baseShipping, $shipping, false, ' ');
    }
}
