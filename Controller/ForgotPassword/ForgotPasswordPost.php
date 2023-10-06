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

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Math\Random;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;


/**
 * ForgotPasswordPost controller
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\AbstractAccount implements HttpPostActionInterface
{
    /**
     * Configuration paths for remind email identity
     * @see \Magento\Customer\Model\EmailNotification::XML_PATH_REGISTER_EMAIL_IDENTITY
     */
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $ksCustomerAccountManagement;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $ksEscaper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;
     
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;
    
    /**
     * @var DateTime
     */

    protected $ksDateTime;

    /**
     * @var CustomerRepositoryInterface
     */
    private $ksCustomerRepository;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @var CustomerRegistry
     */
    private $ksCustomerRegistry;
    
    /**
     * @var DateTimeFactory
     */
    private $ksDateTimeFactory;

    /**
     * @var Random
     */
    private $ksMathRandom;

    /**
     * @param Context $ksContext
     * @param Session $ksCustomerSession
     * @param KsEmailHelper $ksEmailHelper
     * @param Random $ksMathRandom
     * @param AccountManagementInterface $ksCustomerAccountManagement
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $ksCustomerRepository
     * @param CustomerRegistry $ksCustomerRegistry
     * @param DateTime $ksDateTime
     * @param Escaper $ksEscaper
     * @param DateTimeFactory $ksDateTimeFactory
     */
    public function __construct(
        Context $ksContext,
        Session $ksCustomerSession,
        KsEmailHelper $ksEmailHelper,
        Random $ksMathRandom,
        AccountManagementInterface $ksCustomerAccountManagement,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $ksCustomerRepository,
        CustomerRegistry $ksCustomerRegistry,
        DateTime $ksDateTime,
        Escaper $ksEscaper,
        DateTimeFactory $ksDateTimeFactory = null
    ) {
        $this->ksSession = $ksCustomerSession;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksMathRandom = $ksMathRandom;
        $this->ksCustomerAccountManagement = $ksCustomerAccountManagement;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCustomerRepository = $ksCustomerRepository;
        $this->ksCustomerRegistry = $ksCustomerRegistry;
        $ksObjectManager = ObjectManager::getInstance();
        $this->ksDateTimeFactory = $ksDateTimeFactory ?: $ksObjectManager->get(DateTimeFactory::class);
        $this->ksEscaper = $ksEscaper;
        parent::__construct($ksContext);
    }

    /**
     * Forgot password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $ksResultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksEmail = (string)$this->getRequest()->getPost('email');
        if ($ksEmail) {
            if (!\Zend_Validate::is($ksEmail, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->ksSession->setForgottenEmail($ksEmail);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $ksResultRedirect->setPath('multivendor/forgotpassword/index');
            }
            try {
                try {
                    //Your Website Id
                    $ksWebsiteId = $this->ksStoreManager->getStore()->getWebsiteId();
                    $ksCustomer = $this->ksCustomerRepository->get($ksEmail, $ksWebsiteId);
                } catch (Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("The customer email isn't defined."));
                }
                $ksNewPasswordToken = $this->ksMathRandom->getUniqueHash();
                $this->changeResetPasswordLinkToken($ksCustomer, $ksNewPasswordToken);
                
                $this->ksEmailHelper->ksSellerForgotPasswordMail(['customer-name'=>$ksCustomer->getFirstname(),'ksToken'=> $ksNewPasswordToken], self::XML_PATH_REGISTER_EMAIL_IDENTITY, $ksCustomer->getFirstname(), $ksEmail);
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $ksResultRedirect->setPath('multivendor/forgotpassword/index');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $ksResultRedirect->setPath('multivendor/forgotpassword/index');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($ksEmail));
            return $ksResultRedirect->setPath('multivendor/forgotpassword/index');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $ksResultRedirect->setPath('multivendor/forgotpassword/index');
        }
    }

    public function changeResetPasswordLinkToken($ksCustomer, $ksPasswordLinkToken)
    {
        if (!is_string($ksPasswordLinkToken) || empty($ksPasswordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $ksPasswordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        }
        if (is_string($ksPasswordLinkToken) && !empty($ksPasswordLinkToken)) {
            $ksCustomerSecure = $this->ksCustomerRegistry->retrieveSecureData($ksCustomer->getId());
            $ksCustomerSecure->setRpToken($ksPasswordLinkToken);
            $ksCustomerSecure->setRpTokenCreatedAt(
                $this->ksDateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->setIgnoreValidationFlag($ksCustomer);
            $this->ksCustomerRepository->save($ksCustomer);
        }
        return true;
    }
    private function setIgnoreValidationFlag($ksCustomer)
    {
        $ksCustomer->setData('ignore_validation_flag', true);
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($ksEmail)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->ksEscaper->escapeHtml($ksEmail)
        );
    }
}
