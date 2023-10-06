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

namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsSecondaryConfigurableProductOwner
 */
class KsSecondaryConfigurableProductOwner implements OptionSourceInterface
{
    /**
     * To show Types of Product for restrictions for price comparison product
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'creation_date_asc',
                'label' => __('Creation Date (Ascending)'),
            ],
            [
                'value' => 'creation_date_desc',
                'label' => __('Creation Date (Descending)'),
            ],
            [
                'value' => 'min_variants',
                'label' => __('Minimum Variants'),
            ],
            [
                'value' => 'max_variants',
                'label' => __('Maximum Variants'),
            ],
        ];
    }
}
