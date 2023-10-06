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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\ProductAttribute;

/**
 * KsEdit Block Class For Overriding The Back Button of Attribute Form
 */
class KsEdit extends \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit
{

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->buttonList->update(
            'back',
            'onclick',
            sprintf("Javascript:history.back();")
        );
    }
}
