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

namespace Ksolves\MultivendorMarketplace\Controller\Login;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\Phrase;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Framework\App\ObjectManager;

/**
 *  login seller action.
 */
class Login extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $ksCustomerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $ksFormKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $ksAccountRedirect;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @var ScopeConfigInterface
     */
    private $ksScopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $ksCookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $ksCookieMetadataManager;

    /**
     * @var CustomerUrl
     */
    private $ksCustomerUrl;
      
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @var SessionCleanerInterface
     */
    private $ksSessionCleaner;

    /**
     * @param Context $ksContext
     * @param Session $ksCustomerSession
     * @param AccountManagementInterface $ksCustomerAccountManagement
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param CustomerUrl $ksCustomerHelperData
     * @param Validator $ksFormKeyValidator
     * @param AccountRedirect $ksAccountRedirect
     * @param SessionCleanerInterface $ksSessionCleaner
     */
    public function __construct(
        Context $ksContext,
        Session $ksCustomerSession,
        AccountManagementInterface $ksCustomerAccountManagement,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        CustomerUrl $ksCustomerHelperData,
        Validator $ksFormKeyValidator,
        AccountRedirect $ksAccountRedirect,
        SessionCleanerInterface $ksSessionCleaner = null
    ) {
        $this->ksSession = $ksCustomerSession;
        $this->ksCustomerAccountManagement = $ksCustomerAccountManagement;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCustomerUrl = $ksCustomerHelperData;
        $this->ksFormKeyValidator = $ksFormKeyValidator;
        $this->ksAccountRedirect = $ksAccountRedirect;
        $ksObjectManager = ObjectManager::getInstance();
        $this->ksSessionCleaner = $ksSessionCleaner ?? $ksObjectManager->get(SessionCleanerInterface::class);
        parent::__construct($ksContext);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->ksScopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->ksScopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->ksCookieMetadataManager) {
            $this->ksCookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->ksCookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->ksCookieMetadataFactory) {
            $this->ksCookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->ksCookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $ksRequest
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksResultRedirect->setPath('*/*/');

        return new InvalidRequestException(
            $ksResultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $ksRequest): ?bool
    {
        return null;
    }

    /**
     * Login action
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {   
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller == 1) {
        if ($this->ksSession->isLoggedIn() || !$this->ksFormKeyValidator->validate($this->getRequest())) {
                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultRedirect = $this->resultRedirectFactory->create();
                $ksResultRedirect->setPath('multivendor/sellerprofile/homepage');
                return $ksResultRedirect;
            } else {
                $this->messageManager->addWarning(__("Customer account can't login."));
                return $this->resultRedirectFactory->create()->setPath(
                    'multivendor/login/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
        
        if ($this->getRequest()->isPost()) {
            $ksLogin = $this->getRequest()->getPost('login');
            if (!empty($ksLogin['username']) && !empty($ksLogin['password'])) {
                try {
                    $ksCustomer = $this->ksCustomerAccountManagement->authenticate($ksLogin['username'], $ksLogin['password']);
                    $this->ksSession->setCustomerDataAsLoggedIn($ksCustomer);
                    $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
                    // check for seller
                    if ($ksIsSeller == 1) {
                        /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                        $ksResultRedirect = $this->resultRedirectFactory->create();
                        $ksResultRedirect->setPath('multivendor/sellerprofile/homepage');
                        return $ksResultRedirect;
                    }
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $ksMetadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $ksMetadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $ksMetadata);
                    }
                    $ksRedirectUrl = $this->ksAccountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $ksRedirectUrl) {
                        $this->ksAccountRedirect->clearRedirectCookie();
                        $ksResultRedirect = $this->resultRedirectFactory->create();
                        $ksResultRedirect->setUrl('multivendor/login/index');
                        return $ksResultRedirect;
                    } else {
                        $this->messageManager->addWarning(__("Customer account can't login."));
                        return $this->resultRedirectFactory->create()->setPath(
                            'multivendor/login/index',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } catch (EmailNotConfirmedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'confirmAccountErrorMessage',
                        ['url' => $this->ksCustomerUrl->getEmailConfirmationUrl($ksLogin['username'])]
                    );
                    $this->ksSession->setUsername($ksLogin['username']);
                } catch (AuthenticationException $e) {
                    $ksMessage = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $ksMessage = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($ksMessage)) {
                        $this->messageManager->addErrorMessage($ksMessage);
                        $this->ksSession->setUsername($ksLogin['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }
        return $this->resultRedirectFactory->create()->setPath(
            'multivendor/login/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
