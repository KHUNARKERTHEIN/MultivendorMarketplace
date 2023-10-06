<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ksolves\MultivendorMarketplace\Plugin\Indexer;

use Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule\KsCommissionRuleProcessor;

class KsCategory
{
    /**
     * @var KsCommissionRuleProcessor
     */
    protected $ksCommissionRuleProcessor;

    /**
     * @param KsCommissionRuleProcessor $ksCommissionRuleProcessor
     */
    public function __construct(
        KsCommissionRuleProcessor $ksCommissionRuleProcessor
    ) {
        $this->ksCommissionRuleProcessor = $ksCommissionRuleProcessor;
    }

    /**
     * @param \Magento\Catalog\Model\Category $ksSubject
     * @param \Magento\Catalog\Model\Category $ksResult
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Category $ksSubject,
        \Magento\Catalog\Model\Category $ksResult
    ) {
        /** @var \Magento\Catalog\Model\Category $ksResult */
        $ksProductIds = $ksResult->getChangedProductIds();
        if (!empty($ksProductIds) && !$this->ksCommissionRuleProcessor->isIndexerScheduled()) {
            $this->ksCommissionRuleProcessor->getIndexer()->reindexList($ksProductIds);
        }
        return $ksResult;
    }

    /**
     * @param \Magento\Catalog\Model\Category $ksSubject
     * @param \Magento\Catalog\Model\Category $ksResult
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\Category $ksSubject,
        \Magento\Catalog\Model\Category $ksResult
    ) {
        $this->ksCommissionRuleProcessor->markIndexerAsInvalid();
        return $ksResult;
    }
}
