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
use Magento\Sales\Model\Order\Email\Sender\ShipmentCommentSender;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class AddComment
 */
class AddComment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface

     */
    protected $ksShipmentRepository;

    /**
     * @var InvoiceCommentSender
     */
    protected $shipmentCommentSender;

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
    protected $ksSalesShipment;

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
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $ksShipmentRepository
     * @param ShipmentCommentSender $shipmentCommentSender
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param KsSalesShipment $ksSalesShipment
     * @param KsOrderHelper $ksOrderHelper
     */
    public function __construct(
        Action\Context $ksContext,
        ShipmentRepositoryInterface $ksShipmentRepository,
        ShipmentCommentSender $shipmentCommentSender,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        KsSalesShipment $ksSalesShipment,
        KsOrderHelper $ksOrderHelper
    ) {
        $this->ksShipmentRepository = $ksShipmentRepository;
        $this->shipmentCommentSender = $shipmentCommentSender;
        $this->resultPageFactory = $resultPageFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->ksSalesShipment = $ksSalesShipment;
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
                $shipment = $this->ksShipmentRepository->get($ksParentId);
                $shipment->addComment(
                    $data['comment'],
                    isset($data['is_customer_notified']),
                    isset($data['is_visible_on_front'])
                );
                $this->ksShipmentRepository->save($shipment);

                $this->shipmentCommentSender->send($shipment, !empty($data['is_customer_notified']), $data['comment']);
            }else{              
                /*set invoice details*/ 
                $ksSalesShipmentDetails->setData('ks_customer_note',$data['comment']);
                $ksSalesShipmentDetails->setData('ks_customer_note_notify',isset($data['is_customer_notified']));     
                $ksSalesShipmentDetails->save();

            }
                        
            return $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode(["success" => true]));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot add new comment.')];
        }
    }
}