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

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem;
use Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Backend\Model\Session;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order\Creditmemo\Comment;
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
    const XML_PATH_CREDITMEMO_APPROVAL_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_approval_notification_template';
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo
     */
    protected $ksSalesCreditmemo;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemoItem
     */
    protected $ksSalesCreditmemoItem;

    /**
     * @var CreditmemoLoader
     */
    protected $ksCreditMemoLoader;

    /**
     * @var CreditmemoSender
     */
    protected $ksCreditmemoSender;

    /**
     * @var KsOrderHeler
     */
    protected $ksOrderHelper;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $ksMemoManagement;

    /**
     * @var OrderRepositoryInterface
     */
    protected $ksOrderRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $ksMemoStates;

    /**
     * @var ShipmentSender
     */
    protected $ksDate;

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
     * @var Comment
     */
    protected $ksComment;

    /**
     * Approve constructor.
     * @param Context $ksContext
     * @param KsSalesCreditMemo $ksSalesCreditmemo
     * @param KsSalesCreditMemoItem $ksSalesCreditmemoItem
     * @param CreditmemoLoader $ksCreditMemoLoader
     * @param CreditmemoSender $ksCreditmemoSender
     * @param KsOrderHelper $ksOrderHelper
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $ksMemoManagement
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsDataHelper $ksDataHelper
     * @param CreditmemoRepositoryInterface $ksMemoRepository
     * @param Session $ksSession
     * @param Comment $ksComment
     * @param PriceCurrencyInterface $ksPriceCurrency
     * @param Currency $ksCurrency
     * @param TimezoneInterface $ksTimezoneInterface
     */
    public function __construct(
        Context $ksContext,
        KsSalesCreditMemo $ksSalesCreditmemo,
        KsSalesCreditMemoItem $ksSalesCreditmemoItem,
        CreditmemoLoader $ksCreditMemoLoader,
        CreditmemoSender $ksCreditmemoSender,
        KsOrderHelper $ksOrderHelper,
        \Magento\Sales\Api\CreditmemoManagementInterface $ksMemoManagement,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksDataHelper,
        CreditmemoRepositoryInterface $ksMemoRepository,
        Session $ksSession,
        Comment $ksComment,
        PriceCurrencyInterface $ksPriceCurrency,
        Currency $ksCurrency,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesCreditmemo = $ksSalesCreditmemo;
        $this->ksSalesCreditmemoItem = $ksSalesCreditmemoItem;
        $this->ksCreditMemoLoader = $ksCreditMemoLoader;
        $this->ksCreditmemoSender = $ksCreditmemoSender;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksMemoManagement = $ksMemoManagement;
        $this->ksDate = $ksDate;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        ;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksMemoStates = $ksMemoRepository->create()->getStates();
        $this->ksSession = $ksSession;
        $this->ksComment = $ksComment;
        $this->ksPriceCurrency = $ksPriceCurrency;
        $this->ksCurrency =$ksCurrency;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksSalesCreditmemoId = $this->getRequest()->getParam('entity_id');
        $ksCreditmemoRequest = $this->ksSalesCreditmemo->load($ksSalesCreditmemoId);
        $ksShowStoreCurrency = $this->ksOrderHelper->canKsShowOrderCurrencyValue();

        try {
            //check creditmemo Id
            if ($ksSalesCreditmemoId) {
                $this->ksSession->setKsMemoApprovalReq(1);

                $ksSellerId= $ksCreditmemoRequest->getKsSellerId();
                $ksCreditmemoRequest = $this->ksSalesCreditmemo->load($ksSalesCreditmemoId);
                $ksCreditMemoReqId = $ksCreditmemoRequest->getKsRequestIncrementId();
                $ksCreditMemoName =  $this->ksOrderRepository->get($ksCreditmemoRequest->getKsOrderId())->getBillingAddress()->getName();
                $ksCreditMemoStatus = $ksCreditmemoRequest->getKsState() ? $this->ksMemoStates[$ksCreditmemoRequest->getKsState()] : "";
                $ksOrderId = $ksCreditmemoRequest->getKsOrderId();
                $ksCreditmemoRequestItems = $this->ksSalesCreditmemoItem->getCollection()->addFieldToFilter('ks_parent_id', $ksSalesCreditmemoId);
                $memoData = [];
                foreach ($ksCreditmemoRequestItems as $item) {
                    $memoData['items'][$item->getKsOrderItemId()]['qty'] = $item->getKsQty();
                    if ($item->getKsBackToStock() == 1) {
                        $memoData['items'][$item->getKsOrderItemId()]['back_to_stock'] = $item->getKsBackToStock();
                    }
                }
                $memoData['shipping_amount'] = 0;
                $memoData['ks_approval_flag'] = 1;
                $this->ksCreditMemoLoader->setOrderId($ksOrderId);
                $this->ksCreditMemoLoader->setCreditmemo($memoData);
                $ksCreditmemo = $this->ksCreditMemoLoader->load();
                if (!$ksCreditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }
                if (!empty($ksCreditmemoRequest->getKsCustomerNote())) {
                    $ksCreditmemo->addComment(
                        $ksCreditmemoRequest->getKsCustomerNote(),
                        $ksCreditmemoRequest->getKsCommentCustomerNotify()
                    );
                    $ksCreditmemo->setCustomerNote($ksCreditmemoRequest->getKsCustomerNote());
                    $ksCreditmemo->setCustomerNoteNotify($ksCreditmemoRequest->getKsCommentCustomerNotify());
                }
                $ksCurrencySymbol=$this->ksCurrency->load($ksCreditmemo->getOrder()->getOrderCurrencyCode())->getCurrencySymbol();
                $ksCreditMemoRefund = $ksCreditmemoRequest->getKsGrandTotal();
                $ksCreditMemoRefund=number_format($ksCreditMemoRefund, 2, ".", ",");
                /*send email to customer*/
                try {
                    $this->ksCreditmemoSender->send($ksCreditmemo);
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    $this->messageManager->addErrorMessage(__('We can\'t send the creditmemo email right now.'));
                }

                $doOffline = false;
                $ksCreditmemo = $this->ksMemoManagement->refund($ksCreditmemo, $doOffline);

                if (!empty($ksCreditmemoRequest->getKsCustomerNote())) {
                    $creditmemoComment = $ksCreditmemo->load($ksCreditmemo->getId());
                    $LastComment = current($creditmemoComment->getCommentsCollection()->getData());
                    $this->ksComment->load($LastComment['entity_id'])->setData('ks_seller_id', $ksSellerId)->save();
                }

                $transaction = $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                );
                $transaction->addObject(
                    $ksCreditmemo
                )->addObject(
                    $ksCreditmemo->getOrder()
                )->save();

                /*update custom data*/
                $ksCreditmemoRequest->setKsCreditmemoIncrementId($ksCreditmemo->getIncrementId());
                $ksCreditmemoRequest->setKsApprovalStatus($this->ksSalesCreditmemo::KS_STATUS_APPROVED);
                $ksCreditmemoRequest->setKsRejectionReason("");
                $ksCreditmemoRequest->setKsState($ksCreditmemo->getState());
                $ksCreditmemoRequest->save();
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_approval_notification_template',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_creditmemo_settings/ks_sender_of_email',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                    $ksCreditMemoStatus = $ksCreditmemo->getState() ? $this->ksMemoStates[$ksCreditmemo->getState()] : "";
                    $ksTemplateVariable = [
                        "ks-credditmemo-approval-notification-name"=> $ksReceiverDetails["name"],
                        "ks-request-id"=> $ksCreditMemoReqId,
                        "ks-creditmemo-date"=>$this->ksTimezoneInterface->date(new \DateTime($ksCreditmemoRequest->getKsCreatedAt()))->format('m/d/y H:i:s'),
                        "ks-creditmemo-name"=>$ksCreditMemoName,
                        "ks-creditmemo-status"=>$ksCreditMemoStatus,
                        "ks-refunded-price"=>$ksCurrencySymbol.$ksCreditMemoRefund,
                        "ks_seller_email" => $ksReceiverDetails['email'],
                        "ksSellerName" => ucwords($ksReceiverDetails['name'])
                    ];

                    // Send Mail
                    $this->ksEmailHelper->ksCreditMemoApprovalMail($ksTemplateVariable, self::XML_PATH_CREDITMEMO_APPROVAL_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
                $this->messageManager->addSuccessMessage(__("Credit Memo approved successfully"));
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
