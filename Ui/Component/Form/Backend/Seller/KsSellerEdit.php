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

namespace Ksolves\MultivendorMarketplace\Ui\Component\Form\Backend\Seller;

use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class KsFieldset.
 */
class KsFieldset extends Fieldset implements ComponentVisibilityInterface
{
    public function __construct(
        ContextInterface $ksContext,
        array $ksComponents = [],
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksComponents, $ksData);
    }

    //This method is responsible for show and hide the fieldset

    public function isComponentVisible(): bool
    {
        $visible = 0; // For now Hide the fieldset statically, later write the logics here
        return (bool)$visible;
    }
}
