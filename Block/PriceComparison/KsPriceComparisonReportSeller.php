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

namespace Ksolves\MultivendorMarketplace\Block\PriceComparison;

use Magento\Framework\View\Element\Template;
use Ksolves\MultivendorMarketplace\Block\Report\KsReportSeller;

/**
 * KsPriceComparisonReportSeller Block class
 */
class KsPriceComparisonReportSeller extends Template
{
    /**
     * @var Ksolves\MultivendorMarketplace\Block\Report\KsReportSeller
     */
    protected $ksReportSellerBlock;
 
    /**
     * KsPriceComparison constructor.
     * @param Template\Context $ksContext
     * @param KsReportSeller $ksReportSellerBlock
     * @param array $data
     */
    public function __construct(
        Template\Context $ksContext,
        KsReportSeller $ksReportSellerBlock,
        array $data = []
    ) {
        $this->ksReportSellerBlock = $ksReportSellerBlock;
        parent::__construct($ksContext, $data);
    }

    /**
     * @return Ksolves\MultivendorMarketplace\Block\Report\KsReportSeller
     */
    public function getKsReportBlock()
    {
        return $this->ksReportSellerBlock;
    }
}
