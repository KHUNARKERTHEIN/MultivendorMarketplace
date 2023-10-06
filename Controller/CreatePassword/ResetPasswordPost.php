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

namespace Ksolves\MultivendorMarketplace\Controller\CreatePassword;


use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Framework\App\Action\Action;

/**
 * Reset password controller
 */
class ResetPasswordPost extends Action
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $ksAccountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $ksCustomerRepository;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @param Context $ksContext
     * @param Session $ksCustomerSession
     * @param AccountManagementInterface $ksAccountManagement
     * @param CustomerRepositoryInterface $ksCustomerRepository
     * @param CredentialsValidator|null $ksCredentialsValidator
     */
    public function __construct(
        Context $ksContext,
        Session $ksCustomerSession,
        AccountManagementInterface $ksAccountManagement,
        CustomerRepositoryInterface $ksCustomerRepository,
        CredentialsValidator $ksCredentialsValidator = null
    ) {
        $this->ksSession = $ksCustomerSession;
        $this->ksAccountManagement = $ksAccountManagement;
        $this->ksCustomerRepository = $ksCustomerRepository;
        parent::__construct($ksContext);
    }

    /**
     * Reset forgotten password
     * 
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $ksResultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksResetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $ksPassword = (string)$this->getRequest()->getPost('password');
        $ksPasswordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        if ($ksPassword !== $ksPasswordConfirmation) {
            $this->messageManager->addErrorMessage(__("New Password and Confirm New Password values didn't match."));
            $ksResultRedirect->setPath('multivendor/createpassword/index', ['token' => $ksResetPasswordToken]);

            return $ksResultRedirect;
        }
        if (iconv_strlen($ksPassword) <= 0) {
            $this->messageManager->addErrorMessage(__('Please enter a new password.'));
            $ksResultRedirect->setPath('multivendor/createpassword/index', ['token' => $ksResetPasswordToken]);

            return $ksResultRedirect;
        }

        try {
            $this->ksAccountManagement->resetPassword(
                null,
                $ksResetPasswordToken,
                $ksPassword
            );
            // logout from current session if password changed.
            if ($this->ksSession->isLoggedIn()) {
                $this->ksSession->logout();
                $this->ksSession->start();
            }
            $this->ksSession->unsRpToken();
            $this->messageManager->addSuccessMessage(__('You updated your password.'));
            $ksResultRedirect->setPath('multivendor/login/index');

            return $ksResultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addErrorMessage($error->getMessage());
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the new password.'));
        }
        $ksResultRedirect->setPath('multivendor/createpassword/index', ['token' => $ksResetPasswordToken]);
        return $ksResultRedirect;
    }
}