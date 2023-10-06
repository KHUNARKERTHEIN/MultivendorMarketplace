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

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * View form
 */
class View extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;
    
    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesShipment;

    /**
     * @var KsSalesInvoiceItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $ksShipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $ksShipmentModel;

    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var Registry
     */
    protected $ksRegistry;
    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItem
     * @param ShipmentRepositoryInterface $ksShipmentRepository,
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     * @param PageFactory $ksResultPageFactory
     * @param \Magento\Sales\Model\Order\Shipment $ksShipmentModel
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        ShipmentRepositoryInterface $ksShipmentRepository,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory,
        PageFactory $ksResultPageFactory,
        \Magento\Sales\Model\Order\Shipment $ksShipmentModel,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesShipmentItem = $ksSalesShipmentItem;
        $this->ksShipmentRepository = $ksShipmentRepository;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksShipmentModel = $ksShipmentModel;
        $this->ksOrderRepository = $ksOrderRepository;
        parent::__construct($ksContext);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksShipmentId = $this->getRequest()->getParam('shipment_id');
        $ksSalesShipmentDetails = $this->ksSalesShipment->load($ksShipmentId);
        if (!count((array)$ksSalesShipmentDetails)) {
            /** @var \Magento\Framework\Controller\Result\Forward $ksResultForward */
            $ksResultForward = $this->ksResultForwardFactory->create();
            return $ksResultForward->forward('noroute');
        }
        $ksOrder = $this->ksOrderRepository->get($ksSalesShipmentDetails->getKsOrderId());
        $ksShipmentRequest = $this->ksSalesShipment->load($ksShipmentId);
        $this->ksRegistry->register('current_shipment_request', $ksShipmentRequest);
        $ksShipmentItems = $this->ksSalesShipmentItem->getCollection()->addFieldToFilter('ks_parent_id', $ksShipmentId);
        $this->ksRegistry->register('current_shipment_items', $ksShipmentItems);

        if ($ksShipmentRequest->getKsApprovalStatus() == $this->ksSalesShipment::KS_STATUS_APPROVED) {
            $this->ksRegistry->register('current_view_shipment', $this->ksShipmentModel->loadByIncrementId($ksShipmentRequest->getKsShipmentIncrementId()));
        }
        $this->ksRegistry->register('current_order', $ksOrder);
        /** @var \Magento\Backend\Model\View\Result\Page $ksResultPage */
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->setActiveMenu('Ksolves_MultivendorMarketplace::all_orders');
        $ksResultPage->getConfig()->getTitle()->prepend(__(sprintf("#%s", $ksShipmentRequest->getKsShipmentIncrementId())));
        
        return $ksResultPage;
    }
}
