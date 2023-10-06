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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ReportProduct;

/**
 * NewSubReason Controller class
 *
 * Class NewReason
 */
class NewSubReason extends \Magento\Backend\App\Action
{

    /**
     * NewSubReason constructor.
     *
     * @param Action\Context $ksContext
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext
    ) {
        parent::__construct($ksContext);
    }
    
    /**
     * Execute action
     *
     * Forward to Edit Controller
     */
    public function execute()
    {
        $this->_forward('editsubreason');
    }
}
