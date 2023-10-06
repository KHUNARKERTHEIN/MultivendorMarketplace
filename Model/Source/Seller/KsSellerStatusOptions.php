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
class KsSellerStatusOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSeller
     */
    protected $ksSellerModel;

    /**
     * Constructor
     *
     * @param \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerModel
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerModel
    ) {
        $this->ksSellerModel = $ksSellerModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksAvailableStatus = $this->ksSellerModel->getKsAvailableSellerStatus();
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
