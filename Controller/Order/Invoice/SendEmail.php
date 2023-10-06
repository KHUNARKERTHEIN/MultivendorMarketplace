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

use Magento\Framework\App\Action\Action;

/**
 * SendEmail Controller
 */
class SendEmail extends Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $ksResultForwardFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $ksInvoiceRepository;
    
    /**
     * @var \Magento\Sales\Api\InvoiceManagementInterface
     */
    protected $ksInvoiceManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $ksInvoiceRepository
     * @param \Magento\Sales\Api\InvoiceManagementInterface $ksInvoiceManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Sales\Api\InvoiceRepositoryInterface $ksInvoiceRepository,
        \Magento\Sales\Api\InvoiceManagementInterface $ksInvoiceManagement
    ) {
        $this->ksInvoiceRepository = $ksInvoiceRepository;
        $this->ksInvoiceManagement = $ksInvoiceManagement;
        parent::__construct($ksContext);
    }

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$invoiceId) {
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        $invoice = $this->ksInvoiceRepository->get($invoiceId);
        if (!$invoice) {
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }

        $this->ksInvoiceManagement->notify($invoice->getEntityId());

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
