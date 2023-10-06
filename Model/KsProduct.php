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
 * KsProduct Model Class
 */
class KsProduct extends AbstractModel
{

    /**
     * Product Statuses
     */
    const KS_STATUS_PENDING           = 0;
    const KS_STATUS_APPROVED          = 1;
    const KS_STATUS_REJECTED          = 2;
    const KS_STATUS_PENDING_UPDATE    = 3;
    const KS_STATUS_NOT_SUBMITTED     = 4;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_product_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_product_details';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_product_details';

    /**
     * Prepare product's statuses.
     *
     * @return array
     */
    public function getKsAvailableProductStatus()
    {
        return [
          self::KS_STATUS_PENDING            => __('Pending New'),
          self::KS_STATUS_APPROVED           => __('Approved'),
          self::KS_STATUS_REJECTED           => __('Rejected'),
          self::KS_STATUS_PENDING_UPDATE     => __('Pending Update'),
          self::KS_STATUS_NOT_SUBMITTED      => __('Not Submitted')
        ];
    }
}
