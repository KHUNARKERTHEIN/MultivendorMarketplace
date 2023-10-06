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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

use Magento\Framework\App\Action\Action;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;

/**
 * Class RemoveTrack
 */
class RemoveTrack extends Action
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $ksTrack;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param KsSalesShipment $ksSalesShipment
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $ksTrack
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsOrderHelper $ksOrderHelper,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        KsSalesShipment $ksSalesShipment,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $ksTrack
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksTrack = $ksTrack->create();
        parent::__construct($context);
    }

    /**
     * Remove tracking number from shipment
     *
     * @return void
     */
    public function execute()
    {
        $trackId = $this->getRequest()->getParam('track_id');
        $ksShipmentId = $this->getRequest()->getParam('sales_shipment_id');
        $response='';
        
        if ($this->ksOrderHelper->getShipmentApprovalStatus($ksShipmentId)) {
            /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
            $track = $this->ksTrack->load($trackId);
            if ($track->getId()) {
                try {
                    $shipmentId = $this->ksOrderHelper->getShipmentId($ksShipmentId);
                    $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                    $this->shipmentLoader->setShipmentId($shipmentId);
                    $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
                    $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                    $shipment = $this->shipmentLoader->load();
                    if ($shipment) {
                        $track->delete();
                        $response = [];
                    } else {
                        $response = [
                            'error' => true,
                            'message' => __('We can\'t initialize shipment for delete tracking number.'),
                        ];
                    }
                } catch (\Exception $e) {
                    $response = ['error' => true, 'message' => __('We can\'t delete tracking number.')];
                }
            } else {
                $response = [
                    'error' => true,
                    'message' => __('We can\'t load track with retrieving identifier right now.')
                ];
            }
        } else {
            $this->ksSalesShipmentTrack->load($trackId)->delete();
        }
        
        if (is_array($response)) {
            $response = $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
