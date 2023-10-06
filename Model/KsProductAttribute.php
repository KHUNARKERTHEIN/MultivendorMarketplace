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
 * KsProductAttribute Model Class
 */
class KsProductAttribute
{

    /**
     * Product Attribute Statuses
     */
    const KS_STATUS_PENDING           = 0;
    const KS_STATUS_APPROVED          = 1;
    const KS_STATUS_REJECTED          = 2;
    const KS_STATUS_PENDING_UPDATE    = 3;
    const KS_STATUS_NOT_SUBMITTED     = 4;

    /**
     * Prepare Product Attribute's statuses.
     *
     * @return array
     */
    public function getKsAvailableProductAttributeStatus()
    {
        return [
          self::KS_STATUS_PENDING            => __('Pending New'),
          self::KS_STATUS_REJECTED           => __('Rejected'),
          self::KS_STATUS_PENDING_UPDATE     => __('Pending Update'),
          self::KS_STATUS_APPROVED           => __('Approved')
        ];
    }
}
