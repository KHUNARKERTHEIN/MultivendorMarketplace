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
 * KsCustomerListing Model Class
 */
class KsCustomerListing extends AbstractModel
{

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCustomerListing');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'customer_grid_flat';

    /**
     * @var string
     */
    protected $_cacheTag = 'customer_grid_flat';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'customer_grid_flat';
}
