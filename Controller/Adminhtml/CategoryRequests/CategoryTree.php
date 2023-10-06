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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CategoryRequests;

use Magento\Backend\App\Action;

/**
 * CategoryTree controller class
 */
class CategoryTree extends \Magento\Backend\App\Action
{
    /**
     * Vendor Information constructor.
     * @param Action\Context $ksContext
     * @param \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        parent::__construct($ksContext);
    }
    
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\LayoutFactory $ksResultLayoutFactory */
        $ksResultLayout = $this->ksResultLayoutFactory->create();
        return $ksResultLayout;
    }
}
