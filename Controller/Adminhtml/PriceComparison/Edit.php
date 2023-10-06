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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\PriceComparison;

use Magento\Backend\App\Action;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Edit Controller Class
 */
class Edit extends Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ksolves_MultivendorMarketplace::pricecomparison';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksRegistry
     * @param ProductRepositoryInterface $ksProductRepository
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksRegistry,
        KsProductFactory $ksProductFactory,
        ProductRepositoryInterface $ksProductRepository,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksStoreManager = $ksStoreManager ?: ObjectManager::getInstance()
                                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        parent::__construct($ksContext);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu

        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->setActiveMenu('Ksolves_MultivendorMarketplace::all_price_comparison_products');
        return $ksResultPage;
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('id');

        // get current url
        $ksCurrentUrl = $this->ksStoreManager->getStore()->getCurrentUrl();

        // get the url to redirect to seller edit or pending seller edit
        if (str_contains($ksCurrentUrl, 'multivendor/pricecomparison/pendingedit')) {
            $ksRedirectUrl = '*/*/pendingedit';
        } else {
            $ksRedirectUrl = '*/*/edit';
        }

        $ksModel = $this->ksProductFactory->create();
        if ($ksId) {
            $ksModel->load($ksId);
            $ksStoreId = $this->getRequest()->getParam('store') ? $this->getRequest()->getParam('store') : 0;
            $ksProduct = $this->ksProductRepository->getById($ksModel->getKsProductId(), true, $ksStoreId);
            $this->ksCoreRegistry->register('ks_product_id', $ksModel->getKsProductId());
            $this->ksCoreRegistry->register('ks_product_modal', $ksProduct);
            if (!$ksModel->getId()) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));

                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath($this->_redirect->getRefererUrl());
            }
        }

        $ksResultPage = $this->ksResultPageFactory->create();

        // get the products's website storeview in store switcher
        if (!$this->ksStoreManager->isSingleStoreMode() && ($ksSwitchBlock = $ksResultPage->getLayout()->getBlock('store_switcher'))) {
            $ksSwitchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($ksProduct->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        $ksRedirectUrl,
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }

        $ksResultPage->getConfig()->getTitle()->prepend(__($ksProduct->getName()));

        return $ksResultPage;
    }
}
