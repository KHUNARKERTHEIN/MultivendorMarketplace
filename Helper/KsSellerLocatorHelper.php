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
use Magento\Framework\App\RequestInterface;

/**
 * KsSellerLocatorHelper Class
 */
class KsSellerLocatorHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KS_MODULE_PATH = 'ks_marketplace_seller_locator';

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory
     */
    protected $ksSellerStore;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory
     */
    protected $ksCollection;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequestInterface;

    /**
     * @var  \Magento\Framework\View\Asset\Repository
     */
    protected $ksRepository;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $ksHelperImageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\SellerLocator\KsMapStyles
     */
    protected $ksMapStyle;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
     */
    protected $ksSellerCollection;

    /**
     * KsSellerLocatorHelper Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory $ksSellerStore
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksCollection
     * @param RequestInterface $ksRequestInterface
     * @param \Magento\Framework\View\Asset\Repository $ksRepository
     * @param \Magento\Catalog\Helper\ImageFactory $ksHelperImageFactory
     * @param \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\SellerLocator\KsMapStyles $ksMapStyle
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory $ksSellerStore,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksCollection,
        \Magento\Framework\View\Asset\Repository $ksRepository,
        \Magento\Catalog\Helper\ImageFactory $ksHelperImageFactory,
        \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\SellerLocator\KsMapStyles $ksMapStyle,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        RequestInterface $ksRequestInterface
    ) {
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerStore = $ksSellerStore;
        $this->ksCollection = $ksCollection;
        $this->ksRequestInterface = $ksRequestInterface;
        $this->ksRepository = $ksRepository;
        $this->ksHelperImageFactory = $ksHelperImageFactory;
        $this->ksMapStyle = $ksMapStyle;
        $this->ksSellerCollection = $ksSellerCollection;
        parent::__construct($ksContext);
    }

    /**
     * Get Configuration Values
     * @param $ksField, $ksStoreId = null
     * @return mixed
     */
    public function getKsSLConfigValue($ksField, $ksStoreId = null)
    {
        return $this->ksScopeConfig->getValue($ksField, ScopeInterface::SCOPE_STORE, $ksStoreId);
    }

    /**
     * Get Seller Locator Group Configuration Values
     * @param $ksCode = '', $ksStoreId = null
     * @return mixed
     */
    public function getKsSLConfig($ksCode = '', $ksStoreId = null)
    {
        $ksCode = ($ksCode !== '') ? '/' . $ksCode : '';
        return $this->getKsSLConfigValue(static::KS_MODULE_PATH  . '/ks_seller_locator' . $ksCode, $ksStoreId);
    }

    /**
     * Is Seller Locator enabled
     * @return bool
     */
    public function isKsSLEnabled()
    {
        return $this->getKsSLConfig('ks_seller_locator_enable');
    }

    /**
     * Get Google API Key
     * @return string
     */
    public function getKsApiKey()
    {
        return $this->getKsSLConfig('ks_seller_locator_google_map_key');
    }

    /**
     * Get Title for footer
     * @return string
     */
    public function getKsSLTitle()
    {
        return $this->getKsSLConfig('ks_seller_locator_title_footer');
    }

    /**
     * Get Title for product page
     * @return string
     */
    public function getKsLocateStoresTitle()
    {
        return $this->getKsSLConfig('ks_seller_locator_title_product_page');
    }

    /**
     * Get Seller Logo
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerLogo($ksSellerId)
    {
        $ksCurrentStore = $this->ksDataHelper->getKsCurrentStoreView();
        $ksSellerLogo = $this->ksSellerStore->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksCurrentStore)->getFirstItem()->getKsStoreLogo();
        return $this->getKsSellerStoreLogo($ksSellerLogo);
    }

    /**
     * Get Seller Banner
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerBanner($ksSellerId)
    {
        $ksCurrentStore = $this->ksDataHelper->getKsCurrentStoreView();
        $ksSellerBanner = $this->ksSellerStore->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksCurrentStore)->getFirstItem()->getKsStoreBanner();
        return $this->getKsSellerStoreLogo($ksSellerBanner);
    }

    /**
     * Get Seller Store
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerStoreName($ksSellerId)
    {
        $ksCurrentStore = $this->ksDataHelper->getKsCurrentStoreView();
        $ksSellerStoreName = $this->ksSellerCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getKsStoreName();
        return $ksSellerStoreName;
    }

    /**
     * Get Logo of Store
     * @param string $ksImage
     * @return string
     */
    public function getKsSellerStoreLogo($ksImage)
    {
        $ksImageDirectory = 'ksolves/multivendor/';
        $ksSellerStoreLogo = "";
        if ($ksImage) {
            $ksImagePath = $this->ksDataHelper->getKsStoreMediaUrl();
            $ksSellerStoreLogo = $ksImagePath.$ksImageDirectory.$ksImage;
        } else {
            $ksImagePlaceholder = $this->ksHelperImageFactory->create();
            return $this->ksRepository->getUrl($ksImagePlaceholder->getPlaceholder('small_image'));
        }
        return $ksSellerStoreLogo;
    }

    /**
     * Get KsLocation Field
     * @param int $ksSellerid
     * @return string
     */
    public function getKsLocation($ksSellerId)
    {
        $collection = $this->ksCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksLocation = "";
        foreach ($collection as $ksCollection) {
            $ksLocation = $ksCollection->getData('ks_location');
        }
        return $ksLocation;
    }
    
    /**
     * Get KsLatitude Field
     * @param int $ksSellerid
     * @return float
     */
    public function getKsLatitude($ksSellerId)
    {
        $collection = $this->ksCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksLatitude = "";
        foreach ($collection as $ksCollection) {
            $ksLatitude = $ksCollection->getData('ks_latitude');
        }
        return $ksLatitude;
    }

    /**
     * Get Seller Id
     * @return int
     */
    public function getKsSellerId()
    {
        return $this->ksRequestInterface->getParam('seller_id');
    }

    /**
     * Get KsLongitude Field
     * @param int $ksSellerid
     * @return float
     */
    public function getKsLongitude($ksSellerId)
    {
        $collection = $this->ksCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksLongitude = "";
        foreach ($collection as $ksCollection) {
            $ksLongitude = $ksCollection->getData('ks_longitude');
        }
        return $ksLongitude;
    }

    /**
     * Get Map style
     * @param $ksMapStyle
     * @return string
     */
    public function getKsMapTheme($ksMapStyle)
    {
        return $this->ksMapStyle->getKsMapData($ksMapStyle);
    }

    /**
    * Get KsSellerLocation State and Country
    * @param int $ksSellerid
    * @return string
    */

    public function getKsSellerLocation($ksSellerId)
    {
        $collection = $this->ksCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksLocation = "";
        $ksState="";

        foreach ($collection as $ksCollection) {
            $ksLocation = $ksCollection->getData('ks_location');
        }

        $ksCountryArray= explode(',', $ksLocation);

        if (is_numeric(end($ksCountryArray))) {
            unset($ksCountryArray[sizeof($ksCountryArray)-1]);
        }
        if (sizeof($ksCountryArray)>=2) {
            $ksState = $ksCountryArray[sizeof($ksCountryArray)-2];
        }
       
        $ksStateArray = explode(' ', $ksState);

        if (is_numeric(end($ksStateArray))) {
            unset($ksStateArray[sizeof($ksStateArray)-1]);
            $ksState = implode(' ', $ksStateArray);
        }

        $ksAddress = $ksState.",".end($ksCountryArray);
        return trim($ksAddress, ",");
    }
}
