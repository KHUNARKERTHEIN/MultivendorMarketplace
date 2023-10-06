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

namespace Ksolves\MultivendorMarketplace\Plugin\Indexer\Product;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule\KsCommissionRuleProcessor;

class KsAction
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
     * @param ProductAction $object
     * @param ProductAction $ksResult
     * @return ProductAction
     *
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateAttributes(ProductAction $object, ProductAction $ksResult)
    {
        $this->ksCommissionRuleProcessor->getIndexer()->reindexList($ksResult->getProductIds());
        return $ksResult;
    }
}
