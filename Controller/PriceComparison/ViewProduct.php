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

/**
 * ViewProduct Controller class
 */
class ViewProduct extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry $ksRegistry
     */
    protected $ksRegistry;

    /**
     * @var CollectionFactory
     */
    protected $ksMainProductCollection;

    /**
     * @var CollectionFactory
     */
    protected $ksProductCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksProductHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksMainProductCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksMainProductCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection,
        \Magento\Framework\Registry $ksRegistry
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksMainProductCollection = $ksMainProductCollection;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Add Product Page of Price Comparsion
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        $this->ksRegistry->register('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                // Get Query from the Page
                $ksQuery = $this->getRequest()->getParam('query');
                $ksResultPage = $this->ksResultPageFactory->create();
                // Check Query
                if ($ksQuery) {
                    // Get Seller Id
                    $ksSellerId = $this->ksSellerHelper->getKsCustomerId();

                    // get All the Seller Product in the Array
                    $ksSellerProductId = $this->ksProductHelper->GetKsSellerPriceComparisonProduct($ksSellerId);

                    // Get Price Comparison Product when has no limit remains
                    $ksLimitProduct = $this->ksProductHelper->CheckKsPriceComparisonLimit();
                    // Get Price Comparison Product
                    $ksPriceComparisonProduct = $this->ksProductHelper->GetKsPriceComparisonProduct();

                    $ksSellerProductId = array_merge($ksSellerProductId, $ksLimitProduct);

                    $ksSellerProductId = array_merge($ksSellerProductId, $ksPriceComparisonProduct);
                    // Filter the Collection
                    $ksCollection = $this->ksMainProductCollection->create();
                    // Remove Sellers Product When Seller and his Store is Disabled
                    $ksSellerRejectProductId = $this->ksProductHelper->ksDisabledSellerProduct();
                    $ksSellerProductId = array_merge($ksSellerRejectProductId, $ksSellerProductId);
                    $ksSellerProductId = array_unique($ksSellerProductId);

                    // If Admin not allowed their product as being price comparison Product
                    $ksCollection = $this->ksProductHelper->ksRemoveAdminProductFromPriceComparison($ksCollection);

                    // Remove Restricted Product Type From the Collection
                    $ksCollection = $this->ksProductHelper->ksRemoveRestrictProductTypeFromPriceComparison($ksCollection);
                    // Check if the Array is not empty
                    if (!empty($ksSellerProductId)) {
                        $ksCollection = $ksCollection->addAttributeToSelect('*')->addFieldToFilter('status', 1)->addFieldToFilter('visibility', ['neq' => 1])->addAttributeToFilter('name', ['like' => '%'.$ksQuery.'%'])->addFieldToFilter('entity_id', ['nin' => $ksSellerProductId]);
                    } else {
                        $ksCollection = $ksCollection->addAttributeToSelect('*')->addFieldToFilter('status', 1)->addAttributeToFilter('name', ['like' => '%'.$ksQuery.'%']);
                    }
                    // Set Product Collection in the Registry
                    $this->ksRegistry->register('ks_product_collection', $ksCollection);
                    /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                    $ksResultPage->getConfig()->getTitle()->set(__('Manage Price Comparsion Product'));
                } else {
                    /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                    $ksResultPage->getConfig()->getTitle()->set(__('Manage Price Comparsion Product'));
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __($e->getMessage())
                );
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__('Manage Price Comparsion Product'));
            }
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
