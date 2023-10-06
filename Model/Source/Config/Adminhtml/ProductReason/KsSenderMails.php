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
namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\ProductReason;

class KsSenderMails implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0', 'label' => __('General Contact')],
            ['value' => '1', 'label' => __('Sales Representative')],
            ['value' => '2', 'label' => __('Customer Support')],
            ['value' => '3', 'label' => __('Customer Email 1')],
            ['value' => '4', 'label' => __('Customer Email 2')]
        ];
    }
}
