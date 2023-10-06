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

/*
 * KsSellerGroup Model
 * */
class KsSellerGroup extends \Magento\Framework\Model\AbstractModel
{
    const KS_DEFAULT_GROUP_ID = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerGroup');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_seller_group';
    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_group';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_group';
}
