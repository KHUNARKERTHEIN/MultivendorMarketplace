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
namespace Ksolves\MultivendorMarketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class KsAfterSellerStoreChange
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsAfterSellerStoreChange implements ObserverInterface
{
    /**
     * @var IndexerRegistry
     */
    protected $ksIndexerRegistry;

    /**
     * constructor.
     * @param IndexerRegistry $ksFullTextProcessor
     */
    public function __construct(
        IndexerRegistry $ksIndexerRegistry
    ) {
        $this->ksIndexerRegistry  = $ksIndexerRegistry;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        //reindex
        $indexer = $this->ksIndexerRegistry->get('catalogsearch_fulltext');
        $indexer->reindexAll();

        $indexer = $this->ksIndexerRegistry->get('catalog_product_category');
        $indexer->reindexAll();
    }
}
