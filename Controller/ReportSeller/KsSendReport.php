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

namespace Ksolves\MultivendorMarketplace\Controller\ReportSeller;

use Ksolves\MultivendorMarketplace\Model\KsReportSellerSubReasonFactory;
use Ksolves\MultivendorMarketplace\Helper\KsReportSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsSendReport Controller class
 */
class KsSendReport extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var KsReportSellerHelper
     */
    protected $ksReportHelper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $ksResultRedirectFactory;

    /**
     * KsSendReport Constructor
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param KsReportSellerHelper $ksReportHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksHelperData
     * @param \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        KsReportSellerHelper $ksReportHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksHelperData,
        \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
    ) {
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksReportHelper = $ksReportHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksHelperData = $ksHelperData;
        $this->ksResultRedirectFactory = $ksResultRedirectFactory;
        parent::__construct($ksContext);
    }

    /**
     * Send mail of report
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /*get form data*/
        $ksReportFormData   = $this->getRequest()->getPostValue();
        $ksAllowAlert = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_allow_admin_alert',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        /*fetch receiver's emails*/
        $ksAdminEmailOption = 'ks_marketplace_report/ks_report_seller/ks_report_seller_admin_email_option';
        $ksAdminSecondaryEmail ='ks_marketplace_report/ks_report_seller/ks_report_seller_admin_email';
        $ksStoreId = $this->ksHelperData->getKsCurrentStoreView();
        $ksReceiverDetails = $this->ksHelperData->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);

        $ksSender = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_sender_mail',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        $ksSenderInfo = $this->getKsSenderInfo($ksSender);
        /*initialise email validator*/
        $ksEmailValidator = new \Zend\Validator\EmailAddress();
        /*Fetch copy method*/
        $ksCopyMethod = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_mail_copy_method',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        /*set email template variables*/
        if ($this->ksReportHelper->getKsCurrentCustomer()) {
            $ksReportFormData['ks-report-seller-customer-email'] = $this->ksReportHelper->getKsCurrentCustomer()->getEmail();
            $ksReportFormData['ks-report-seller-customer-name'] = $this->ksReportHelper->getKsCurrentCustomer()->getName();
        }
        if (!isset($ksReportFormData['ks-report-seller-reason']) || (isset($ksReportFormData['ks-report-seller-reason']) && !$ksReportFormData['ks-report-seller-reason'])) {
            $ksReportFormData['ks-report-seller-reason'] = 'N/A';
        }
        if (!isset($ksReportFormData['ks-report-seller-sub-reason']) || (isset($ksReportFormData['ks-report-seller-sub-reason']) && !$ksReportFormData['ks-report-seller-sub-reason'])) {
            $ksReportFormData['ks-report-seller-sub-reason'] = 'N/A';
        }
        if (!isset($ksReportFormData['ks-report-seller-comment']) || (isset($ksReportFormData['ks-report-seller-comment']) && !$ksReportFormData['ks-report-seller-comment'])) {
            $ksReportFormData['ks-report-seller-comment'] = 'N/A';
        }
        /*Send admin emails*/
        try {
            if ($ksAllowAlert) {
                /*fetch copy recievers data*/
                $ksEmailCopyRecievers = $this->ksHelperData->getKsConfigValue(
                    'ks_marketplace_report/ks_report_seller/ks_mail_copy_receiver_emails',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $ksEmailCopyRecievers = explode(',', $ksEmailCopyRecievers);
                /*validate emails*/
                foreach ($ksEmailCopyRecievers as $key => $email) {
                    if ($ksEmailValidator->isValid($email)) {
                        $ksEmailCopyRecievers[$key] = $email;
                    } else {
                        unset($ksEmailCopyRecievers[$key]);
                    }
                }
                $ksAdminName =  $ksReceiverDetails['name'];
                $ksReportFormData['ks-admin-name'] = $ksAdminName;
                if ($ksCopyMethod==0) {
                    /*shoot email with all copy receiver's in BCC*/
                        $this->ksEmailHelper->ksSendAdminReportSellerMail($ksReportFormData, $ksSenderInfo, $ksAdminName, $ksReceiverDetails['email'], $ksEmailCopyRecievers);
                } else {
                        $this->ksEmailHelper->ksSendAdminReportSellerMail($ksReportFormData, $ksSenderInfo, $ksAdminName, $ksReceiverDetails['email'], $ksEmailCopyRecievers);
                    foreach ($ksEmailCopyRecievers as $email) {
                        $recieverInfo['name']  = $ksAdminName;
                        $recieverInfo['email'] = $email;
                        $this->ksEmailHelper->ksSendReportSellerMailSeprateCopy($ksReportFormData, $ksSenderInfo, $recieverInfo);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect = $this->ksResultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        $this->ksReportHelper->setKsSellerReportCount($ksReportFormData['ks-report-seller-id']);
        /*send acknowledgment*/
        $ksAcknowledgeCustomer = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_customer_report_seller_acknowledgement_mail',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        if ($ksAcknowledgeCustomer) {
            try {
                $ksSender = $this->ksHelperData->getKsConfigValue(
                    'ks_marketplace_report/ks_report_seller/ks_report_seller_acknowledgement_mail_sender',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $ksSenderInfo = $this->getKsSenderInfo($ksSender);
                $recieverInfo = ['name'=> $ksReportFormData['ks-report-seller-customer-name'],'email'=>$ksReportFormData['ks-report-seller-customer-email']];
                $this->ksEmailHelper->ksSendReportSellerAcknowledgementMail($ksReportFormData, $ksSenderInfo, $recieverInfo);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Your report could not be sent')
                );
                $resultRedirect = $this->ksResultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        }
        $this->messageManager->addSuccess(
            __('Your report against the seller has been sent successfully.')
        );
        $resultRedirect = $this->ksResultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * get sender name and email
     * @return Array
     */
    public function getKsSenderInfo($sender)
    {
        switch ($sender) {
            case 0:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 1:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_sales/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_sales/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 2:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_support/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_support/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 3:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom1/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom1/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 4:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom2/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom2/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;
            
            default:
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;
        }
        return $senderInfo;
    }
}
