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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sales\Model\Order\Shipment\Comment;
/**
 * Class AddComment
 */
class AddComment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $ksShipmentLoader;

    /**
     * @var ShipmentCommentSender
     */
    protected $shipmentCommentSender;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $ksResultRawFactory;

    /**
     * @var KsSalesCreditMemo
     */
    protected $ksSalesShipment;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsCommentInvoice
     */
    protected $ksCommentShipment;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Sales\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader
     * @param ShipmentCommentSender $shipmentCommentSender
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory
     * @param KsSalesShipment $ksSalesShipment
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param Comment $ksCommentShipment
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $ksShipmentLoader,
        ShipmentCommentSender $shipmentCommentSender,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\Controller\Result\RawFactory $ksResultRawFactory,
        KsSalesShipment $ksSalesShipment,
        KsOrderHelper $ksOrderHelper,
        KsSellerHelper $ksSellerHelper,
        Comment $ksCommentShipment
    ) {
        $this->ksShipmentLoader = $ksShipmentLoader;
        $this->shipmentCommentSender = $shipmentCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksResultRawFactory = $ksResultRawFactory;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksOrderHelper = $ksOrderHelper; 
        $this->ksSellerHelper = $ksSellerHelper; 
        $this->ksCommentShipment = $ksCommentShipment;    
        parent::__construct($ksContext);
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
            $this->getRequest()->setParam('shipment_id', $this->getRequest()->getPost('id'));
            $ksShipmentId=$this->getRequest()->getParam('shipment_id');
            $ksSalesShipmentDetails = $this->ksSalesShipment->load($ksShipmentId);
            $data = $this->getRequest()->getPost('comment');
            $ksParentId = $this->ksOrderHelper->getShipmentId($ksShipmentId);
           
          
            if (empty($data['comment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The comment is missing. Enter and try again.')
                );
            }
            if ($ksSalesShipmentDetails->getKsApprovalStatus() == $this->ksSalesShipment::KS_STATUS_APPROVED){
                $this->ksShipmentLoader->setShipmentId($ksParentId);
                $shipment = $this->ksShipmentLoader->load();
                if (!empty($data['comment'])){
                    $comment = $shipment->addComment(
                        $data['comment'],
                        isset($data['is_customer_notified']),
                        isset($data['is_visible_on_front'])
                    );
            }
                $comment->save();

                $LastComment = current($comment->getCommentsCollection()->getData());
                $this->ksCommentShipment->load($LastComment['entity_id'])->setData('ks_seller_id',$ksSellerId)->save();   
            $this->shipment->send($shipment, !empty($data['is_customer_notified']), $data['comment']);
        }else{              
            /*set shipment details*/ 
            $ksSalesShipmentDetails->setData('ks_customer_note',$data['comment']);
            $ksSalesShipmentDetails->setData('ks_customer_note_notify',isset($data['is_customer_notified']));     
            $ksSalesShipmentDetails->save();

        }
        /*set html for response*/
        $html = $this->resultPageFactory->create()->getLayout()
        ->createBlock('Ksolves\MultivendorMarketplace\Block\Order\Shipment\View\CommentsHistory')
        ->setTemplate('Ksolves_MultivendorMarketplace::order/shipment/view/comments.phtml')
        ->toHtml();
        
        return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($html));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
    }
}