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

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultInterface;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as KsShipmentCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsShipmentListing\CollectionFactory;

/**
 * MassPrintShippingLabel
 */
class MassPrintShippingLabel extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var LabelGenerator
     */
    protected $ksLabelGenerator;

    /**
     * @var FileFactory
     */
    protected $ksFileFactory;

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
     * @param FileFactory $ksFileFactory
     * @param LabelGenerator $ksLabelGenerator
     * @param CollectionFactory $collectionFactory
     * @param KsShipmentCollection $ksShipmentCollection
     * @param KsSalesShipment $ksSalesShipment
     */
    public function __construct(
        Context $ksContext,
        Filter $filter,
        FileFactory $ksFileFactory,
        LabelGenerator $ksLabelGenerator,
        CollectionFactory $collectionFactory,
        KsShipmentCollection $ksShipmentCollection,
        KsSalesShipment $ksSalesShipment
    ) {
        $this->ksFileFactory = $ksFileFactory;
        $this->ksLabelGenerator = $ksLabelGenerator;
        $this->collectionFactory = $collectionFactory;
        $this->ksShipmentCollection = $ksShipmentCollection;
        $this->ksSalesShipment = $ksSalesShipment;
        parent::__construct($ksContext, $filter);
    }

    /**
     * Batch print shipping labels for whole shipments.
     * Push pdf document with shipping labels to user browser
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $collection = $collection->addFieldToFilter('ks_approval_status',$this->ksSalesShipment::KS_STATUS_APPROVED)->addFieldToSelect('ks_shipment_increment_id')->getData();
        $ksShipmentsCollection = $this->ksShipmentCollection
            ->create()
            ->addFieldToFilter('increment_id',['in' => $collection]);
        $labelsContent = [];
        if ($ksShipmentsCollection->getSize()) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            foreach ($ksShipmentsCollection as $shipment) {
                $labelContent = $shipment->getShippingLabel();
                if ($labelContent) {
                    $labelsContent[] = $labelContent;
                }
            }
        }

        if (!empty($labelsContent)) {
            $outputPdf = $this->ksLabelGenerator->combineLabelsPdf($labelsContent);
            return $this->ksFileFactory->create(
                'ShippingLabels.pdf',
                $outputPdf->render(),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        }

        $this->messageManager->addError(__('There are no shipping labels related to selected shipments.'));
        return $this->resultRedirectFactory->create()->setPath('sales/shipment/');
    }
}
