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
 * KsReportSellerReasons Model Class
 */
class KsReportSellerReasons extends AbstractModel
{

    /**
     * Reason Statuses
     */
    const KS_STATUS_DISABLED     = 0;
    const KS_STATUS_ENABLED     = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsReportSellerReasons');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_report_seller_reasons';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_report_seller_reasons';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_report_seller_reasons';

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
