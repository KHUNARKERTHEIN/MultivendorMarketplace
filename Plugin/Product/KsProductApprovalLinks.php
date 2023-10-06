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

namespace Ksolves\MultivendorMarketplace\Plugin\Product;

use Magento\Catalog\Model\Product\Link;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection;

/**
 * Class ProductApprovalLinks
 * @package Ksolves\MultivendorMarketplace\Model\Plugin
 */
class KsProductApprovalLinks
{
    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param KsCatalogProductHelper constructor.
     */
    public function __construct(
        KsProductHelper $ksProductHelper
    ) {
        $this->ksProductHelper = $ksProductHelper;
    }

    /**
     * @param Link $ksSubject
     * @param Collection $ksCollection
     * @return Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetProductCollection(Link $ksSubject, Collection $ksCollection)
    {
        return $this->ksProductHelper->ksFilterSellerProductCollection($ksCollection, true);
    }
}
