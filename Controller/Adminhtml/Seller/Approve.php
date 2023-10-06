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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Approve Controller Class
 */
class Approve extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
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
     * Approve constructor.
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksDate = $ksDate;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksSellerStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_APPROVED;
        $ksId = $this->getRequest()->getParam('id');
        //check Id
        if ($ksId) {
            //get model data
            $ksSellerModel = $this->ksSellerFactory->create()->load($ksId);
            //check model data
            if ($ksSellerModel) {
                $ksSellerId = $ksSellerModel->getKsSellerId();
                $ksSellerModel->setKsRejectionReason(" ");
                $ksSellerModel->setKsSellerStatus($ksSellerStatus);
                $ksSellerModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksSellerModel->save();

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_seller/ks_seller_settings/ks_seller_approved_templates'
                );

                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_seller/ks_seller_settings/ks_email_sender'
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                    $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                    $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }

                $this->messageManager->addSuccessMessage(
                    __('A seller has been approved successfully.')
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while approving seller.')
                );
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while approving seller.')
            );
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
