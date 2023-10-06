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

namespace Ksolves\MultivendorMarketplace\Model\Source\Seller;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * KsSellerStatusOptions Model Class
 */
class KsAddSellerStatusOptions implements OptionSourceInterface
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
                'label' => "Pending",
                'value' => 0,
            ],
            [
                'label' => "Approved",
                'value' => 1,
            ],

        ];
        return $ksOptions;
    }
}
