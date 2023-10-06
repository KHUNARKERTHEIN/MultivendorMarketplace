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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\App\Action\Action;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;

/**
 * Class RemoveTrack
 */
class RemoveTrack extends Action
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $ksShipmentLoader;

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
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    protected $ksTrack;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $ksTrack
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsOrderHelper $ksOrderHelper,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $ksTrack
    ) {
        $this->ksShipmentLoader = $ksShipmentLoader;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksTrack = $ksTrack->create();
        parent::__construct($ksContext);
    }

    /**
     * Remove tracking number from shipment
     *
     * @return void
     */
    public function execute()
    {
        $ksTrackId = $this->getRequest()->getParam('track_id');
        $ksSalesShipmentId = $this->getRequest()->getParam('sales_shipment_id');
        $ksResponse =array();
        if ($this->ksOrderHelper->getShipmentApprovalStatus($ksSalesShipmentId)) {
            /** @var \Magento\Sales\Model\Order\Shipment\Track $ksTrack */
            $ksTrack = $this->ksTrack->load($ksTrackId);
            if ($ksTrack->getId()) {
                try {
                    $ksShipmentId = $this->ksOrderHelper->getShipmentId($ksSalesShipmentId);
                    $this->ksShipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                    $this->ksShipmentLoader->setShipmentId($ksShipmentId);
                    $ksShipment = $this->ksShipmentLoader->load();
                    if ($ksShipment) {
                        $ksTrack->delete();
                        $ksResponse = [];
                    } else {
                        $ksResponse = [
                            'error' => true,
                            'message' => __('We can\'t initialize shipment for delete tracking number.'),
                        ];
                    }
                } catch (\Exception $e) {
                    $ksResponse = ['error' => true, 'message' => __('We can\'t delete tracking number.')];
                }
            } else {
                $ksResponse = [
                    'error' => true,
                    'message' => __('We can\'t load track with retrieving identifier right now.')
                ];
            }
        } else {
            $this->ksSalesShipmentTrack->load($ksTrackId)->delete();
        }
        
        if (is_array($ksResponse)) {
            $ksResponse = $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($ksResponse);
            $this->getResponse()->representJson($ksResponse);
        } else {
            $this->getResponse()->setBody($ksResponse);
        }
    }
}
