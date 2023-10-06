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

namespace Ksolves\MultivendorMarketplace\Model\Source\Favourite\Adminhtml;

/**
 * Class KsEmailNotificationofNewlyAddedFollowers
 */
class KsEmailNotificationofNewlyAddedFollowers
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'seller',
                'label' => __('Only Seller'),
            ],
            [
                'value' => 'admin',
                'label' => __('Only Admin'),
            ],
            [
                'value' => 'both',
                'label' => __('Both'),
            ],
            [
                'value' => 'no_action',
                'label' => __('No Action'),
            ]
        ];
    }
}
