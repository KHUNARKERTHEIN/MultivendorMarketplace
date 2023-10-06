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

namespace Ksolves\MultivendorMarketplace\Controller\ReportProduct;

use Ksolves\MultivendorMarketplace\Model\KsReportProductSubReasonFactory;
use Ksolves\MultivendorMarketplace\Helper\KsReportProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * KsSendReport Controller class
 */
class KsSendReport extends \Magento\Framework\App\Action\Action
{

    /**
     * @var KsReportProductSubReasonFactory
     */
    protected $ksSubReasonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * @var KsReportProductHelper
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * KsSendReport Constructor
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsReportProductSubReasonFactory $ksSubReasonFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param KsReportProductHelper $ksReportHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksHelperData
     * @param \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        KsReportProductSubReasonFactory $ksSubReasonFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        KsReportProductHelper $ksReportHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksHelperData,
        \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository
    ) {
        $this->ksSubReasonFactory = $ksSubReasonFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        $this->ksReportHelper = $ksReportHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksHelperData = $ksHelperData;
        $this->ksResultRedirectFactory = $ksResultRedirectFactory;
        $this->ksProductRepository = $ksProductRepository;
        parent::__construct($ksContext);
    }

    /**
     * Send mail for seller
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /*get form data*/
        $ksReportFormData   = $this->getRequest()->getPostValue();
        $ksAllowAlert = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_allow_admin_alert',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        /*fetch receiver's email*/
        $ksAdminEmailOption = 'ks_marketplace_report/ks_report_product/ks_report_product_admin_email_option';
        $ksAdminSecondaryEmail ='ks_marketplace_report/ks_report_product/ks_report_product_admin_email';
        $ksStoreId = $this->ksHelperData->getKsCurrentStoreView();
        $ksReceiverDetails = $this->ksHelperData->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);

        $ksSender = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_sender_mail',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        $ksSenderInfo = $this->getKsSenderInfo($ksSender);
        /*initialise email validator*/
        $ksEmailValidator = new \Zend\Validator\EmailAddress();
        /*Fetch copy method*/
        $ksCopyMethod = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_mail_copy_method',
            $this->ksHelperData->getKsCurrentStoreView()
        );

        /*set email template variables*/
        if ($this->ksReportHelper->getKsCurrentCustomer()) {
            $ksReportFormData['ks-report-product-customer-email'] = $this->ksReportHelper->getKsCurrentCustomer()->getEmail();
            $ksReportFormData['ks-report-product-customer-name'] = $this->ksReportHelper->getKsCurrentCustomer()->getName();
        }
        if (!isset($ksReportFormData['ks-report-product-reason']) || (isset($ksReportFormData['ks-report-product-reason']) && !$ksReportFormData['ks-report-product-reason'])) {
            $ksReportFormData['ks-report-product-reason'] = 'N/A';
        }
        if (!isset($ksReportFormData['ks-report-product-sub-reason']) || (isset($ksReportFormData['ks-report-product-sub-reason']) && !$ksReportFormData['ks-report-product-sub-reason'])) {
            $ksReportFormData['ks-report-product-sub-reason'] = 'N/A';
        }
        if (!isset($ksReportFormData['ks-report-product-comment']) || (isset($ksReportFormData['ks-report-product-comment']) && !$ksReportFormData['ks-report-product-comment'])) {
            $ksReportFormData['ks-report-product-comment'] = 'N/A';
        }

        /*Send admin emails*/
        try {
            if ($ksAllowAlert) {
                /*fetch copy recievers data*/
                $ksEmailCopyRecievers = $this->ksHelperData->getKsConfigValue(
                    'ks_marketplace_report/ks_report_product/ks_mail_copy_receiver_emails',
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
                $ksReportFormData['ks-product-url'] = $this->ksProductRepository->get($ksReportFormData['ks-report-product-item-sku'])->getProductUrl();
                if ($ksCopyMethod==0) {
                    /*shoot email with all copy receiver's in BCC*/
                        $this->ksEmailHelper->ksSendAdminReportProductMail($ksReportFormData, $ksSenderInfo, $ksAdminName, $ksReceiverDetails['email'], $ksEmailCopyRecievers);
                } else {
                        $this->ksEmailHelper->ksSendAdminReportProductMail($ksReportFormData, $ksSenderInfo, $ksAdminName, $ksReceiverDetails['email'], $ksEmailCopyRecievers);
                    foreach ($ksEmailCopyRecievers as $email) {
                        $ksRecieverInfo['name']  = $ksAdminName;
                        $ksRecieverInfo['email'] = $email;
                        $this->ksEmailHelper->ksSendReportProductMailSeprateCopy($ksReportFormData, $ksSenderInfo, $ksRecieverInfo);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError("Your report could not be sent");
            $resultRedirect = $this->ksResultRedirectFactory->create();
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        $this->ksReportHelper->setKsSellerReportCount($ksReportFormData['ks-report-product-item-id']);
        /*send acknowledgment to customer*/
        $ksAcknowledgeCustomer = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_product/ks_customer_report_product_acknowledgement_mail',
            $this->ksHelperData->getKsCurrentStoreView()
        );
        if ($ksAcknowledgeCustomer) {
            try {
                $ksSender = $this->ksHelperData->getKsConfigValue(
                    'ks_marketplace_report/ks_report_product/ks_report_product_acknowledgement_mail_sender',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $ksSenderInfo = $this->getKsSenderInfo($ksSender);
                $ksRecieverInfo = ['name'=> $ksReportFormData['ks-report-product-customer-name'],'email'=>$ksReportFormData['ks-report-product-customer-email']];
                $this->ksEmailHelper->ksSendReportProductAcknowledgementMail($ksReportFormData, $ksSenderInfo, $ksRecieverInfo);
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Your report could not be sent')
                );
                $resultRedirect = $this->ksResultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        }
        $this->messageManager->addSuccess(
            __('Your report against the product has been sent successfully.')
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
