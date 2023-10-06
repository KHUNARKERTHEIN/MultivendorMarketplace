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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Tabs;

use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * KsSellerLocationSettings Block class
 */
class KsSellerLocationSettings extends \Magento\Backend\Block\Template implements TabInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     */
    protected $ksScopeInterface;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory
     */
    protected $ksSellerLocatorCollection ;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper
     */
    protected $ksHelperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Framework\App\Request\Http $ksRequest
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeInterface,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Framework\App\Request\Http $ksRequest,
        array $data = []
    ) {
        $this->ksSellerLocatorCollection = $ksSellerLocatorCollection;
        $this->ksScopeInterface = $ksScopeInterface;
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksRequest = $ksRequest;
        $this->ksSellerHelper =$ksSellerHelper;
        $this->ksHelperData = $ksHelperData;
        parent::__construct($ksContext, $data);
    }
 
    /**
     * Get Current Seller Id
     * @return int
     */
    public function getKsCurrentSellerId()
    {
        return $this->ksCoreRegistry->registry('current_seller_id');
    }
    
    /**
     * Get Store Id
     * @return int
     */
    public function getKsStoreId()
    {
        return (int)$this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
    }

    /**
     * Set Lable in Tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Seller Locator');
    }

    /**
     * Set Title in Tab
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Seller Locator');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class Getter
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded through Ajax call
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
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
     * Get Api Key from the admin
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
        return $this->getRequest()->getParam('seller_id');
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
