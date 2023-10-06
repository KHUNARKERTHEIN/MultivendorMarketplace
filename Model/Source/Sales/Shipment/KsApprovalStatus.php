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

namespace Ksolves\MultivendorMarketplace\Model\Source\Sales\Shipment;

use Magento\Framework\Option\ArrayInterface;

/**
 * KsApprovalStatus Model Class
 */
class KsApprovalStatus implements ArrayInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesShipment
     */
    protected $ksSalesShipment;

    /**
     * KsApprovalStatus constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesShipment $ksSalesShipment
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsSalesShipment $ksSalesShipment
    ) {
        $this->ksSalesShipment = $ksSalesShipment;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $ksResult = [];
        foreach ($this->ksSalesShipment->getKsApprovalStatuses() as $value => $label) {
            $ksResult[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $ksResult;
    }
}
