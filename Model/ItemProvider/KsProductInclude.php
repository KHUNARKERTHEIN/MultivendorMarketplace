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

namespace Ksolves\MultivendorMarketplace\Model\ItemProvider;

use Ksolves\MultivendorMarketplace\Helper\KsSellerSitemapHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;

class KsProductInclude extends \Magento\Sitemap\Model\ItemProvider\Product
{
    /**
    * @var KsSellerConfigReader
    */
    private $ksConfigReader;

    /**
    * Product factory
    * @var ProductFactory
    */
    private $productFactory;

    /**
     * Sitemap item factory
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * Config reader
     * @var ConfigReaderInterface
     */
    private $configReader;

    /**
     * @var array Product
     */
    protected $ksProductFactory;

    /**
    * @var array
    */
    protected $ksSitemapItems = [];

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerSitemapHelper
     */
    protected $ksSellerSitemapHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * ProductSitemapItemResolver constructor.
     *
     * @param KsSellerProductConfigReader $configReader
     * @param KsProductFactory $ksProductFactory
     * @param KsSellerSitemapHelper $ksSellerSitemapHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param KsProductHelper $ksProductHelper
     * @param ProductFactory $productFactory
     * @param SitemapItemInterfaceFactory $itemFactory
     */

    public function __construct(
        KsSellerProductConfigReader $ksConfigReader,
        ProductFactory $productFactory,
        SitemapItemInterfaceFactory $itemFactory,
        KsProductFactory $ksProductFactory,
        KsSellerSitemapHelper $ksSellerSitemapHelper,
        KsSellerHelper $ksSellerHelper,
        KsProductHelper $ksProductHelper
    ) {
        $this->ksConfigReader  = $ksConfigReader;
        $this->productFactory = $productFactory;
        $this->itemFactory = $itemFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksSellerSitemapHelper = $ksSellerSitemapHelper;
        $this->ksSellerHelper= $ksSellerHelper;
        $this->ksProductHelper=$ksProductHelper;
    }

    /**
     * {@inheritdoc}
     */

    public function getItems($storeId)
    {
        $ksSellerProduct=$this->ksProductFactory->create()->getCollection($storeId);
        $ksId=array();
        //store the seller product id in array
        foreach ($ksSellerProduct as $ksProductId) {
            array_push($ksId, $ksProductId->getKsProductId());
        }
        $ksAdminProductCollection = $this->productFactory->create()->getCollection($storeId);
        foreach ($ksAdminProductCollection as $ksProduct) {
            //Product-Id
            $ksSellerProductId=$ksProduct->getEntityId();
            //check the seller's product
            if (in_array($ksProduct->getEntityId(), $ksId)) {
                //SellerID of product
                $ksSellerId=$this->ksProductHelper->getKsSellerId($ksSellerProductId);
                if ($this->ksProductHelper->ksSellerProductFilter($ksSellerProductId)){                    
                    if ($this->ksSellerHelper->getKsSellerStoreStatus($ksSellerId)) {
                        if ($this->getKsSellerSitemapStatus($storeId)==1 && $this->getKsIncludeProductUrl($storeId)== 1) {
                            if ($this->getKsIncludedSitemapProduct($ksSellerId, $storeId)) {
                                $this->ksSitemapItems[] = $this->itemFactory->create(
                                    [
                                    'url' => $ksProduct->getUrl(),
                                    'updatedAt' => $ksProduct->getUpdatedAt(),
                                    'images' => $ksProduct->getImages(),
                                    'priority' => $this->getKsPriority($storeId),
                                    'changeFrequency' =>$this->getKsChangeFrequency($storeId)
                                    ]
                                );
                            }
                        }
                    }
                }
            } else {
                $this->ksSitemapItems[] = $this->itemFactory->create(
                    [
                    'url' => $ksProduct->getUrl(),
                    'updatedAt' => $ksProduct->getUpdatedAt(),
                    'images' => $ksProduct->getImages(),
                    'priority' => $this->getKsPriority($storeId),
                    'changeFrequency' =>$this->getKsChangeFrequency($storeId)
                    ]
                );
            }
        }
        return $this->ksSitemapItems;
    }

    /**
    * @param int $storeId
    * @return string
    */
    public function getKsChangeFrequency(int $ksStoreId): string
    {
        return $this->ksConfigReader->getChangeFrequency($ksStoreId);
    }

    /**
    * @param int $storeId
    * @return string
    */
    public function getKsPriority(int $ksStoreId): string
    {
        return $this->ksConfigReader->getPriority($ksStoreId);
    }

    /**
    * @param int $storeId
    * @return string
    */
    public function getKsSellerSitemapStatus(int $ksStoreId)
    {
        return $this->ksConfigReader->getKsSellerSitemapStatus($ksStoreId);
    }

    /**
    * @param int $storeId
    * @return string
    */
    public function getKsIncludeProductUrl(int $ksStoreId)
    {
        return $this->ksConfigReader->getKsIncludeProductUrl($ksStoreId);
    }

    /**
    * @param int $PoductId
    * @return string
    */
    public function getKsIncludedSitemapProduct(int $ksSellerId, int $ksStoreId)
    {
        return $this->ksSellerSitemapHelper->getKsIncludedSitemapProduct($ksSellerId, $ksStoreId);
    }
}
