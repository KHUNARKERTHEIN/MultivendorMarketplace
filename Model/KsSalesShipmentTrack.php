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
 * KsSalesShipmentTrack Model Class
 */
class KsSalesShipmentTrack extends AbstractModel
{
	/**
     * Invoice Approval Statuses
     */
    const KS_STATUS_PENDING     = 0;
    const KS_STATUS_APPROVED    = 1;
    const KS_STATUS_REJECTED    = 2;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSalesShipmentTrack');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_sales_shipment_track';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_sales_shipment_track';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_sales_shipment_track';

    /**
     * Set Track item
     *
     * @param Object $ksShipment
     * @param Int    $ksParentId
     * @param Int    $ksTrackId
     */
    public function setKsTrackingDetails($ksTrackingItem,$ksParentId,$ksTrackId=0)
    {
        $ksData = [
            'ks_parent_id' => $ksParentId,
            'ks_tracking_id' => $ksTrackId,
            'ks_weight' => $ksTrackingItem->getWeight(),
            'ks_qty' => $ksTrackingItem->getQty(),
            'ks_order_id' => $ksTrackingItem->getOrderId(),
            'ks_track_number' => $ksTrackingItem->getTrackNumber(),
            'ks_description' => $ksTrackingItem->getDescription(),
            'ks_title' => $ksTrackingItem->getTitle(),
            'ks_carrier_code' => $ksTrackingItem->getCarrierCode()
        ];
        return $this->setData($ksData)->save();
    }

    /**
     * Get Tracking items
     *
     * @param Int    $ksOrderId
     * @param Int    $ksSellerId
     */
    public function getKsTrackingDetails($ksOrderId,$ksSellerId=0)
    {
        return $this->getCollection()->addFieldToFilter('ks_order_id',$ksOrderId);
        
    }
}