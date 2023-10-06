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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;

/**
   class KsProductOverride 
 */
class KsProductOverride extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{
    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;
    
    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param Product\Builder $ksProductBuilder
     * @param Product\Initialization\Helper $ksInitializationHelper
     * @param \Magento\Catalog\Model\Product\Copier $ksProductCopier
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $ksProductTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository
     * @param \Magento\Framework\Escaper $ksEscaper = null
     * @param \Psr\Log\LoggerInterface $ksLogger = null
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $ksCategoryLinkManagement = null
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null,
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        Product\Builder $ksProductBuilder,
        Product\Initialization\Helper $ksInitializationHelper,
        \Magento\Catalog\Model\Product\Copier $ksProductCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $ksProductTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $ksProductRepository,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Framework\Escaper $ksEscaper = null,
        \Psr\Log\LoggerInterface $ksLogger = null,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $ksCategoryLinkManagement = null,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager = null
    ) {
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        $this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
        $this->ksProductFactory = $ksProductFactory;
        parent::__construct($ksContext, $ksProductBuilder, $ksInitializationHelper, $ksProductCopier, $ksProductTypeManager, $ksProductRepository, $ksEscaper, $ksLogger, $ksCategoryLinkManagement, $ksStoreManager);
    }

    /**
     * Admin Product Save Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksProductArray = $this->getRequest()->getPostValue('product');
        $ksProductCategoryIds = isset($ksProductArray['category_ids']) ? $ksProductArray['category_ids'] : [];
        $ksCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$this->getRequest()->getParam('id'));
        //iterate product category backup collection
        foreach($ksCollection as $ksItem){
            $ksIsSellerProduct = $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_product_id',$ksItem->getKsProductId())->getFirstItem();
            if(!empty($ksIsSellerProduct->getData())){
                $ksCollection = $this->ksSellerCategoryCollection->create()
                ->addFieldToFilter('ks_seller_id', $ksIsSellerProduct->getKsSellerId())
                ->addFieldToFilter('ks_category_id',$ksItem->getKsCategoryId());
                if($ksCollection->getSize() > 0){
                    if (($ksKey = array_search($ksItem->getKsCategoryId(),$ksProductCategoryIds)) !== false) {
                        unset($ksProductCategoryIds[$ksKey]);
                    } else {
                        $ksItem->delete();
                    }
                }
            }
        }
        if(isset($ksProductArray['category_ids'])){
            $ksProductArray['category_ids'] = $ksProductCategoryIds;
            $this->getRequest()->setPostValue('product',$ksProductArray);
        }
        return parent::execute();
    }
} 