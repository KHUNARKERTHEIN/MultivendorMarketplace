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

namespace Ksolves\MultivendorMarketplace\Model\Source\Sales\Invoice;

use Magento\Framework\Option\ArrayInterface;

/**
 * KsApprovalStatus Model Class
 */
class KsApprovalStatus implements ArrayInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice
     */
    protected $ksSalesInvoice;

    /**
     * KsApprovalStatus constructor.
     * @param \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\KsSalesInvoice $ksSalesInvoice
    ) {
        $this->ksSalesInvoice = $ksSalesInvoice;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $ksResult = [];
        foreach ($this->ksSalesInvoice->getKsApprovalStatuses() as $value => $label) {
            $ksResult[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $ksResult;
    }
}
