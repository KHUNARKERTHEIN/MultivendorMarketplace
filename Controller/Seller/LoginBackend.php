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

namespace Ksolves\MultivendorMarketplace\Controller\Seller;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Login as Customer storefront login action
 */
class LoginBackend implements HttpGetActionInterface
{
    /**
     * @var ResultFactory
     */
    private $ksResultFactory;

    /**
     * @var RequestInterface
     */
    private $ksRequest;

    /**
     * @var CustomerRepositoryInterface
     */
    private $ksCustomerRepository;

    /**
     * @var GetAuthenticationDataBySecretInterface
     */
    private $ksGetAuthenticationDataBySecret;

    /**
     * @var AuthenticateCustomerBySecretInterface
     */
    private $ksAuthenticateCustomerBySecret;

    /**
     * @var ManagerInterface
     */
    private $ksMessageManager;

    /**
     * @var SessionManagerInterface
     */
    protected $ksCoreSession;

    /**
     * @param ResultFactory $ksResultFactory
     * @param RequestInterface $ksRequest
     * @param CustomerRepositoryInterface $ksCustomerRepository
     * @param GetAuthenticationDataBySecretInterface $ksGetAuthenticationDataBySecret
     * @param AuthenticateCustomerBySecretInterface $ksAuthenticateCustomerBySecret
     * @param ManagerInterface $ksMessageManager
     * @param SessionManagerInterface $ksCoreSession
     */
    public function __construct(
        ResultFactory $ksResultFactory,
        RequestInterface $ksRequest,
        CustomerRepositoryInterface $ksCustomerRepository,
        GetAuthenticationDataBySecretInterface $ksGetAuthenticationDataBySecret,
        AuthenticateCustomerBySecretInterface $ksAuthenticateCustomerBySecret,
        ManagerInterface $ksMessageManager,
        SessionManagerInterface $ksCoreSession
    ) {
        $this->ksResultFactory = $ksResultFactory;
        $this->ksRequest = $ksRequest;
        $this->ksCustomerRepository = $ksCustomerRepository;
        $this->ksGetAuthenticationDataBySecret = $ksGetAuthenticationDataBySecret;
        $this->ksAuthenticateCustomerBySecret = $ksAuthenticateCustomerBySecret;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksCoreSession = $ksCoreSession;
    }

    /**
     * Login as Customer storefront login
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Redirect $ksResultRedirect */
        $ksResultRedirect = $this->ksResultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $ksSecret = $this->ksRequest->getParam('secret');
            if (empty($ksSecret) || !is_string($ksSecret)) {
                throw new LocalizedException(__('Cannot login to account. No secret key provided.'));
            }

            $ksAuthenticationData = $this->ksGetAuthenticationDataBySecret->execute($ksSecret);

            try {
                $ksCustomer = $this->ksCustomerRepository->getById($ksAuthenticationData->getCustomerId());
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('Seller are no longer exist.'));
            }

            $this->ksAuthenticateCustomerBySecret->execute($ksSecret);

            $this->ksMessageManager->addSuccessMessage(
                __('You are logged in as seller: %1', $ksCustomer->getFirstname() . ' ' . $ksCustomer->getLastname())
            );

            // set the value in custom session
            $this->ksCoreSession->start();
            $this->ksCoreSession->setKsIsLoginFromAdmin(true);

            $ksResultPage = $this->ksResultFactory->create(ResultFactory::TYPE_PAGE);
            $ksResultPage->getConfig()->getTitle()->set(__('You are logged in'));
            return $this->ksResultFactory->create(ResultFactory::TYPE_PAGE);
        } catch (LocalizedException $e) {
            $this->ksMessageManager->addErrorMessage($e->getMessage());
            $ksResultRedirect->setPath('/');
        } catch (\Exception $e) {
            $this->ksMessageManager->addErrorMessage(__('Cannot login to account.'));
            $ksResultRedirect->setPath('/');
        }
        return $ksResultRedirect;
    }
}
