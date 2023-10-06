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
namespace Ksolves\MultivendorMarketplace\Model\Source\Config\Adminhtml\SellerLocator;

/**
 * KsDefaultUnit SellerLocator Model Class
 */
class KsDefaultUnit implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'miles', 'label' => __('Miles')],
            ['value' => 'km', 'label' => __('Kilometers')]
        ];
    }
}
