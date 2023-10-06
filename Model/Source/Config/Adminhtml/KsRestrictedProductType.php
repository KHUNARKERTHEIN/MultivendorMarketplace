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
 * Class KsRestrictedProductType
 */
class KsRestrictedProductType implements OptionSourceInterface
{
    /**
     * To show Types of Product for restrictions for price comparison product
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'simple',
                'label' => __('Simple Product'),
            ],
            [
                'value' => 'configurable',
                'label' => __('Configurable Product'),
            ],
            [
                'value' => 'virtual',
                'label' => __('Virtual Product'),
            ],
            [
                'value' => 'downloadable',
                'label' => __('Downloadable Product'),
            ],
        ];
    }
}
