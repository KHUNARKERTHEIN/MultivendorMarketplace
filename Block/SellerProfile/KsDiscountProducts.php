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
 * KsDiscountProducts Block class
*/
class KsDiscountProducts extends \Ksolves\MultivendorMarketplace\Block\SellerProfile\KsRecentlyProducts
{
    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getKsProductCollection()
    {
        $ksCollection = $this->_productCollectionFactory->create();
        $ksCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $ksCollection->addAttributeToSelect('*');
        $ksCollection->addWebsiteFilter($this->ksStoreManager->getStore()->getWebsiteId());
        $ksCollection->addStoreFilter();
        $ksCollection->addFinalPrice();
        $this->ksStockInventory->addIsInStockFilterToCollection($ksCollection);

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
            ->where('ks_seller_id = ?', $this->getKsSellerId())
            ->where('ks_parent_product_id = ?', 0);


        $ksCollection->getSelect()->where('price > final_price')->where('price > 0');

        $ksCollection->getSelect()->columns(
            array(
                'discount' => new \Zend_Db_Expr('((price - final_price)/price)*100')
            )
        );

        $ksCollection->getSelect()
            ->limit($this->getKsSellerConfigData()->getKsDiscountProductsCount())
            ->order('discount desc');

        //return collection
        return $ksCollection;
    }
}
