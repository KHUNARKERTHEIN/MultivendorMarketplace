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

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\InvoiceCommentSender;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class AddComment
 */
class AddComment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $ksInvoiceRepository;

    /**
     * @var InvoiceCommentSender
     */
    protected $invoiceCommentSender;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesInvoice;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $ksInvoiceRepository
     * @param InvoiceCommentSender $invoiceCommentSender
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param KsSalesInvoice $ksSalesInvoice
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        Action\Context $ksContext,
        InvoiceRepositoryInterface $ksInvoiceRepository,
        InvoiceCommentSender $invoiceCommentSender,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        KsSalesInvoice $ksSalesInvoice,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksInvoiceRepository = $ksInvoiceRepository;
        $this->invoiceCommentSender = $invoiceCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksOrderHelper = $ksOrderHelper;     
        parent::__construct($ksContext);
    }

    /**
     * Add comment to creditmemo history
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        try {
            $this->getRequest()->setParam('invoice_id', $this->getRequest()->getPost('id'));
            $ksInvoiceId=$this->getRequest()->getParam('invoice_id');
            $ksSalesInvoiceDetails = $this->ksSalesInvoice->load($ksInvoiceId);
            $data = $this->getRequest()->getPost('comment');
            $ksParentId = $this->ksOrderHelper->getInvoiceId($ksInvoiceId);
            /*check if comment is empty*/
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            if ($ksSalesInvoiceDetails->getKsApprovalStatus() == $this->ksSalesInvoice::KS_STATUS_APPROVED){
                $invoice = $this->ksInvoiceRepository->get($ksParentId);
                $invoice->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
                $this->ksInvoiceRepository->save($invoice);

                $this->invoiceCommentSender->send($invoice, !empty($data['is_customer_notified']), $data['comment']);
            }else{              
                /*set invoice details*/ 
                $ksSalesInvoiceDetails->setData('ks_customer_note',$data['comment']);
                $ksSalesInvoiceDetails->setData('ks_customer_note_notify',isset($data['is_customer_notified']));     
                $ksSalesInvoiceDetails->save();

            }
                        
            return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(["success" => true]));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
    }
}