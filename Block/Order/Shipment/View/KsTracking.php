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

namespace Ksolves\MultivendorMarketplace\Block\Order\Shipment\View;

use Magento\Framework\App\ObjectManager;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;

/**
 * KsTracking block
 */
class KsTracking extends \Ksolves\MultivendorMarketplace\Block\Order\KsTracking
{
    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Magento\Shipping\Model\Config $ksShippingConfig
     * @param \Magento\Framework\Registry $ksRegistry
     * @param array $ksData
     * @param ShippingHelper|null $ksShippingHelper
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Shipping\Model\Config $ksShippingConfig,
        \Magento\Framework\Registry $ksRegistry,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        array $ksData = [],
        ?ShippingHelper $ksShippingHelper = null
    ) {
        $ksData['shippingHelper'] = $ksShippingHelper ?? ObjectManager::getInstance()->get(ShippingHelper::class);
        parent::__construct($ksContext, $ksShippingConfig, $ksRegistry, $ksData);
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
    public function getKsTracks($ksSalesShipmentId)
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
}
