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

namespace Ksolves\MultivendorMarketplace\Model\Source\ProductAttribute;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * KsProductAttributeStatusOptions Model Class
 */
class KsProductAttributeStatusOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductAttribute
     */
    protected $ksProductAttributeModel;

    /**
     * Constructor
     *
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductAttribute $ksProductAttributeModel
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsProductAttribute $ksProductAttributeModel
    ) {
        $this->ksProductAttributeModel = $ksProductAttributeModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksAvailableStatus = $this->ksProductAttributeModel->getKsAvailableProductAttributeStatus();
        $ksOptions = [];
        foreach ($ksAvailableStatus as $key => $value) {
            $ksOptions[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $ksOptions;
    }
}
