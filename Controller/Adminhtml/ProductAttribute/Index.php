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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;

/**
 * Index Controller Class
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * Initialize Product Attribute Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        parent::__construct($ksContext);
    }
    
    /**
     * Seller Default Attribute list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $ksResultPage */
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->setActiveMenu('Ksolves_MultivendorMarketplace::default_attribute');
        $ksResultPage->getConfig()->getTitle()->prepend(__('Default Seller Product Attributes'));
        $ksResultPage->addBreadcrumb(__('Default'), __('Default'));
        return $ksResultPage;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_product_attribute');
    }
}
