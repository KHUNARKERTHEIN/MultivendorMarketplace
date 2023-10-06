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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * NewAction Controller Class
 */
class NewAction extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * Initialize Group Controller
     *
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->prepend(__('Add New Seller'));
        return $ksResultPage;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_sellers');
    }
}
