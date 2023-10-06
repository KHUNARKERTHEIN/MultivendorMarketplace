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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Configurable\Steps;

/**
 * KsAttributeValues.
 */
class KsAttributeValues extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getCaption()
    {
        return __('Attribute Values');
    }
}
