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

namespace Ksolves\MultivendorMarketplace\Model\Source\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsProductListSellerNameOptions
 * @package Ksolves\MultivendorMarketplace\Model\Source\Product
 */
class KsProductListSellerNameOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProduct
     */
    protected $ksProductModel;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerModel;

    /**
     * KsProductListSellerNameOptions constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\KsProduct $ksProductModel
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerModel
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsProduct $ksProductModel,
        \Magento\Customer\Model\CustomerFactory $ksCustomerModel
    ) {
        $this->ksCustomerModel=$ksCustomerModel;
        $this->ksProductModel = $ksProductModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksProductCollection = $this->ksProductModel->getCollection();
        $ksOptions = [];
        $ksSellerId=[];
        foreach ($ksProductCollection as $ksData) {
            if (!in_array($ksData->getKsSellerId(), $ksSellerId)) {
                array_push($ksSellerId, $ksData->getKsSellerId());
                $ksCustomer = $this->ksCustomerModel->create()->load($ksData->getKsSellerId());
                $ksOptions[] = [
                    'label' => $ksCustomer->getName(),
                    'value' => $ksData->getKsSellerId(),
                ];
            }
        }
        return $ksOptions;
    }
}
