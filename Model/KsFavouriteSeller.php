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
 * KsFavouriteSeller Model Class
 */
class KsFavouriteSeller extends AbstractModel
{
    /**
     * New Product Alert Statuses
     */
    const KS_STATUS_NEW_PRODUCT_ALERT_DISABLED = 0;
    const KS_STATUS_NEW_PRODUCT_ALERT_ENABLED  = 1;

    /**
     * Price Change Alert Statuses
     */
    const KS_STATUS_PRICE_CHANGE_DISABLED = 0;
    const KS_STATUS_PRICE_CHANGE_ENABLED  = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_favourite_seller';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_favourite_seller';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_favourite_seller';

    /**
     * Prepare new product alert statuses.
     *
     * @return array
     */
    public function getKsAvailableNewProductAlertStatus()
    {
        return [
          self::KS_STATUS_NEW_PRODUCT_ALERT_DISABLED => __('Disabled'),
          self::KS_STATUS_NEW_PRODUCT_ALERT_ENABLED => __('Enabled')
        ];
    }

    /**
     * Prepare price change alert statuses.
     *
     * @return array
     */
    public function getKsAvailablePriceChangeAlertStatus()
    {
        return [
          self::KS_STATUS_PRICE_CHANGE_DISABLED => __('Disabled'),
          self::KS_STATUS_PRICE_CHANGE_ENABLED => __('Enabled')
        ];
    }
}
