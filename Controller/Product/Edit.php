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

namespace Ksolves\MultivendorMarketplace\Controller\Product;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Edit Controller class
 */
class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var ProductRepositoryInterface
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
     * @var StoreManagerInterface|mixed
     */
    protected $ksStoreManager;

    /**
     * @var KsSellerProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param ProductRepositoryInterface $ksProductRepository
     * @param ProductFactory $ksProductFactory
     * @param Registry $ksRegistry
     * @param KsSellerHelper $ksSellerHelper
     * @param KsSellerFactory $ksSellerProductFactory
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder
     * @param KsDataHelper $ksDataHelper
     * @param StoreManagerInterface $ksStoreManager
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        ProductRepositoryInterface $ksProductRepository,
        ProductFactory $ksProductFactory,
        Registry $ksRegistry,
        KsSellerHelper $ksSellerHelper,
        KsSellerProductFactory $ksSellerProductFactory,
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        KsDataHelper $ksDataHelper,
        StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksStoreManager = $ksStoreManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksProductId = (int) $this->getRequest()->getParam('id');
                if (!$ksProductId) {
                    throw new \Exception(__('No Product exists'));
                }

                //check current product is associate tocurrent seller id
                $ksSellerProduct = $this->ksSellerProductFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter('ks_product_id', $ksProductId);

                if ($ksSellerProduct->getSize() > 0) {
                    $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
                    if ($ksSellerProduct->getFirstItem()->getKsSellerId()!=$ksSellerId) {
                        throw new \Exception(__('This product is not associate to current seller.'));
                    }
                } else {
                    throw new \Exception(__('This product is not associate to current seller.'));
                }
                $ksStoreId = $this->getRequest()->getParam('store');

                if (!$ksStoreId) {
                    $ksStoreId = 0;
                }

                $ksProduct = $this->ksProductRepository->getById($ksProductId, true, $ksStoreId);

                $this->ksRegistry->register('product', $ksProduct);

                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__($ksProduct->getName() ? $ksProduct->getName() : "Edit Product"));

                if (!$this->ksStoreManager->isSingleStoreMode()
                    && ($switchBlock = $ksResultPage->getLayout()->getBlock('ks_switcher'))
                ) {
                    $switchBlock->setDefaultStoreName(__('Default Values'))
                        ->setWebsiteIds($ksProduct->getWebsiteIds())
                        ->setSwitchUrl(
                            $this->ksUrlBuilder->getUrl(
                                'multivendor/*/*',
                                ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                            )
                        );
                }

                return $ksResultPage;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
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
