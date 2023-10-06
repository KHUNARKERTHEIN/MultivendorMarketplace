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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax;

use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * SellerReject Controller class
 */
class SellerReject extends \Magento\Backend\App\Action
{
    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsSellerFactory $ksSellerFactory
     * @param DateTime $KsDate
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        Context $ksContext,
        KsSellerFactory $ksSellerFactory,
        DateTime $KsDate,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDate = $KsDate;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * Seller reject
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksId =$this->getRequest()->getPost("ks_id");
        $ksNotify =$this->getRequest()->getPost("ks_notify");
        $ksRejecttionReason =$this->getRequest()->getPost("ks_rejection_reason");

        $ksSellerStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_REJECTED;
        $ksSellerStoreStatus = \Ksolves\MultivendorMarketplace\Model\KsSellerStore::KS_STATUS_DISABLED;
        if ($ksId) {
            try {
                $ksModel = $this->ksSellerFactory->create()->load($ksId);
                $ksSellerId = $ksModel->getKsSellerId();
                $ksModel->setKsRejectionReason($ksRejecttionReason);
                $ksModel->setKsSellerStatus($ksSellerStatus);
                $ksModel->setKsStoreStatus($ksSellerStoreStatus);
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->save();

                $this->_eventManager->dispatch('ksseller_store_change_after');

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_seller/ks_seller_settings/ks_seller_rejection_templates'
                );
                if ($ksEmailEnabled != "disable" && $ksNotify == 'true') {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_seller/ks_seller_settings/ks_email_sender'
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                    $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                    $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                
                    if (trim($ksModel->getKsRejectionReason()) == "") {
                        $ksTemplateVariable['ksReason'] = "";
                    } else {
                        $ksTemplateVariable['ksReason'] = $ksModel->getKsRejectionReason();
                    }
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }

                $ksResponse = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['success' => true,
                    'message' => $this->messageManager->addSuccess("A seller has been rejected successfully.")
                ]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while rejecting seller.")
                    ]);
            } catch (\Exception $e) {
                $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while rejecting seller.")
                    ]);
            }
        } else {
            $ksResponse = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData(['error' => true,
                        'message' => $this->messageManager->addErrorMessage("Something went wrong while rejecting seller.")
                    ]);
        }
        return $ksResponse;
    }
}
