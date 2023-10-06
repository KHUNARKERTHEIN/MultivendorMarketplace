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

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesInvoice\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as KsInvoiceCollection;

/**
 * Pdfinvoices
 */
class Pdfinvoices extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var DateTime
     */
    protected $ksDateTime;

    /**
     * @var Invoice
     */
    protected $ksPdfInvoice;

    /**
     * @var KsInvoiceCollection
     */
    protected $ksInvoiceCollection;

    /**
     * @param Context $ksContext
     * @param Filter $filter
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param Invoice $ksPdfInvoice
     * @param CollectionFactory $collectionFactory
     * @param KsInvoiceCollection $ksInvoiceCollection
     */
    public function __construct(
        Context $ksContext,
        Filter $filter,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        Invoice $ksPdfInvoice,
        KsInvoiceCollection $ksInvoiceCollection,
        CollectionFactory $collectionFactory
    ) {
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfInvoice = $ksPdfInvoice;
        $this->collectionFactory = $collectionFactory;
        $this->ksInvoiceCollection = $ksInvoiceCollection;
        parent::__construct($ksContext, $filter);
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        $collection = $collection->addFieldToFilter('ks_approval_status',\Ksolves\MultivendorMarketplace\Model\KsSalesInvoice::KS_STATUS_APPROVED)->addFieldToSelect('ks_invoice_increment_id')->getData();
        $invoicesCollection = $this->ksInvoiceCollection->create()->addFieldToFilter('increment_id',['in' => $collection]);
        if (!$invoicesCollection->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
        $pdf = $this->ksPdfInvoice->getPdf($invoicesCollection->getItems());
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->ksFileFactory->create(
            sprintf('invoice%s.pdf', $this->ksDateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
        
    }
}
