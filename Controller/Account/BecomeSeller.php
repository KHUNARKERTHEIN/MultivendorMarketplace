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

namespace Ksolves\MultivendorMarketplace\Controller\Account;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;

/**
 * BecomeSeller controller class
 */
class BecomeSeller extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $ksForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory
     * @param CollectionFactory $ksCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory,
        CollectionFactory $ksCollectionFactory
    ) {
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksForwardFactory = $ksForwardFactory;
        parent::__construct($ksContext);
    }

    /**
     * Became Vendor page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // Check Seller Login is Enable or Not
        $ksEnable = $this->ksDataHelper->getKsConfigLoginAndRegistrationSetting('ks_enable_seller_login');
        if ($ksEnable) {
            $ksCustomerId = $this->ksSellerHelper->getKsCustomerId();

            $ksSellerCollection = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksCustomerId)->addFieldToFilter('ks_seller_status', ['eq' => 1]);
            if (!$this->ksCustomerSession->isLoggedIn()) {
                return $this->resultRedirectFactory->create()->setPath(
                    'customer/account/login',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } elseif (!$ksSellerCollection->getSize()) {
                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__('Become Seller'));
                return $ksResultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'customer/account/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            $ksResultForward = $this->ksForwardFactory->create();
            $ksResultForward->setController('index');
            $ksResultForward->forward('defaultNoRoute');
            return $ksResultForward;
        }
    }
}
