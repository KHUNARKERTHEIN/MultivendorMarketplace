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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Invoice\AbstractInvoice;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class View
 */
abstract class View extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ksolves_MultivendorMarketplace::all_invoice';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        InvoiceRepositoryInterface $invoiceRepository = null
    ) {
        parent::__construct($context);
        $this->registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->invoiceRepository = $invoiceRepository ?:
            ObjectManager::getInstance()->get(InvoiceRepositoryInterface::class);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        if ($this->getRequest()->getParam('invoice_id')) {
            $resultForward->setController('order_invoice')
                ->setParams(['come_from' => 'invoice'])
                ->forward('view');
        } else {
            $resultForward->forward('noroute');
        }
        return $resultForward;
    }

    /**
     * Get invoice using invoice Id from request params
     *
     * @return \Magento\Sales\Model\Order\Invoice|bool
     */
    protected function getInvoice()
    {
        try {
            $invoice = $this->invoiceRepository->get($this->getRequest()->getParam('invoice_id'));
            $this->registry->register('current_invoice', $invoice);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Invoice capturing error'));
            return false;
        }

        return $invoice;
    }
}
