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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CommissionRule\Buttons;

use Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Buttons\KsGenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class KsResetButton
 */
class KsResetButton extends KsGenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

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
        $this->ksCoreRegistry         = $ksRegistry;
        parent::__construct($ksContext, $ksRegistry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $ksCommissionModel = $this->ksCoreRegistry->registry('commission_rule');
        if ($ksCommissionModel->getId() != 1) {
            return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30
            ];
        }
    }
}
