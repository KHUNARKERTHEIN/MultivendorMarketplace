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

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * KsSelectAttributes.
 */
class KsSelectAttributes extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry = null;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry
    ) {
        $this->ksRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getCaption()
    {
        return __('Select Attributes');
    }
}
