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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;

/**
 * Class ProductPendingList
 * @package Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
 */
class ProductPendingList extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * ProductPendingList constructor.
     * @param Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     */
    public function __construct(
        Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->prepend((__('Pending Approval Seller Products')));
        return $ksResultPage;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::product_pending');
    }
}
