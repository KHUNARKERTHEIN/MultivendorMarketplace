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
 * KsSellerStore Model Class
 */
class KsSellerStore extends AbstractModel
{
    /**
     * Seller Store Statuses
     */
    const KS_STATUS_DISABLED     = 0;
    const KS_STATUS_ENABLED      = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_seller_store_details';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_store_details';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_store_details';

    /**
     * Prepare seller store's statuses.
     *
     * @return array
     */
    public function getKsAvailableStoreStatus()
    {
        return [
          self::KS_STATUS_DISABLED    => __('Disabled'),
          self::KS_STATUS_ENABLED     => __('Enabled')
        ];
    }
}
