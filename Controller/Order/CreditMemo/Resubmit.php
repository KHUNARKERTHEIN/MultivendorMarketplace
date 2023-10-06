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
use Magento\Directory\Model\Currency;

/**
 * Resubmit action.
 */
class Resubmit extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrency;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param ksSalesCreditMemo $ksSalesCreditMemo
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
        KsSalesCreditMemo $ksSalesCreditMemo,
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
        $this->ksSalesCreditMemo = $ksSalesCreditMemo;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksCurrency =$ksCurrency;
        parent::__construct($ksContext);

    }

    /**
     * Memo save page
     *
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_creditmemo_settings/ks_create_creditmemo', $this->ksDataHelper->getKsCurrentStoreView());
        $ksApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_approval', $this->ksDataHelper->getKsCurrentStoreView());
        $creditMemoData = $this->getRequest()->getPostValue();
        $ksSalesCreditMemoId = $creditMemoData['id'];
        $ksOrderId =0 ;
        try {
            $ksCreditMemoRequest = $this->ksSalesCreditMemo->load($ksSalesCreditMemoId);
            $ksSellerId = $ksCreditMemoRequest->getKsSellerId();
            $ksOrderId = $ksCreditMemoRequest->getKsOrderId();
            $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($ksOrderId);
            if ($ksIsSeller && $ksAllowSeller) {
                $ksOrder = $this->ksOrderRepository->get($ksOrderId);
                $ksCurrencySymbol=$this->ksCurrency->load($ksOrder->getOrderCurrencyCode())->getCurrencySymbol();
                $ksCreditMemoRequest->setKsApprovalStatus($this->ksSalesCreditMemo::KS_STATUS_PENDING);
                $ksCreditMemoRequest->setKsCustomerNote($creditMemoData['comment']['comment']);
                $ksCreditMemoRequest->setKsCommentCustomerNotify(isset($creditMemoData['creditmemo']['comment_customer_notify']));
                $ksCreditMemoRequest->setKsSendEmail(isset($creditMemoData['creditmemo']['send_email']));
                $ksCreditMemoRequest->save();
                $ksCreditMemoReqId =  $ksCreditMemoRequest->getKsRequestIncrementId();
                $ksCreditMemoDate =  $ksCreditMemoRequest->getKsCreatedAt();
                $ksCreditMemoName =  $ksOrder->getBillingAddress()->getName();
                $ksCreditMemoStatus =  $ksCreditMemoRequest->getKsState();
                $ksCreditMemoRefund =  $ksCreditMemoRequest->getKsBaseGrandTotal();
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_email_template',
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
                    $this->messageManager->addSuccessMessage(__('The credit memo request has been created.'));
                }
                
                return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
            } else {
                $this->messageManager->addErrorMessage(
                    __('You are not authorized to create a credit memo for the order')
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
