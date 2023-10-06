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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Buttons;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class KsSaveAndContinueButton
 */
class KsSaveAndContinueButton extends KsGenericButton implements ButtonProviderInterface
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
            'label' => __('Save and Continue Edit'),
            'class' => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndContinueEdit'],
                ],
            ],
            'sort_order' => 40,
        ];
    }
}
