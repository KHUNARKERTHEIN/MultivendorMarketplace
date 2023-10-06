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
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Backend\Model\Session;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Comment;
use Magento\Shipping\Model\Order\Track;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Approve
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Shipment
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
    * XML Path
    */
    const XML_PATH_SHIPMENT_APPROVAL_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_shipment_approval_notification_template';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var ShipmentLoader
     */
    protected $ksShipmentLoader;

    /**
     * @var ShipmentSender
     */
    protected $ksShipmentSender;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @var InvoiceService
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var Track
     */
    protected $ksTrack;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $ksShipmentRepositoryInterface;

    /**
     * @var Comment
     */
    protected $ksComment;

    /**
     * Approve constructor.
     * @param Context $ksContext
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItem
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param ShipmentLoader $ksShipmentLoader
     * @param ShipmentSender $ksShipmentSender
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsDataHelper $ksDataHelper
     * @param Session $ksSession
     * @param ShipmentRepositoryInterface $ksShipmentRepositoryInterface
     * @param Comment $ksComment
     * @param Track $ksTrack
     * @param TimezoneInterface $ksTimezoneInterface
     * @param OrderRepositoryInterface $ksOrderRepository = null
     */
    public function __construct(
        Context $ksContext,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        ShipmentLoader $ksShipmentLoader,
        ShipmentSender $ksShipmentSender,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksDataHelper,
        Session $ksSession,
        ShipmentRepositoryInterface $ksShipmentRepositoryInterface,
        Comment $ksComment,
        Track $ksTrack,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesShipmentItem = $ksSalesShipmentItem;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksShipmentLoader = $ksShipmentLoader;
        $this->ksShipmentSender = $ksShipmentSender;
        $this->ksDate = $ksDate;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSession = $ksSession;
        $this->ksShipmentRepositoryInterface = $ksShipmentRepositoryInterface;
        $this->ksComment = $ksComment;
        $this->ksTrack = $ksTrack;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksSalesShipmentId = $this->getRequest()->getParam('entity_id');
        $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
        $ksSellerId = $ksShipmentRequest->getKsSellerId();
        
        try {
            //check shipment Id
            if ($ksSalesShipmentId) {
                $ksSellerId= $ksShipmentRequest->getKsSellerId();
                $this->ksSession->setKsShipmentApprovalReq(1);
                $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
                $ksOrderId = $ksShipmentRequest->getKsOrderId();
                $ksShipmentRequestItems = $this->ksSalesShipmentItem->getCollection()->addFieldToFilter('ks_parent_id', $ksSalesShipmentId);
                $ksShipmentTracks = $this->ksSalesShipmentTrack->getCollection()->addFieldToFilter('ks_parent_id', $ksSalesShipmentId);
                $ksItemsArray =[];
                foreach ($ksShipmentRequestItems as $item) {
                    $ksItemsArray[$item->getKsOrderItemId()] = $item->getKsQty();
                }
                $ksItems['items'] = $ksItemsArray;
                $ksTracks =[];
                foreach ($ksShipmentTracks as $track) {
                    $ksTrackData =[
                        'carrier_code' => $track->getKsCarrierCode(),
                        'title' => $track->getKsTitle(),
                        'number' => $track->getKsTrackNumber()
                    ];
                    array_push($ksTracks, $ksTrackData);
                    $track->delete();
                }
                $ksItems['ks_approval_flag'] = 1;
                $this->ksShipmentLoader->setOrderId($ksOrderId);
                $this->ksShipmentLoader->setShipment($ksItems);
                $this->ksShipmentLoader->setTracking($ksTracks);
               
                $ksShipment = $this->ksShipmentLoader->load();
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                if (!$ksShipment || !$ksShipment->getTotalQty()) {
                    //for redirecting url
                    return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                }
                
                
                $ksShipment->getOrder()->setIsInProcess(true);

                /*save customer comment status and email*/
                if (!empty($ksShipmentRequest->getKsCustomerNote())) {
                    $ksShipment->addComment(
                        $ksShipmentRequest->getKsCustomerNote(),
                        $ksShipmentRequest->getKsCommentCustomerNotify()
                    );

                    $ksShipment->setCustomerNote($ksShipmentRequest->getKsCustomerNote());
                    $ksShipment->setCustomerNoteNotify($ksShipmentRequest->getKsCommentCustomerNotify());
                }

                $ksShipment->register();
                $ksShipment->getOrder()->setCustomerNoteNotify($ksShipmentRequest->getKsSendEmail());

                $transaction = $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                );
                $transaction->addObject(
                    $ksShipment
                )->addObject(
                    $ksShipment->getOrder()
                )->save();
                foreach ($ksShipment->getAllTracks() as $track) {
                    $this->ksSalesShipmentTrack->setKsTrackingDetails($track, $ksSalesShipmentId, $track->getId());
                }
                /*send email to customer*/
                try {
                    $this->ksShipmentSender->send($ksShipment);
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    $this->messageManager->addErrorMessage(__('We can\'t send the shipment email right now.'));
                }

                $shipmentData = $this->ksShipmentRepositoryInterface->get($ksShipment->getId());
                if (!empty($ksShipmentRequest->getKsCustomerNote())) {
                    $ksLastComment = current($shipmentData->getCommentsCollection()->getData());
                    $this->ksComment->load($ksLastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                }
                $ksTracks = $shipmentData->getTracksCollection()->getData();
                if (!empty($ksTracks)) {
                    $ksLastTrack = array_slice($ksTracks, -1)[0];
                    $shipmentData = $this->ksTrack->load($ksLastTrack['entity_id']);
                    $shipmentData->setData('ks_seller_id', $ksSellerId)->save();
                }

                /*update custom data*/
                $ksShipmentRequest->setKsShipmentIncrementId($ksShipment->getIncrementId());
                $ksShipmentRequest->setKsApprovalStatus($this->ksSalesShipment::KS_STATUS_APPROVED);
                $ksShipmentRequest->setKsRejectionReason("");
                $ksShipmentRequest->save();
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_shipment_settings/ks_shipment_approval_notification_template',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );

                $ksShipmentReqId = $ksShipmentRequest->getKsRequestIncrementId();
                $ksShipmentDate = $this->ksTimezoneInterface->date(new \DateTime($ksShipmentRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                $ksShipmentName =  $this->ksOrderRepository->get($ksShipmentRequest->getKsOrderId())->getShippingAddress()->getName();
                $ksShipmentStatus = $ksShipmentRequest->getKsState();
                $ksShipmentTotalQuantity = round($ksShipmentRequest->getKsTotalQty());
                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_shipment_settings/ks_sender_of_email',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                    $ksTemplateVariable = [
                        "ks-shipment-seller-name"=> $ksReceiverDetails["name"],
                        "ks-request-id"=> $ksShipmentReqId,
                        "ks-shipment-date"=>$ksShipmentDate,
                        "ks-shipment-name"=>$ksShipmentName,
                        "ks-total-qty"=>$ksShipmentTotalQuantity,
                        "ks_seller_email" => $ksReceiverDetails['email'],
                        "ksSellerName" => ucwords($ksReceiverDetails['name'])
                    ];
                    
                    // Send Mail
                    $this->ksEmailHelper->ksShipmentApprovalMail($ksTemplateVariable, self::XML_PATH_SHIPMENT_APPROVAL_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
                $this->messageManager->addSuccessMessage(__("Shipment approved successfully"));
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while approving shipment"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
