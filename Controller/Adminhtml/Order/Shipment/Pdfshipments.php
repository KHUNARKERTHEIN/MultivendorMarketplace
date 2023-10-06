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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as KsShipmentCollection;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsShipmentListing\CollectionFactory;


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
     * @var KsShipmentCollection
     */
    protected $ksShipmentCollection;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @param Context $ksContext
     * @param Filter $filter
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param Shipment $ksPdfShipment
     * @param CollectionFactory $collectionFactory
     * @param KsShipmentCollection $ksShipmentCollection
     * @param KsSalesShipment $ksSalesShipment
     */
    public function __construct(
        Context $ksContext,
        Filter $filter,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        Shipment $ksPdfShipment,
        CollectionFactory $collectionFactory,
        KsShipmentCollection $ksShipmentCollection,
        KsSalesShipment $ksSalesShipment
    ) {
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfShipment = $ksPdfShipment;
        $this->collectionFactory = $collectionFactory;
        $this->ksShipmentCollection = $ksShipmentCollection;
        $this->ksSalesShipment = $ksSalesShipment;
        parent::__construct($ksContext, $filter);
    }

    /**
     * @param AbstractCollection $collection
     * @return $this|ResponseInterface
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        $collection = $collection->addFieldToFilter('ks_approval_status',$this->ksSalesShipment::KS_STATUS_APPROVED)->addFieldToSelect('ks_shipment_increment_id')->getData();
        $ksShipmentsCollection = $this->ksShipmentCollection
            ->create()
            ->addFieldToFilter('increment_id',['in' => $collection]);
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
