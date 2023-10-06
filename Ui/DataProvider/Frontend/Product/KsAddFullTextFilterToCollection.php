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
namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\Product;

use Magento\CatalogSearch\Model\ResourceModel\Search\Collection as SearchCollection;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Class KsAddFullTextFilterToCollection
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend\Product
 */
class KsAddFullTextFilterToCollection implements AddFilterToCollectionInterface
{

    /**
     * Search Collection
     *
     * @var SearchCollection
     */
    private $searchCollection;

    /**
     * @param SearchCollection $searchCollection
     */
    public function __construct(SearchCollection $searchCollection)
    {
        $this->searchCollection = $searchCollection;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        if (isset($condition['fulltext']) && (string)$condition['fulltext'] !== '') {
            $this->searchCollection->setFlag('has_stock_status_filter', true);
            $this->searchCollection->addBackendSearchFilter($condition['fulltext']);
            $productIds = $this->searchCollection->load()->getAllIds();
            if (empty($productIds)) {
                //add dummy id to prevent returning full unfiltered collection
                $productIds = -1;
            }
            $collection->addIdFilter($productIds);
        }
    }
}
