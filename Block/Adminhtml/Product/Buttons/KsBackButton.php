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
namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\Product\Buttons;

/**
 * Class KsBackButton
 * @package Ksolves\MultivendorMarketplace\Block\Adminhtml\Product\Buttons
 */
class KsBackButton extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => $this->getBackUrl(),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back
     *
     * @return string
     */
    private function getBackUrl()
    {
        if ($this->context->getRequestParam('customerId')) {
            return
                sprintf("location.href = '%s';", $this->getUrl(
                    'customer/index/edit',
                    ['id' => $this->context->getRequestParam('customerId')]
                ));
        }
        return sprintf("Javascript:history.back();");
    }
}
