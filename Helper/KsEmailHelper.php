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

namespace Ksolves\MultivendorMarketplace\Helper;

use Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper;

class KsEmailHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML Path
     */
    public const XML_PATH_EMAIL_ADMIN_REPORT_PRODUCT = 'ks_marketplace_report/ks_report_product/ks_admin_alert_mail_template';
    public const XML_PATH_EMAIL_REPORT_PRODUCT_ACKNOWLEDGEMENT = 'ks_marketplace_report/ks_report_product/ks_acknowledgement_mail_template';
    public const XML_PATH_EMAIL_ADMIN_REPORT_SELLER = 'ks_marketplace_report/ks_report_seller/ks_admin_alert_mail_template';
    public const XML_PATH_EMAIL_REPORT_SELLER_ACKNOWLEDGEMENT = 'ks_marketplace_report/ks_report_seller/ks_acknowledgement_mail_template';
    public const XML_PATH_SELLER_NEW_ORDER_MAIL = 'ks_marketplace_sales/ks_order_settings/ks_seller_new_order_email_template';
    public const ORDER_EMAIL_TEMPLATE_ID = 'ks_marketplace_sales_ks_order_settings_ks_new_order_email_template';
    public const XML_PATH_REQUEST_INVOICE_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_request_invoice_email_template';
    public const XML_PATH_INVOICE_APPROVAL_NOTIFICATION_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_approval_notification_template';
    public const XML_PATH_INVOICE_REJECTION_MAIL = 'ks_marketplace_sales/ks_invoice_settings/ks_invoice_rejection_notification_template';
    public const XML_PATH_REQUEST_SHIPMENT_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_request_shipment_email_template';
    public const XML_PATH_SHIPMENT_APPROVAL_NOTIFICATION_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_shipment_approval_notification_template';
    public const XML_PATH_SHIPMENT_REJECTION_MAIL = 'ks_marketplace_sales/ks_shipment_settings/ks_shipment_rejection_notification_template';
    public const XML_PATH_REQUEST_CREDITMEMO_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_request_creditmemo_email_template';
    public const XML_PATH_CREDITMEMO_APPROVAL_NOTIFICATION_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_approval_notification_template';
    public const XML_PATH_CREDITMEMO_REJECTION_MAIL = 'ks_marketplace_sales/ks_creditmemo_settings/ks_creditmemo_rejection_notification_template';
    public const XML_PATH_SELLER_FORGOT_PASSWORD_MAIL = 'marketplace/ks_forgot_password_settings/ks_seller_forgot_password_email';
    public const KSOLVES_CONFIG_MODULE_PATH = 'ks_marketplace_catalog/ks_product_settings/ks_admin_email';
    public const XML_PATH_PRODUCT_APPROVE_MAIL = 'ks_marketplace_catalog/ks_product_settings/ks_approval_email';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $ksInlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $ksTransportBuilder;

    /**
     * @var ksTemplate
     */
    protected $ksTemplate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $ksLocaleCurrency;

    /**
     * @param Magento\Framework\App\Helper\Context              $ksContext
     * @param Magento\Framework\Translate\Inline\StateInterface $ksInlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder  $ksTransportBuilder
     * @param Magento\Store\Model\StoreManagerInterface         $ksStoreManager
     * @param Magento\Framework\Locale\CurrencyInterface        $ksLocaleCurrency
     * @param KsDataHelper $ksHelperData
     * @param KsProductTypeHelper $ksProductTypeHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Framework\Translate\Inline\StateInterface $ksInlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $ksTransportBuilder,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency,
        KsDataHelper $ksHelperData,
        KsProductTypeHelper $ksProductTypeHelper
    ) {
        parent::__construct($ksContext);
        $this->ksInlineTranslation = $ksInlineTranslation;
        $this->ksTransportBuilder = $ksTransportBuilder;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksHelperData = $ksHelperData;
        $this->ksLocaleCurrency        = $ksLocaleCurrency;
        $this->ksProductTypeHelper      = $ksProductTypeHelper;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int $storeId
     *
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->ksStoreManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $senderEmail = $senderInfo['email'];
        $adminEmail = $this->getConfigValue(
            'trans_email/ident_general/email',
            $this->getStore()->getStoreId()
        );
        $senderInfo['email'] = $adminEmail;
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name'])
            ->setReplyTo($senderEmail, $senderInfo['name']);
        return $this;
    }

    /**
     * [ksSendAdminReportProductMail description].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     * @param Array $bcc
     */
    public function ksSendAdminReportProductMail($emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail, $bcc = [])
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_REPORT_PRODUCT);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);

        if (count($bcc) > 0) {
            foreach ($bcc as $bccAddress) {
                $this->ksTransportBuilder->addBcc($bccAddress);
            }
        }
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSendReportProductMailSeprateCopy description].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksSendReportProductMailSeprateCopy($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_REPORT_PRODUCT);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSendReportProductAcknowledgementMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function ksSendReportProductAcknowledgementMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_REPORT_PRODUCT_ACKNOWLEDGEMENT);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSendAdminReportSellerMail description].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     * @param Array $bcc
     */
    public function ksSendAdminReportSellerMail($emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail, $bcc = [])
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_REPORT_SELLER);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverEmail, $receiverName);
        if (count($bcc) > 0) {
            foreach ($bcc as $bccAddress) {
                $ksTemplate->addBcc($bccAddress);
            }
        }
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSendReportSellerMailSeprateCopy description].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksSendReportSellerMailSeprateCopy($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_REPORT_SELLER);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSendReportSellerAcknowledgementMail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function ksSendReportSellerAcknowledgementMail($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_EMAIL_REPORT_SELLER_ACKNOWLEDGEMENT);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * Send Email
     *
     * @param [string] $ksTemplatePath
     * @param [array] $ksTemplateVariables
     * @param [array] $ksSenderInfo
     * @param [array] $ksReceiverInfo
     */
    public function ksSendEmail($ksTemplatePath, $ksTemplateVariables, $ksSenderInfo, $ksReceiverInfo)
    {
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplatePath)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($ksTemplateVariables)
            ->setFrom($ksSenderInfo)
            ->addTo($ksReceiverInfo['email'], $ksReceiverInfo['name']);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksGetNameFromEmail description].
     *
     * @param String $emailTemplateVariables
     */
    public function ksGetNameFromEmail($email)
    {
        $ksEmail = explode('@', $email);
        $ksNameArray = explode('.', $ksEmail[0]);
        $ksName = "";
        if (count($ksNameArray) > 1) {
            foreach ($ksNameArray as $ksVar) {
                $ksName = $ksName . " " . ucfirst($ksVar);
            }
        } else {
            $ksName = ucfirst($ksNameArray[0]);
        }
        return $ksName;
    }

    /**
     * [ksSellerOrderMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksSellerOrderMail($emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail)
    {
        $this->ksTemplate = self::ORDER_EMAIL_TEMPLATE_ID;
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksSellerNewOrderMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksSellerNewOrderMail($emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail)
    {
        $this->ksTemplate = $this->getTemplateId(self::XML_PATH_SELLER_NEW_ORDER_MAIL);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksRequestInvoiceMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksRequestInvoiceMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksInvoiceApprovalMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksInvoiceApprovalMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksInvoiceRejectionMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksInvoiceRejectionMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksRequestShipmentMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksRequestShipmentMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksShipmentApprovalMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksShipmentApprovalMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksShipmentRejectionMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksShipmentRejectionMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksRequestCreditMemoMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksRequestCreditMemoMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksCreditMemoApprovalMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksCreditMemoApprovalMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * [ksCreditMemoRejectionMail].
     *
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param Array $receiverInfo
     */
    public function ksCreditMemoRejectionMail($emailTemplateVariables, $ksTemplate, $senderInfo, $receiverName, $receiverEmail)
    {
        $ksTemplate = $this->getTemplateId($ksTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($senderInfo)->addTo($receiverEmail, $receiverName);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }

    /**
    * Get sender email and name
    *
    * @return Array
    */
    public function getKsSenderInfo($sender)
    {
        switch ($sender) {
            case 'general':
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_general/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 'sales':
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_sales/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_sales/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 'support':
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_support/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_support/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 'custom1':
                $senderInfo['name'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom1/name',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                $senderInfo['email'] = $this->ksHelperData->getKsConfigValue(
                    'trans_email/ident_custom1/email',
                    $this->ksHelperData->getKsCurrentStoreView()
                );
                break;

            case 'custom2':
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

    /**
     * [sendEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCategoryRequestsNotification($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_CATEGORY_REQUESTS_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCategoryApprovalNotification($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_CATEGORY_APPROVAL_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCategoryRejectionNotification($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_CATEGORY_REJECTION_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCategoryAssignNotification($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_CATEGORY_ASSIGN_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * [sendEmail description].
     *
     * @param Mixed $emailTemplateVariables
     * @param Mixed $senderInfo
     * @param Mixed $receiverInfo
     */
    public function sendCategoryUnassignNotification($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->_template = $this->getTemplateId(self::XML_PATH_CATEGORY_UNASSIGN_MAIL);
        $this->_inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->_inlineTranslation->resume();
    }

    /**
     * Get Admin Email
     *
     * @return string
     */
    public function getKsDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getKsAdminEmailId($storeId = null)
    {
        return $this->getConfigValue(static::KSOLVES_CONFIG_MODULE_PATH, $storeId);
    }

    /**
     * To Send Mail When Product Attribute of Seller is Approved
     *
     * @param Mixed $ksEmailTemplateVariables
     * @param Mixed $ksSenderInfo
     * @param Mixed $ksReceiverInfo
     */
    public function ksSendProductAttributeMail($ksEmailTemplate, $ksEmailTemplateVariables, $ksSender, $ksReceiverInfo, $ksMultiple = null)
    {
        $this->ksTemplate = $this->getTemplateId($ksEmailTemplate);
        $ksSenderInfo = $this->getKsSenderInfo($ksSender);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($ksEmailTemplateVariables)
            ->setFrom($ksSenderInfo)
            ->addTo($ksReceiverInfo['email'], $ksReceiverInfo['name']);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            if (!$ksMultiple) {
                $this->ksMessageManager->addError($e->getMessage());
            }
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * To Send Mail When Product Attribute of Seller is Requested
     *
     * @param Mixed $ksEmailTemplateVariables
     * @param Mixed $ksSenderInfo
     * @param Mixed $ksReceiverInfo
     */
    public function ksSendRequestProductAttributeMail($ksEmailTemplate, $ksEmailTemplateVariables, $ksRecieverInfo, $ksSenderInfo, $ksMultiple = null)
    {
        $this->ksTemplate = $this->getTemplateId($ksEmailTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($ksEmailTemplateVariables)
            ->setFrom($ksSenderInfo)
            ->addTo($ksRecieverInfo['email'], $ksRecieverInfo['name']);
        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            if (!$ksMultiple) {
                $this->ksMessageManager->addError($e->getMessage());
            }
        }
        $this->ksInlineTranslation->resume();
    }

    /**
     * To send mail to seller when product is approved,rejected.
     *
     * @param String $ksEmailTemplate
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param String $receiverName
     * @param String $receiverEmail
     */
    public function ksProductApproval($ksEmailTemplate, $emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail)
    {
        $this->ksTemplate = $this->getTemplateId($ksEmailTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverEmail, $receiverName);

        $transport = $this->ksTransportBuilder->getTransport();
        $transport->sendMessage();
        $this->ksInlineTranslation->resume();
    }

    /**
     * To send mail to seller when product is assigned by admin
     *
     * @param String $ksEmailTemplate
     * @param Array $emailTemplateVariables
     * @param Array $senderInfo
     * @param String $receiverName
     * @param String $receiverEmail
     */
    public function ksAssignProductMail($ksEmailTemplate, $emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail)
    {
        $this->ksTemplate = $this->getTemplateId($ksEmailTemplate);
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->ksStoreManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverEmail, $receiverName);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }


    /**
     * Send Mail to Seller when Admin Approves Product Request
     * @param int $ksSellerId, int $ksProductIds
     */
    public function ksSendEmailProductApprove($ksSellerId, $ksProductIds)
    {
        $ksEmailEnabled = $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_catalog/ks_product_settings/ks_approval_email',
            $this->ksHelperData->getKsCurrentStoreView()
        );

        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSender = $this->ksHelperData->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_settings/ks_email_sender',
                $this->ksHelperData->getKsCurrentStoreView()
            );
            $ksSenderInfo = $this->getKsSenderInfo($ksSender);

            //Get Receiver Info
            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);

            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductIds'] = $ksProductIds;

            // Send Mail
            $this->ksProductApproval(
                self::XML_PATH_PRODUCT_APPROVE_MAIL,
                $ksTemplateVariable,
                $ksSenderInfo,
                ucwords($ksReceiverDetails['name']),
                $ksReceiverDetails['email']
            );
        }
    }

    /**
     * get Product Price with currency
     *
     * @param String $ksPrice
     */
    public function ksProductPriceBaseCurrency($ksPrice)
    {
        $ksStore = $this->ksStoreManager->getStore(0);
        $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());

        return $ksCurrency->toCurrency(sprintf("%f", $ksPrice));
    }

    /**
     * Send Mail for resetting password
     */
    public function ksSellerForgotPasswordMail($emailTemplateVariables, $senderInfo, $receiverName, $receiverEmail)
    {
        $this->ksTemplate = 'marketplace_ks_forgot_password_settings_ks_seller_forgot_password_email';
        $this->ksInlineTranslation->suspend();
        $ksTemplate = $this->ksTransportBuilder->setTemplateIdentifier($this->ksTemplate)
        ->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $this->ksStoreManager->getStore()->getId(),
            ]
        )
        ->setTemplateVars($emailTemplateVariables)
        ->setFrom($this->scopeConfig->getValue(
            $senderInfo,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        ))->addTo($receiverEmail, $receiverName);

        try {
            $transport = $this->ksTransportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
        $this->ksInlineTranslation->resume();
    }
}
