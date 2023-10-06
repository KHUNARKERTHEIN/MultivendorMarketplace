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
 * KsSeller Model Class
 */
class KsSeller extends AbstractModel
{

    /**
     * Seller Statuses
     */
    const KS_STATUS_PENDING     = 0;
    const KS_STATUS_APPROVED    = 1;
    const KS_STATUS_REJECTED    = 2;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_seller_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_details';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_details';

    /**
     * Prepare seller's statuses.
     *
     * @return array
     */
    public function getKsAvailableSellerStatus()
    {
        return [
          self::KS_STATUS_PENDING      => __('Pending'),
          self::KS_STATUS_APPROVED     => __('Approved'),
          self::KS_STATUS_REJECTED     => __('Rejected')
        ];
    }
}
