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
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Reject
 */
class Reject extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const XML_PATH_INVOICE_REJECTION_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_rejection_notification_template';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice
     */
    protected $ksSalesInvoice;

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
     * @var InvoiceRepositoryInterface
     */
    protected $ksInvoiceStates;

    /**
     * @var Currency
     */
    protected $ksCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * Reject constructor.
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
     * @param KsDataHelper $ksDataHelper,
     * @param InvoiceRepositoryInterface $ksInvoiceRepository
     * @param Currency $ksCurrency
     * @param TimezoneInterface $ksTimezoneInterface
     * @param OrderRepositoryInterface $ksOrderRepository = null
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksDataHelper,
        InvoiceRepositoryInterface $ksInvoiceRepository,
        Currency $ksCurrency,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksInvoiceStates = $ksInvoiceRepository->create()->getStates();
        $this->ksCurrency = $ksCurrency;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksInvoiceId = $this->getRequest()->getPost('ks_id');
        $ksRejectionReason = $this->getRequest()->getPost('ks_rejection_reason');
        $ksInvoiceRequest = $this->ksSalesInvoice->load($ksInvoiceId);
        try {
            //check creditmemo Id
            if ($ksInvoiceId) {
                $ksSellerId = $ksInvoiceRequest->getKsSellerId();
                $ksInvoiceRequest = $this->ksSalesInvoice->load($ksInvoiceId);
                $ksInvoiceReqId = $ksInvoiceRequest->getKsRequestIncrementId();
                $ksInvoiceDate = $this->ksTimezoneInterface->date(new \DateTime($ksInvoiceRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                $ksOrder = $this->ksOrderRepository->get($ksInvoiceRequest->getKsOrderId());
                $ksInvoiceName =  $ksOrder->getBillingAddress()->getName();
                $ksCurrencySymbol=$this->ksCurrency->load($ksOrder->getOrderCurrencyCode())->getCurrencySymbol();
                $ksInvoiceStatus = isset($this->ksInvoiceStates[$ksInvoiceRequest->getKsState()]) ? $this->ksInvoiceStates[$ksInvoiceRequest->getKsState()] : '';
                $ksInvoiceGrandTotal = $ksInvoiceRequest->getKsGrandTotal();
                /*update custom data*/
                $ksInvoiceRequest->setKsRejectionReason($ksRejectionReason);
                $ksInvoiceRequest->setKsApprovalStatus($this->ksSalesInvoice::KS_STATUS_REJECTED);
                $ksInvoiceRequest->save();
                                
                $this->messageManager->addSuccessMessage(__("Invoice rejected successfully"));
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_invoice_settings/ks_invoice_rejection_notification_template',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable" && $this->getRequest()->getPost('ks_notify_seller')=='true') {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_invoice_settings/ks_sender_of_email',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                    $ksTemplateVariable = ["ks-seller-invoice-rejected-email-name"=> $ksReceiverDetails["name"],"ks-request-id"=> $ksInvoiceReqId,"ks-invoice-date"=>$ksInvoiceDate,"ks-invoice-name"=>$ksInvoiceName,"ks-invoice-status"=> $ksInvoiceStatus,"ks-total-amount"=> $ksCurrencySymbol.$ksInvoiceGrandTotal,"ks_seller_email" => $ksReceiverDetails['email'],
                       "ksSellerName" => ucwords($ksReceiverDetails['name'])];

                    if (trim($ksInvoiceRequest->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksRejection'] = "";
                    } else {
                        $ksTemplateVariable['ksRejection'] = $ksInvoiceRequest->getKsRejectionReason();
                    }

                    
                    // Send Mail
                    $this->ksEmailHelper->ksInvoiceRejectionMail($ksTemplateVariable, self::XML_PATH_INVOICE_REJECTION_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while rejecting invoice"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        //for redirecting url
        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }
}
