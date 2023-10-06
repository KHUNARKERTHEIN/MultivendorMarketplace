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

/**
 * Class Email
 */
class Email extends \Magento\Backend\App\Action
{    
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
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
        $ks_invoice_id = $this->getRequest()->getParam('ks_invoice_id');
        if (!$invoiceId) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
        $invoice = $this->_objectManager->create(\Magento\Sales\Api\InvoiceRepositoryInterface::class)->get($invoiceId);
        if (!$invoice) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $this->_objectManager->create(
            \Magento\Sales\Api\InvoiceManagementInterface::class
        )->notify($invoice->getEntityId());

        $this->messageManager->addSuccessMessage(__('You sent the message.'));
        return $this->resultRedirectFactory->create()->setPath(
            'multivendor/order_invoice/view',
            ['invoice_id' => $ks_invoice_id]
        );
    }
}
