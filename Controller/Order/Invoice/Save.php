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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Invoice;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentItem;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipmentTrack;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;
use Magento\Sales\Helper\Data as SalesData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Save new invoice action.
 */
class Save extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * XML Path
     */
    const XML_PATH_INVOICE_REQUEST_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_email_template';
    
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
     * @var InvoiceService
     */
    protected $ksInvoiceService;

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
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoiceItem;

    /**
     * @var ShipmentFactory
     */
    protected $ksShipmentFactory;

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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsCommentInvoice
     */
    protected $ksCommentInvoice;

    /**
     * @var PriceCurrencyInterface
     */
    protected $ksPriceCurrency;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrency;

    /**
     * @var SalesData
     */
    protected $ksSalesData;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var KsSalesShipmentItem
     */
    protected $ksSalesShipmentItem;

    /**
     * @var Data
     */
    protected $ksPriceHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param InvoiceSender $invoiceSender
     * @param ShipmentSender $shipmentSender
     * @param ShipmentFactory $ksShipmentFactory,
     * @param InvoiceService $invoiceService
     * @param OrderRepositoryInterface|null $ksOrderRepository
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesOrderItem $ksSalesOrderItem
     * @param KsSalesInvoice $ksSalesInvoice
     * @param KsSalesInvoiceItem $ksSalesInvoiceItem
     * @param  KsSalesShipment $ksSalesShipment
     * @param KsSalesShipmentItem $ksSalesShipmentItem
     * @param KsSalesShipmentTrack $ksSalesShipmentTrack
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
     * @param KsSellerHelper $ksSellerHelper
     * @param Comment $ksCommentInvoice
     * @param PriceCurrencyInterface $ksPriceCurrency
     * @param Currency $ksCurrency
     * @param SalesData $ksSalesData
     * @param TimezoneInterface $ksTimezoneInterface
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        ShipmentFactory $ksShipmentFactory,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $ksOrderRepository,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesOrderItem $ksSalesOrderItem,
        KsSalesInvoice $ksSalesInvoice,
        KsSalesInvoiceItem $ksSalesInvoiceItem,
        KsSalesShipment $ksSalesShipment,
        KsSalesShipmentItem $ksSalesShipmentItem,
        KsSalesShipmentTrack $ksSalesShipmentTrack,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsSellerHelper $ksSellerHelper,
        Comment $ksCommentInvoice,
        Data $ksPriceHelper,
        PriceCurrencyInterface $ksPriceCurrency,
        Currency $ksCurrency,
        SalesData $ksSalesData,
        TimezoneInterface $ksTimezoneInterface
    ) {
        parent::__construct($ksContext);
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository;
        $this->invoiceSender = $invoiceSender;
        $this->shipmentSender = $shipmentSender;
        $this->ksShipmentFactory = $ksShipmentFactory;
        $this->invoiceService = $invoiceService;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesOrderItem = $ksSalesOrderItem;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksSalesInvoiceItem = $ksSalesInvoiceItem;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksSalesShipmentItem = $ksSalesShipmentItem;
        $this->ksSalesShipmentTrack = $ksSalesShipmentTrack;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCommentInvoice = $ksCommentInvoice;
        $this->ksPriceHelper =$ksPriceHelper;
        $this->ksPriceCurrency = $ksPriceCurrency;
        $this->ksCurrency = $ksCurrency;
        $this->ksSalesData = $ksSalesData;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $itemArr = $this->getRequest()->getPost('items');

        foreach ($itemArr as $ksShipmentItemId => $ksQty) {
            $ksOrderItem = $this->ksSalesOrderItem->loadByKsOrderItemId($ksShipmentItemId);
            if ($ksQty > $ksOrderItem->getKsQtyToShip()) {
                $itemArr[$ksShipmentItemId] = $ksOrderItem->getKsQtyToShip();
            }
        }

        $shipment = $this->ksShipmentFactory->create(
            $invoice->getOrder(),
            $itemArr,
            $this->getRequest()->getPost('tracking')
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment;
    }

    /**
     * Invoice save page
     *
     */
    public function execute()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_invoice_settings/ks_create_invoice', $this->ksDataHelper->getKsCurrentStoreView());
        $ksApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_approval', $this->ksDataHelper->getKsCurrentStoreView());
        $ksShipmentApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_approval', $this->ksDataHelper->getKsCurrentStoreView());
        $invoiceData = $this->getRequest()->getPost('invoice');
        $orderId = $this->getRequest()->getParam('order_id');

        if (!empty($data['comment_text'])) {
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCommentText($data['comment_text']);
        }

        if ($ksAllowSeller && $this->ksOrderHelper->ksIsSellerOrder($orderId)) {
            try {
                $ksData = $this->getRequest()->getPostValue();
                $invoiceItems = isset($ksData['items']) ? $ksData['items'] : [];
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
                if (!$order->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
                }

                if (!$order->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order does not allow an invoice to be created.')
                    );
                }

                if (!$ksApproval) {
                    if ($order->getInvoiceCollection()->getSize() == 0) {
                        $this->createInvoiceForShipping($order, $invoiceItems);
                    }
                }

                $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);

                if (!$invoice) {
                    throw new LocalizedException(__("The invoice can't be saved at this time. Please try again later."));
                }

                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("The invoice can't be created without products. Add products and try again.")
                    );
                }

                $this->ksRegistry->register('current_invoice', $invoice);

                if (!empty($invoiceData['capture_case'])) {
                    $invoice->setRequestedCaptureCase($invoiceData['capture_case']);
                }

                if (!empty($invoiceData['comment_text'])) {
                    $invoice->addComment(
                        $invoiceData['comment_text'],
                        isset($invoiceData['comment_customer_notify']),
                        isset($invoiceData['is_visible_on_front'])
                    );

                    $invoice->setCustomerNote($invoiceData['comment_text']);
                    $invoice->setCustomerNoteNotify(isset($invoiceData['comment_customer_notify']));
                }


                $invoice->getOrder()->setCustomerNoteNotify(!empty($invoiceData['send_email']));
                $invoice->getOrder()->setIsInProcess(true);
                $shipment = false;

                if (!empty($invoiceData['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                }

                if ((!$ksShipmentApproval) && (!$ksApproval)) {
                    $invoice->register();
                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject($order)->addObject(
                        $invoice
                    );
                    if ($shipment) {
                        $shipment->register();
                        $transactionSave->addObject($shipment);
                    }
                    $transactionSave->save();
                } elseif (!$ksApproval) {
                    $invoice->register();
                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject($order)->addObject(
                        $invoice
                    )->save();
                } elseif (!$ksShipmentApproval && $shipment) {
                    $shipment->register();
                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject($order)->addObject(
                        $shipment
                    )->save();
                }
                

                if (!$ksApproval) {
                    if (!empty($invoiceData['comment_text'])) {
                        $invoiceComment = $invoice->load($invoice->getId());
                        $LastComment = current($invoiceComment->getCommentsCollection()->getData());
                        $this->ksCommentInvoice->load($LastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                    }
                }

                $ksTotalBaseCommission =0;
                $ksTotalBaseEarning = 0;
                $ksTotalCommission =0;
                $ksTotalEarning = 0;
                $ksTotalQuantity = 0;

                foreach ($invoice->getAllItems() as $ks_item) {
                    if (!$ks_item->getOrderItem()->isDummy()) {
                        $ksTotalBaseCommission += $this->ksOrderHelper->getKsItemBaseCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksBaseWeeeTax = ($ks_item->getOrderItem()->getBaseWeeeTaxAppliedRowAmnt()/$ks_item->getOrderItem()->getQtyOrdered())*$ks_item->getQty();
                        $ksWeeeTax = ($ks_item->getOrderItem()->getWeeeTaxAppliedRowAmount()/$ks_item->getOrderItem()->getQtyOrdered())*$ks_item->getQty();
                        $ksTotalBaseEarning += $ks_item->getBaseRowTotal() + ($this->ksOrderHelper->ksCalcItemBaseTaxAmt($ks_item->getOrderItem(), $ks_item->getQty())) + $ksBaseWeeeTax - $ks_item->getBaseDiscountAmount() - $this->ksOrderHelper->getKsItemBaseCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksTotalCommission += $this->ksOrderHelper->getKsItemCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksTotalEarning += $ks_item->getRowTotal() + ($this->ksOrderHelper->ksCalcItemTaxAmt($ks_item->getOrderItem(), $ks_item->getQty())) + $ksWeeeTax - $ks_item->getDiscountAmount() - $this->ksOrderHelper->getKsItemCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                    }
                    //for product type
                    $ksProductType = $ks_item->getOrderItem()->getProductType();

                    if ($ksProductType == "configurable" || $ksProductType == "bundle") {
                        $ksTotalQuantity += 0;
                    } else {
                        $ksTotalQuantity += $ks_item->getQty();
                    }
                }

                $invoiceData['ks_base_total_commission'] = $ksTotalBaseCommission;
                $invoiceData['ks_base_total_earning'] = $ksTotalBaseEarning>0 ? $ksTotalBaseEarning : 0;
                $invoiceData['ks_total_commission'] = $ksTotalCommission;
                $invoiceData['ks_total_earning'] = $ksTotalEarning>0 ? $ksTotalEarning : 0;
                $invoiceData['ks_total_qty'] = $ksTotalQuantity;

                if ($ksApproval) {
                    $invoice->register();
                    $ksSalesInvoiceId = $this->ksSalesInvoice->setKsInvoice($invoice, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $invoiceData);
                    $ksInvoiceRequest = $this->ksSalesInvoice->load($ksSalesInvoiceId);
                    $ksSellerId = $ksInvoiceRequest->getKsSellerId();
                    $ksInvoiceRequest = $this->ksSalesInvoice->load($ksSalesInvoiceId);
                    $ksInvoiceReqId = $ksInvoiceRequest->getKsRequestIncrementId();
                    $ksInvoiceDate = $this->ksTimezoneInterface->date(new \DateTime($ksInvoiceRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                    $ksOrder = $this->ksOrderRepository->get($orderId);
                    $ksInvoiceName =  $ksOrder->getBillingAddress()->getName();
                    $ksCurrencySymbol=$this->ksCurrency->load($invoice->getOrder()->getOrderCurrencyCode())->getCurrencySymbol();
                    $ksInvoiceGrandTotal=$ksInvoiceRequest->getKsGrandTotal();
                    $ksInvoiceGrandTotal=number_format($ksInvoiceGrandTotal, 2, ".", ",");
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_email_template',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );

                    if ($ksEmailEnabled != "disable") {
                        //Get Sender Info
                        $ksSender = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_sales/ks_invoice_settings/ks_sender_of_email',
                            $this->ksDataHelper->getKsCurrentStoreView()
                        );
                        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                        // Receivers Detail
                        $ksAdminEmailOption = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_admin_email_option';
                        $ksAdminSecondaryEmail ='ks_marketplace_sales/ks_invoice_settings/ks_invoice_admin_email';
                        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                        $ksReceiverDetails = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);

                        $ksTemplateVariable = ["ks-seller-request-invoice-email-name"=> $ksSenderInfo["name"],
                        "ks-request-id"=> $ksInvoiceReqId,
                        "ks-invoice-date"=>$ksInvoiceDate,
                        "ks-invoice-name"=>$ksInvoiceName,
                        "ks-total-amount"=> $ksCurrencySymbol.$ksInvoiceGrandTotal,
                        "ksAdminName" => ucwords($ksReceiverDetails['name'])];

                        // Send Mail
                        $this->ksEmailHelper->ksRequestInvoiceMail($ksTemplateVariable, self::XML_PATH_INVOICE_REQUEST_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                    }
                } else {
                    $ksSalesInvoiceId = $this->ksSalesInvoice->setKsInvoice($invoice, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $invoiceData, $invoice->getIncrementId());
                }

                $ksSalesShipmentId = 0;
                /*shipment*/
                if ($ksShipmentApproval && $shipment) {
                    $shipment->register();
                    $ksSalesShipmentId = $this->ksSalesShipment->setKsShipment($shipment, $this->ksDataHelper->getKsCustomerId(), $ksShipmentApproval, $invoiceData);

                    foreach ($shipment->getAllTracks() as $track) {
                        $this->ksSalesShipmentTrack->setKsTrackingDetails($track, $ksSalesShipmentId);
                    }
                } elseif ((!$ksShipmentApproval) && $shipment) {
                    $ksSalesShipmentId = $this->ksSalesShipment->setKsShipment($shipment, $this->ksDataHelper->getKsCustomerId(), $ksShipmentApproval, $invoiceData, $shipment->getIncrementId());
                }

                foreach ($invoice->getAllItems() as $invoiceItem) {
                    $this->ksSalesInvoiceItem->setKsInvoiceItem($invoiceItem, $ksSalesInvoiceId);
                    /*increment invoice quantity count in ks_sales_order_item table*/
                    $ksOrderItem = $this->ksSalesOrderItem->loadByKsOrderItemId($invoiceItem->getOrderItemId());
                    $ksInvoiceCount = $ksOrderItem->getKsQtyInvoiced();
                    $ksOrderItem->setData('ks_qty_invoiced', $ksInvoiceCount + $invoiceItem->getQty());

                    $ksOrderItem->save();
                }
                /*save shipment items*/
                if ($ksSalesShipmentId) {
                    foreach ($shipment->getAllItems() as $ksShipmentItem) {
                        $ksOrderItem = $this->ksSalesOrderItem->loadByKsOrderItemId($ksShipmentItem->getOrderItemId());
                        $this->ksSalesShipmentItem->setKsShipmentItem($ksShipmentItem, $ksSalesShipmentId);
                        $ksShipmentCount = $ksOrderItem->getKsQtyShipped();
                        $ksOrderItem->setData('ks_qty_shipped', $ksShipmentCount + $ksShipmentItem->getQty())->save();
                    }
                }

                if (!$ksApproval) {
                    // send invoice/shipment emails
                    try {
                        $this->invoiceSender->send($invoice);
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t send the invoice email right now.'));
                    }

                    if (!empty($invoiceData['do_shipment'])) {
                        $this->messageManager->addSuccessMessage(__('You created the invoice and shipment.'));
                    } else {
                        $this->messageManager->addSuccessMessage(__('The invoice has been created.'));
                    }
                } else {
                    $this->messageManager->addSuccessMessage(__('The invoice request has been created.'));
                }

                if ($shipment && (!$ksShipmentApproval)) {
                    try {
                        if (!empty($invoiceData['send_email']) || $this->ksSalesData->canSendNewShipmentEmail() && (!$ksShipmentApproval)) {
                            $this->shipmentSender->send($shipment);
                        }
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t send the shipment right now.'));
                    }
                } elseif ($shipment && $ksShipmentApproval) {
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_email_template',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    /*send shipment request email*/
                    if ($ksEmailEnabled!="disable") {
                        $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
                        $ksSellerId = $ksShipmentRequest->getKsSellerId();
                        $ksShipmentReqId = $ksShipmentRequest->getKsRequestIncrementId();
                        $ksShipmentDate = $this->ksTimezoneInterface->date(new \DateTime($ksShipmentRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                        $ksShipmentName =  $this->ksOrderRepository->get($ksShipmentRequest->getKsOrderId())->getShippingAddress()->getName();
                        $ksShipmentTotalQuantity = round($ksShipmentRequest->getKsTotalQty());
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

                return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    $e->getMessage()
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }

            return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
        } else {
            $this->messageManager->addErrorMessage(
                __('You are not authorized to create an invoice for the order')
            );

            return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
        }
    }

    /**
     * Create Invoice For shipping amount
     *
     * @param $ksOrder \Magento\Sales\Model\Order
     * @param $ksInvoiceRequestItems array
     */
    public function createInvoiceForShipping($ksOrder, $ksInvoiceRequestItems)
    {
        $ksItems = [];

        foreach ($ksInvoiceRequestItems as $key =>$ksRequestItem) {
            $ksItems[$key] = 0;
        }

        $ksInvoice = $this->invoiceService->prepareInvoice($ksOrder, $ksItems);

        if (!$ksInvoice) {
            throw new LocalizedException(__("The invoice can't be saved at this time. Please try again later."));
        }

        $ksInvoice->setBaseGrandTotal($ksOrder->getBaseShippingAmount() - $ksOrder->getBaseShippingDiscountAmount() + $ksOrder->getBaseShippingTaxAmount());
        $ksInvoice->setGrandTotal($ksOrder->getShippingAmount() - $ksOrder->getShippingDiscountAmount() + $ksOrder->getShippingTaxAmount());
        
        $ksInvoice->register();
        $ksInvoice->getOrder()->setIsInProcess(true);

        $transactionSave = $this->_objectManager->create(
            \Magento\Framework\DB\Transaction::class
        )->addObject(
            $ksInvoice
        )->addObject(
            $ksInvoice->getOrder()
        );

        $transactionSave->save();
    }
}
