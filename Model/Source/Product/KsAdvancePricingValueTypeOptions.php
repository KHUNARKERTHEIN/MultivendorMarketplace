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

namespace Ksolves\MultivendorMarketplace\Model\Source\Product;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * KsAdvancePricingValueTypeOptions Model class
 */
class KsAdvancePricingValueTypeOptions implements OptionSourceInterface
{

   
    /**
     * Get value type options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [ 'value'=> 'fixed', 'label' => 'Fixed'],
            [ 'value'=> 'discount', 'label' => 'Discount']
        ];
    }
}
