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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as KsInvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

/**
 * Class Pdfinvoices
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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var KsInvoiceCollection
     */
    protected $ksInvoiceCollection;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param KsInvoiceCollection $ksInvoiceCollection
     * @param KsSalesInvoice $ksSalesInvoice,
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param Invoice $ksPdfInvoice
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        KsInvoiceCollection $ksInvoiceCollection,
        KsSalesInvoice $ksSalesInvoice,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        Invoice $ksPdfInvoice
    ) { 
        $this->collectionFactory = $collectionFactory;
        $this->ksInvoiceCollection = $ksInvoiceCollection;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfInvoice = $ksPdfInvoice;
        parent::__construct($context, $filter);
    }

    /**
     * Print invoices for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $ksCollection)
    {
        $ksOrderId =[];
        foreach ($ksCollection as $ksOrder) {
            array_push($ksOrderId, $ksOrder->getKsOrderId());
        }
        $ksSalesInvoices = $this->ksSalesInvoice->getCollection()->addFieldToFilter('ks_order_id',['in'=> $ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesInvoice::KS_STATUS_APPROVED)->addFieldToSelect('ks_invoice_increment_id');
        
        $invoicesCollection = $this->ksInvoiceCollection->create()->addFieldToFilter('increment_id',['in' => $ksSalesInvoices->getData()]);
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