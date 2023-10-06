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

use Magento\Framework\App\Helper\AbstractHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap\CollectionFactory as KsSellerSitemapCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ItemProvider\KsSellerProfileConfigReader;
use Ksolves\MultivendorMarketplace\Model\ItemProvider\KsSellerProductConfigReader;

/**
 * KsSellerSitemapHelper Class
 */
class KsSellerSitemapHelper extends AbstractHelper
{
    /**
     * @var KsSellerSitemapCollectionFactory
     */
    protected $ksSellerSitemapCollectionFactory;

    /**
    * @var KsSellerConfigReader
    */
    private $kssellerProfileConfigReader;

    /**
    * @var KsSellerProductConfigReader
    */
    private $ksSellerProductConfigReader;

    /**
     * @var KsProductHelper
     */
    private $ksProductHelper;

    public function __construct(
        KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory,
        KsSellerProfileConfigReader $kssellerProfileConfigReader,
        KsSellerProductConfigReader $ksSellerProductConfigReader,
        KsProductHelper $ksProductHelper
    ) {
        $this->ksSellerSitemapCollectionFactory = $ksSellerSitemapCollectionFactory;
        $this->ksProductHelper                  = $ksProductHelper;
        $this->kssellerProfileConfigReader      = $kssellerProfileConfigReader;
        $this->ksSellerProductConfigReader      = $ksSellerProductConfigReader;
    }

    /**
     * get the value of ks_included_sitemap_profile from the ks_seller_sitemap table
     */
    public function getKsIncludedSitemapProfile($ksSellerId, $ksStoreId)
    {
        $ksSellerSitemapCollection = $this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId);
        if ($ksSellerSitemapCollection->getSize()>0) {
            return $ksSellerSitemapCollection->getFirstItem()->getKsIncludedSitemapProfile();
        } else {
            return $this->kssellerProfileConfigReader->getKsIncludeProfileUrl($ksStoreId);
        }
    }

    /**
     * get the value of ks_included_sitemap_product from the ks_seller_sitemap table
    */
    public function getKsIncludedSitemapProduct($ksSellerId, $ksStoreId)
    {
        $ksSellerSitemapCollection = $this->ksSellerSitemapCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId);
        if ($ksSellerSitemapCollection->getSize()>0) {
            return $ksSellerSitemapCollection->getFirstItem()->getKsIncludedSitemapProduct();
        }
        else {
            return $this->ksSellerProductConfigReader->getKsIncludeProductUrl($ksStoreId);
        }
    }
}