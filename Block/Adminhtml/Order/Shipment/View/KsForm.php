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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\Shipment\View;

use Magento\Framework\App\ObjectManager;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Shipping\Helper\Data as ShippingHelper;

/**
 * Shipment view form
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\KsAbstractOrder
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Sales\Helper\Admin $ksAdminHelper
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param TaxHelper|null $taxHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Sales\Helper\Admin $ksAdminHelper,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        array $data = [],
        ?ShippingHelper $shippingHelper = null,
        ?TaxHelper $taxHelper = null
    ) {
        $data['taxHelper'] = $taxHelper ?? ObjectManager::getInstance()->get(TaxHelper::class);
        $this->_carrierFactory = $carrierFactory;
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        parent::__construct($context, $ksRegistry, $ksAdminHelper, $data,$shippingHelper,$taxHelper);
    }
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return  $this->_coreRegistry->registry('current_order');
    }

    /**
     * Retrieve source
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getSource()
    {
        return $this->getShipment();
    }

    /**
     * Retrieve invoice model instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment_request');
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getShipment()->getOrderId()]);
    }

    /**
     * Retrieve formatted price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->getShipment()->getOrder()->formatPrice($price);
    }

    /**
     * Get create label button html
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getCreateLabelButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('adminhtml/order_shipment/createLabel', $data);
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Create Shipping Label...'),
                'onclick' => 'packaging.showWindow();',
                'class' => 'action-create-label'
            ]
        )->toHtml();
    }

    /**
     * Get print label button html
     *
     * @return string
     */
    public function getPrintLabelButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('adminhtml/order_shipment/printLabel', $data);
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            ['label' => __('Print Shipping Label'), 'onclick' => 'setLocation(\'' . $url . '\')']
        )->toHtml();
    }

    /**
     * Check is carrier has functionality of creation shipping labels
     *
     * @return bool
     */
    public function canCreateShippingLabel()
    {
        $shippingCarrier = $this->_carrierFactory->create(
            $this->getOrder()->getShippingMethod(true)->getCarrierCode()
        );
        return $shippingCarrier && $shippingCarrier->isShippingLabelsAvailable();
    }

     /**
     * Get reject url
     *
     * @return string
     */
    public function getKsRejectUrl()
    {
        return $this->getUrl('multivendor/order_shipment/reject');
    }
}