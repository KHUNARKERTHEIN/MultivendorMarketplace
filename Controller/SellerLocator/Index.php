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
namespace Ksolves\MultivendorMarketplace\Controller\SellerLocator;

/**
 * SellerLocator Index Controller class
 */
class Index extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Registry $ksRegistry
     */
    protected $ksRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
     */
    protected $ksHelperData;
 
    /**
     * SellerLocator Index Constructor
     *
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksRegistry = $ksRegistry;
        $this->ksHelperData = $ksHelperData;
        parent::__construct($ksContext);
    }

    /**
     * Seller Dashboard page.
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        $this->ksRegistry->register('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        // check for seller
        if ($ksIsSeller == 1 && $this->ksHelperData->isKsSLEnabled()) {
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Location Details'));
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
