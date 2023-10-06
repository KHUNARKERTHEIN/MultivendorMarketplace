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
 * Class KsAnalyser
 */
class KsAnalyser extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Commission Analyzer'),
            'class' => 'action-secondary ks-analyse',
            'on_click' => '',
            'sort_order' => 20
        ];
    }
}
