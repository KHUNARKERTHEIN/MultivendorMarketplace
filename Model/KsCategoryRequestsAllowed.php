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
 * KsCategoryRequestsAllowed
 */
class KsCategoryRequestsAllowed extends AbstractModel
{
    public const KS_STATUS_DISABLED = 0;
    public const KS_STATUS_ENABLED  = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed');
    }

    public const CACHE_TAG = 'ks_category_requests_allowed';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_category_requests_allowed';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_category_requests_allowed';
}
