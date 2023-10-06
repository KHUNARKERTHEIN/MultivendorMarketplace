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
 * KsOrdersListing Model Class
*/
class KsOrdersListing extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'ks_sales_order';

    /**
     * @var string
    */
    protected $_cacheTag = 'ks_sales_order';

    /**
     * Prefix of model events names.
     *
     * @var string
    */
    protected $_eventPrefix = 'ks_sales_order';

    /**
     * Define resource model
    */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsOrdersListing');
    }

    /**
     * @return array
    */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
    */
    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}