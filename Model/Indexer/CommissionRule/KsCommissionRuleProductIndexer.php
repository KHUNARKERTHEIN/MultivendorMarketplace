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

namespace Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;

class KsCommissionRuleProductIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @var IndexBuilder
     */
    protected $ksIndexBuilder;

    /**
     * @param IndexBuilder $ksIndexBuilder
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Model\Indexer\IndexBuilder $ksIndexBuilder
    ) {
        $this->ksIndexBuilder = $ksIndexBuilder;
    }
    /*
    * Used by mview, allows process indexer in the "Update on schedule" mode
    */
    public function execute($ids)
    {
        $this->ksIndexBuilder->reindexByIds($ids);
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        $this->ksIndexBuilder->reindexFull();
    }

    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids)
    {
        $this->ksIndexBuilder->reindexByIds($ids);
    }

    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id)
    {
        $this->ksIndexBuilder->reindexById($id);
    }
}
