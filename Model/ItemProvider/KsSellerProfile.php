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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerSitemapHelper;

/**
 * Class KsSeller
 * @package Ksolves\MultivendorMarketplace\Model\ItemProvider
 */
class KsSellerProfile implements ItemProviderInterface
{
    /**
    * @var KsSellerConfigReader
    */
    private $ksConfigReader;

    /**
    * @var SitemapItemFactory
    */
    private $ksItemFactory;

    protected $ksSellerFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerSitemapHelper
     */
    protected $ksSellerHelper;

    /**
    * @var array
    */
    protected $ksSitemapItems = [];

    /**
     * @var KsSellerSitemapHelper
     */
    protected $ksSellerSitemapHelper;

    /**
    * KsSeller constructor.
    * @param KsSellerConfigReader $configReader
    * @param SitemapItemFactory $itemFactory
    */

    public function __construct(
        KsSellerProfileConfigReader $ksConfigReader,
        SitemapItemFactory $ksItemFactory,
        KsSellerFactory $ksSellerFactory,
        KsSellerSitemapHelper $ksSellerSitemapHelper
    ) {
        $this->ksConfigReader         = $ksConfigReader;
        $this->ksItemFactory 		  = $ksItemFactory;
        $this->ksSellerFactory 		  = $ksSellerFactory;
        $this->ksSellerSitemapHelper  = $ksSellerSitemapHelper;
    }

    /**
    * @param int $storeId
    * @return array
    * @throws NoSuchEntityException
    */
    public function getItems($storeId): array
    {
        $ksSeller = $this->ksSellerFactory->create();
        $ksSellerCollection = $ksSeller->getCollection($storeId);
        foreach ($ksSellerCollection as $ksItem) {
            if($ksItem->getKsStoreStatus()){
                if ($this->getKsSellerSitemapStatus($storeId)==1 && $this->getKsIncludeProfileUrl($storeId)==1) {
                    if ($this->getKsIncludedSitemapProfile($ksItem->getKsSellerId(), $storeId)=='1') {
                        $this->ksSitemapItems[] = $this->ksItemFactory->create(
                            [
                                'url' =>'multivendor/'.$ksItem->getKsStoreUrl(),
                                'updatedAt' => $ksItem->getKsUpdatedAt(),
                                'priority' => $this->getKsPriority($storeId),
                                'changeFrequency' => $this->getKsChangeFrequency($storeId)
                            ]
                        );
                    }
                }
            }
        }
        return $this->ksSitemapItems;
    }

    /**
    * @param int $ksStoreId
    *
    * @return string
    *
    */
    private function getKsChangeFrequency(int $ksStoreId): string
    {
        return $this->ksConfigReader->getChangeFrequency($ksStoreId);
    }

    /**
    * @param int $ksStoreId
    *
    * @return string
    *
    */
    private function getKsPriority(int $ksStoreId): string
    {
        return $this->ksConfigReader->getPriority($ksStoreId);
    }

    /**
    * @param int $ksStoreId
    *
    * @return string
    *
    */
    private function getKsSellerSitemapStatus(int $ksStoreId)
    {
        return $this->ksConfigReader->getKsSellerSitemapStatus($ksStoreId);
    }

    /**
    * @param int $ksStoreId
    *
    * @return string
    *
    */
    private function getKsIncludeProfileUrl(int $ksStoreId)
    {
        return $this->ksConfigReader->getKsIncludeProfileUrl($ksStoreId);
    }

    /**
    * @param int $ksStoreId
    * @param int $ksSellerId
    * @return string
    *
    */
    public function getKsIncludedSitemapProfile(int $ksSellerId, $ksStoreId)
    {
        return $this->ksSellerSitemapHelper->getKsIncludedSitemapProfile($ksSellerId, $ksStoreId);
    }
}
