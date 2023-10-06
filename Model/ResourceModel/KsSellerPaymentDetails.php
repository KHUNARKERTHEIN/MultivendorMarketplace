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
 * KsSellerPaymentDetails ResourceModel Class
 */
class KsSellerPaymentDetails extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @param Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }
    
    /**
     * Initialize resource model
     *
     * @return void
     *
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ks_seller_payment_details', 'id');   //here "ks_seller_payment_details" is table name and "id" is the primary key of table
    }
}
