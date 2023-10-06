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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Creditmemo;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sales\Model\Order\Creditmemo\Comment;
/**
 * AddComment view form
 */
class AddComment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $ksCreditmemoLoader;

    /**
     * @var CreditmemoCommentSender
     */
    protected $creditmemoCommentSender;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsCommentCreditMemo
     */
    protected $ksCommentCreditMemo;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader
     * @param CreditmemoCommentSender $creditmemoCommentSender
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param KsSalesCreditMemo $ksSalesCreditMemo
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper  
     * @param KsOrderHelper $ksOrderHelper 
     * @param KsSellerHelper $ksSellerHelper
     * @param Comment $ksCommentCreditMemo       
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $ksCreditmemoLoader,
        CreditmemoCommentSender $creditmemoCommentSender,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        KsSalesCreditMemo $ksSalesCreditMemo,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        KsOrderHelper $ksOrderHelper,
        KsSellerHelper $ksSellerHelper,
        Comment $ksCommentCreditMemo
    ) {
        $this->ksCreditmemoLoader = $ksCreditmemoLoader;
        $this->creditmemoCommentSender = $creditmemoCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo; 
        $this->ksJsonHelper = $ksJsonHelper; 
        $this->ksOrderHelper = $ksOrderHelper;  
        $this->ksSellerHelper = $ksSellerHelper; 
        $this->ksCommentCreditMemo = $ksCommentCreditMemo;
        parent::__construct($context);
    }

    /**
     * Add comment to creditmemo history
     *
     * @return \Magento\Framework\Controller\Result\Raw|\Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ksSellerId=$this->ksSellerHelper->getKsCustomerId();
        try {
            $this->getRequest()->setParam('creditmemo_id', $this->getRequest()->getPost('id'));
            $ksCreditMemoId=$this->getRequest()->getParam('creditmemo_id');

            $ksSalesCreditMemoDetails = $this->ksSalesCreditMemo->load($ksCreditMemoId);
            $ksParentId = $this->ksOrderHelper->getCreditMemoId($ksCreditMemoId);
            $data = $this->getRequest()->getPost('comment');
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            if ($ksSalesCreditMemoDetails->getKsApprovalStatus() == $this->ksSalesCreditMemo::KS_STATUS_APPROVED){


                $this->ksCreditmemoLoader->setCreditmemoId($ksParentId);

                $creditmemo = $this->ksCreditmemoLoader->load();
                if (!empty($data['comment'])){
                $comment = $creditmemo->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
            }
               $comment->save();
               $LastComment = current($creditmemo->getCommentsCollection()->getData());
               $this->ksCommentCreditMemo->load($LastComment['entity_id'])->setData('ks_seller_id',$ksSellerId)->save();              
            $this->creditmemoCommentSender->send($creditmemo, !empty($data['is_customer_notified']), $data['comment']);
        }else{  

            $creditmemoData=$this->getRequest()->getPostValue();            
            if($creditmemoData['id']){
               $ksSalesCreditMemoDetails->setData('ks_customer_note',$data['comment']);
                    
                $ksSalesCreditMemoDetails->setData('ks_customer_note_notify',isset($data['is_customer_notified']));
                $ksSalesCreditMemoDetails->save();
                 
            }
        }
         /*set html for response*/
        $html = $this->resultPageFactory->create()->getLayout()
        ->createBlock('Ksolves\MultivendorMarketplace\Block\Order\CreditMemo\Create\KsItems')
        ->setTemplate('Ksolves_MultivendorMarketplace::order/creditmemo/view/comments.phtml')
        ->toHtml();
        return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($html));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
    }
}
