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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\PriceComparison\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Buttons\KsGenericButton;

/**
 * Class KsSaveButton
 */
class KsSaveButton extends KsGenericButton implements ButtonProviderInterface
{
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $ksContext
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $ksContext,
        \Magento\Framework\Registry $ksRegistry
    ) {
        parent::__construct($ksContext, $ksRegistry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 50,
        ];
    }
}
