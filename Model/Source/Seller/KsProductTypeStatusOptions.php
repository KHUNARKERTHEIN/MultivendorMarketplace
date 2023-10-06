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
use Ksolves\MultivendorMarketplace\Model\KsProductType;

/**
 * KsProductTypeStatusOptions Model Class
 */
class KsProductTypeStatusOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductType
     */
    protected $ksProductTypeModel;

    /**
     * Constructor
     *
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductType $ksProductTypeModel
     */
    public function __construct(
        KsProductType $ksProductTypeModel
    ) {
        $this->ksProductTypeModel = $ksProductTypeModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksProductTypeAvailableStatus = $this->ksProductTypeModel->getKsAvailableSellerProductTypeStatus();
        $ksOptions = [];
        foreach ($ksProductTypeAvailableStatus as $key => $value) {
            $ksOptions[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $ksOptions;
    }
}
