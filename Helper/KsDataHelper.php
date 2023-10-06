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

use Magento\Store\Model\ScopeInterface;

/**
 * KsDataHelper Class
 */
class KsDataHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const KSOLVES_MARKETPLACE_CONFIG_SETTING_PATH = "marketplace";
    public const KSOLVES_MARKETPLACE_SELLER_CONFIG_SETTING_PATH = "ks_marketplace_seller";
    public const KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH = "ks_marketplace_catalog";
    public const KSOLVES_SENDER_PATH = "trans_email";
    public const KSOLVES_MARKETPLACE_SALES_CONFIG_SETTING_PATH = "ks_marketplace_sales";
    public const KSOLVES_MARKETPLACE_LOGIN_AND_REGISTRATION_SETTING_PATH = "ks_marketplace_login_and_registration";

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $ksTimeZone;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $ksCustomerModel;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $ksCustomerCollectionFactory;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * AbstractData constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksTimeZone
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession,
     * @param Magento\Framework\Registry $ksRegistry
     * @param \Magento\Customer\Model\Customer $ksCustomerModel
     * @param \Magento\Directory\Model\CurrencyFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksTimeZone,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Magento\Customer\Model\Customer $ksCustomerModel,
        \Magento\Directory\Model\CurrencyFactory $ksCurrencyFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory
    ) {
        $this->ksTimeZone = $ksTimeZone;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksRegistry = $ksRegistry;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksCustomerModel = $ksCustomerModel;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksCustomerCollectionFactory = $ksCustomerCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * get configuration values
     * @param $ksField
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigValue($ksField, $ksStoreId = null)
    {
        return $this->scopeConfig->getValue(
            $ksField,
            ScopeInterface::SCOPE_STORE,
            $ksStoreId
        );
    }

    /**
     * get store id
     * @return int
     */
    public function getKsStore()
    {
        return $this->ksStoreManager->getStore();
    }


    /**
     * get Website id
     * @return int
     */
    public function getKsWebsiteId()
    {
        return $this->getKsStore()->getWebsiteId();
    }

    /**
     * get customer configuration values
     * @param $ksField
     * @param null $ksWebsiteId
     * @return mixed
     */
    public function getKsCustomerConfigValue($ksField, $ksWebsiteId = null)
    {
        return $this->scopeConfig->getValue(
            $ksField,
            ScopeInterface::SCOPE_WEBSITE,
            $ksWebsiteId
        );
    }

    /**
     * get seller setting group configuration values
     * @param string $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSellerSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static:: KSOLVES_MARKETPLACE_SELLER_CONFIG_SETTING_PATH . '/ks_seller_settings' . $ksCode, $ksStoreId);
    }

    /**
     * get customer configuration values
     * @param string $ksCode
     * @param int $ksWebsiteId
     * @return mixed
     */
    public function getKsConfigCustomerField($ksWebsiteId, $ksCode = '')
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsCustomerConfigValue('customer/address' . $ksCode, $ksWebsiteId);
    }

    /**
     * @param string $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigProductSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH . '/ks_product_settings' . $ksCode, $ksStoreId);
    }

    /**
     * get date and time according to current timezone
     * @param string $ksDateTime
     * @return string
     */
    public function getKsDateTime($ksDateTime)
    {
        return $this->ksTimeZone->formatDateTime(
            $ksDateTime,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
    }

    /**
     * Get Config Value of Product Type Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSellerProductSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH . '/ks_product_type_settings' . $ksCode, $ksStoreId);
    }

    /**
     * Get Config Value of Seller Panel Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSellerPanelSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CONFIG_SETTING_PATH . '/ks_seller_panel' . $ksCode, $ksStoreId);
    }

    /**
     * Get Config Value of Price Comparsion Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigPriceComparisonSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CONFIG_SETTING_PATH . '/ks_price_comparison_product_settings' . $ksCode, $ksStoreId);
    }

    /**
     * @param string $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSenderSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_SENDER_PATH . $ksCode, $ksStoreId);
    }

    /**
     * @param string $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigCustomerScopeField($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsCustomerConfigValue('customer/account_share' . $ksCode, $ksStoreId);
    }

    /**
     * Get Current Store View id
     * @return int
     */
    public function getKsCurrentStoreView()
    {
        return $this->ksStoreManager->getStore()->getStoreId();
    }

    /**
     * Get Current Website id
     * @return int
     */
    public function getKsCurrentWebsiteId()
    {
        return $this->ksStoreManager->getStore()->getWebsiteId();
    }


    /**
     * Get Current Customer id
     * @return int
     */
    public function getKsCustomerId()
    {
        return $this->ksCustomerSession->getCustomer()->getId();
    }

    /**
     * Get Current Store Media Base Url
     * @return string
     */
    public function getKsStoreMediaUrl()
    {
        return $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get Config Value of Product Attribute Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSellerProductAttributeSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH . '/ks_product_attribute_settings' . $ksCode, $ksStoreId);
    }

    /**
     * Get current product id
     *
     * @return int
     */
    public function getKsCurrentProductId()
    {
        return $this->ksRegistry->registry('current_product')->getId();
    }

    /**
     * Get admin info
     *
     * @return array
     */
    public function getKsAdminInfo()
    {
        $ksAdmin = [];
        $ksAdmin['email'] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_email');
        $ksAdmin['name'] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_name');
        return $ksAdmin;
    }

    /**
     * Get Config Value of Product Attribute Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigShipmentSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_SALES_CONFIG_SETTING_PATH . '/ks_shipment_settings' . $ksCode, $ksStoreId);
    }

    /**
     * get customer details by id
     * @param Integer $ksCustomerId
     */
    public function getKsCustomerById($ksCustomerId)
    {
        return $this->ksCustomerModel->load($ksCustomerId);
    }

    /**
     * Get Default Attributes
     * @return array
     */
    public function getKsDefaultAttributes()
    {
        // Get Default Attribute Set Value
        $ksDefault = $this->ksProductFactory->create()->getDefaultAttributeSetId();
        // Get Admin's Given Attribute Set
        $ksAdminGiven = $this->getKsConfigSellerProductAttributeSetting('ks_product_attribute_set');
        // Add Default
        $ksSetArray = [$ksDefault];
        if ($ksAdminGiven) {
            $ksDefaultArray = explode(',', $ksAdminGiven);
            foreach ($ksDefaultArray as $ksValue) {
                $ksSetArray[] = $ksValue;
            }
        }
        return $ksSetArray;
    }

    /**
     * Get Base Currency Code
     * @return string
     */
    public function getKsBaseCurrenyCode()
    {
        return $this->getKsConfigValue('currency/options/base', 0);
    }

    /**
     * Get Currency Symbol
     * @return string
     */
    public function ksGetCurrency()
    {
        $ksCurrencyCode = $this->ksStoreManager->getStore()->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }

    /**
     * Get Currency Symbol
     * @return string
     */
    public function ksGetCurrencyAccordingToStore($ksStoreId)
    {
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }

    /**
     * Get Config Value of Product Type Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigSellerProductTypeSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static:: KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH . '/ks_product_type_settings' . $ksCode, $ksStoreId);
    }

    /**
     * Get Config Value of Product Category Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigProductCategorySetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_CATALOG_CONFIG_SETTING_PATH . '/ks_product_categories' . $ksCode, $ksStoreId);
    }

    /**
     * Get customer details
     *
     * @param  [int] $ksId
     * @return [string] array
     */
    public function getKsCustomerInfo($ksId)
    {
        $ksCustomer= $this->ksCustomerCollectionFactory->create()->addFieldToFilter('entity_id', $ksId)->getFirstItem();
        $kData = [];
        $kData['email'] = $ksCustomer->getEmail();
        $kData['name'] = $ksCustomer->getName();
        return $kData;
    }

    /**
     * Get Config Value of Login and Registration Settings
     * @param $ksCode
     * @param null $ksStoreId
     * @return mixed
     */
    public function getKsConfigLoginAndRegistrationSetting($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsConfigValue(static::KSOLVES_MARKETPLACE_LOGIN_AND_REGISTRATION_SETTING_PATH . '/ks_marketplace_login_and_registration_settings' . $ksCode, $ksStoreId);
    }

    /**
     * Get Admin's Email Details
     * @param $ksAdminEmailOption
     * @param $ksAdminSecondaryEmail
     * @param $ksStoreId
     */
    public function getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId)
    {
        $ksAdminEmailOptions = $this->getKsConfigValue($ksAdminEmailOption, $ksStoreId);
        $ksAdmin = [];
        if ($ksAdminEmailOptions==0) {
            $ksAdmin['email'][] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_email');
            $ksAdmin['name'] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_name');
        } elseif ($ksAdminEmailOptions==1) {
            $ksAdmin['email'][]= $this->getKsConfigValue($ksAdminSecondaryEmail, $ksStoreId);
            $ksAdmin['name'] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_name');
        } else {
            $ksAdmin['email'][]= $this->getKsConfigValue($ksAdminSecondaryEmail, $ksStoreId);
            $ksAdmin['email'][]= $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_email');
            $ksAdmin['name'] = $this->getKsConfigValue('ks_marketplace_general/ks_general_settings/ks_admin_name');
        }
        return $ksAdmin;
    }
}
