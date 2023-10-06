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
use Magento\Shipping\Helper\Data as ShippingHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;

/**
 * KsTracking block
 */
class KsTracking extends \Ksolves\MultivendorMarketplace\Block\Adminhtml\Order\KsTracking
{
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Shipping\Model\Config $shippingConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     * @param ShippingHelper|null $shippingHelper
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        array $data = [],
        ?ShippingHelper $shippingHelper = null
    ) {
        $data['shippingHelper'] = $shippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        parent::__construct($context, $shippingConfig, $registry, $data);
        $this->_carrierFactory = $carrierFactory;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
    }

    /**
     * Retrieve save url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('multivendor/*/addTrack/', ['shipment_id' => $this->getShipment()->getId()]);
    }

    /**
     * Retrieve remove url
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $track
     * @return string
     */
    public function getRemoveUrl($track)
    {
        return $this->getUrl(
            'multivendor/*/removetrack/',
            ['shipment_id' => $this->getShipment()->getId(), 'track_id' => $track->getId()]
        );
    }

    /**
     * Get carrier details
     *
     * @param integer $ksSalesShipmentId
     *
     * @return \Magento\Framework\Phrase|string|bool
     */
    public function getTracks($ksSalesShipmentId)
    {
        if ($this->isKsShipmentApproved()) {
            return $this->ksRegistry->registry('current_view_shipment')->getAllTracks();
        } else {
            return $this->ksSalesShipmentTrack->getCollection()->addFieldToFilter('ks_parent_id', $ksSalesShipmentId);
        }
    }

    /**
     * Retrieve shipment id
     *
     * @return integer
     */
    public function getKsShipmentId()
    {
        return $this->ksRegistry->registry('current_shipment_id');
    }

    /**
    * Get carrier title
    *
    * @param string $code
    *
    * @return \Magento\Framework\Phrase|string|bool
    */
    public function getCarrierTitle($code)
    {
        $carrier = $this->_carrierFactory->create($code);
        return $carrier ? $carrier->getConfigData('title') : __('Custom Value');
    }
}
