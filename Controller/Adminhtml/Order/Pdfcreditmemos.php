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
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesOrder\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as KsCreditmemoCollection;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

/**
 * Class Pdfcreditmemos
 */
class Pdfcreditmemos extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\AbstractMassAction
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
     * @var Creditmemo
     */
    protected $ksPdfCreditmemo;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var KsCreditmemoCollection
     */
    protected $ksCreditmemoCollection;

    /**
     * @var ksSalesCreditMemo
     */
    protected $ksSalesCreditMemo;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param Creditmemo $ksPdfCreditmemo
     * @param KsCreditmemoCollection $ksCreditmemoCollection
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        Creditmemo $ksPdfCreditmemo,
        KsCreditmemoCollection $ksCreditmemoCollection,
        KsSalesCreditMemo $ksSalesCreditMemo
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->ksPdfCreditmemo = $ksPdfCreditmemo;
        $this->ksCreditmemoCollection = $ksCreditmemoCollection;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        parent::__construct($context, $filter);
    }

    /**
     * Print credit memos for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $ksCollection)
    {
        $ksOrderId = [];
        foreach($ksCollection as $ksOrder){
            array_push($ksOrderId, $ksOrder->getKsOrderId());

        }
        $ksSalesCreditMemos=$this->ksSalesCreditMemo->getCollection()->addFieldToFilter('ks_order_id',['in',$ksOrderId])->addFieldToFilter('ks_approval_status',$this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToSelect('ks_creditmemo_increment_id');
        $creditmemoCollection = $this->ksCreditmemoCollection->create()->addFieldToFilter('increment_id',['in' => $ksSalesCreditMemos->getData()]);
        if (!$creditmemoCollection->getSize()) {
            $this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }
        $pdf = $this->ksPdfCreditmemo->getPdf($creditmemoCollection->getItems());
        $fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

        return $this->ksFileFactory->create(
            sprintf('creditmemo%s.pdf', $this->ksDateTime->date('Y-m-d_H-i-s')),
            $fileContent,
            DirectoryList::VAR_DIR,
            'application/pdf'
        );
    }
}
