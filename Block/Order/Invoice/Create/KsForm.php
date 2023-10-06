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

use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * KsForm block
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractOrder
{
    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Sales\Model\OrderRepository $ksOrderRepository
     * @param array $ksData
     * @param ShippingHelper|null $ksShippingHelper
     * @param TaxHelper|null $ksTaxHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Ksolves\MultivendorMarketplace\Block\Order\KsViewOrder $ksViewOrderBlock,
        \Magento\Sales\Model\OrderRepository $ksOrderRepository,
        array $ksData = [],
        ?ShippingHelper $ksShippingHelper = null,
        ?TaxHelper $ksTaxHelper = null
    ) {
        $ksData['taxHelper'] = $ksTaxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        $ksData['ksViewOrderBlock'] = $ksViewOrderBlock;
        parent::__construct($ksContext, $ksRegistry, $ksAdminHelper, $ksOrderRepository, $ksData, $ksShippingHelper, $ksTaxHelper);
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getKsInvoice()
    {
        return $this->ksRegistry->registry('current_invoice');
    }

    /**
     * Check shipment availability for current invoice
     *
     * @return bool
     */
    public function canKsCreateShipment()
    {
        foreach ($this->getKsInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->getQtyToShip()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check invoice shipment type mismatch
     *
     * @return bool
     */
    public function hasKsInvoiceShipmentTypeMismatch()
    {
        foreach ($this->getKsInvoice()->getAllItems() as $item) {
            if ($item->getOrderItem()->isChildrenCalculated() && !$item->getOrderItem()->isShipSeparately()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check shipment availability for partially item
     *
     * @return bool
     */
    public function canKsShipPartiallyItem()
    {
        $value = $this->getKsInvoice()->getOrder()->getCanShipPartiallyItem();
        if ($value !== null && !$value) {
            return false;
        }
        return true;
    }

    /**
     * Return forced creating of shipment flag
     *
     * @return int
     */
    public function getKsForcedShipmentCreate()
    {
        return (int)$this->getKsInvoice()->getOrder()->getForcedShipmentWithInvoice();
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getKsBackUrl()
    {
        return sprintf("Javascript:history.back();");
    }

    /**
     * Get Reset url
     *
     * @return string
     */
    public function getKsResetUrl()
    {
        return $this->getUrl('multivendor/order_invoice/new/', ['order_id'=>$this->getKsInvoice()->getOrderId()]);
    }
}
