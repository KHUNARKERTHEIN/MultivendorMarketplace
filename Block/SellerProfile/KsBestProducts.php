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
 * KsBestProducts Block class
 */
class KsBestProducts extends \Ksolves\MultivendorMarketplace\Block\SellerProfile\KsRecentlyProducts
{
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
        $ksRatingTable = $ksProductCollection->getTable('review_entity_summary');
        $ksSalesTable = $ksProductCollection->getTable('ks_sales_order_item');
        $ksProductTable = $ksProductCollection->getTable('ks_product_details');

        $ksProductCollection->getSelect()->join(
            $ksProductTable. ' as ks_pro',
            'e.entity_id = ks_pro.ks_product_id',
            [
                'ks_report_count'    =>'ks_pro.ks_report_count',
                'ks_product_approval_status' => 'ks_pro.ks_product_approval_status',

            ]
        )->where('ks_product_approval_status =? ', KsProduct::KS_STATUS_APPROVED)
        ->where('ks_pro.ks_seller_id = ?', $this->getKsSellerId())
        ->where('ks_parent_product_id = ?', 0)
        ->order('ks_report_count asc');

        $ksProductCollection->getSelect()->joinLeft(
            $ksSalesTable. ' as kso',
            'e.entity_id = kso.ks_product_id',
            array('ks_qty_ordered'=>'SUM(kso.ks_qty_ordered)')
        )->group('e.entity_id')
        ->order('ks_qty_ordered DESC')->where('kso.ks_qty_ordered > 0');

        $ksStoreId = $this->ksStoreManager->getStore()->getId();
        $ksProductCollection->joinField(
            'rating_summary',                // alias
            $ksRatingTable,      // table
            'rating_summary',               // field
            'entity_pk_value=entity_id',    // bind
            array(
                'entity_type' => 1,
                'store_id' => $ksStoreId
            ),                              // conditions
            'left'                          // join type
        );
        $ksProductCollection->getSelect()->order('rating_summary desc');
        $ksProductCollection->joinField(
            'reviews_count',                // alias
            $ksRatingTable,      // table
            'reviews_count',               // field
            'entity_pk_value=entity_id',    // bind
            array(
                'entity_type' => 1,
                'store_id' => $ksStoreId
            ),                              // conditions
            'left'                          // join type
        );
        $ksProductCollection->getSelect()->order('reviews_count desc');

        $ksProductCollection->setPageSize($this->getKsSellerConfigData()->getKsBestProductsCount())->setCurPage($this->getCurrentPage());
        return $ksProductCollection;
    }
}
