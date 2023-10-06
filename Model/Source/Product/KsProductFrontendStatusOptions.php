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

namespace Ksolves\MultivendorMarketplace\Model\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsProductFrontendStatusOptions
 * @package Ksolves\MultivendorMarketplace\Model\Source\Product
 */
class KsProductFrontendStatusOptions implements OptionSourceInterface
{

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProduct
     */
    protected $ksProductModal;

    /**
     * KsProductFrontendStatusOptions constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\KsProduct $ksProductModal
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsProduct $ksProductModal
    ) {
        $this->ksProductModal = $ksProductModal;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksData = $this->ksProductModal->getKsAvailableProductStatus();

        $ksOptions = [];
        foreach ($ksData as $ksKey => $ksValue) {
            $ksOptions[] = [
                'label' => $ksValue,
                'value' => $ksKey,
            ];
        }
        return $ksOptions;
    }
}
