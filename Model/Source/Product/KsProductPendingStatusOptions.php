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
 * Class KsProductPendingStatusOptions
 * @package Ksolves\MultivendorMarketplace\Model\Source\Product
 */
class KsProductPendingStatusOptions implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksOptions = [
            [
                'label' => "Pending New",
                'value' => \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING,
            ],
            [
                'label' => "Pending Update",
                'value' => \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING_UPDATE,
            ]
        ];
        return $ksOptions;
    }
}
