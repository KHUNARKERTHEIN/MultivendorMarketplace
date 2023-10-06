<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * A Select object processor.
 *
 * Adds seller product limitations to a given Select object.
 */
class KsSellerProductBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var ResourceConnection
     */
    private $ksResource;

    /**
     * @param ResourceConnection $ksResource
     */
    public function __construct(
        ResourceConnection $ksResource
    ) {
        $this->ksResource = $ksResource;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Select $select)
    {
        $ksSellerProductTable = $this->ksResource->getTableName('ks_product_details');
        $ksSellerTable = $this->ksResource->getTableName('ks_seller_details');

        $select->joinLeft(
            ['ks_cgf' => $ksSellerProductTable],
            sprintf('ks_cgf.ks_product_id = %s.entity_id', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
            [
                'ks_product_approval_status'=>'ks_product_approval_status'
            ]
        )->where('ks_product_approval_status= '.KsProduct::KS_STATUS_APPROVED.' 
        or ks_product_approval_status IS NULL');

        $select->joinLeft(
            ['ks_seller' => $ksSellerTable],
            ('ks_seller.ks_seller_id = ks_cgf.ks_seller_id'),
            [
                'ks_store_status'=>'ks_store_status'
            ]
        )->where('ks_seller.ks_store_status= 1
          or ks_seller.ks_store_status IS NULL');

        return $select;
    }
}
