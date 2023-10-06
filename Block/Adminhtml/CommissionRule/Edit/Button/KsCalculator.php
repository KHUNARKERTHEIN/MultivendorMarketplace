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


namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CommissionRule\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;

/**
 * Class KsCalculator
 */
class KsCalculator extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Commission Calculator'),
            'class' => 'action-secondary ks-calculate',
            'on_click' => '',
            'sort_order' => 20
        ];
    }
}
