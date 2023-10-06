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

use Magento\Framework\App\ObjectManager;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Shipment Form block
 */
class KsForm extends \Ksolves\MultivendorMarketplace\Block\Order\KsAbstractOrder
{
    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getKsShipment()
    {
        return $this->ksRegistry->registry('current_shipment');
    }

    /**
     * Return payment html.
     *
     * @return string
     */
    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    /**
     * Generate save url.
     *
     * @return string
     */
    public function getKsSaveUrl()
    {
        return $this->getUrl('multivendor/order_shipment/save', ['order_id' => $this->getKsShipment()->getOrderId()]);
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
        return $this->getUrl('multivendor/order_shipment/new/', ['order_id'=>$this->getKsShipment()->getOrderId()]);
    }
}
