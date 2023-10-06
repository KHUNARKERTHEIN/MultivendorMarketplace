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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CategoryRequests;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;

/**
 * KsCategoryProductDetails block class
 */
class KsCategoryProductDetails extends \Magento\Backend\Block\Template
{
    /**
     * @var ProductFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var Visibility
     */
    private $ksProductVisibilty;

    /**
     * @var KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var KsProductCollection
     */
    protected $ksCatalogProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param ProductFactory $ksProductCollectionFactory
     * @param KsProductFactory $ksProductFactory
     * @param KsProductCollection $ksCatalogProductFactory
     * @param Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Directory\Model\CurrencyFactory
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     * @param Visibility $ksProductVisibilty = null
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        ProductFactory $ksProductCollectionFactory,
        KsProductFactory $ksProductFactory,
        KsProductCollection $ksCatalogProductFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Directory\Model\CurrencyFactory $ksCurrencyFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection,
        Visibility $ksProductVisibilty = null,
        array $data = []
    ) {
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksProductVisibilty = $ksProductVisibilty ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        $this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
        parent::__construct($ksContext, $data);
    }

    /**
     * get category product collection
     * @return array
     */
    public function getCategoryProductCollection()
    {
        //get category id
        $ksCategoryId = $this->getRequest()->getParam('ks_category_id');
        //get seller id
        $ksSellerId = (int)$this->getRequest()->getParam('ks_seller_id');
        //get store id
        $ksStoreId = (int)$this->getRequest()->getParam('ks_store_id');
        //get id
        $ksId = $this->getRequest()->getParam('ks_product_id');
        //get name
        $ksName = $this->getRequest()->getParam('ks_name');
        //get sku
        $ksSku = $this->getRequest()->getParam('ks_sku');
        //get visibility
        $ksVisibility = $this->getRequest()->getParam('ks_visibility');
        //get status
        $ksStatus = $this->getRequest()->getParam('ks_status');
        //get price from
        $ksPriceFrom = $this->getRequest()->getParam('ks_price_from');
        //get price to
        $ksPriceTo = $this->getRequest()->getParam('ks_price_to');
        //get position from
        $ksPositionFrom = $this->getRequest()->getParam('ks_position_from');
        //get position to
        $ksPositionTo = $this->getRequest()->getParam('ks_position_to');

        $ksCollection = $this->ksCatalogProductFactory->create()->addAttributeToSelect(
            '*'
        );

        //join with seller table
        $ksProductTable = $ksCollection->getTable('ks_product_details');
        $ksCollection->getSelect()->join(
            $ksProductTable. ' as ks_pro',
            'e.entity_id = ks_pro.ks_product_id',
            [
                        'ks_seller_id' => 'ks_seller_id',
                        'ks_created_at' =>'ks_created_at',
                        'ks_product_approval_status' => 'ks_product_approval_status',
                    ]
        )->where('ks_product_approval_status =? ', KsProduct::KS_STATUS_APPROVED)
            ->where('ks_seller_id = ?', $ksSellerId)
            ->where('ks_parent_product_id = ?', 0)
            ->order('ks_created_at desc');

        if ($this->getKsIsCategoryDisabled()) {
            $ksBackupTable = $ksCollection->getTable('ks_product_category_backup');

            $ksCollection->getSelect()->join(
                $ksBackupTable. ' as ks_cat',
                'ks_pro.ks_product_id = ks_cat.ks_product_id',
                [
                    'ks_category_id' => 'ks_category_id',
                    'position' => 'ks_position'
                ]
            )->where('ks_category_id = ?', $ksCategoryId);
        } else {
            $ksCollection->addCategoriesFilter(['eq' => $ksCategoryId]);
            $ksCollection->joinField(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                'category_id=' . $ksCategoryId,
                'left'
            );
        }

        if ($ksId) {
            $ksCollection->addFieldToFilter('entity_id', ['eq' => $ksId]);
        }
        if ($ksName) {
            $ksCollection->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => '%'.$ksName.'%']
                ]
            );
        }
        if ($ksSku) {
            $ksCollection->addAttributeToFilter(
                [
                    ['attribute' => 'sku', 'like' => '%'.$ksSku.'%']
                ]
            );
        }
        if ($ksVisibility) {
            $ksCollection->addAttributeToFilter('visibility', $ksVisibility);
        }
        if ($ksStatus) {
            $ksCollection->addAttributeToFilter('status', $ksStatus);
        }
        if ($ksPriceFrom != null) {
            $ksCollection->addAttributeToFilter('price', array('gteq' => $ksPriceFrom));
        }
        if ($ksPriceTo != null) {
            $ksCollection->addAttributeToFilter('price', array('lteq' => $ksPriceTo));
        }
        if ($ksPositionFrom != null) {
            $ksCollection->addAttributeToFilter('position', array('gteq' => $ksPositionFrom));
        }
        if ($ksPositionTo != null) {
            $ksCollection->addAttributeToFilter('position', array('lteq' => $ksPositionTo));
        }

        $ksShowPerPage = '5';
        if ($this->getRequest()->getParam('ksShowPerPage')) {
            $ksShowPerPage = $this->getRequest()->getParam('ksShowPerPage');
        }

        $ksCurrentPage = '1';
        if ($this->getRequest()->getParam('ksCurrentPage')) {
            $ksCurrentPage = $this->getRequest()->getParam('ksCurrentPage');
        }
        $ksCollection->setPageSize($ksShowPerPage);
        $ksCollection->setCurPage($ksCurrentPage);

        return $ksCollection;
    }

    /**
     * get store id
     * @return in
     */
    public function getKsStoreId()
    {
        return $this->getData('ks_store_id');
    }

    /**
     * get category product collection by product id
     * @param $ksProductId
     * @return array
     */
    public function getProductCollectionByProductId($ksProductId)
    {
        return $this->ksProductCollectionFactory->create()->load($ksProductId)->setStoreId($this->getKsStoreId());
    }

    /**
     * Get Product Visibility
     * @return array
     */
    public function getKsProductVisibility()
    {
        return $this->ksProductVisibilty->toOptionArray();
    }

    /**
     * Return category status
     *
     * @return boolean
     */
    public function getKsIsCategoryDisabled()
    {
        $ksCategoryCollection = $this->ksSellerCategoryCollection->create()->addFieldToFilter('ks_seller_id', (int)$this->getRequest()->getParam('ks_seller_id'))->addFieldToFilter('ks_category_id', $this->getRequest()->getParam('ks_category_id'))->addFieldToFilter('ks_category_status', 0);

        if ($ksCategoryCollection->getSize() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get current currency symbol
     *
     * @return [string]
     */
    public function getKsCurrentBaseCurrency()
    {
        $ksStoreId = (int)$this->getRequest()->getParam('ks_store_id');
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode)->getCurrencySymbol();
        return $ksCurrency ? $ksCurrency : $ksCurrencyCode;
    }
}
