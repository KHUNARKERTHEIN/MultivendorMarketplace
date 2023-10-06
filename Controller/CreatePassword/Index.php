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

namespace Ksolves\MultivendorMarketplace\Controller\CreatePassword;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $ksAccountManagement;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken
     */
    private $ksConfirmByToken;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $ksAccountManagement
     * @param \Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken $ksConfirmByToken
     */
    public function __construct(
        Context $ksContext,
        Session $ksCustomerSession,
        PageFactory $ksResultPageFactory,
        AccountManagementInterface $ksAccountManagement,
        ConfirmCustomerByToken $ksConfirmByToken = null
    ) {
        $this->ksSession = $ksCustomerSession;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksAccountManagement = $ksAccountManagement;
        $this->ksConfirmByToken = $ksConfirmByToken
            ?? ObjectManager::getInstance()->get(ConfirmCustomerByToken::class);
        parent::__construct($ksContext);
    }

    /**
     * Resetting password handler
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksResetPasswordToken = (string)$this->getRequest()->getParam('token');
        $ksIsDirectLink = $ksResetPasswordToken != '';
        if (!$ksIsDirectLink) {
            $ksResetPasswordToken = (string)$this->ksSession->getRpToken();
        }
        try {
            $this->ksAccountManagement->validateResetPasswordLinkToken(null, $ksResetPasswordToken);

            $this->ksConfirmByToken->execute($ksResetPasswordToken);

            if ($ksIsDirectLink) {
                $this->ksSession->setRpToken($ksResetPasswordToken);
                $ksResultRedirect = $this->resultRedirectFactory->create();
                $ksResultRedirect->setPath('multivendor/createpassword/index');

                return $ksResultRedirect;
            } else {
                /** @var \Magento\Framework\View\Result\Page $ksResultPage */
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__('Reset Password'));
                $ksResultPage->getLayout()
                           ->getBlock('resetpassword')
                           ->setResetPasswordLinkToken($ksResetPasswordToken);

                return $ksResultPage;                
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $ksResultRedirect */
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $ksResultRedirect->setPath('multivendor/forgotpassword/index');
            return $ksResultRedirect;
        }
    }
}