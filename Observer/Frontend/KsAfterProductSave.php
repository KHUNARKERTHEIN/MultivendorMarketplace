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

namespace Ksolves\MultivendorMarketplace\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class KsAfterProductSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsAfterProductSave implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * @var MessageManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @param  Http $ksRequest
     * @param KsSellerHelper $ksSellerHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerFactory $ksSellerFactory
     * @param KsSellerProductFactory $ksSellerProductFactory
     * @param DateTime $ksDate
     * @param ManagerInterface $ksessageManager
     */
    public function __construct(
        Http $ksRequest,
        KsSellerHelper $ksSellerHelper,
        KsDataHelper $ksDataHelper,
        KsSellerFactory $ksSellerFactory,
        KsSellerProductFactory $ksSellerProductFactory,
        DateTime $ksDate,
        MessageManagerInterface $ksMessageManager
    ) {
        $this->ksRequest = $ksRequest;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerFactory  = $ksSellerFactory;
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksDate = $ksDate;
        $this->ksMessageManager = $ksMessageManager;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        $ksStatus = $this->ksRequest->getParam('back');
        if (!$ksStatus) {
            $ksStatus = "submit";
        }
        try {
            $ksProductId = $ksObserver->getProduct()->getId();
            $this->saveKsSellerProduct($ksProductId, $ksStatus);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * save seller product
     * @param int $ksProductId
     */
    protected function saveKsSellerProduct($ksProductId, string $ksStatus)
    {
        if ($ksStatus=='new' || $ksStatus=='duplicate' || $ksStatus=='close' || $ksStatus=='save') {
            $ksPendingStatus = KsProduct::KS_STATUS_NOT_SUBMITTED;
            $ksApprovalStatus = KsProduct::KS_STATUS_NOT_SUBMITTED;
            $ksPendingUpdateStatus = KsProduct::KS_STATUS_NOT_SUBMITTED;
        } else {
            $ksPendingStatus = KsProduct::KS_STATUS_PENDING;
            $ksApprovalStatus = KsProduct::KS_STATUS_APPROVED;
            $ksPendingUpdateStatus = KsProduct::KS_STATUS_PENDING_UPDATE;
        }

        try {
            $ksSellerId = $this->ksSellerHelper->getKsCustomerId();

            $ksProductAutoApproval = $this->ksSellerFactory->create()
                ->load($ksSellerId, 'ks_seller_id')
                ->getKsProductAutoApproval();

            //get product collection
            $ksSellerProductCollection = $this->ksSellerProductFactory->create()->getCollection()
                ->addFieldToFilter('ks_product_id', $ksProductId);
            //check size
            if ($ksSellerProductCollection->getSize()) {
                $ksId = $ksSellerProductCollection->getFirstItem()->getId();
                $ksCollection = $this->ksSellerProductFactory->create()->load($ksId);
                if ($ksProductAutoApproval) {
                    $ksProductAdminStatus = $ksApprovalStatus;
                } else {
                    //check approval status condition
                    if ($ksCollection->getKsEditMode() == 1) {
                        if (!$this->ksDataHelper->getKsConfigProductSetting('ks_update_approval') && $this->ksDataHelper->getKsConfigProductSetting('ks_admin_approval')) {
                            $ksProductAdminStatus = $ksApprovalStatus;
                        } else {
                            $ksProductAdminStatus = $ksPendingUpdateStatus;
                        }
                    } else {
                        $ksProductAdminStatus = $ksPendingStatus;
                    }
                }
            } else {
                $ksCollection = $this->ksSellerProductFactory->create();
                $ksCollection->setKsCreatedAt($this->ksDate->gmtDate());
                if ($ksProductAutoApproval) {
                    $ksProductAdminStatus = $ksApprovalStatus;
                } else {
                    $ksProductAdminStatus = $ksPendingStatus;
                }
            }

            if (isset($ksSellerId) && $ksSellerId != '') {
                $ksCollection->setKsProductId($ksProductId);
                $ksCollection->setKsSellerId($ksSellerId);
                $ksCollection->setKsProductApprovalStatus($ksProductAdminStatus);

                if ($ksProductAdminStatus == KsProduct::KS_STATUS_APPROVED) {
                    $ksCollection->setKsRejectionReason("");
                    $ksCollection->setKsEditMode(1);
                }

                $ksCollection->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksCollection->save();
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
        }
    }
}
