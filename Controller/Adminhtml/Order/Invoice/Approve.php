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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Backend\Model\Session;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice\Comment;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Approve
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const XML_PATH_INVOICE_APPROVAL_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_approval_notification_template';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem
     */
    protected $ksSalesInvoiceItem;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var InvoiceSender
     */
    protected $ksInvoiceSender;

    /**
     * @var InvoiceService
     */
    protected $ksInvoiceService;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $ksInvoiceStates;

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
     * @var Session
     */
    protected $ksSession;

    /**
     * @var KsCommentInvoice
     */
    protected $ksCommentInvoice;

    /**
     * @var KsOrderHelper
     */
    protected $ksOrderHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $ksPriceCurrency;

    /**
     * @var Currency
     */
    protected $ksCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $ksItemFactory;

    /**
     * Approve constructor.
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem $ksSalesInvoiceItem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param InvoiceSender $ksInvoiceSender
     * @param InvoiceService $ksInvoiceService
     * @param KsDataHelper $ksDataHelper
     * @param Session $ksSession
     * @param Comment $ksCommentInvoice
     * @param KsOrderHelper $ksOrderHelper
     * @param PriceCurrencyInterface $ksPriceCurrency
     * @param Currency $ksCurrency
     * @param TimezoneInterface $ksTimezoneInterface
     * @param \Magento\Sales\Model\Order\ItemFactory $ksItemFactory
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice,
        \Ksolves\MultivendorMarketplace\Model\KsSalesInvoiceItem $ksSalesInvoiceItem,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        InvoiceSender $ksInvoiceSender,
        InvoiceService $ksInvoiceService,
        KsDataHelper $ksDataHelper,
        Session $ksSession,
        InvoiceRepositoryInterface $ksInvoiceRepository,
        Comment $ksCommentInvoice,
        KsOrderHelper $ksOrderHelper,
        PriceCurrencyInterface $ksPriceCurrency,
        Currency $ksCurrency,
        TimezoneInterface $ksTimezoneInterface,
        \Magento\Sales\Model\Order\ItemFactory $ksItemFactory,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksSalesInvoiceItem = $ksSalesInvoiceItem;
        $this->ksDate = $ksDate;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksInvoiceSender = $ksInvoiceSender;
        $this->ksInvoiceService = $ksInvoiceService;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSession = $ksSession;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksInvoiceStates = $ksInvoiceRepository->create()->getStates();
        $this->ksCommentInvoice = $ksCommentInvoice;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksPriceCurrency = $ksPriceCurrency;
        $this->ksCurrency = $ksCurrency;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        $this->ksItemFactory = $ksItemFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksInvoiceId = $this->getRequest()->getParam('id');
        $ksShowStoreCurrency = $this->ksOrderHelper->canKsShowOrderCurrencyValue();
        try {
            //check invoice Id
            if ($ksInvoiceId) {
                
                // load model
                $ksInvoiceRequest = $this->ksSalesInvoice->load($ksInvoiceId);
                $ksInvoiceReqId = $ksInvoiceRequest->getKsRequestIncrementId();
                $ksSellerId=$ksInvoiceRequest->getKsSellerId();
                $ksInvoiceName =  $this->ksOrderRepository->get($ksInvoiceRequest->getKsOrderId())->getBillingAddress()->getName();
                $ksInvoiceStatus = isset($this->ksInvoiceStates[$ksInvoiceRequest->getKsState()]) ? $this->ksInvoiceStates[$ksInvoiceRequest->getKsState()] : '';
                $ksOrderId = $ksInvoiceRequest->getKsOrderId();
                /** @var \Magento\Sales\Model\Order $order */
                $ksOrder = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($ksOrderId);
                
                $ksInvoiceRequestItems = $this->ksSalesInvoiceItem->getCollection()->addFieldToFilter('ks_parent_id', $ksInvoiceId);

                if (!$ksOrder->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("The order does not allow an invoice to be created."));
                }
                
                if ($ksOrder->getInvoiceCollection()->getSize() == 0) {
                    $this->createInvoiceForShipping($ksOrder, $ksInvoiceRequestItems);
                }

                $this->ksSession->setKsInvoiceApprovalReq(1);
                $ksItems = [];
                
                foreach ($ksInvoiceRequestItems as $ksRequestItem) {
                    $ksOrderItem = $this->ksItemFactory->create()->load($ksRequestItem->getKsOrderItemId());

                    if ($ksOrderItem->getProductType()=="bundle" && $ksOrderItem->isShipSeparately() && !$this->ksOrderHelper->isKsChildCalculated($ksOrderItem)) {
                        continue;
                    }
                    $ksItems[$ksRequestItem->getKsOrderItemId()] = $ksRequestItem->getKsQty();
                }
                $ksItems['ks_approval_flag'] = 1;

                $ksInvoice = $this->ksInvoiceService->prepareInvoice($ksOrder, $ksItems);
                $ksCurrencySymbol=$ksCurrencySymbol=$this->ksCurrency->load($ksInvoice->getOrder()->getOrderCurrencyCode())->getCurrencySymbol();
                $ksInvoiceGrandTotal = $ksInvoiceRequest->getKsGrandTotal();
                $ksInvoiceGrandTotal=number_format($ksInvoiceGrandTotal, 2, ".", ",");

                if (!$ksInvoice) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("The invoice can't be saved at this time. Please try again later."));
                }

                if (!$ksInvoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("The invoice can't be created without products. Add products and try again.")
                    );
                }

                if (!empty($ksInvoiceRequest->getKsCustomerNote())) {
                    $ksInvoice->addComment(
                        $ksInvoiceRequest->getKsCustomerNote(),
                        $ksInvoiceRequest->getKsCommentCustomerNotify()
                    );

                    $ksInvoice->setCustomerNote($ksInvoiceRequest->getKsCustomerNote());
                    $ksInvoice->setCustomerNoteNotify($ksInvoiceRequest->getKsCommentCustomerNotify());
                }

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
                if (!empty($ksInvoiceRequest->getKsCustomerNote())) {
                    $invoiceComment = $ksInvoice->load($ksInvoice->getId());
                    $LastComment = current($invoiceComment->getCommentsCollection()->getData());
                    $this->ksCommentInvoice->load($LastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                }
                $ksInvoiceRequest->getKsSendEmail();
                $this->ksInvoiceSender->send($ksInvoice);
                
                //check model
                if ($ksInvoiceRequest) {
                    $ksSellerId= $ksInvoiceRequest->getKsSellerId();
                    $ksInvoiceRequest->setKsApprovalStatus($this->ksSalesInvoice::KS_STATUS_APPROVED);
                    $ksInvoiceRequest->setKsInvoiceIncrementId($ksInvoice->getIncrementId());
                    $ksInvoiceRequest->setKsRejectionReason("");
                    $ksInvoiceRequest->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksInvoiceRequest->save();
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_invoice_settings/ks_invoice_approval_notification_template',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );

                    if ($ksEmailEnabled != "disable") {
                        //Get Sender Info
                        $ksSender = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_sales/ks_invoice_settings/ks_sender_of_email',
                            $this->ksDataHelper->getKsCurrentStoreView()
                        );
                        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                        $ksInvoiceDate = $this->ksTimezoneInterface->date(new \DateTime($ksInvoiceRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                        $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                        $ksTemplateVariable = ["ks-invoice-approval-item-name"=> $ksReceiverDetails["name"],
                        "ks-request-id"=> $ksInvoiceReqId,
                        "ks-invoice-date"=>$ksInvoiceDate,
                        "ks-invoice-name"=>$ksInvoiceName,
                        "ks-invoice-status"=>$ksInvoiceStatus,
                        "ks-total-amount"=>$ksCurrencySymbol.$ksInvoiceGrandTotal,
                        "ks_seller_email" => $ksReceiverDetails['email'],
                        "ksSellerName" => ucwords($ksReceiverDetails['name'])];
                        
                        // Send Mail
                        $this->ksEmailHelper->ksInvoiceApprovalMail($ksTemplateVariable, self::XML_PATH_INVOICE_APPROVAL_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                    }
                    $this->messageManager->addSuccessMessage(__("Invoice approved successfully"));
                } else {
                    $this->messageManager->addErrorMessage(__("Something went wrong while approving invoice"));
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while approving invoice"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Create invoice with empty items for shipping
     *
     * @param $ksOrder
     * @param $ksInvoiceRequestItem
     * @return \Magento\Sales\Model\Order\Invoice|bool
     */
    public function createInvoiceForShipping($ksOrder, $ksInvoiceRequestItems)
    {
        $ksItems = [];
        foreach ($ksInvoiceRequestItems as $ksRequestItem) {
            $ksItems[$ksRequestItem->getKsOrderItemId()] = 0;
        }
        $ksInvoice = $this->ksInvoiceService->prepareInvoice($ksOrder, $ksItems);

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
