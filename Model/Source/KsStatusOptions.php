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

namespace Ksolves\MultivendorMarketplace\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsStatusOptions
 */
class KsStatusOptions implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value'=>0,
                'label'=>'Disabled'
            ],
            [
                'value'=>1,
                'label'=>'Enabled'
            ],
        ];
    }
}
