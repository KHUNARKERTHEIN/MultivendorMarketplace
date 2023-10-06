<?php
namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class SearchProduct extends Action
{
    /**
     * @var JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

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
     * Search constructor.
     * @param Context $context
     * @param JsonFactory $ksResultJsonFactory
     * @param PageFactory $ksResultPageFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksMainProductCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper 
     */
    public function __construct(
        Context $context,
        PageFactory $ksResultPageFactory,
        JsonFactory $ksResultJsonFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksMainProductCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper   
    ) {
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksMainProductCollection = $ksMainProductCollection;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductHelper = $ksProductHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            // Get Keyword
            $ksQuery = $this->getRequest()->getParam('keyword');
            // Get Seller Id
            $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
            // Get Seller Product
            $ksSellerProduct = $this->ksProductHelper->GetKsSellerPriceComparisonProduct($ksSellerId);
            // Get Limit on Price Comparison Product
            $ksLimitProductId = $this->ksProductHelper->CheckKsPriceComparisonLimit();
            // Get Price Comparison Product
            $ksPriceComparisonProduct = $this->ksProductHelper->GetKsPriceComparisonProduct();
            // Merge Limit Product and Seller Product
            $ksSellerProductId = array_merge($ksSellerProduct, $ksLimitProductId);

            $ksSellerProductId = array_merge($ksSellerProductId, $ksPriceComparisonProduct);

            $ksCollection = $this->ksMainProductCollection->create();
            // If Admin not allowed their product as being price comparison Product
            $ksCollection = $this->ksProductHelper->ksRemoveAdminProductFromPriceComparison($ksCollection);

            // Remove Sellers Product When Seller and his Store is Disabled
            $ksSellerRejectProductId = $this->ksProductHelper->ksDisabledSellerProduct();
       
            $ksSellerProductId = array_merge($ksSellerRejectProductId, $ksSellerProductId);
            $ksSellerProductId = array_unique($ksSellerProductId);

            // Remove Restricted Product Type From the Collection
            $ksCollection = $this->ksProductHelper->ksRemoveRestrictProductTypeFromPriceComparison($ksCollection);
            // Filter Product Collection According to name
            $ksCollection = $ksCollection->addAttributeToSelect('*')->addFieldToFilter('status', 1);
            $ksCollection = $ksCollection->addFieldToFilter('visibility', ['neq' => 1]);
            $ksCollection = $ksCollection->addAttributeToFilter('name', ['like' => '%'.$ksQuery.'%']);
            // Declare Suggested Product Name Array
            $ksProductName = [];
            foreach ($ksCollection as $ksRecord) {
                // Check the Suggestion is not Seller's Product
                if (!in_array($ksRecord->getEntityId(), $ksSellerProductId)) {
                    $ksProductName[] = $ksRecord->getName();
                }
            }
            $ksResult = $this->ksResultJsonFactory->create();
            $ksResult->setData(['ksproduct' => $ksProductName]);
            return $ksResult;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
