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

namespace Ksolves\MultivendorMarketplace\Model\Source\CommissionRule;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * KsRuleType Model class
 */
class KsRuleType implements OptionSourceInterface
{
    const KS_GLOBAL = 1;
    const KS_SELLER_SPECIFIC = 2;
    
    /**
     * Get gender options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=>1, 'label' => 'Global'],
            ['value'=>2, 'label'=>'Seller Specific']
        ];
    }
}
