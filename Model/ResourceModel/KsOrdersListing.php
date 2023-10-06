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

namespace Ksolves\MultivendorMarketplace\Model\ResourceModel;

/**
 * KsOrdersListing Model Class
*/
class KsOrdersListing extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{   
    /**
     * Initialize resource model
     *
     * @return void
     *
     * Define main table
    */
    protected function _construct()
    {
        $this->_init('ks_sales_order', 'id');   //here "ks_sales_order" is table name and "id" is the primary key of table
    }
}