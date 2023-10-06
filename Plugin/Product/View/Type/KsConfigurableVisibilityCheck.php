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

namespace Ksolves\MultivendorMarketplace\Plugin\Product\View\Type;

use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Class KsConfigurableVisibilityCheck
 */
class KsConfigurableVisibilityCheck
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
     * Filter out seller product .
     *
     * @param $ksCollection
     * @return array
     */
    public function afterGetAllowProducts(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $ksSubject,
        $ksResult
    ) {
        $ksProducts = [];
        foreach ($ksResult as $ksProduct) {
            $ksCheckSellerProduct = $this->ksProductHelper->ksSellerProductFilter($ksProduct->getId());

            if ($ksCheckSellerProduct) {
                $ksProducts[] = $ksProduct;
            }
        }

        return $ksProducts;
    }
}
