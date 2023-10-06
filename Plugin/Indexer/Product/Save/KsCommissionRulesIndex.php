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

namespace Ksolves\MultivendorMarketplace\Plugin\Indexer\Product\Save;

use Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule\KsCommissionRuleProcessor;

class KsCommissionRulesIndex
{
    /**
    * @var KsCommissionRuleProcessor
    */
    protected $ksCommissionRuleProcessor;

    /**
     * @param KsCommissionRuleProcessor $ksCommissionRuleProcessor
     */
    public function __construct(KsCommissionRuleProcessor $ksCommissionRuleProcessor)
    {
        $this->ksCommissionRuleProcessor = $ksCommissionRuleProcessor;
    }

    /**
     * Apply commission rules after product resource model save
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $ksSubject
     * @param \Magento\Catalog\Model\ResourceModel\Product $ksProductResource
     * @param \Magento\Framework\Model\AbstractModel $ksProduct
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $ksSubject,
        \Magento\Catalog\Model\ResourceModel\Product $ksProductResource,
        \Magento\Framework\Model\AbstractModel $ksProduct
    ) {
        if (!$ksProduct->getIsMassupdate()) {
            $this->ksCommissionRuleProcessor->getIndexer()->reindexRow($ksProduct->getId());
        }
        return $ksProductResource;
    }
}
