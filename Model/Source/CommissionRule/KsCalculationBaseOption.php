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
 * KsCalculationBaseOption Model class
 */
class KsCalculationBaseOption implements OptionSourceInterface
{
    const KS_PRICE_INCLUDE_TAX = 1;
    const KS_PRICE_EXCLUDE_TAX = 2;
    const KS_PRICE_INCLUDE_TAX_AFTER_DISCOUNT = 3;
    const KS_PRICE_EXCLUDE_TAX_AFTER_DISCOUNT = 4;
    
    /**
     * Get CalculationBase options
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::KS_PRICE_INCLUDE_TAX => __('Product Price (Including Taxes)'),
            self::KS_PRICE_EXCLUDE_TAX => __('Product Price (Excluding Taxes)'),
            self::KS_PRICE_INCLUDE_TAX_AFTER_DISCOUNT => __('Product Price After Discount (Including Taxes)'),
            self::KS_PRICE_EXCLUDE_TAX_AFTER_DISCOUNT => __('Product Price After Discount (Excluding Taxes)')
        ];
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::getOptionArray() as $index => $value) {
            $options[] = ['value' => $index, 'label' => $value];
        }
        return $options;
    }
}
