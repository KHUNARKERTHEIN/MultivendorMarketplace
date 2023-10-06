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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class View
 */
class View extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Invoice\AbstractInvoice\View implements HttpGetActionInterface
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsSalesInvoiceItem
     */
    protected $ksSalesInvoiceItem;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $ksInvoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $ksInvoiceModel;

    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param KsSalesInvoice $ksSalesInvoice
     * @param KsSalesInvoiceItem $ksSalesInvoiceItem
     * @param InvoiceRepositoryInterface $ksInvoiceRepository
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param \Magento\Sales\Model\Order\Invoice $ksInvoiceModel
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        KsSalesInvoice $ksSalesInvoice,
        KsSalesInvoiceItem $ksSalesInvoiceItem,
        InvoiceRepositoryInterface $ksInvoiceRepository,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        \Magento\Sales\Model\Order\Invoice $ksInvoiceModel,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
    ) {
        $this->ksRegistry = $registry;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksSalesInvoiceItem = $ksSalesInvoiceItem;
        $this->ksInvoiceRepository = $ksInvoiceRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksInvoiceModel = $ksInvoiceModel;
        $this->ksOrderRepository = $ksOrderRepository;
        parent::__construct($context, $registry, $resultForwardFactory);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
        $ksInvoiceId = $this->getRequest()->getParam('invoice_id');
        $ksSalesInvoiceDetails = $this->ksSalesInvoice->load($ksInvoiceId);

        if (!count((array)$ksSalesInvoiceDetails)) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        $ksOrder = $this->ksOrderRepository->get($ksSalesInvoiceDetails->getKsOrderId());
        $ksInvoiceRequest = $this->ksSalesInvoice->load($ksInvoiceId);
        $this->ksRegistry->register('current_invoice_request', $ksInvoiceRequest);
        $ksInvoiceItems = $this->ksSalesInvoiceItem->getCollection()->addFieldToFilter('ks_parent_id',$ksInvoiceId);
        $this->ksRegistry->register('current_invoice_items', $ksInvoiceItems); 
        $this->ksRegistry->register('current_order',$ksOrder);        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ksolves_MultivendorMarketplace::all_orders');
        $resultPage->getConfig()->getTitle()->prepend(__(sprintf("#%s",$ksInvoiceRequest->getKsInvoiceIncrementId())));
        return $resultPage;
    }
}