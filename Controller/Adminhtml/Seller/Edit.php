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

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Edit Controller
 */
class Edit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
      * @var DataPersistorInterface
      */
    protected $ksDataPersistor;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     */
    protected $ksStoreManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param CollectionFactory $ksCollectionFactory
     * @param ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        DataPersistorInterface $ksDataPersistor,
        CollectionFactory $ksCollectionFactory,
        ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager ?: ObjectManager::getInstance()
                                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        //seller Id
        $ksSellerId = $this->getRequest()->getParam('seller_id');
        // get current url
        $ksCurrentUrl = $this->ksStoreManager->getStore()->getCurrentUrl();
        //set default seller config data
        $this->ksSellerHelper->setKsSellerConfigData($ksSellerId);
        // get the url to redirect to seller edit or pending seller edit
        if (str_contains($ksCurrentUrl, 'multivendor/seller/pendingedit')) {
            $ksRedirectUrl = '*/*/pendingedit';
        } else {
            $ksRedirectUrl = '*/*/edit';
        }
        
        // check seller id
        if ($ksSellerId) {
            try {
                //get seller collection
                $ksSeller = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
                // check seller collection
                if ($ksSeller->getSize() > 0) {
                    // set seller id in registry
                    $this->ksCoreRegistry->register('current_seller_id', $ksSellerId);
                    // set seller id in datapersistor
                    $this->ksDataPersistor->set('ks_current_seller_id', $ksSellerId);
                } else {
                    $this->messageManager->addError(__('Something went wrong while editing the seller.'));
                    $ksResultRedirect = $this->resultRedirectFactory->create();
                    $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                    return $ksResultRedirect;
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while editing the seller.'));
                $ksResultRedirect = $this->resultRedirectFactory->create();
                $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                return $ksResultRedirect;
            }
        }
        $ksResultPage = $this->ksResultPageFactory->create();

        if ($this->ksScopeConfig->getValue('customer/account_share/scope')) {
            $ksSellerWebsiteId = [$this->ksSellerHelper->getksSellerWebsiteId($ksSellerId)];
        } else {
            $ksSellerWebsiteId = [];
        }
        
        // get the seller's website storeview in store switcher
        if (!$this->ksStoreManager->isSingleStoreMode() && ($ksSwitchBlock = $ksResultPage->getLayout()->getBlock('store_switcher'))) {
            $ksSwitchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($ksSellerWebsiteId)
                ->setSwitchUrl(
                    $this->getUrl(
                        $ksRedirectUrl,
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }
        // check id to set the page title
        if ($ksSellerId) {
            $ksResultPage->getConfig()->getTitle()->prepend($this->ksSellerHelper->getKsSellerName($ksSellerId));
        } else {
            $ksResultPage->getConfig()->getTitle()->prepend(__('Add New Seller'));
        }
        return $ksResultPage;
    }
}
