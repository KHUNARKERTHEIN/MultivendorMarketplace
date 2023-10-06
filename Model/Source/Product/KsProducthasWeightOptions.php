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

namespace Ksolves\MultivendorMarketplace\Model\Source\Product;

/**
 * KsProducthasWeightOptions Model Class
 */
class KsProducthasWeightOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Value which equal New Condition for Condition dropdown.
     */
    const KS_NEW_CONDITION = 1;

    /**
     * Value which equal Used Condition for Condition dropdown.
     */
    const KS_USED_CONDITION = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('This item has weight')],
            ['value' => 0, 'label' => __('This item has no weight')],
        ];
    }
}
