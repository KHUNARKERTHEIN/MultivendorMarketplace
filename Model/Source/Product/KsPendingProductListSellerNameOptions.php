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
 * Class KsPendingProductListSellerNameOptions
 * @package Ksolves\MultivendorMarketplace\Model\Source\Product
 */
class KsPendingProductListSellerNameOptions implements OptionSourceInterface
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
     * KsPendingProductListSellerNameOptions constructor.
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
        $ksCustomerModel=$this->ksCustomerModel->create();
        $ksOptions = [];
        foreach ($ksProductCollection as $ksData) {
            if ($ksData->getKsApprovalStatus()==0) {
                $ksOptions[] = [
                    'label' => $ksCustomerModel->load($ksData->getKsSellerId())->getName(),
                    'value' => $ksData->getKsSellerId(),
                ];
            }
        }
        $ksOptions = array_map("unserialize", array_unique(array_map("serialize", $ksOptions)));
        return $ksOptions;
    }
}
