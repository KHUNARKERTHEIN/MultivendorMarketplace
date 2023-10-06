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
 * HomePage Controller Class
 */
class HomePage extends \Magento\Framework\App\Action\Action
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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper
     */
    protected $ksKsSellerDbMyProfileHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $ksKsSellerDbMyProfileHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerDashboardMyProfileHelper $ksKsSellerDbMyProfileHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksKsSellerDbMyProfileHelper = $ksKsSellerDbMyProfileHelper;
        parent::__construct($ksContext);
    }

    /**
     * Vendor Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            // check homepage link is enable/disable
            if ($this->ksKsSellerDbMyProfileHelper->getKsSellerConfigData('ks_homepage')) {
                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__('Homepage'));
                $this->ksSellerHelper->ksFlushCache();
                return $ksResultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'multivendor/product/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
