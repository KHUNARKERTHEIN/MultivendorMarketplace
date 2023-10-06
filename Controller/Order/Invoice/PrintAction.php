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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;

/**
 * Print Controller
 */
class PrintAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $ksResultForwardFactory;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\App\Response\Http\FileFactory $ksFileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksFileFactory = $ksFileFactory;
        $this->ksResultForwardFactory = $ksResultForwardFactory;
        $this->ksOrderHelper = $ksOrderHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return ResponseInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $ksInvoiceId = $this->ksOrderHelper->getInvoiceId($invoiceId);
            $invoice = $this->_objectManager->create(
                \Magento\Sales\Api\InvoiceRepositoryInterface::class
            )->get($ksInvoiceId);
            if ($invoice) {
                $pdf = $this->_objectManager->create(\Magento\Sales\Model\Order\Pdf\Invoice::class)->getPdf([$invoice]);
                $date = $this->_objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
                $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

                return $this->ksFileFactory->create(
                    'invoice' . $date . '.pdf',
                    $fileContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } else {
            return $this->ksResultForwardFactory->create()->forward('noroute');
        }
    }
}
