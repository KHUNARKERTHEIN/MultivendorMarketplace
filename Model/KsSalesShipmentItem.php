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

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * KsSalesShipmentItem Model Class
 */
class KsSalesShipmentItem extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipmentItem');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_shipment_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_shipment_item';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_shipment_item';

    /**
     * Set Order Details
     *
     * @param Object $ksOrder
     * @param Int    $ksSellerId
     */
    public function setKsShipmentItem($ksShipmentItem,$ksParentId)
    {
        $ksData =[
            'ks_parent_id' => $ksParentId,
            'ks_row_total' => $ksShipmentItem->getRowTotal(),
            'ks_price' => $ksShipmentItem->getPrice(),
            'ks_weight' => $ksShipmentItem->getWeight(),
            'ks_qty' => $ksShipmentItem->getQty(),
            'ks_product_id' => $ksShipmentItem->getProductId(),
            'ks_order_item_id' => $ksShipmentItem->getOrderItemId(),
            'ks_additional_data' => $ksShipmentItem->getAdditionalData(),
            'ks_description' => $ksShipmentItem->getDescription(),
            'ks_name' => $ksShipmentItem->getName(),
            'ks_sku' => $ksShipmentItem->getSku()
        ];
        $this->setData($ksData)->save();
    }
}