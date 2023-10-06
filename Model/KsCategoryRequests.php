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
 * KsCategoryRequests
 */
class KsCategoryRequests extends AbstractModel
{
    /**
     * Category Statuses
     */
    const KS_STATUS_PENDING     = 0;
    const KS_STATUS_APPROVED    = 1;
    const KS_STATUS_REJECTED    = 2;
    const KS_STATUS_ASSIGNED    = 3;
    const KS_STATUS_UNASSIGNED  = 4;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests');
    }

    const CACHE_TAG = 'ks_category_requests';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'ks_category_requests';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_category_requests';

    /**
     * Prepare category's statuses.
     * Available event multivendor available statuses to customize statuses.
     *
     * @return array
     */
    public function getKsAvailableCategoryStatus()
    {
        return [
          self::KS_STATUS_PENDING      => __('Pending'),
          self::KS_STATUS_REJECTED     => __('Rejected')
        ];
    }
}
