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

namespace Ksolves\MultivendorMarketplace\Controller\ReportSeller;

use Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory;

/**
 * KsSubReasons Controller class
 */
class KsSubReasons extends \Magento\Framework\App\Action\Action
{
    const KS_STATUS_ENABLED = 1;

    /**
     * @var KsReportSellerReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $ksJsonHelper;

    /**
     * KsSubReasons Constructor
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsReportSellerReasonsFactory $ksReasonFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        KsReportSellerReasonsFactory $ksReasonFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper
    ) {
        $this->ksReasonFactory = $ksReasonFactory;
        $this->ksJsonHelper = $ksJsonHelper;
        parent::__construct($ksContext);
    }

    /**
     * Return sub reasons for the passed reason
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function execute()
    {
        $ksReason = $this->getRequest()->getParams('reason');
        $ksSubReasons = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('ks_reason', $ksReason)->addFieldToFilter("ks_reason_status", self::KS_STATUS_ENABLED)->addFieldToFilter("ks_subreason_status", self::KS_STATUS_ENABLED);
        if ($ksSubReasons->getSize()) {
            $this->getResponse()->representJson($this->ksJsonHelper->jsonEncode($ksSubReasons->getData()));
        }
    }
}
