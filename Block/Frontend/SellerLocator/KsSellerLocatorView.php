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
namespace Ksolves\MultivendorMarketplace\Block\Frontend\SellerLocator;

use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;

/**
 * KsSellerLocatorView content block
 */
class KsSellerLocatorView extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper
     */
    protected $ksHelperData;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequestInterface;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $ksJsonEncoder;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     */
    protected $ksSellerCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsLogo
     */
    protected $ksIcon;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     * @param Registry $ksRegistry
     * @param RequestInterface $ksRequestInterface
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
        Registry $ksRegistry,
        RequestInterface $ksRequestInterface,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Ksolves\MultivendorMarketplace\Helper\KsLogo $ksIcon,
        array $data = []
    ) {
        $this->ksHelperData = $ksHelperData;
        $this->ksRegistry = $ksRegistry;
        $this->ksRequestInterface = $ksRequestInterface;
        $this->ksJsonEncoder = $ksJsonEncoder;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksIcon = $ksIcon;
        parent::__construct($ksContext, $data);
    }

    /**
     * Check Module Status
     * @return string
     */
    public function getKsModuleStatus()
    {
        return $this->ksHelperData->isKsSLEnabled();
    }
    /**
     * Get api from the admin
     * @return string
     */
    public function getKsGoogleApiKey()
    {
        return $this->ksHelperData->getKsApiKey();
    }

    /**
     * Get Default Distance from the admin
     * @return float
     */
    public function getKsDefaultDistance()
    {
        return $this->ksHelperData->getKsSLConfig("ks_seller_locator_distance");
    }

    /**
     * Get Default Location from the admin
     * @return string
     */
    public function getKsDefaultLocation()
    {
        return $this->ksHelperData->getKsSLConfig("ks_seller_locator_location");
    }

    /**
     * Get Default Unit from the admin
     * @return string
     */
    public function getKsDefaultUnit()
    {
        return $this->ksHelperData->getKsSLConfig("ks_seller_locator_distance_unit");
    }

    /**
     * Get Map Latitude from the admin
     * @return float
     */
    public function getKsMapLatitude()
    {
        return $this->ksHelperData->getKsSLConfig("ks_seller_locator_latitude");
    }

    /**
     * Get Map Longitude from the admin
     * @return float
     */
    public function getKsMapLongitude()
    {
        return $this->ksHelperData->getKsSLConfig("ks_seller_locator_longitude");
    }

    /**
     * Get Map Style from the admin
     * @return string
     */
    public function getKsMapTemplate()
    {
        $ksMapStyle = $this->ksHelperData->getKsSLConfig("ks_seller_locator_map_style");
        return $this->ksJsonEncoder->encode($this->ksHelperData->getKsMapTheme($ksMapStyle));
    }

    /**
     * Get Store Name(if customer filter data by Store Name)
     * @return string
     */
    public function getKsStoreName()
    {
        return $this->ksRegistry->registry('ks_store_name');
    }

    /**
     * Get Product Name(if customer filter data by Product Name)
     * @return string
     */
    public function getKsProductName()
    {
        return $this->ksRegistry->registry('ks_product_name');
    }

    /**
     * Get Distance(if customer filter data by Distance)
     * @return float
     */
    public function getKsDistance()
    {
        return $this->ksRegistry->registry('ks_distance');
    }

    /**
     * Get Address(if customer filter data by Address)
     * @return string
     */
    public function getKsAddress()
    {
        return $this->ksRegistry->registry('ks_address');
    }

    /**
     * Get Distance Unit(if customer filter data by Distance Unit)
     * @return string
     */
    public function getKsDistanceUnit()
    {
        return $this->ksRegistry->registry('ks_distance_unit');
    }

    /**
     * Get Sort By value (if customer filter data by sort by)
     * @return string
     */
    public function getKsSortBy()
    {
        return $this->ksRegistry->registry('ks_sellerLocator_sortby');
    }
    
    /**
     * Get Seller Registry
     * @return array
     */
    public function getKsSellerRegistry()
    {
        return $this->ksRegistry->registry('ks_sellerLocator_filter');
    }

    /**
     * Get Image of Center Marker
     * @return string
     */
    public function getKsCenterMarkerImage()
    {
        $ksImg = $this->ksIcon->getKsIcon('Ksolves_MultivendorMarketplace::images/ks_center_marker.png');
        return $ksImg;
    }
    
    /**
     * Get Seller Details
     * @return array
     */
    public function getKsSellerDetails()
    {
        $ksSellerCollection = $this->getKsSellerRegistry();
        if ($ksSellerCollection) {
            $ksSortBy = $this->ksRegistry->registry('ks_sellerLocator_sortby');
            switch ($ksSortBy) {
                case "Name(AtoZ)":
                    $ksSellerCollection->setOrder('ks_store_name', 'ASC');
                    break;
                case "Name(ZtoA)":
                    $ksSellerCollection->setOrder('ks_store_name', 'DESC');
                    break;
                default:
                    break;
            }
            $ksSellerName = [];
            $ksSellerData = $ksSellerCollection->getData();
            $ksSellerIds = $this->ksRegistry->registry('ks_seller_id_near_far');

            if ($ksSortBy == 'Distance(NeartoFar)' || $ksSortBy == 'Distance(FartoNear)') {
                foreach ($ksSellerIds as $ksSellerId) {
                    $ksSellerName[$ksSellerId]=
                    [
                    'name'=> $this->ksHelperData->getKsSellerStoreName($ksSellerId),
                    'logo'=> $this->ksHelperData->getKsSellerLogo($ksSellerId),
                    'banner'=> $this->ksHelperData->getKsSellerBanner($ksSellerId),
                    'latitude'=>$this->ksHelperData->getKsLatitude($ksSellerId),
                    'longitude'=>$this->ksHelperData->getKsLongitude($ksSellerId),
                    'location'=>$this->ksHelperData->getKsLocation($ksSellerId),
                    'sellerlocation'=>$this->ksHelperData->getKsSellerLocation($ksSellerId),
                    'sellerId'=>$ksSellerId
                    ];
                }
            } else {
                foreach ($ksSellerData as $ksSellerRecord) {
                    $ksSellerName[$ksSellerRecord['ks_seller_id']]=
                    [
                    'name'=> $this->ksHelperData->getKsSellerStoreName($ksSellerRecord['ks_seller_id']),
                    'logo'=> $this->ksHelperData->getKsSellerLogo($ksSellerRecord['ks_seller_id']),
                    'banner'=> $this->ksHelperData->getKsSellerBanner($ksSellerRecord['ks_seller_id']),
                    'latitude'=>$this->ksHelperData->getKsLatitude($ksSellerRecord['ks_seller_id']),
                    'longitude'=>$this->ksHelperData->getKsLongitude($ksSellerRecord['ks_seller_id']),
                    'location'=>$this->ksHelperData->getKsLocation($ksSellerRecord['ks_seller_id']),
                    'sellerlocation'=>$this->ksHelperData->getKsSellerLocation($ksSellerRecord['ks_seller_id']),
                    'sellerId'=>$ksSellerRecord['ks_seller_id']
                    ];
                }
            }
            return $ksSellerName;
        } else {
            return false;
        }
    }

    /**
     * Get Seller locator view
     * @return bool
     */
    public function getKsView()
    {
        $ksView=false;
        if ($this->getRequest()->getParam('view') == 'map') {
            $ksView=true;
        }
        return $ksView;
    }
}
