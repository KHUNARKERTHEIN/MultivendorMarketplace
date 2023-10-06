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
use Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
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
    const XML_PATH_CREDITMEMO_REJECTION_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_rejection_notification_template';
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo
     */
    protected $ksSalesCreditmemo;

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
     * @var CreditmemoRepositoryInterface
     */
    protected $ksMemoStates;

    /**
     * @var Currency
     */
    protected $ksCurrency;

    /**
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * Approve constructor.
     * @param Context $ksContext
     * @param KsSalesCreditMemo $ksSalesCreditmemo
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsDataHelper $ksDataHelper
     * @param CreditmemoRepositoryInterface $ksMemoRepository
     * @param OrderRepositoryInterface $ksOrderRepository = null
     * @param Currency $ksCurrency
     * @param TimezoneInterface $ksTimezoneInterface
     */
    public function __construct(
        Context $ksContext,
        KsSalesCreditMemo $ksSalesCreditmemo,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksDataHelper,
        CreditmemoRepositoryInterface $ksMemoRepository,
        Currency $ksCurrency,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesCreditmemo = $ksSalesCreditmemo;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksMemoStates = $ksMemoRepository->create()->getStates();
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksCurrency = $ksCurrency;
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksSalesCreditmemoId = $this->getRequest()->getPost('ks_id');
        $ksRejectionReason = $this->getRequest()->getPost('ks_rejection_reason');

        $ksCreditmemoRequest = $this->ksSalesCreditmemo->load($ksSalesCreditmemoId);
        try {
            //check creditmemo Id
            if ($ksSalesCreditmemoId) {
                $ksSellerId = $ksCreditmemoRequest->getKsSellerId();
                $ksCreditmemoRequest = $this->ksSalesCreditmemo->load($ksSalesCreditmemoId);
                $ksCreditmemoReqId = $ksCreditmemoRequest->getKsRequestIncrementId();
                $ksCreditMemoDate = $this->ksTimezoneInterface->date(new \DateTime($ksCreditmemoRequest->getCreatedAt()))->format('m/d/y H:i:s');
                $ksOrder = $this->ksOrderRepository->get($ksCreditmemoRequest->getKsOrderId());
                $ksCurrencySymbol=$this->ksCurrency->load($ksOrder->getOrderCurrencyCode())->getCurrencySymbol();
                $ksCreditMemoName =  $ksOrder->getBillingAddress()->getName();
                $ksCreditMemoRefund = $ksCreditmemoRequest->getKsBaseGrandTotal();
                /*update custom data*/
                $ksCreditmemoRequest->setKsRejectionReason($ksRejectionReason);
                $ksCreditmemoRequest->setKsApprovalStatus($this->ksSalesCreditmemo::KS_STATUS_REJECTED);
                $ksCreditmemoRequest->save();
                $this->messageManager->addSuccessMessage(__("Creditmemo rejected successfully"));
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_rejection_notification_template',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable"&& $this->getRequest()->getPost('ks_notify_seller')=='true') {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_creditmemo_settings/ks_sender_of_email',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                    $ksTemplateVariable = ["ks-credditmemo-rejection-name"=> $ksReceiverDetails["name"],"ks-request-id"=>  $ksCreditmemoReqId,
                    "ks-creditmemo-date"=>$ksCreditMemoDate,
                    "ks-creditmemo-name"=>$ksCreditMemoName,
                    "ks-refunded-price"=>$ksCurrencySymbol.$ksCreditMemoRefund,
                    "ks_seller_email" => $ksReceiverDetails['email'],
                    "ksSellerName" => ucwords($ksReceiverDetails['name'])];


                    if (trim($ksCreditmemoRequest->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksRejection'] = "";
                    } else {
                        $ksTemplateVariable['ksRejection'] = $ksCreditmemoRequest->getKsRejectionReason();
                    }
                    
                    // Send Mail
                    $this->ksEmailHelper->ksCreditMemoRejectionMail($ksTemplateVariable, self::XML_PATH_CREDITMEMO_REJECTION_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while rejecting creditmemo"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }
}
