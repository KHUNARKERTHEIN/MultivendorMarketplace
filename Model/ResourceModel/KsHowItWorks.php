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
 * KsHowItWorks ResourceModel Class
 */
class KsHowItWorks extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('ks_marketplace_howitworks', 'id'); //here "ks_marketplace_howitworks" is table name and "id" is the primary key of custom table
    }
}
