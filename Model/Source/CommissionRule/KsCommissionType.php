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
 * KsCommissionType Model class
 */
class KsCommissionType implements OptionSourceInterface
{
    const KS_PERCENTAGE = 'to_percent';
    const KS_FIXED = 'to_fixed';
    
    /**
     * Get Commission type options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [ 'value'=> self::KS_PERCENTAGE, 'label' => 'Percentage'],
            [ 'value'=> self::KS_FIXED, 'label' => 'Fixed']
        ];
    }
}
