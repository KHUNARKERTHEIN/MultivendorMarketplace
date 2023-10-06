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

namespace Ksolves\MultivendorMarketplace\Model\Source\CategoryRequests;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KsRequestStatusOptions
 */
class KsRequestStatusOptions implements OptionSourceInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests
     */
    protected $ksCategoryRequestsModel;

    /**
     * Constructor
     *
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests $ksCategoryRequestsModel
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests $ksCategoryRequestsModel
    ) {
        $this->ksCategoryRequestsModel = $ksCategoryRequestsModel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $ksAvailableStatus = $this->ksCategoryRequestsModel->getKsAvailableCategoryStatus();
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
