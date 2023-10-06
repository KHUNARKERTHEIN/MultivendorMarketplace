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

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

/**
 * Logout Controller class
 */
class Logout extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    protected$redirect;
    
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->redirect = $ksContext->getRedirect();
        return parent::__construct($ksContext);
    }
    
    /**
     * Seller Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            $ksCustomerId = $this->ksCustomerSession->getId();
            if ($ksCustomerId) {
                $this->ksCustomerSession->logout()
                     ->setBeforeAuthUrl($this->redirect->getRefererUrl())
                     ->setLastCustomerId($ksCustomerId);
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->messageManager->addSuccessMessage("Seller has been logged out successfully.");
                $resultRedirect->setPath('multivendor/login/index/');
                return $resultRedirect;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
