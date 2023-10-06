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

/**
 * KsSellerLocatorProfileSettings block class
 */
class KsSellerLocatorProfileSettings extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory
     */
    protected $ksSellerLocatorCollection;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeInterface;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper
     */
    protected $ksHelperData;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerLocatorHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
        array $data = []
    ) {
        $this->ksSellerLocatorCollection = $ksSellerLocatorCollection;
        $this->ksScopeInterface = $ksScopeInterface;
        $this->ksSellerHelper =$ksSellerHelper;
        $this->ksHelperData = $ksHelperData;
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
     * Get Api from the admin
     * @return string
     */
    public function getKsGoogleApiKey()
    {
        return $this->ksHelperData->getKsApiKey();
    }

    /**
     * Get Seller Id
     * @return int
     */
    public function getKsSellerId()
    {
        return $this->ksSellerHelper->getKsCustomerId();
    }
  
    /**
     * Get Base Url
     * @return string
     */
    public function getKsBaseUrl()
    {
        return $this->ksScopeInterface->getValue("web/secure/base_url");
    }

    /**
     * Get location Details Data
     * @param int $ksSellerId
     * @return array
     */
    public function getKsLocationData($ksSellerId)
    {
        return $this->ksSellerLocatorCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getData();
    }
}
