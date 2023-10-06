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
 * KsBanners
 */
class KsBanners extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'ks_seller_profile_banners';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_profile_banners';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_profile_banners';
    
    /**
     * Prepare banner construct
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBanners');
    }
}
