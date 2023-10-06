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
 * KsBenefits
 */
class KsBenefits extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'ks_marketplace_benefits';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_marketplace_benefits';
    
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_marketplace_benefits';

    /**
     * Prepare benefit construct
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsBenefits');
    }
}
