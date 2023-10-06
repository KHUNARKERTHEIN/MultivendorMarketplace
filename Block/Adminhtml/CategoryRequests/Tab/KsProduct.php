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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\CategoryRequests\Tab;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory;

/**
 * KsProduct block class
 */
class KsProduct extends \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
{
    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
    * @var KsSellerProductFactory
    */
    protected $ksSellerProductFactory;

    /**
    * @var KsSellerCategoriesFactory
    */
    protected $ksSellerCategoriesFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @param Visibility|null $visibility
     * @param Status|null $status
     * @param KsSellerProductFactory $ksSellerProductFactory
     * @param KsSellerCategoriesFactory $ksSellerCategoriesFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Backend\Helper\Data $ksBackendHelper,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        KsSellerProductFactory $ksSellerProductFactory,
        KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        array $ksData = [],
        Visibility $visibility = null,
        Status $status = null
    ) {
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        parent::__construct($ksContext, $ksBackendHelper, $ksProductFactory, $ksCoreRegistry, $ksData);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'price'
        )->joinField(
            'position',
            'catalog_category_product',
            'position',
            'product_id=entity_id',
            'category_id=' . (int)$this->getRequest()->getParam('id', 0),
            'left'
        );

        if ($this->getCategory()->getId()) {
            if ((int) $this->getCategory()->getKsIncludeInMarketplace()==0) {
                $collection->joinField(
                    'id',
                    'ks_product_details',
                    'id',
                    'ks_product_id=entity_id',
                    [],
                    'left'
                );
                $collection->addFieldToFilter('id', array('null' => true));
            } else {
                $ksSellerProductCollection = $this->ksSellerProductFactory
                ->create()
                ->getCollection()
                ->distinct(true)
                ->addFieldToSelect('ks_seller_id')
                ->addFieldToFilter('ks_seller_id', array('neq' => 'NULL'));

                $ksProductIds =[];
                foreach ($ksSellerProductCollection as $ksSellerProduct) {
                    $ksSellerId = $ksSellerProduct['ks_seller_id'];

                    $ksCatCollection = $this->ksSellerCategoriesFactory->create()
                                        ->getCollection()
                                        ->addFieldToFilter('ks_seller_id', $ksSellerId)
                                        ->addFieldToFilter('ks_category_id', $this->getCategory()->getId())
                                        ->addFieldToFilter('ks_category_status',\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);

                    if ($ksCatCollection->getSize()==0) {
                        $ksSellerProductCol = $this->ksSellerProductFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToSelect('ks_product_id')
                        ->addFieldToFilter('ks_seller_id', $ksSellerId);

                        foreach ($ksSellerProductCol as $ksProduct) {
                            $ksProductIds[]= $ksProduct->getKsProductId();
                        }
                    }
                }
                if(sizeof($ksProductIds) > 0){
                    $collection->addAttributeToFilter('entity_id', array('nin'=>$ksProductIds));
                }
            }
        }


        $ksStoreId = (int)$this->getRequest()->getParam('store', 0);
        if ($ksStoreId > 0) {
            $collection->addStoreFilter($ksStoreId);
        }
        $this->setCollection($collection);

        if ($this->getCategory()->getProductsReadonly()) {
            $ksProductIds = $this->_getSelectedProducts();
            if (empty($ksProductIds)) {
                $ksProductIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $ksProductIds]);
        }

        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }
}