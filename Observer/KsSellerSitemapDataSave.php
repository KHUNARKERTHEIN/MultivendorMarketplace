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

namespace Ksolves\MultivendorMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * KSSellerSitemapSave observer class
 * 
 */
class KsSellerSitemapDataSave implements ObserverInterface
{
	/**
	 * @var $ksSellerSitemapFactory
	 */
    protected $ksSellerSitemapFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequest;

    /**
     * @var KsSellerHelper
     * 
     */
    protected $ksSellerHelper;

    /**
     * @var $ksDataHelper
     * 
     */
    protected $ksDataHelper;

    /**
     * @param CollectionFactory $ksSellerSitemapFactory
     * @param ManagerInterface $ksMessageManager
     * @param RequestInterface $ksRequest
     * @param KsSellerHelper $ksSellerHelper
     * @param KsDataHelper $ksDataHelper
     * */


    public function __construct(
    	CollectionFactory $ksSellerSitemapFactory,
    	ManagerInterface $ksMessageManager,
    	RequestInterface $ksRequest,
    	KsSellerHelper $ksSellerHelper,
        KsDataHelper $ksDataHelper
    ){
    	$this->ksSellerSitemapFactory=$ksSellerSitemapFactory;
    	$this->ksMessageManager=$ksMessageManager;
    	$this->ksRequest=$ksRequest;
    	$this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper=$ksDataHelper;
    }

    /**
     * Getting Sitemap Attribute Set When Configuration Page Save
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        try {
            // Get the Field Of the System.xml File
            $ksFieldData = $this->ksRequest->getParam('groups');
            $ksStoreId = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') :0;
            $ksEnableSitemap=$ksIncludeSellerProfileUrl =$ksIncludeSellerProductUrl = "";
            // Get The details of Sitemap Selected by the admin
      
                //Check the value if selected default than catch block will run 
                try {
                    $ksEnableSitemap= $ksFieldData['ks_sitemap']['fields']['ks_enable_sitemap']['value'];
                } catch (\Exception $e) {
                    $ksEnableSitemap= $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_enable_sitemap',0);
                }   

                if ($ksEnableSitemap) {
                    try{
                        $ksIncludeSellerProfileUrl = $ksFieldData['ks_sitemap']['fields']['ks_include_seller_profile_url']['value'];
                    } catch (\Exception $e) {
                        $ksIncludeSellerProfileUrl =$this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url',0);
                    }
                    try {
                    $ksIncludeSellerProductUrl = $ksFieldData['ks_sitemap']['fields']['ks_include_seller_product_url']['value'];    
                    } catch (\Exception $e) {
                        $ksIncludeSellerProductUrl = $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url',0);
                    }

                } else {
                    // value not found on configurtion page
                    $ksIncludeSellerProfileUrl = 0;
                    $ksIncludeSellerProductUrl = 0;
                }	
            $this->ksSetConfigurationValueInSellerSitemapTable($ksIncludeSellerProfileUrl,$ksIncludeSellerProductUrl,$ksStoreId);
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }	
	}

    /**
     * Save SellerSitemap Values in the Database
     * @param  $ksIncludeSellerProfileUrl
     * @param  $ksIncludeSellerProductUrl
     * @param  $ksStoreId
     * @return void
     */
	public function ksSetConfigurationValueInSellerSitemapTable($ksIncludeSellerProfileUrl,$ksIncludeSellerProductUrl,$ksStoreId)
	{
        // Get Seller List
        $ksSellerList = $this->ksSellerHelper->getKsSellerList();
        // Iterate Seller List
        foreach ($ksSellerList as $ksSellerId) {
            // Get the Model for the Save

            $ksSellerSitemapModel = $this->ksSellerSitemapFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id',$ksStoreId)->getFirstItem();
            $ksSellerSitemapModel->setKsSellerId($ksSellerId);
            $ksSellerSitemapModel->setKsStoreId($ksStoreId);
            $ksSellerSitemapModel->setKsIncludedSitemapProfile($ksIncludeSellerProfileUrl);
            $ksSellerSitemapModel->setKsIncludedSitemapProduct($ksIncludeSellerProductUrl);
            $ksSellerSitemapModel->save();
        }
	}
}