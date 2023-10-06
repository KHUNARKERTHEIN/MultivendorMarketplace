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

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class View
 */
class View extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var Registry
     */
    protected $ksRegistry;
    
    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
    * @var KsSalesShipment
    */
    protected $ksSalesShipment;

    /**
     * @var KsSalesShipmentItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $ksShipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $ksShipmentModel;

    /**
     * @param Context $context
     * @param PageFactory $ksResultPageFactory
     * @param Registry $ksRegistry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItem
     * @param ShipmentRepositoryInterface $ksShipmentRepository
     * @param \Magento\Sales\Model\Order\Shipment $ksShipmentModel
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        Registry $ksRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        ShipmentRepositoryInterface $ksShipmentRepository,
        \Magento\Sales\Model\Order\Shipment $ksShipmentModel
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesShipmentItem = $ksSalesShipmentItem;
        $this->ksShipmentRepository = $ksShipmentRepository;
        $this->ksShipmentModel = $ksShipmentModel;
        parent::__construct($ksContext);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $ksSalesShipmentDetails = $this->ksSalesShipment->load($shipmentId);
        $ksOrder = $this->ksOrderRepository->get($ksSalesShipmentDetails->getKsOrderId());
        $ksShipmentRequest = $this->ksSalesShipment->load($shipmentId);
        $this->ksRegistry->register('current_shipment_id', $shipmentId);
        $this->ksRegistry->register('current_shipment_request', $ksShipmentRequest);
        
        if ($ksShipmentRequest->getKsApprovalStatus() == $this->ksSalesShipment::KS_STATUS_APPROVED) {
            $this->ksRegistry->register('current_view_shipment', $this->ksShipmentModel->loadByIncrementId($ksShipmentRequest->getKsShipmentIncrementId()));
        }
        
        $ksShipmentItems = $this->ksSalesShipmentItem->getCollection()->addFieldToFilter('ks_parent_id', $shipmentId);
        $this->ksRegistry->register('current_shipment_items', $ksShipmentItems);
        $this->ksRegistry->register('current_order', $ksOrder);
        $resultPage = $this->ksResultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Shipment'));
        $resultPage->getConfig()->getTitle()->prepend(__('#'.$ksShipmentRequest->getKsShipmentIncrementId()));
        
        return $resultPage;
    }
}
