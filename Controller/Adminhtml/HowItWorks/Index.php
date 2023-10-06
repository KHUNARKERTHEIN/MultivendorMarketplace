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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\HowItWorks;

/**
 * Index Controller class
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_ksPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksPageFactory
    ) {
        $this->_ksPageFactory = $ksPageFactory;
        return parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $ksResultPage = $this->_ksPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->prepend(__('How It Works?'));
        return $ksResultPage;
    }
}
