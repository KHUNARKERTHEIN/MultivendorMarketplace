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

namespace Ksolves\MultivendorMarketplace\Block\SellerProfile;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollectionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * KsProfile Block class
 */
class KsProfile extends \Magento\Directory\Block\Data
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\KsSellerGroup
     */
    protected $ksSellerGroup;
    
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $ksCountry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;
    
    /**
     * @var KsFavouriteSellerCollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper
     */
    protected $KsSellerDashboardMyProfileHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext,
     * @param \Magento\Directory\Helper\Data $ksDirectoryHelper,
     * @param \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
     * @param \Magento\Framework\App\Cache\Type\Config $ksConfigCacheType,
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $ksRegionCollectionFactory,
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $ksCountryCollectionFactory,
     * @param \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\KsSellerGroup $ksSellerGroup,
     * @param \Magento\Directory\Model\Config\Source\Country $ksCountry,
     * @param \Magento\Customer\Model\Session $ksCustomerSession,
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
     * @param KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper,
     * @param array $ksData = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Directory\Helper\Data $ksDirectoryHelper,
        \Magento\Framework\Json\EncoderInterface $ksJsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $ksConfigCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $ksRegionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $ksCountryCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\KsSellerGroup $ksSellerGroup,
        \Magento\Directory\Model\Config\Source\Country $ksCountry,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $KsSellerDashboardMyProfileHelper,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksDirectoryHelper, $ksJsonEncoder, $ksConfigCacheType, $ksRegionCollectionFactory, $ksCountryCollectionFactory, $ksData);
        $this->ksSellerGroup = $ksSellerGroup;
        $this->ksCountry = $ksCountry;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->KsSellerDashboardMyProfileHelper = $KsSellerDashboardMyProfileHelper;
    }
        
    /**
     * Return coutnries
     *
     * @return select html
     */
    public function getKsCountries()
    {
        $ksCountry = $this->getCountryHtmlSelect($this->getKsSellerDetails()->getKsCompanyCountry());
        return $ksCountry;
    }

    /**
     * Returns region collection
     *
     * @return array
     */
    public function getKsRegion()
    {
        $ksRegion = $this->getRegionHtmlSelect();
        return $ksRegion;
    }

    /**
     * redirect to country page
     *
     * @return page
     */
    public function getKsCountryAction()
    {
        return $this->getUrl('multivendor/sellerprofile/country', ['_secure' => true]);
    }

    /**
     * Get Store Id
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->getRequest()->getParam('store') ? $this->getRequest()->getParam('store') : 0;
    }
    
    /**
     * Return seller group collection
     *
     * @return array
     */
    public function getKsSellerGroup()
    {
        return $this->ksSellerGroup->toOptionArray();
    }
    
    /**
     * Return all countries
     *
     * @return array
     */
    public function getKsCountry()
    {
        return $this->ksCountry->toOptionArray();
    }
    
    /**
     * Return customer id
     *
     * @return int
     */
    public function getKsCustomerId()
    {
        $ksCustomerId = ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getCustomer()->getId();
        return $ksCustomerId;
    }
    
    /**
     * Return seller collection
     *
     * @return array
     */
    public function getKsSellerDetails()
    {
        return $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsCustomerId())->getFirstItem();
    }

    /**
     * Get followers count
     *
     * @param  int $ksSellerId
     * @return int
     */
    public function getKsSellerFollowersSize($ksSellerId)
    {
        return $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getSize();
    }

    /**
     * Check wether favourite seller module is enabled or not
     * @return boolean
     */
    public function isKsFavouriteSellerEnable()
    {
        return $this->ksDataHelper->getKsConfigValue('ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_favourite_seller_enable', $this->ksDataHelper->getKsCurrentStoreView());
    }

    /**
     * Check whether social media is enabled or not
     * @return boolean
     */
    public function isKsSocialMediaEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_social_media');
    }

    /**
    * Check whether refund policy is enabled or not
    * @return boolean
    */
    public function isKsRefundPolicyEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_refund_policy');
    }

    /**
     * Check whether privacy policy is enabled or not
     * @return boolean
     */
    public function isKsPrivacyPolicyEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_privacy_policy');
    }
    
    /**
     * Check whether shipping policy is enabled or not
     * @return boolean
     */
    public function isKsShippingPolicyEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_shipping_policy');
    }

    /**
     * Check whether legal notice is enabled or not
     * @return boolean
     */
    public function isKsLegalNoticeEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_legal_notice');
    }

    /**
     * Check whether term of service is enabled or not
     * @return boolean
     */
    public function isKsTermsOfServiceEnabled()
    {
        return $this->KsSellerDashboardMyProfileHelper->getKsSellerConfigData('ks_terms_of_service');
    }
}