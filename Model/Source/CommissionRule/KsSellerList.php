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

namespace Ksolves\MultivendorMarketplace\Model\Source\CommissionRule;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\OptionSourceInterface;
use \Ksolves\MultivendorMarketplace\Model\KsSellerFactory;

/**
 * KsSellerList Model class
 */
class KsSellerList implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @param KsSellerFactory $ksSellerFactory
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     */
    public function __construct(
        KsSellerFactory $ksSellerFactory,
        \Magento\Framework\App\ResourceConnection $ksResource
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksResource = $ksResource;
    }

    /**
     * Get seller options
     *
     * @return array
     */
    public function getOptionArray()
    {
        $ksSellerCollection = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_status', 1)->addOrder('ks_seller_email', 'ASC');

        $ksConnection  = $this->ksResource->getConnection();
        $ksCustomerGridFlat = $this->ksResource->getTableName('customer_grid_flat');
        
        // join with customer_grid_flat table
        $ksSellerCollection->getSelect()->join(
            $ksCustomerGridFlat.' as cgf',
            'main_table.ks_seller_id = cgf.entity_id',
            [
                'ks_seller_email' => 'email',
            ]
        );
       
        $ksOptions = [];
        $ksOptions[''] = __("Please Select");
        foreach ($ksSellerCollection as $ksSeller) {
            $ksOptions[$ksSeller->getKsSellerId()] = $ksSeller->getKsSellerEmail();
        }
        return $ksOptions;
    }

    /**
     * Get seller options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksOptions = [];
        foreach (self::getOptionArray() as $index => $value) {
            $ksOptions[] = ['value' => $index, 'label' => $value];
        }
        return $ksOptions;
    }
}
