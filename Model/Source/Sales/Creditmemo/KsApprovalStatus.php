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

namespace Ksolves\MultivendorMarketplace\Model\Source\Sales\Creditmemo;

use Magento\Framework\Option\ArrayInterface;

/**
 * KsApprovalStatus Model Class
 */
class KsApprovalStatus implements ArrayInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo
     */
    protected $ksSalesCreditmemo;

    /**
     * KsApprovalStatus constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo $ksSalesCreditmemo
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsSalesCreditMemo $ksSalesCreditmemo
    ) {
        $this->ksSalesCreditmemo = $ksSalesCreditmemo;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $ksResult = [];
        foreach ($this->ksSalesCreditmemo->getKsApprovalStatuses() as $value => $label) {
            $ksResult[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $ksResult;
    }
}
