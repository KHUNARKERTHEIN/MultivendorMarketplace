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

namespace Ksolves\MultivendorMarketplace\Plugin\Product\Bundle;

use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as KsSelectionCollection;

/**
 * Class KsBundleSelectionFilter
 * @package Ksolves\MultivendorMarketplace\Plugin\Product\Bundle\Pricing
 */
class KsBundleSelectionFilter
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * KsCatalogProductHelper constructor.
     * @param KsProductHelper $ksProductHelper
     */
    public function __construct(
        KsProductHelper $ksProductHelper
    ) {
        $this->ksProductHelper = $ksProductHelper;
    }
    /**
     * @return collection
     */
    public function afterAddQuantityFilter(KsSelectionCollection $ksSubject, $ksResult)
    {
        return $this->ksProductHelper->ksFilterSellerProductCollection($ksResult);
    }
}
