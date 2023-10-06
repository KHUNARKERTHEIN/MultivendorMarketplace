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

namespace Ksolves\MultivendorMarketplace\Plugin\Product;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Framework\App\ResourceConnection;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Class KsSellerApprovedProductFilter
 */
class KsSellerApprovedProductFilter
{
    /**
     * @var ResourceConnection
     */
    private $ksResourceConnection;

    /**
     * @param ResourceConnection $ksResourceConnection
     */
    public function __construct(
        ResourceConnection $ksResourceConnection
    ) {
        $this->ksResourceConnection = $ksResourceConnection;
    }

    /**
     * Filter out seller product approved.
     *
     * @param DataProvider $dataProvider
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePrepareProductIndex(
        DataProvider $dataProvider,
        $indexData,
        $productData,
        $storeId
    ) {
        $ksProductIds = array_keys($indexData);
        $connection = $this->ksResourceConnection->getConnection();

        $ksSelect = $connection->select();
        $ksSelect->from(
            ['product' => $this->ksResourceConnection->getTableName('catalog_product_entity')],
            ['entity_id']
        );
        $ksSelect->joinInner(
            ['seller_product' => $this->ksResourceConnection->getTableName('ks_product_details')],
            'product.entity_id = seller_product.ks_product_id',
            ['ks_product_approval_status']
        );

        $ksSelect->joinInner(
            ['ks_seller' => $this->ksResourceConnection->getTableName('ks_seller_details')],
            'seller_product.ks_seller_id = ks_seller.ks_seller_id',
            ['ks_store_status']
        );

        $ksSelect->where('product.entity_id IN (?)', $ksProductIds);
        $ksSelect->where('seller_product.ks_product_approval_status != ? OR seller_product.ks_parent_product_id !=0 OR ks_seller.ks_store_status != 1', KsProduct::KS_STATUS_APPROVED);


        $sellerProductStatuses = $connection->fetchAssoc($ksSelect);
        $indexData = array_diff_key($indexData, $sellerProductStatuses);

        return [
            $indexData,
            $productData,
            $storeId,
        ];
    }
}
