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
 * Class KsAdminEmailOptions
 */
class KsAdminEmailOptions implements OptionSourceInterface
{
    /**
     * To show Admin's Email Options
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '0',
                'label' => __('Default'),
            ],
            [
                'value' => '1',
                'label' => __('Secondary'),
            ],
            [
                'value' => '2',
                'label' => __('Both'),
            ],
        ];
    }
}
