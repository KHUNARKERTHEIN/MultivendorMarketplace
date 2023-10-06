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

/**
 * KsTracking block
 */
class KsTracking extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $ksShippingConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Shipping\Model\Config $ksShippingConfig
     * @param \Magento\Framework\Registry $ksRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Shipping\Model\Config $ksShippingConfig,
        \Magento\Framework\Registry $ksRegistry,
        array $data = []
    ) {
        $this->ksShippingConfig = $ksShippingConfig;
        $this->ksRegistry = $ksRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->ksRegistry->registry('current_shipment_request');
    }

    /**
     * Retrieve shipment items
     *
     * @return Object
     */
    public function getShipmentItems()
    {
        return $this->ksRegistry->registry('current_shipment_items');
    }

    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }

    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->ksShippingConfig->getAllCarriers($this->getShipment()->getStoreId());
    }

    /**
     * Retrieve shipment approval status
     *
     * @return Boolean
     */
    public function isKsShipmentApproved()
    {
        return $this->getShipment()->getKsApprovalStatus() == \Ksolves\MultivendorMarketplace\Model\KsSalesShipment::KS_STATUS_APPROVED;
    }
}
