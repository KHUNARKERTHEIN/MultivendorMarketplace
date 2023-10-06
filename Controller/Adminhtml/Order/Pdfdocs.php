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
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as KsShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as KsInvoiceCollection;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as KsCreditmemoCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;

/**
 * Class Pdfdocs
 */
class Pdfdocs extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $ksFileFactory;

    /**
     * @var Invoice
     */
    protected $ksPdfInvoice;

    /**
     * @var Shipment
     */
    protected $ksPdfShipment;

    /**
     * @var Creditmemo
     */
    protected $ksPdfCreditmemo;

    /**
     * @var DateTime
     */
    protected $ksDateTime;

    /**
     * @var KsShipmentCollectionFactory
     */
    protected $ksShipmentCollectionFactory;

    /**
     * @var KsInvoiceCollection
     */
    protected $ksInvoiceCollection;

    /**
     * @var KsCreditmemoCollectionFactory
     */
    protected $ksCreditmemoCollection;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * @var ksSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    
    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var ksSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param FileFactory $ksFileFactory
     * @param Invoice $ksPdfInvoice
     * @param Shipment $ksPdfShipment
     * @param Creditmemo $ksPdfCreditmemo
     * @param DateTime $ksDateTime
     * @param KsShipmentCollectionFactory $ksShipmentCollectionFactory
     * @param KsInvoiceCollection $ksInvoiceCollection
     * @param KsCreditmemoCollectionFactory $ksCreditmemoCollection
     * @param CollectionFactory $collectionFactory
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param KsSalesShipment $ksSalesShipment
     * @param ksSalesInvoice $ksSalesInvoice
     */
    public function __construct(
        Context $context,
        Filter $filter,
        FileFactory $ksFileFactory,
        Invoice $ksPdfInvoice,
        Shipment $ksPdfShipment,
        Creditmemo $ksPdfCreditmemo,
        DateTime $ksDateTime,
        KsShipmentCollectionFactory $ksShipmentCollectionFactory,
        KsInvoiceCollection $ksInvoiceCollection,
        KsCreditmemoCollectionFactory $ksCreditmemoCollection,
        CollectionFactory $collectionFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsSalesShipment $ksSalesShipment,
        KsSalesInvoice $ksSalesInvoice
    ) {
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfInvoice = $ksPdfInvoice;
        $this->ksPdfShipment = $ksPdfShipment;
        $this->ksPdfCreditmemo = $ksPdfCreditmemo;
        $this->ksDateTime = $ksDateTime;
        $this->ksShipmentCollectionFactory = $ksShipmentCollectionFactory;
        $this->ksInvoiceCollection = $ksInvoiceCollection;
        $this->ksCreditmemoCollection = $ksCreditmemoCollection;
        $this->collectionFactory = $collectionFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesInvoice = $ksSalesInvoice;
        parent::__construct($context, $filter);
    }

    /**
     * Print all documents for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $ksCollection)
    {
        $ksOrderId = [];
        foreach($ksCollection as $ksOrder){
            array_push($ksOrderId, $ksOrder->getKsOrderId());

        }
        $ksSalesCreditMemos=$this->ksSalesCreditMemo->getCollection()->addFieldToFilter('ks_order_id',['in',$ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToSelect('ks_creditmemo_increment_id');

        $ksCreditmemoCollection = $this->ksCreditmemoCollection->create()->addFieldToFilter('increment_id',['in' => $ksSalesCreditMemos->getData()]);

        $ksSalesShipments = $this->ksSalesShipment->getCollection()->addFieldToFilter('ks_order_id',['in',$ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesShipment :: KS_STATUS_APPROVED)->addFieldToSelect('ks_shipment_increment_id');
        $ksShipmentsCollection = $this->ksShipmentCollectionFactory
            ->create()
            ->addFieldToFilter('increment_id',['in' => $ksSalesShipments->getData()]);
        $ksSalesInvoices = $this->ksSalesInvoice->getCollection()->addFieldToFilter('ks_order_id',['in'=> $ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesInvoice::KS_STATUS_APPROVED)->addFieldToSelect('ks_invoice_increment_id');
        
        $ksInvoicesCollection = $this->ksInvoiceCollection->create()->addFieldToFilter('increment_id',['in' => $ksSalesInvoices->getData()]);


        $documents = [];
        if ($ksInvoicesCollection->getSize()) {
            $documents[] = $this->ksPdfInvoice->getPdf($ksInvoicesCollection);
        }
        if ($ksShipmentsCollection->getSize()) {
            $documents[] = $this->ksPdfShipment->getPdf($ksShipmentsCollection);
        }
        if ($ksCreditmemoCollection->getSize()) {
            $documents[] = $this->ksPdfCreditmemo->getPdf($ksCreditmemoCollection);
        }

        if (empty($documents)) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }

        $pdf = array_shift($documents);
        foreach ($documents as $document) {
            $pdf->pages = array_merge($pdf->pages, $document->pages);
        }
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->ksFileFactory->create(
            sprintf('docs%s.pdf', $this->ksDateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
