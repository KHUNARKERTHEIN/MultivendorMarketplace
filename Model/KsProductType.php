<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * KsProductType Model Class
 */
class KsProductType extends AbstractModel
{

    /**
     * Product Type Request Status
     */
    const KS_REQUEST_STATUS_UNASSIGNED = 0;
    const KS_REQUEST_STATUS_APPROVED   = 1;
    const KS_REQUEST_STATUS_PENDING    = 2;
    const KS_REQUEST_STATUS_REJECTED   = 3;
    const KS_REQUEST_STATUS_ASSIGNED   = 4;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType');
    }

    const CACHE_TAG = 'ks_seller_product_type_requests';
    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_product_type_requests';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_product_type_requests';

    /**
     * Prepare seller's product type status.
     * @return array
     */
    public function getKsAvailableSellerProductTypeStatus()
    {
        return [
          self::KS_REQUEST_STATUS_PENDING      => __('Pending'),
          self::KS_REQUEST_STATUS_REJECTED     => __('Rejected'),
          self::KS_REQUEST_STATUS_APPROVED     => __('Approved')
        ];
    }
}
