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

namespace Ksolves\MultivendorMarketplace\Controller\Order\CreditMemo;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\ksSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItem;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Save new memo action.
 */
class Save extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * XML Path
     */
    const XML_PATH_CREDITMEMO_REQUEST_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_email_template';
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
     * @var ksSalesCreditMemo
     */
    protected $ksSalesCreditMemo;

    /**
     * @var ksSalesCreditMemoItem
     */
    protected $ksSalesCreditMemoItem;

    /**
     * @var CreditmemoSender
     */
    protected $ksCreditmemoSender;

    /**
     * @var CreditmemoSender
     */
    protected $ksCreditmemoLoader;

    /**
     * @var KsSalesOrderItem
     */
    protected $ksSalesOrderItem;

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
     * @var KsCommentCreditMemo
     */
    protected $ksCommentCreditMemo;

    /**
     * @var PriceCurrencyInterface
     */
    protected $ksPriceCurrency;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param ksSalesCreditMemo $ksSalesCreditMemo
     * @param ksSalesCreditMemoItem $ksSalesCreditMemoItem
     * @param CreditmemoSender $ksCreditmemoSender
     * @param CreditMemoLoader $ksCreditmemoLoader
     * @param KsSalesOrderItem $ksSalesOrderItem
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
     * @param KsSellerHelper $ksSellerHelper
     * @param Comment $ksCommentCreditMemo
     * @param PriceCurrencyInterface $ksPriceCurrency
     * @param Currency $ksCurrency
     * @param TimezoneInterface $ksTimezoneInterface
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesCreditMemo $ksSalesCreditMemo,
        ksSalesCreditMemoItem $ksSalesCreditMemoItem,
        CreditmemoSender $ksCreditmemoSender,
        CreditMemoLoader $ksCreditmemoLoader,
        KsSalesOrderItem $ksSalesOrderItem,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsSellerHelper $ksSellerHelper,
        Comment $ksCommentCreditMemo,
        PriceCurrencyInterface $ksPriceCurrency,
        Currency $ksCurrency,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksSalesCreditMemoItem = $ksSalesCreditMemoItem;
        $this->ksCreditmemoSender = $ksCreditmemoSender;
        $this->ksCreditmemoLoader=$ksCreditmemoLoader;
        $this->ksSalesOrderItem = $ksSalesOrderItem;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCommentCreditMemo = $ksCommentCreditMemo;
        $this->ksPriceCurrency = $ksPriceCurrency;
        $this->ksCurrency =$ksCurrency;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * Memo save page
     *
     */
    public function execute()
    {
        $ksSellerId=$this->ksSellerHelper->getKsCustomerId();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_creditmemo_settings/ks_create_creditmemo', $this->ksDataHelper->getKsCurrentStoreView());
        $ksApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_approval', $this->ksDataHelper->getKsCurrentStoreView());
        $creditMemoData = $this->getRequest()->getPost('creditmemo');
        $creditMemoData['shipping_amount'] = 0;
        $orderId = $this->getRequest()->getParam('order_id');
        $ksShowStoreCurrency = $this->ksOrderHelper->canKsShowOrderCurrencyValue();
        if ($ksAllowSeller && $this->ksOrderHelper->ksIsSellerOrder($orderId)) {
            try {
                $creditMemoItems = isset($creditMemoData['items']) ? $creditMemoData['items'] : [];
                /** @var \Magento\Sales\Model\Order $order */
                $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->load($orderId);
                if (!$order->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
                }
                $this->ksCreditmemoLoader->setOrderId($orderId);
                $this->ksCreditmemoLoader->setCreditmemo($creditMemoData);
                $creditmemo=$this->ksCreditmemoLoader->load();
                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                if (!empty($creditMemoData['comment_text'])) {
                    $creditmemo->addComment(
                        $creditMemoData['comment_text'],
                        isset($creditMemoData['comment_customer_notify']),
                        isset($creditMemoData['is_visible_on_front'])
                    );
                    $creditmemo->setCustomerNote($creditMemoData['comment_text']);
                    $creditmemo->setCustomerNoteNotify(isset($creditMemoData['comment_customer_notify']));
                }
                if (isset($creditMemoData['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$creditMemoData['do_offline'] && !empty($creditMemoData['refund_customerbalance_return_enable'])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Cannot create online refund for Refund to Store Credit.')
                        );
                    }
                }
                $creditmemoManagement = $this->_objectManager->create(
                    \Magento\Sales\Api\CreditmemoManagementInterface::class
                );
                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($creditMemoData['send_email']));
                $doOffline = isset($creditMemoData['do_offline']) ? (bool)$creditMemoData['do_offline'] : false;
                
                if ((!empty($creditMemoData['send_email'])) && (!$ksApproval)) {
                    $this->ksCreditmemoSender->send($creditmemo);
                }
                /*calculate commission*/
                $ksTotalBaseCommission =0;
                $ksTotalBaseEarning = 0;
                $ksTotalCommission =0;
                $ksTotalEarning = 0;
                foreach ($creditmemo->getAllItems() as $ks_item) {
                    if (!$ks_item->getOrderItem()->isDummy()) {
                        $ksBaseWeeeTax = ($ks_item->getOrderItem()->getBaseWeeeTaxAppliedRowAmnt()/$ks_item->getOrderItem()->getQtyOrdered())*$ks_item->getQty();
                        $ksWeeeTax = ($ks_item->getOrderItem()->getWeeeTaxAppliedRowAmount()/$ks_item->getOrderItem()->getQtyOrdered())*$ks_item->getQty();
                        $ksTotalBaseCommission += $this->ksOrderHelper->getKsItemBaseCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksTotalBaseEarning += $ks_item->getBaseRowTotal() + ($this->ksOrderHelper->ksCalcItemBaseTaxAmt($ks_item->getOrderItem(), $ks_item->getQty())) + $ksBaseWeeeTax - $ks_item->getBaseDiscountAmount() - $this->ksOrderHelper->getKsItemBaseCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksTotalCommission += $this->ksOrderHelper->getKsItemCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                        $ksTotalEarning += $ks_item->getRowTotal() + ($this->ksOrderHelper->ksCalcItemTaxAmt($ks_item->getOrderItem(), $ks_item->getQty())) + $ksWeeeTax - $ks_item->getDiscountAmount() - $this->ksOrderHelper->getKsItemCommission($ks_item->getOrderItemId(), $ks_item->getQty());
                    }
                }
                $creditMemoData['ks_base_total_commission'] = $ksTotalBaseCommission;
                $creditMemoData['ks_base_total_earning'] = $ksTotalBaseEarning>0 ? $ksTotalBaseEarning : 0;
                $creditMemoData['ks_total_commission'] = $ksTotalCommission;
                $creditMemoData['ks_total_earning'] = $ksTotalEarning>0 ? $ksTotalEarning : 0;
                if ($ksApproval) {
                    $ksSalesCreditMemoId = $this->ksSalesCreditMemo->setKsCreditMemo($creditmemo, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $creditMemoData);
                    $ksCreditMemoRequest = $this->ksSalesCreditMemo->load($ksSalesCreditMemoId);
                    $ksSellerId = $ksCreditMemoRequest->getKsSellerId();
                    $ksCreditMemoRequest = $this->ksSalesCreditMemo->load($ksSalesCreditMemoId);
                    $ksCreditMemoReqId =  $ksCreditMemoRequest->getKsRequestIncrementId();
                    $ksCreditMemoDate =  $this->ksTimezoneInterface->date(new \DateTime($ksCreditMemoRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                    $ksCreditMemoName =  $this->ksOrderRepository->get($orderId)->getBillingAddress()->getName();
                    $ksCreditMemoStatus =  $ksCreditMemoRequest->getKsState();
                    $ksCreditMemoRefund =  $ksCreditMemoRequest->getKsGrandTotal();
                    $ksCurrencySymbol=$this->ksCurrency->load($creditmemo->getOrder()->getOrderCurrencyCode())->getCurrencySymbol();
                    $ksCreditMemoRefund=number_format($ksCreditMemoRefund, 2, ".", ",");
                    $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_email_template',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    if ($ksEmailEnabled != "disable") {
                        //Get Sender Info
                        $ksSender = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_sales/ks_creditmemo_settings/ks_sender_of_email',
                            $this->ksDataHelper->getKsCurrentStoreView()
                        );
                        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                        // Receivers Detail
                        $ksAdminEmailOption = 'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_admin_email_option';
                        $ksAdminSecondaryEmail ='ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_admin_email';
                        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                        $ksReceiverDetails = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                        $ksTemplateVariable = ["ks-creditmemo-request-name"=> $ksSenderInfo["name"],
                        "ks-request-id"=> $ksCreditMemoReqId,
                        "ks-creditmemo-date"=>$ksCreditMemoDate,
                        "ks-creditmemo-name"=>$ksCreditMemoName,
                        "ks-creditmemo-status"=>$ksCreditMemoStatus,
                        "ks-refunded-price"=> $ksCurrencySymbol.$ksCreditMemoRefund,
                        "ksAdminName" => ucwords($ksReceiverDetails['name'])];
                        
                        // Send Mail
                        $this->ksEmailHelper->ksRequestCreditMemoMail($ksTemplateVariable, self::XML_PATH_CREDITMEMO_REQUEST_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                    }
                } else {
                    $creditmemoManagement->refund($creditmemo, $doOffline);

                    if (!empty($creditMemoData['comment_text'])) {
                        $creditmemoComment = $creditmemo->load($creditmemo->getId());
                        $LastComment = current($creditmemoComment->getCommentsCollection()->getData());
                        $this->ksCommentCreditMemo->load($LastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                    }

                    $ksSalesCreditMemoId = $this->ksSalesCreditMemo->setKsCreditMemo($creditmemo, $this->ksDataHelper->getKsCustomerId(), $ksApproval, $creditMemoData, $creditmemo->getIncrementId());
                }
                foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                    $this->ksSalesCreditMemoItem->setKsCreditMemoItem($creditmemoItem, $ksSalesCreditMemoId, isset($creditMemoData[$creditmemoItem->getOrderItemId()]['return_to_stock']) ? $creditMemoData[$creditmemoItem->getOrderItemId()]['return_to_stock']:0);
                    /*increment creditmemo quantity count in ks_sales_order_item table*/
                    $ksOrderItem = $this->ksSalesOrderItem->loadByKsOrderItemId($creditmemoItem->getOrderItemId());
                    $ksCreditMemoCount = $ksOrderItem->getKsQtyRefunded();
                    $ksOrderItem->setData('ks_qty_refunded', $ksCreditMemoCount + $creditmemoItem->getQty());
                    $ksOrderItem->save();
                }
                if (!$ksApproval) {
                    $this->messageManager->addSuccessMessage(__('The credit memo has been created.'));
                    // send creditmemo emails
                    try {
                        $this->ksCreditmemoSender->send($creditmemo);
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t send the credit memo email right now.'));
                    }
                } else {
                    $this->messageManager->addSuccessMessage(__('The credit memo request has been created.'));
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
                __('You are not authorized to create a credit memo for the order')
            );
            return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $orderId]);
        }
    }
}
