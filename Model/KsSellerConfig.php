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

/**
 * KsSellerConfig
 */
class KsSellerConfig extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Category Statuses
     */
    const KS_STATUS_DISABLED = 0;
    const KS_STATUS_ENABLED  = 1;
  
    const CACHE_TAG = 'ks_seller_config_data';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_config_data';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_config_data';
    
    /**
     * Prepare seller config construct
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerConfig');
    }
}
