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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

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
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsSalesInvoiceItem
     */
    protected $ksSalesInvoiceItem;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $ksInvoiceModel;

    /**
     * @var orderRepository
     */
    protected $ksOrderRepository;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param Registry $ksRegistry
     * @param KsSalesInvoice $ksSalesInvoice
     * @param KsSalesInvoiceItem $ksSalesInvoiceItem
     * @param \Magento\Sales\Model\Order\Invoice $ksInvoiceModel
     * @param \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        Registry $ksRegistry,
        KsSalesInvoice $ksSalesInvoice,
        KsSalesInvoiceItem $ksSalesInvoiceItem,
        \Magento\Sales\Model\Order\InvoiceFactory $ksInvoiceModel,
        \Magento\Sales\Api\OrderRepositoryInterface $ksOrderRepository,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksSalesInvoiceItem = $ksSalesInvoiceItem;
        $this->ksInvoiceModel = $ksInvoiceModel->create();
        $this->ksOrderRepository = $ksOrderRepository;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller == 1) {
            $ksInvoiceId = $this->getRequest()->getParam('invoice_id');

            $ksSalesInvoiceDetails = $this->ksSalesInvoice->load($ksInvoiceId);
            $ksOrder = $this->ksOrderRepository->get($ksSalesInvoiceDetails->getKsOrderId());
            if (!($this->ksSellerHelper->getKsCustomerId()==$ksSalesInvoiceDetails->getKsSellerId())) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $ksInvoiceRequest = $this->ksSalesInvoice->load($ksInvoiceId);
            $this->ksRegistry->register('current_invoice_request', $ksInvoiceRequest);
            $ksInvoiceItems = $this->ksSalesInvoiceItem->getCollection()->addFieldToFilter('ks_parent_id', $ksInvoiceId);
            /*register original invoice id if invoice is approved*/
            if ($ksInvoiceRequest->getKsApprovalStatus()==$this->ksSalesInvoice::KS_STATUS_APPROVED) {
                $this->ksRegistry->register('current_invoice_id', $this->ksInvoiceModel->loadByIncrementId($ksInvoiceRequest->getKsInvoiceIncrementId())->getId());
            }
            $this->ksRegistry->register('current_invoice_items', $ksInvoiceItems);
            $this->ksRegistry->register('current_order', $ksOrder);
            
            $resultPage = $this->ksResultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
            $resultPage->getConfig()->getTitle()->prepend(__('#'.$ksInvoiceRequest->getKsInvoiceIncrementId()));
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
