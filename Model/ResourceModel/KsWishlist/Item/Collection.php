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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsWishlist\Item;

use Magento\Wishlist\Model\ResourceModel\Item\Collection as KsWishlistCollection;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Collection Class
 */
class Collection extends KsWishlistCollection
{
    /**
     * @inheritdoc
     * @since 101.1.3
     */
    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        $this->getSelect()->joinLeft(
            ['seller_product' => $this->getTable('ks_product_details')],
            'main_table.product_id = seller_product.ks_product_id',
            ['ks_product_approval_status']
        );

        $this->getSelect()->joinLeft(
            ['ks_seller' => $this->getTable('ks_seller_details')],
            'seller_product.ks_seller_id = ks_seller.ks_seller_id',
            ['ks_store_status']
        );


        $this->getSelect()->where('seller_product.ks_product_approval_status = ? OR seller_product.ks_product_approval_status IS NULL', KsProduct::KS_STATUS_APPROVED);

        $this->getSelect()->where('ks_seller.ks_store_status = ? OR ks_seller.ks_store_status IS NULL', 1);
    }
}
