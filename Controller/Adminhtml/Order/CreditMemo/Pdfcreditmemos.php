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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Creditmemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Model\Order\Pdf\Creditmemo;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesCreditMemo\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory as KsCreditMemoCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;

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
     * @var Creditmemo
     */
    protected $ksPdfCreditmemo;

    /**
     * @var DateTime
     */
    protected $ksDateTime;

    /**
     * @var KsCreditMemoCollectionFactory
     */
    protected $ksCreditMemoCollection;

    /**
     * @var KsSalesCreditMemo;
     */
    protected $ksSalesCreditMemo;

    /**
     * @param Context $ksContext
     * @param Filter $filter
     * @param Creditmemo $ksPdfCreditmemo
     * @param DateTime $ksDateTime
     * @param FileFactory $ksFileFactory
     * @param CollectionFactory $collectionFactory
     * @param KsCreditMemoCollectionFactory $ksCreditMemoCollection,
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     */
    public function __construct(
        Context $ksContext,
        Filter $filter,
        Creditmemo $ksPdfCreditmemo,
        DateTime $ksDateTime,
        FileFactory $ksFileFactory,
        CollectionFactory $collectionFactory,
        KsCreditMemoCollectionFactory $ksCreditMemoCollection,
        KsSalesCreditMemo $ksSalesCreditMemo
    ) {
        $this->ksPdfCreditmemo = $ksPdfCreditmemo;
        $this->ksDateTime = $ksDateTime;
        $this->ksFileFactory = $ksFileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->ksCreditMemoCollection = $ksCreditMemoCollection;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        parent::__construct($ksContext, $filter);
    }

    /**
     * @param AbstractCollection $ksCollection
     * @return ResponseInterface
     * @throws \Exception
     * @throws \Zend_Pdf_Exception
     */
    public function massAction(AbstractCollection $collection)
    {
        $collection = $collection->addFieldToFilter('ks_approval_status',$this->ksSalesCreditMemo::KS_STATUS_APPROVED)->addFieldToSelect('ks_creditmemo_increment_id')->getData();
        $creditmemoCollection = $this->ksCreditMemoCollection->create()->addFieldToFilter('increment_id',['in' => $collection]);
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
