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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Url;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterfaceFactory;
use Magento\LoginAsCustomerApi\Api\DeleteAuthenticationDataForUserInterface;
use Magento\LoginAsCustomerApi\Api\SaveAuthenticationDataInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher\ManageStoreCookie;

/**
 * Login as seller action
 * Generate secret key and forward to the storefront action
 */
class LoginAsSeller extends Action
{
    /**
     * @var Session
     */
    private $ksAuthSession;

    /**
     * @var StoreManagerInterface
     */
    private $ksStoreManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $ksCustomerRepository;

    /**
     * @var AuthenticationDataInterfaceFactory
     */
    private $ksAuthenticationDataFactory;

    /**
     * @var SaveAuthenticationDataInterface
     */
    private $ksSaveAuthenticationData;

    /**
     * @var DeleteAuthenticationDataForUserInterface
     */
    private $ksDeleteAuthenticationDataForUser;

    /**
     * @var Url
     */
    private $ksUrl;

    /**
     * @var Share
     */
    private $ksShare;

    /**
     * @var ManageStoreCookie
     */
    private $ksManageStoreCookie;

    /**
     * @param Context $ksContext
     * @param Session $ksAuthSession
     * @param StoreManagerInterface $ksStoreManager
     * @param CustomerRepositoryInterface $ksCustomerRepository
     * @param AuthenticationDataInterfaceFactory $ksAuthenticationDataFactory
     * @param SaveAuthenticationDataInterface $ksSaveAuthenticationData
     * @param DeleteAuthenticationDataForUserInterface $ksDeleteAuthenticationDataForUser
     * @param Url $ksUrl
     * @param Share $ksShare
     * @param ManageStoreCookie $ksManageStoreCookie
     */
    public function __construct(
        Context $ksContext,
        Session $ksAuthSession,
        StoreManagerInterface $ksStoreManager,
        CustomerRepositoryInterface $ksCustomerRepository,
        AuthenticationDataInterfaceFactory $ksAuthenticationDataFactory,
        SaveAuthenticationDataInterface $ksSaveAuthenticationData,
        DeleteAuthenticationDataForUserInterface $ksDeleteAuthenticationDataForUser,
        Url $ksUrl,
        ?Share $ksShare = null,
        ?ManageStoreCookie $ksManageStoreCookie = null
    ) {
        $this->ksAuthSession = $ksAuthSession;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCustomerRepository = $ksCustomerRepository;
        $this->ksAuthenticationDataFactory = $ksAuthenticationDataFactory;
        $this->ksSaveAuthenticationData = $ksSaveAuthenticationData;
        $this->ksDeleteAuthenticationDataForUser = $ksDeleteAuthenticationDataForUser;
        $this->ksUrl = $ksUrl;
        $this->ksShare = $ksShare ?? ObjectManager::getInstance()->get(Share::class);
        $this->ksManageStoreCookie = $ksManageStoreCookie ?? ObjectManager::getInstance()->get(ManageStoreCookie::class);
        parent::__construct($ksContext);
    }

    /**
     * Login as seller
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Redirect $ksResultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $ksSellerId = (int)$this->_request->getParam('seller_id');

        try {
            $ksCustomer = $this->ksCustomerRepository->getById($ksSellerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage('Seller with this ID are no longer exist.');
            return $ksResultRedirect->setPath('multivendor/seller/index');
        }

        if ($this->ksShare->isGlobalScope()) {
            $ksStoreId = (int)$this->ksStoreManager->getDefaultStoreView()->getId();
        } else {
            $ksStoreId = (int)$ksCustomer->getStoreId();
        }

        $ksAdminUser = $this->ksAuthSession->getUser();
        $ksUserId = (int)$ksAdminUser->getId();

        /** @var AuthenticationDataInterface $ksAuthenticationData */
        $ksAuthenticationData = $this->ksAuthenticationDataFactory->create(
            [
                'customerId' => $ksSellerId,
                'adminId' => $ksUserId,
                'extensionAttributes' => null,
            ]
        );

        $this->ksDeleteAuthenticationDataForUser->execute($ksUserId);
        $ksSecret = $this->ksSaveAuthenticationData->execute($ksAuthenticationData);
        $this->ksAuthSession->setIsLoggedAsCustomer(true);

        $ksRedirectUrl = $this->getKsLoginProceedRedirectUrl($ksSecret, $ksStoreId);
        $ksResultRedirect->setUrl($ksRedirectUrl);
        return $ksResultRedirect;
    }

    /**
     * Get login proceed redirect url
     *
     * @param string $ksSecret
     * @param int $ksStoreId
     * @return string
     * @throws NoSuchEntityException
     */
    private function getKsLoginProceedRedirectUrl(string $ksSecret, int $ksStoreId): string
    {
        $ksTargetStore = $this->ksStoreManager->getStore($ksStoreId);
        $ksQueryParams = ['secret' => $ksSecret];
        $ksRedirectUrl = $this->ksUrl
            ->setScope($ksTargetStore)
            ->getUrl('multivendor/seller/loginbackend', ['_query' => $ksQueryParams, '_nosid' => true]);

        if (!$ksTargetStore->isUseStoreInUrl()) {
            $ksFromStore = $this->ksStoreManager->getStore();
            $ksRedirectUrl = $this->ksManageStoreCookie->switch($ksFromStore, $ksTargetStore, $ksRedirectUrl);
        }
        
        return $ksRedirectUrl;
    }
}
