<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited  (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

/**
 * SellerProfile Controller Class
 */
class SellerProfile extends \Magento\Framework\App\Action\Action
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
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $ksPageConfig;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory
     */
    protected $ksSellerStoreFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
     */
    protected $ksSellerFactory;
    
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory 
     */
    protected $ksForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\View\Page\Config $ksPageConfig
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory $ksSellerStoreFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Framework\View\Page\Config $ksPageConfig,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory $ksSellerStoreFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksPageConfig = $ksPageConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksForwardFactory = $ksForwardFactory;
        parent::__construct($ksContext);
    }

    /**
     * Sell page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksSellerId = $this->getRequest()->getParam('seller_id');
        $ksStoreId = $this->ksStoreManager->getStore()->getId();
        $ksCollection = $this->ksSellerStoreFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', 0)->getFirstItem();
        $ksSellerCollection = $this->ksSellerFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        if($ksSellerCollection->getKsStoreStatus()){
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__($ksSellerCollection->getKsStoreName()));
            $ksResultPage->getConfig()->setKeywords(__($ksCollection->getKsMetaKeyword())); // meta keywords
            $ksResultPage->getConfig()->setDescription(__($ksCollection->getKsMetaDescription())); //meta description
            $this->ksSellerHelper->ksFlushCache();
            return $ksResultPage;
        } else {
            $ksResultForward = $this->ksForwardFactory->create();
            $ksResultForward->setController('index');
            $ksResultForward->forward('defaultNoRoute');
            return $ksResultForward;
        }
    }
}
