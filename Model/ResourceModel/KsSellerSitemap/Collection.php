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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap;

/**
 * Collection of KsSellerSitemap
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
   
    protected function _construct()
    {
        $this->_init(
            'Ksolves\MultivendorMarketplace\Model\KsSellerSitemap',
            'Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerSitemap'
        );
    }
}
