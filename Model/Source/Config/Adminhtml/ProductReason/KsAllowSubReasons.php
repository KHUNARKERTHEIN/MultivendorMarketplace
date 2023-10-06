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

class KsAllowSubReasons implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => '2', 'label' => __('Yes,Required')],
            ['value' => '1', 'label' => __('Yes,Optional')],
            ['value' => '0', 'label' => __('No')],
        ];
    }
}
