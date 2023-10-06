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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as KsShipmentCollectionFactory;

/**
 * Class Pdfshipments
 */
class Pdfshipments extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
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
     * @var Shipment
     */
    protected $ksPdfShipment;

    /**
     * @var KsShipmentCollectionFactory
     */
    protected $ksShipmentCollectionFactory;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param Shipment $ksPdfShipment
     * @param KsShipmentCollectionFactory $ksShipmentCollectionFactory
     * @param KsSalesShipment $ksSalesShipment
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        Shipment $ksPdfShipment,
        KsShipmentCollectionFactory $ksShipmentCollectionFactory,
        KsSalesShipment $ksSalesShipment
    ) { 
        $this->collectionFactory = $collectionFactory;
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfShipment = $ksPdfShipment;
        $this->ksShipmentCollectionFactory = $ksShipmentCollectionFactory;
        $this->ksSalesShipment = $ksSalesShipment;
        parent::__construct($context, $filter);
    }

    /**
     * Print shipments for selected orders
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
        $ksSalesShipments = $this->ksSalesShipment->getCollection()->addFieldToFilter('ks_order_id',['in',$ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesShipment :: KS_STATUS_APPROVED)->addFieldToSelect('ks_shipment_increment_id');
        $ksShipmentsCollection = $this->ksShipmentCollectionFactory
            ->create()
            ->addFieldToFilter('increment_id',['in' => $ksSalesShipments->getData()]);
        if (!$ksShipmentsCollection->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }

        $pdf = $this->ksPdfShipment->getPdf($ksShipmentsCollection->getItems());
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->ksFileFactory->create(
            sprintf('packingslip%s.pdf', $this->ksDateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
