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

namespace Ksolves\MultivendorMarketplace\Plugin\Product\Configurable\Pricing;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Class KsPriceSelectProcessor
 * @package Ksolves\MultivendorMarketplace\Model\Plugin
 */
class KsPriceSelectProcessor implements BaseSelectProcessorInterface
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
     * @param Collection $ksSubject
     * @param ksBundleSelection $ksBundleSelection
     * @return ksResult
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(Select $select)
    {
        $ksSellerProductTable = $this->ksResource->getTableName('ks_product_details');
        $ksSellerTable = $this->ksResource->getTableName('ks_seller_details');

        $select->joinLeft(
            ['ks_cgf' => $ksSellerProductTable],
            'ks_cgf.ks_product_id = l.product_id',
            []
        )->where('ks_product_approval_status= '.KsProduct::KS_STATUS_APPROVED.' 
        or ks_product_approval_status IS NULL');

        return $select;
    }
}
