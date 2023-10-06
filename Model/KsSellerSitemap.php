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
 * KsSellerSitemap Model Class
 */
class KsSellerSitemap extends AbstractModel
{

	protected function _construct()
	{
		$this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap');
	}

	/**
     * @var string
     */
    const CACHE_TAG = 'ks_seller_sitemap';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_seller_sitemap';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_seller_sitemap';
}
