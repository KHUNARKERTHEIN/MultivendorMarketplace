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

use Magento\ConfigurableProduct\Model\Product\Type\Collection\SalableProcessor;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Class KsSellerProductSalableProcessor
 */
class KsSellerProductSalableProcessor
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
     * Adds filters to the collection to help determine if product is available for sale.
     *
     * @param Collection $collection
     * @return Collection
     */
    public function afterProcess(
        SalableProcessor $ksSubject,
        $ksResult
    ) {
        return $this->ksProductHelper->ksFilterSellerProductCollection($ksResult);
    }
}
