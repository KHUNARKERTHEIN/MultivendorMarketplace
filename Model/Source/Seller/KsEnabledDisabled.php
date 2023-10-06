<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Model\Source\Seller;

/**
 * KsEnabledDisabled Model Class
 */
class KsEnabledDisabled implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Value which equal Enable for Enabledisabled dropdown.
     */
    const ENABLE_VALUE = 1;

    /**
     * Value which equal Disable for Enableddisabled dropdown.
     */
    const DISABLE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE_VALUE, 'label' => __('Enabled')],
            ['value' => self::DISABLE_VALUE, 'label' => __('Disabled')],
        ];
    }
}
