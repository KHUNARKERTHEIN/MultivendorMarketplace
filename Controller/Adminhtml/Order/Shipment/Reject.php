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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Reject
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Shipment
 */
class Reject extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const XML_PATH_SHIPMENT_REJECTION_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_shipment_rejection_notification_template';
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesShipment
     */
    protected $ksSalesShipment;

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
     * @var TimezoneInterface
     */
    protected $ksTimezoneInterface;

    /**
     * Reject constructor.
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesShipment $ksSalesShipment
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsDataHelper $ksDataHelper
     * @param TimezoneInterface $ksTimezoneInterface
     * @param OrderRepositoryInterface $ksOrderRepository = null
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSalesShipment $ksSalesShipment,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksDataHelper,
        TimezoneInterface $ksTimezoneInterface,
        OrderRepositoryInterface $ksOrderRepository = null
    ) {
        $this->ksSalesShipment = $ksSalesShipment;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksOrderRepository = $ksOrderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->ksTimezoneInterface = $ksTimezoneInterface;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksShipmentId = $this->getRequest()->getPost('ks_id');
        $ksRejectionReason = $this->getRequest()->getPost('ks_rejection_reason');

        $ksShipmentRequest = $this->ksSalesShipment->load($ksShipmentId);
        try {
            //check shipment Id
            if ($ksShipmentId) {
                $ksSellerId = $ksShipmentRequest->getKsSellerId();
                $ksShipmentRequest = $this->ksSalesShipment->load($ksShipmentId);
                $ksShipmentReqId = $ksShipmentRequest->getKsRequestIncrementId();
                $ksShipmentDate = $this->ksTimezoneInterface->date(new \DateTime($ksShipmentRequest->getKsCreatedAt()))->format('m/d/y H:i:s');
                $ksShipmentName =  $this->ksOrderRepository->get($ksShipmentRequest->getKsOrderId())->getShippingAddress()->getName();
                $ksShipmentTotalQuantity = $ksShipmentRequest->getKsTotalQty();
                /*update custom data*/
                $ksShipmentRequest->setKsRejectionReason($ksRejectionReason);
                $ksShipmentRequest->setKsApprovalStatus($this->ksSalesShipment::KS_STATUS_REJECTED);
                $ksShipmentRequest->save();
                $this->messageManager->addSuccessMessage(__("Shipment rejected successfully"));
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_sales/ks_shipment_settings/ks_shipment_rejection_notification_template',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable" && $this->getRequest()->getPost('ks_notify_seller')=='true') {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_sales/ks_shipment_settings/ks_sender_of_email',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    $ksReceiverDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksSellerId);
                    $ksTemplateVariable = [" ks-shipment-rejection-notification-name"=> $ksReceiverDetails["name"],"ks-request-id"=> $ksShipmentReqId,"ks-shipment-date"=>$ksShipmentDate,"ks-shipment-name"=>$ksShipmentName,"ks-total-quantity"=>$ksShipmentTotalQuantity,
                        "ks_seller_email" => $ksReceiverDetails['email'],
                        "ksSellerName" => ucwords($ksReceiverDetails['name'])];

                    if (trim($ksShipmentRequest->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksRejection'] = "";
                    } else {
                        $ksTemplateVariable['ksRejection'] = $ksShipmentRequest->getKsRejectionReason();
                    }

                    
                    // Send Mail
                    $this->ksEmailHelper->ksShipmentRejectionMail($ksTemplateVariable, self::XML_PATH_SHIPMENT_REJECTION_MAIL, $ksSenderInfo, ucwords($ksReceiverDetails['name']), $ksReceiverDetails['email']);
                }
            } else {
                $this->messageManager->addErrorMessage(__("Something went wrong while rejecting shipment"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON);
    }
}
