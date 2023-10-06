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
 * KsPriceRoundOff Model class
 */
class KsPriceRoundOff implements OptionSourceInterface
{

  
    
    /**
     * Get price round off options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=>0, 'label' => 'No'],
            ['value'=>1, 'label'=>'Yes']
        ];
    }
}
