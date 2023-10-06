<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright(c) Ksolves India Limited(https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\ForgotPassword;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Customer\Model\Session;

/**
 * Index
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;
    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        Session $ksCustomerSession
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSession = $ksCustomerSession;
        parent::__construct($ksContext);
    }

    /**
     * Forget Password page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->ksSession->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $ksResultRedirect */
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $ksResultRedirect->setPath('multivendor/sellerprofile/homepage');
            return $ksResultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $ksResultPage */
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->set(__('Forgot your Password'));
        return $ksResultPage;
    }
}