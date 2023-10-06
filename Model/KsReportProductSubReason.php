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
 * KsReportProductSubReason Model Class
 */
class KsReportProductSubReason extends AbstractModel
{

    /**
     * Sub reason Statuses
     */
    const KS_STATUS_DISABLED     = 0;
    const KS_STATUS_ENABLED     = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsReportProductSubReason');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_report_product_subreason';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_report_product_subreason';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_report_product_subreason';

    /**
     * Prepare reason's statuses.
     *
     * @return array
     */
    public function getKsAvailableStatus()
    {
        return [
          self::KS_STATUS_DISABLED      => __('Disabled'),
          self::KS_STATUS_ENABLED     => __('Enabled')
        ];
    }
}
