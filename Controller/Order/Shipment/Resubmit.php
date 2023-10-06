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
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsOrderHelper;
use Ksolves\MultivendorMarketplace\Model\KsSalesShipment;
use Magento\Framework\Controller\ResultFactory;

/**
 * Resubmit action.
 */
class Resubmit extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
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
     * @var KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param Registry $ksRegistry
     * @param PageFactory $ksResultPageFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsOrderHelper $ksOrderHelper
     * @param KsSalesShipment $ksSalesShipment
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param OrderRepositoryInterface|null $ksOrderRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        Registry $ksRegistry,
        PageFactory $ksResultPageFactory,
        KsDataHelper $ksDataHelper,
        KsOrderHelper $ksOrderHelper,
        KsSalesShipment $ksSalesShipment,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderHelper = $ksOrderHelper;
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        parent::__construct($ksContext);

    }

    /**
     * Shipment resubmit page
     *
     */
    public function execute()
    {
       
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $ksAllowSeller = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_create_shipment', $this->ksDataHelper->getKsCurrentStoreView());
        $ksApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_approval', $this->ksDataHelper->getKsCurrentStoreView());

        $data = $this->getRequest()->getPostValue();
        $ksSalesShipmentId = $data['id'];
        $ksOrderId = 0;
        try {
            $ksShipmentRequest = $this->ksSalesShipment->load($ksSalesShipmentId);
            $ksSellerId = $ksShipmentRequest->getKsSellerId();
            $ksOrderId = $ksShipmentRequest->getKsOrderId();
            $ksIsSeller = $this->ksOrderHelper->ksIsSellerOrder($ksOrderId);
            if ($ksAllowSeller && $ksIsSeller) {
                $ksShipmentRequest->setKsApprovalStatus($this->ksSalesShipment::KS_STATUS_PENDING);
                $ksShipmentRequest->setKsCustomerNote($data['comment']['comment']);
                $ksShipmentRequest->setKsCommentCustomerNotify(isset($data['shipment']['comment_customer_notify']));
                $ksShipmentRequest->setKsSendEmail(isset($data['shipment']['send_email']));
                $ksShipmentRequest->save();
                $ksShipmentReqId = $ksShipmentRequest->getKsRequestIncrementId();
                $ksShipmentDate = $ksShipmentRequest->getKsCreatedAt();
                $ksShipmentName =  $this->ksOrderRepository->get($ksOrderId)->getShippingAddress()->getName();
                $ksShipmentTotalQuantity = $ksShipmentRequest->getksTotalQty();
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
                $this->messageManager->addSuccessMessage(__('The Shipment request has been created.'));
                return $resultRedirect->setPath('multivendor/order/vieworder', ['ks_order_id' => $ksOrderId]);
            } else {
                $this->messageManager->addErrorMessage(
                    __('You are not authorized to create a shipment for the order')
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
