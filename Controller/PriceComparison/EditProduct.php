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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;

/**
 * EditProduct Controller Class
 */
class EditProduct extends Action
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param ProductRepositoryInterface $ksProductRepository
     * @param KsSellerHelper $ksSellerHelper
     * @param Registry $ksRegistry
     * @param ProductFactory $ksProductFactory
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        ProductRepositoryInterface $ksProductRepository,
        KsSellerHelper $ksSellerHelper,
        Registry $ksRegistry,
        ProductFactory $ksProductFactory,
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksRegistry = $ksRegistry;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksStoreManager = $ksStoreManager ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->ksUrlBuilder = $ksUrlBuilder;
        parent::__construct($ksContext);
    }

    /**
     * Add Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            $ksProductId = $this->getRequest()->getParam('id');

            $ksStoreId = $this->getRequest()->getParam('store', 0);

            $ksStore = $this->ksStoreManager->getStore($ksStoreId);
            $this->ksStoreManager->setCurrentStore($ksStore->getCode());

            $ksProduct = $this->ksProductRepository->getById($ksProductId, true, $ksStoreId);

            $this->ksRegistry->register('product', $ksProduct);
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__($ksProduct->getName() ? $ksProduct->getName() : "Edit Product"));

            if (!$this->ksStoreManager->isSingleStoreMode()
                    && ($ksSwitchBlock = $ksResultPage->getLayout()->getBlock('ks_switcher'))
                ) {
                $ksSwitchBlock->setDefaultStoreName(__('Default Values'))
                        ->setWebsiteIds($ksProduct->getWebsiteIds())
                        ->setSwitchUrl(
                            $this->ksUrlBuilder->getUrl(
                                'multivendor/*/*',
                                ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                            )
                        );
            }
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * @param int $ksTypeId
     * @param int $attributeSetId
     * @param int $ksStoreId
     * @return \Magento\Catalog\Model\Product
     */
    private function createEmptyProduct($ksTypeId, $ksAttributeSetId, $ksStoreId): Product
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $ksProduct = $this->ksProductFactory->create();
        $ksProduct->setData('_edit_mode', true);

        if ($ksTypeId !== null) {
            $ksProduct->setTypeId($ksTypeId);
        }

        if ($ksStoreId !== null) {
            $ksProduct->setStoreId($ksStoreId);
        }

        if ($ksAttributeSetId) {
            $ksProduct->setAttributeSetId($ksAttributeSetId);
        }

        return $ksProduct;
    }
}
