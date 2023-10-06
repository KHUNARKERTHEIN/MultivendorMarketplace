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
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesInvoice;
use Magento\Directory\Model\Currency;

/**
 * Resubmit
 */
class Resubmit extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * XML Path
     */
    const XML_PATH_INVOICE_REQUEST_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_email_template';
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
     * @var KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrency;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesInvoice $ksSalesInvoice
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param Currency $ksCurrency
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesInvoice $ksSalesInvoice,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        Currency $ksCurrency,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesInvoice = $ksSalesInvoice;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksCurrency =$ksCurrency;
        parent::__construct($ksContext);
    }

    /**
     * Invoice save page
     *
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksInvoiceData = $this->getRequest()->getPostValue();
        $ksSalesInvoiceId = $ksInvoiceData['id'];
        try {
            $ksInvoiceRequest = $this->ksSalesInvoice->load($ksSalesInvoiceId);
            $ksOrderId = $ksInvoiceRequest->getKsOrderId();
            $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($ksOrderId);
            $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_invoice_settings/ks_create_invoice', $this->ksDataHelper->getKsCurrentStoreView());
            if ($ksIsSeller && $ksIsSeller) {
                $ksInvoiceRequest->setKsApprovalStatus($this->ksSalesInvoice::KS_STATUS_PENDING);
                $ksInvoiceRequest->setKsCustomerNote($ksInvoiceData['comment']['comment']);
                $ksInvoiceRequest->setKsCommentCustomerNotify(isset($ksInvoiceData['invoice']['comment_customer_notify']));
                $ksInvoiceRequest->setKsSendEmail(isset($ksInvoiceData['invoice']['send_email']));
                $ksInvoiceRequest->save();
                $ksOrder = $this->ksOrderRepository->get($ksOrderId);
                $ksCurrencySymbol=$this->ksCurrency->load($ksOrder->getOrderCurrencyCode())->getCurrencySymbol();
                $ksSellerId = $ksInvoiceRequest->getKsSellerId();
                $ksInvoiceRequest = $this->ksSalesInvoice->load($ksSalesInvoiceId);
                $ksInvoiceReqId = $ksInvoiceRequest->getKsRequestIncrementId();
                $ksInvoiceDate = $ksInvoiceRequest->getksCreatedAt();
                $ksInvoiceName =  $ksOrder->getBillingAddress()->getName();
                $ksInvoiceStatus = $ksInvoiceRequest->getKsState();
                $ksInvoiceGrandTotal = $ksCurrencySymbol.$ksInvoiceRequest->getKsGrandTotal();
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_email_template',
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
                    $ksAdminEmailOption = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_admin_email_option';
                    $ksAdminSecondaryEmail ='ks_marketplace_sales/ks_invoice_settings/ks_invoice_admin_email';
                    $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                    $ksReceiverDetails = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                    $ksTemplateVariable = ["ks-seller-request-invoice-email-name"=> $ksSenderInfo["name"],
                    "ks-request-id"=> $ksInvoiceReqId,
                    "ks-invoice-date"=>$ksInvoiceDate,
                    "ks-invoice-name"=>$ksInvoiceName,
                    "ks-total-amount"=> $ksInvoiceGrandTotal,
                    "ksAdminName" => ucwords($ksReceiverDetails['name'])];
                    
                    // Send Mail
                    $this->ksEmailHelper->ksRequestInvoiceMail($ksTemplateVariable, self::XML_PATH_INVOICE_REQUEST_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
                $this->messageManager->addSuccessMessage(__('The invoice request has been created.'));
                return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
            } else {
                $this->messageManager->addErrorMessage(
                    __('You are not authorized to create an invoice for the order')
                );
                return $this->resultRedirectFactory->create()->setPath(
                    'customer/account/create',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
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
        return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
    }
}
