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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CategoryRequests;

/**
 * KsAssignProducts block class
 */
class KsAssignProducts extends \Magento\Catalog\Block\Adminhtml\Category\AssignProducts
{
	/**
     * Block template
     *
     * @var string
     */
    protected $_template = 'Ksolves_MultivendorMarketplace::category/edit/ks_assign_products.phtml';

}