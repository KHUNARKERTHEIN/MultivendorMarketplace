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

namespace Ksolves\MultivendorMarketplace\Block\SellerProfile;

use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * KsRecentlyProducts Block class
 */
class KsRecentlyProducts extends \Magento\Catalog\Block\Product\Widget\NewWidget
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $ksStockInventory;

    /**
     * KsRecentlyProducts constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $ksContext
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksProductCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $ksCatalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $ksHttpContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\CatalogInventory\Helper\Stock $ksStockInventory
     * @param array $ksData
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $ksContext,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksProductCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $ksCatalogProductVisibility,
        \Magento\Framework\App\Http\Context $ksHttpContext,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        \Magento\Customer\Model\Session $ksCustomerSession,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\CatalogInventory\Helper\Stock $ksStockInventory,
        array $ksData = []
    ) {
        parent::__construct(
            $ksContext,
            $ksProductCollectionFactory,
            $ksCatalogProductVisibility,
            $ksHttpContext,
            $ksData
        );
        $this->ksProductFactory = $ksProductFactory;
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksStockInventory = $ksStockInventory;
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function _getProductCollection()
    {
        switch ($this->getDisplayType()) {
            case self::DISPLAY_TYPE_NEW_PRODUCTS:
                $collection = parent::_getProductCollection()
                    ->setPageSize($this->getPageSize())
                    ->setCurPage($this->getCurrentPage());
                break;
            default:
                $collection = $this->getKsProductCollection();
                break;
        }
        return $collection;
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getKsProductCollection()
    {
        $ksProductCollection = $this->_productCollectionFactory->create();
        $ksProductCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $ksProductCollection->addAttributeToSelect('*');
        $ksProductCollection->addWebsiteFilter($this->ksStoreManager->getStore()->getWebsiteId());
        $ksProductCollection->addStoreFilter();
        $this->ksStockInventory->addIsInStockFilterToCollection($ksProductCollection);
        $ksProductTable = $ksProductCollection->getTable('ks_product_details');

        $ksProductCollection->getSelect()->join(
            $ksProductTable. ' as ks_pro',
            'e.entity_id = ks_pro.ks_product_id',
            [
                'ks_seller_id' => 'ks_seller_id',
                'ks_created_at'    =>'ks_created_at',
                'ks_product_approval_status' => 'ks_product_approval_status',
            ]
        );

        $ksProductCollection->getSelect()->where('ks_product_approval_status =? ', KsProduct::KS_STATUS_APPROVED)
            ->where('ks_seller_id = ?', $this->getKsSellerId())
            ->where('ks_parent_product_id = ?', 0)
            ->order('ks_created_at desc');

        $ksProductCollection->setPageSize($this->getKsSellerConfigData()->getKsRecentlyProductsCount())->setCurPage($this->getCurrentPage());
        return $ksProductCollection;
    }

    /**
     * Return customer id
     *
     * @return int
     */
    public function getKsSellerId()
    {
        return (int)$this->getRequest()->getParam('seller_id') ? (int)$this->getRequest()->getParam('seller_id') : $this->ksCustomerSession->getCustomer()->getId();
    }

    /**
     * Return seller config data
     *
     * @return string
     */
    public function getKsSellerConfigData()
    {
        return $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
    }
}
