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

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sales\Model\Order\Shipment\Comment;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\Order\Track;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Save new shipment action.
 */
class Save extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * XML Path
     */
    const XML_PATH_SHIPMENT_REQUEST_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_email_template';
    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksSalesOrderItem;

    /**
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var KsSalesShipment
     */
    protected $KsSalesShipmentItem;

    /**
     * @var KsSalesShipmentTrack
     */
    protected $ksSalesShipmentTrack;

    /**
     * @var ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsCommentShipment
     */
    protected $ksCommentShipment;

    /**
    * @var KsCommentShipmentRepository
    */
    protected $ksCommentShipmentRepository;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var KsSalesShipmentItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var Track
     */
    protected $ksTrack;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param ShipmentSender $shipmentSender
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItem $ksSalesOrderItem
     * @param KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItemr
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param ShipmentLoader $shipmentLoader
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param Comment $ksCommentShipment
     * @param ShipmentRepositoryInterface $ksCommentShipmentRepository
     * @param Track $ksTrack
     * @param TimezoneInterface $ksTimezoneInterface
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        ShipmentSender $shipmentSender,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItem $ksSalesOrderItem,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsSellerHelper $ksSellerHelper,
        Comment $ksCommentShipment,
        ShipmentRepositoryInterface $ksCommentShipmentRepository,
        Track $ksTrack,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->shipmentSender = $shipmentSender;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->get(OrderRepositoryInterface::class);
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesOrderItem = $ksSalesOrderItem;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesShipmentItem = $ksSalesShipmentItem;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->shipmentLoader = $shipmentLoader;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCommentShipment = $ksCommentShipment;
        $this->ksCommentShipmentRepository = $ksCommentShipmentRepository;
        $this->ksTrack = $ksTrack;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }

    /**
     * Shipment save page
     *
     */
    public function execute()
    {
        $ksSellerId=$this->ksSellerHelper->getKsCustomerId();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_create_shipment', $this->ksDataHelper->getKsCurrentStoreView());
        $ksApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_approval', $this->ksDataHelper->getKsCurrentStoreView());

        $data = $this->getRequest()->getPost('shipment');
        $ksOrderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCommentText($data['comment_text']);
        }

        if ($this->ksOrderHelper->ksIsSellerOrder($ksOrderId) && $ksAllowSeller) {
            try {
                $this->shipmentLoader->setOrderId($ksOrderId);
                $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setShipment($data);
                if (!empty($this->getRequest()->getParam('tracking'))) {
                    $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                }
               
                $shipment = $this->shipmentLoader->load();
                if (!$shipment || !$shipment->getTotalQty()) {
                    $this->messageManager->addErrorMessage(
                        __("You can't create a shipment without products.")
                    );
                    return $resultRedirect->setPath('multivendor/order_shipment/new', ['order_id' => $ksOrderId]);
                }
                
                if (!empty($data['comment_text'])) {
                    $shipment->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $shipment->setCustomerNote($data['comment_text']);
                    $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                }

                $shipment->register();
                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                
                if (!$ksApproval) {
                    $savedShipment = $this->_saveShipment($shipment);
                    
                    $shipmentData = $this->ksCommentShipmentRepository->get($shipment->getId());
                    if (!empty($data['comment_text'])) {
                        $LastComment = current($shipmentData->getCommentsCollection()->getData());
                        $this->ksCommentShipment->load($LastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                    }
                    $ksTracks = $shipmentData->getTracksCollection()->getData();
                    if (!empty($ksTracks)) {
                        $ksLastTrack = array_slice($ksTracks, -1)[0];
                        $shipmentData = $this->ksTrack->load($ksLastTrack['entity_id']);
                        $shipmentData->setData('ks_seller_id', $ksSellerId)->save();
                    }
                    $ksSalesShipmentId = $this->ksSalesShipment->setKsShipment($shipment, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $data, $shipment->getIncrementId());
                } else {
                    $ksSalesShipmentId = $this->ksSalesShipment->setKsShipment($shipment, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $data);
                    $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
                    foreach ($shipment->getAllTracks() as $track) {
                        $this->ksSalesShipmentTrack->setKsTrackingDetails($track, $ksSalesShipmentId);
                    }
                    $ksSellerId = $ksShipmentRequest->getKsSellerId();
                    $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
                    $ksShipmentReqId = $ksShipmentRequest->getKsRequestIncrementId();
                    $ksShipmentDate = $this->ksTimezoneInterface->date(new \DateTime($ksShipmentRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                    $ksShipmentName =  $this->ksOrderRepository->get($ksOrderId)->getShippingAddress()->getName();
                    $ksShipmentTotalQuantity = round($ksShipmentRequest->getKsTotalQty());
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_email_template',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    if ($ksEmailEnabled != "disable") {
                        //Get Sender Info
                        $ksSender = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_sales/ks_shipment_settings/ks_sender_of_email',
                            $this->ksDataHelper->getKsCurrentStoreView()
                        );
                        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                        // Receivers Detail
                        $ksAdminEmailOption = 'ks_marketplace_sales/ks_shipment_settings/ks_shipment_admin_email_option';
                        $ksAdminSecondaryEmail ='ks_marketplace_sales/ks_shipment_settings/ks_shipment_admin_email';
                        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                        $ksReceiverDetails = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                        $ksTemplateVariable = ["ks-shipment-approval-name"=> $ksSenderInfo["name"],"ks-request-id"=> $ksShipmentReqId,
                        "ks-shipment-date"=>$ksShipmentDate,
                        "ks-shipment-name"=>$ksShipmentName,
                        "ks-total-quantity"=>$ksShipmentTotalQuantity,
                        "ksAdminName" => ucwords($ksReceiverDetails['name'])];
                        
                        // Send Mail
                        $this->ksEmailHelper->ksRequestShipmentMail($ksTemplateVariable, self::XML_PATH_SHIPMENT_REQUEST_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                    }
                }
                foreach ($shipment->getAllItems() as $shipmentItem) {
                    $this->ksSalesShipmentItem->setKsShipmentItem($shipmentItem, $ksSalesShipmentId);
                    /*increment shipment quantity count in ks_sales_order_item table*/
                    $ksOrderItem = $this->ksSalesOrderItem->loadByKsOrderItemId($shipmentItem->getOrderItemId());
                    $ksShipmentCount = $ksOrderItem->getKsQtyShipped();
                    $ksOrderItem->setData('ks_qty_shipped', $ksShipmentCount + $shipmentItem->getQty());
                    $ksOrderItem->save();
                }
                if (!$ksApproval) {
                    $this->messageManager->addSuccessMessage(__('The shipment has been created.'));
                    // send invoice/shipment emails
                    try {
                        $this->shipmentSender->send($shipment);
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t send the shipment email right now.'));
                    }
                } else {
                    $this->messageManager->addSuccessMessage(__('The Shipment request has been created.'));
                }
                return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
                return $resultRedirect->setPath('multivendor/order_shipment/new', ['order_id' => $ksOrderId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                return $resultRedirect->setPath('multivendor/order_shipment/new', ['order_id' => $ksOrderId]);
            }
            return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
        } else {
            $this->messageManager->addErrorMessage(
                __('You are not authorized to create an shipment for the order')
            );
            return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
        }
    }
}
