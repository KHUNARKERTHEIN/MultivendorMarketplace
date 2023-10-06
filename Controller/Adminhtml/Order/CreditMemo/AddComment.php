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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\CreditMemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class AddComment
 */
class AddComment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Api\CreditMemoRepositoryInterface
     */
    protected $ksCreditMemoRepository;

    /**
     * @var CreditMemoCommentSender
     */
    protected $ksCreditmemoCommentSender;

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
    protected $ksSalesCreditMemo;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var CreditmemoRepositoryInterface 
     */
    protected $ksCreditmemoRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Sales\Api\CreditMemoRepositoryInterface $ksCreditmemoRepository
     * @param CreditMemoCommentSender $ksCreditmemoCommentSender
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        Action\Context $ksContext,
        CreditmemoRepositoryInterface $ksCreditmemoRepository,
        CreditmemoCommentSender $ksCreditmemoCommentSender,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksCreditmemoRepository = $ksCreditmemoRepository;
        $this->ksCreditmemoCommentSender = $ksCreditmemoCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
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
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getPost('id'));
            $ksCreditMemoId=$this->getRequest()->getParam('creditmemo_id');
            $ksSalesCreditMemoDetails = $this->ksSalesCreditMemo->load($ksCreditMemoId);
            $data = $this->getRequest()->getPost('comment');
            $ksParentId = $this->ksOrderHelper->getCreditMemoId($ksCreditMemoId);

          
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            if ($ksSalesCreditMemoDetails->getKsApprovalStatus() == $this->ksSalesCreditMemo::KS_STATUS_APPROVED){
                $creditmemo = $this->ksCreditmemoRepository->get($ksParentId);
                $creditmemo->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
                $this->ksCreditmemoRepository->save($creditmemo);

                $this->ksCreditmemoCommentSender->send($creditmemo, !empty($data['is_customer_notified']), $data['comment']);
            }else{              
                /*set creditmemo details*/ 
                $ksSalesCreditMemoDetails->setData('ks_customer_note',$data['comment']);
                $ksSalesCreditMemoDetails->setData('ks_customer_note_notify',isset($data['is_customer_notified']));     
                $ksSalesCreditMemoDetails->save();

            }
                        
            return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(["success" => true]));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
    }
}