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

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * KsSellDataHelper Class
 */
class KsSellDataHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KSOLVES_FAQ_PATH = 'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_faq';
    const KSOLVES_WHY_TO_SELL_PATH = 'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_why_to_sell';
 
    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;
    
    /**
     * @var Json
     */
    protected $serialize;
    
    /**
     * AbstractData constructor.
     * @param Context $ksContext
     * @param StoreManagerInterface $ksStoreManager
     * @param Json $serialize
     */
    public function __construct(
        Context $ksContext,
        StoreManagerInterface $ksStoreManager,
        Json $serialize
    ) {
        $this->ksStoreManager   = $ksStoreManager;
        $this->serialize      = $serialize;
        parent::__construct($ksContext);
    }
    
    /**
     * Return store id
     * @return int
     */
    public function getStoreid()
    {
        return $this->ksStoreManager->getStore()->getId();
    }
    
    /**
     * Return config data
     * @return string
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Return base url
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->ksStoreManager->getStore()->getBaseUrl();
    }  

    /**
     * Return banner url
     * @return string
     */
    public function getKsBannerUrl()
    {
        $ks_bannerPath = $this->scopeConfig->getValue(
            'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_banner',
            ScopeInterface::SCOPE_STORE
        );
        $ks_banner = '';
        if ($ks_bannerPath) {
            $ks_banner = $this->ksStoreManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ).'ksolves/multivendor/banner/'.$ks_bannerPath;
        }
        return $ks_banner;
    }
    
    /**
     * Return why to sell
     * @return array
     */
    public function getKsWhyToSell()
    {
        $ksWhyToSell = $this->scopeConfig->getValue(self:: KSOLVES_WHY_TO_SELL_PATH,ScopeInterface::SCOPE_STORE, $this->getStoreid());
        if($ksWhyToSell == '' || $ksWhyToSell == null)
            return;
        $unserializedata = $this->serialize->unserialize($ksWhyToSell);
        return $unserializedata;
    }
    
    /**
     * Return benefit url
     * @return string
     */
    public function getKsBenefits()
    {
        $ksBenefits = $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) ."ksolves/multivendor/";
 
        return $ksBenefits;
    }
    
    /**
     * Return how it works url
     * @return string
     */
    public function getKsHowItWorks()
    {
        $ksHowItWorks = $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) ."ksolves/multivendor/";
 
        return $ksHowItWorks;
    }
    
    /**
     * Return fag
     * @return array
     */
    public function getKsFaq()
    {
        $ksFaq = $this->scopeConfig->getValue(self:: KSOLVES_FAQ_PATH,ScopeInterface::SCOPE_STORE, $this->getStoreid());
        if($ksFaq == '' || $ksFaq == null)
            return;
        $unserializedata = $this->serialize->unserialize($ksFaq);
        return $unserializedata;
    }
}