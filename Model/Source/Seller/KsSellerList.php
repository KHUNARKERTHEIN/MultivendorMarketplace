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

namespace Ksolves\MultivendorMarketplace\Model\Source\Seller;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsSellerStatusOptions
 */
class KsSellerList implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSeller
     */
    protected $ksSellerModel;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * Constructor
     *
     * @param \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerModel
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerModel,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
    ) {
        $this->ksSellerModel = $ksSellerModel;
        $this->ksCustomerFactory = $ksCustomerFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksSellers = $this->ksSellerModel->create()->getCollection();
        $ksOptions = [];
        $ksOptions[] = [
            'label' => '<-- Select Seller Name -->',
            'value' => null,
        ];
        foreach ($ksSellers as $ksSeller) {
            $ksSellerName = $this->ksCustomerFactory->create()->load($ksSeller->getKsSellerId())->getName();
            $ksOptions[] = [
                'label' => $ksSellerName,
                'value' => $ksSeller->getKsSellerId(),
            ];
        }

        return $ksOptions;
    }
}
