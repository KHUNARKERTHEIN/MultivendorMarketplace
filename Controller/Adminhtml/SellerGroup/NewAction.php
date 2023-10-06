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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\SellerGroup;

/**
 * NewAction Controller Class
 */
class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext
    ) {
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $this->_forward('edit');
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::seller_group');
    }
}
