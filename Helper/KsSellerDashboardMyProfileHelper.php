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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup\CollectionFactory as KsSellerGroupCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory as KsSellerStoreCollectionFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * KsSellerDashboardMyProfileHelper Class
 */
class KsSellerDashboardMyProfileHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var KsSellerStoreCollectionFactory
     */
    protected $ksSellerStoreCollectionFactory;
    
    /**
     * @var KsSellerGroupCollectionFactory
     */
    protected $ksSellerGroupCollectionFactory;
    
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $ksCountryFactory;
    
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $ksRegionFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsBanners\CollectionFactory
     */
    protected $ksBannersCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsLogo
     */
    protected $ksLogo;

    /**
     * @var \Magento\Framework\View\Page\Title
     */
    protected $ksPageTitle;
    
    /**
     * AbstractData constructor.
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param KsSellerCollectionFactory $ksSellerCollectionFactory
     * @param KsSellerGroupCollectionFactory $ksSellerGroupCollectionFactory
     * @param KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory
     * @param \Magento\Directory\Model\CountryFactory $ksCountryFactory
     * @param \Magento\Directory\Model\RegionFactory $ksRegionFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBanners\CollectionFactory $ksBannersCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Magento\Customer\Model\Session $ksCustomerSession,
        KsSellerGroupCollectionFactory $ksSellerGroupCollectionFactory,
        KsSellerCollectionFactory $ksSellerCollectionFactory,
        KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory,
        \Magento\Directory\Model\CountryFactory $ksCountryFactory,
        \Magento\Directory\Model\RegionFactory $ksRegionFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBanners\CollectionFactory $ksBannersCollection,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsLogo $ksLogo,
        \Magento\Framework\View\Page\Title $ksPageTitle
    ) {
        $this->ksCustomerFactory                = $ksCustomerFactory;
        $this->ksCustomerSession                = $ksCustomerSession;
        $this->ksSellerCollectionFactory        = $ksSellerCollectionFactory;
        $this->ksSellerGroupCollectionFactory   = $ksSellerGroupCollectionFactory;
        $this->ksSellerStoreCollectionFactory   = $ksSellerStoreCollectionFactory;
        $this->ksCountryFactory                 = $ksCountryFactory;
        $this->ksRegionFactory                  = $ksRegionFactory;
        $this->ksBannersCollection              = $ksBannersCollection;
        $this->ksStoreManager                   = $ksStoreManager;
        $this->ksLogo                           = $ksLogo;
        $this->ksPageTitle                      = $ksPageTitle;
        parent::__construct($ksContext);
    }

    /**
     * Return seller collection
     * @param $ksSellerId
     * @return array
     */
    public function getKsSellerCollection($ksSellerId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        return $ksSellerCollection;
    }
    
    /**
     * Return customer collection
     * @param $ksSellerId
     * @return array
     */
    public function getCustomerCollection($ksSellerId)
    {
        return $this->ksCustomerFactory->create()->load($ksSellerId);
    }

    /**
     * Return seller phone
     * @param $ksSellerId
     * @return int
     */
    public function getKsSellerPhone($ksSellerId)
    {
        $ksSeller = $this->getCustomerCollection($ksSellerId);
        if ($ksSeller->getPrimaryBillingAddress()== false) {
            return null;
        }
        $ksSellerPhone   = $ksSeller->getPrimaryBillingAddress();
        return $ksSellerPhone;
    }

    /**
     * Return store status
     * @param $ksSellerId
     * @return string
     */
    public function getKsStoreStatus($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        $ksStoreStatus = $ksSellerCollection->getKsStoreStatus();
        if ($ksStoreStatus==1) {
            $ksStoreStatus = "Active";
        } else {
            $ksStoreStatus = "Not Active";
        }
        return $ksStoreStatus;
    }
    
    /**
     * Return seller status
     * @param $ksSellerId
     * @return string
     */
    public function getKsSellerStatus($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        $ksSellerStatus = $ksSellerCollection->getKsSellerStatus();
        if ($ksSellerStatus==1) {
            $ksSellerStatus = "Active";
        } else {
            $ksSellerStatus = "Not Active";
        }
        return $ksSellerStatus;
    }

    /**
     * Return seller available countries name
     * @param $ksSellerId
     * @return string
     */
    public function getKsAvailableCountryName($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        if ($ksSellerCollection->getKsStoreAvailableCountries()) {
            $ksCountryCodes = explode(',', $ksSellerCollection->getKsStoreAvailableCountries());
            foreach ($ksCountryCodes as $ksCountryCode) {
                $ksCountry = $this->ksCountryFactory->create()->loadByCode($ksCountryCode);
                $ksCountryName[] = $ksCountry->getName();
            }
            return implode(',', $ksCountryName);
        } else {
            return " ";
        }
    }

    /**
     * Return country name
     * @param $ksSellerId
     * @return string
     */
    public function getKsCountryName($ksSellerId)
    {
        $ksSeller = $this->getCustomerCollection($ksSellerId);
        if ($ksSeller->getPrimaryBillingAddress()) {
            $ksCountryCode = $ksSeller->getPrimaryBillingAddress()->getCountryId();
            $ksCountry = $this->ksCountryFactory->create()->loadByCode($ksCountryCode);
            return $ksCountry->getName();
        } else {
            return " ";
        }
    }
    
    /**
     * Return seller available countries code
     * @param $ksSellerId
     * @return string
     */
    public function getKsAvailableCountryCode($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        return $ksSellerCollection->getKsStoreAvailableCountries();
    }
    
    /**
     * Return seller group name
     * @param $ksSellerId
     * @return string
     */
    public function getKsSellerGroup($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId);
        $ksSellerGroupId="";
        if ($ksSellerCollection->getSize() > 0) {
            foreach ($ksSellerCollection as $ksData) {
                $ksSellerGroupId = $ksData->getKsSellerGroupId();
            }
        }
        $ksSellerGroupCollection = $this->ksSellerGroupCollectionFactory->create()
                            ->addFieldToFilter('id', $ksSellerGroupId);
        if ($ksSellerGroupCollection->getSize() > 0) {
            foreach ($ksSellerGroupCollection as $ksData) {
                $ksSellerGroupName = $ksData->getKsSellerGroupName();
            }
        } else {
            return " ";
        }
        return $ksSellerGroupName;
    }
    
    /**
     * Return seller company name
     * @param $ksSellerId
     * @return string
     */
    public function getKsCompanyName($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        return $ksSellerCollection->getKsCompanyName();
    }
    
    /**
     * Return seller comapny address
     * @param $ksSellerId
     * @return string
     */
    public function getKsCompanyAddress($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        return $ksSellerCollection->getKsCompanyAddress();
    }
    
    /**
     * Return seller company contact number
     * @param $ksSellerId
     * @return int
     */
    public function getKsCompanyContactNumber($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        return $ksSellerCollection->getKsCompanyContactNo();
    }

    /**
     * Return seller company email
     * @param $ksSellerId
     * @return string
     */
    public function getKsCompanyEmail($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        return $ksSellerCollection->getKsCompanyContactEmail();
    }
    
    /**
     * Return seller country name
     * @param $ksSellerId
     * @return string
     */
    public function getKsCountry($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        $ksCompanyCountryCode = $ksSellerCollection->getKsCompanyCountry();
        if ($ksCompanyCountryCode) {
            $ksCountry = $this->ksCountryFactory->create()->loadByCode($ksCompanyCountryCode);
            $ksCompanyCountry = $ksCountry->getName();
            return $ksCompanyCountry;
        } else {
            return "";
        }
    }
    
    /**
     * Return seller state name
     * @param $ksSellerId
     * @return string
     */
    public function getKsState($ksSellerId)
    {
        $ksSellerCollection = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        $ksCompanyStateCode = $ksSellerCollection->getKsCompanyStateId();
        if ($ksCompanyStateCode) {
            $ksRegion = $this->ksRegionFactory->create()->load((int)$ksCompanyStateCode);
            $ksCompanyState = $ksRegion->getName();
            return $ksCompanyState;
        } else {
            return "";
        }
    }
    
    /**
     * Return seller store collection
     * @param $ksSellerId ,ksStoreId
     * @return array
     */
    public function getKsSellerStoreCollection($ksSellerId, $ksStoreId)
    {
        $ksCollection = $this->ksSellerStoreCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId);
        if ($ksStoreId != 0) {
            if (count($ksCollection) != 0) {
                return $ksCollection;
            } else {
                return  $this->ksSellerStoreCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', 0);
            }
        } else {
            return $ksCollection;
        }
    }

    /**
     * Return seller config data
     * @return boolean
     */
    public function getKsSellerConfigData($ksCode)
    {
        return $this->scopeConfig->getValue(
            'ks_marketplace_seller_portal_profile/ks_seller_profile_page/'.$ksCode,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return catalog config data
     * @return boolean
     */
    public function getKsCatalogConfigData()
    {
        return $this->scopeConfig->getValue(
            'catalog/frontend/grid_per_page_values',
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Return seller banner collection
     * @return array
     */
    public function getKsSellerBannerCollection($ksSellerId)
    {
        return $this->ksBannersCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
    }

    /**
     * Get Seller Panel Logo
     * @return string
     */
    public function getKsSellerPanelLogo()
    {
        $ksImg = $this->scopeConfig->getValue(
            'ks_marketplace_seller_portal_profile/ks_marketplace_sellerpanel/ks_sellerpanel_logo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($ksImg)) {
            $ksImg = $this->ksLogo->getKsIcon('Ksolves_MultivendorMarketplace::images/KMM_Logo.png');
        } else {
            $ksImg = $this->ksStoreManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'marketplace/sellerpanel/' . $ksImg;
        }
        return $ksImg;
    }

    /**
     * Get Page Title
     * @return string
     */
    public function getKsPageTitle()
    {
        return $this->ksPageTitle->getShort();
    }


    /**
     * Get Company Info Array
     *
     * @param Integer $ksSellerId
     * @return Array
     */
    public function getKsCompanyDetails($ksSellerId)
    {
        $ksSellerDetails = $this->getKsSellerCollection($ksSellerId)->getFirstItem();
        $ksCompanyDetails = [
            $ksSellerDetails->getKsCompanyName(),
            $ksSellerDetails->getKsCompanyAddress(),
            $ksSellerDetails->getKsCompanyContactNo(),
            $ksSellerDetails->getKsCompanyEmail(),
            $ksSellerDetails->getKsCompanyEmail(),
            $ksSellerDetails->getKsCompanyPostcode(),
            $this->getKsState($ksSellerId),
            $this->getKsCountry($ksSellerId),
            $ksSellerDetails->getKsCompanyTaxvatNumber()
        ];

        $ksCompanyDetailsFiltered = array_filter($ksCompanyDetails, function($ksValue) {
            return ($ksValue !== null && strlen($ksValue) > 0);
        });

        return $ksCompanyDetailsFiltered;
    }

    /**
     * Check wether to show seller panel logo
     * @return boolean
     */
    public function isKsSellerPanelLogoEnable()
    {
        return $this->scopeConfig->getValue('ks_marketplace_seller_portal_profile/ks_marketplace_sellerpanel/ks_sellerpanel_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * get seller portal path
     * @return string
     */
    public function getSellerPortalPath()
    {
        if ($this->getKsSellerConfigData('ks_homepage')) {
            $ksPath = 'multivendor\sellerprofile\homepage';
        } else {
            $ksPath = 'multivendor/producttype/index';
        }
        return $ksPath;
    }
}
