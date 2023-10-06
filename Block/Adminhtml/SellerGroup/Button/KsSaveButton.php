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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\SellerGroup\Button;

/**
 * KsSaveButton Block Class
 */
class KsSaveButton extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    /**
     * @return  array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'primary',
            'action' => $this->getUrl('multivendor/sellergroup/save'),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            'sort_order' => 10
        ];
    }
}
