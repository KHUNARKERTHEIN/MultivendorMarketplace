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
 * KsFavouriteSellerEmail Model Class
 */
class KsFavouriteSellerEmail extends AbstractModel
{
    /**
     * Email Statuses
     */
    const KS_STATUS_PENDING = 0;
    const KS_STATUS_SENT = 1;

    /**
     * Product States
     */
    const KS_NEW_PRODUCT = 0;
    const KS_EDIT_PRODUCT = 1;

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSellerEmail');
    }

    /**
     * @var string
     */
    const CACHE_TAG = 'ks_favourite_seller_email';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_favourite_seller_email';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_favourite_seller_email';
}
