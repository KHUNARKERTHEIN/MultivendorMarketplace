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
namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\PriceComparison\Listing;

use Magento\Framework\App\Request\Http;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * KsProductPriceComparisonDataProvider Ui Class
 */
class KsProductPriceComparisonDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product collection
     *
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\App\RequestInterface,
     */
    private $ksRequest;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param RequestInterface $ksRequest
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        Http $ksRequest,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->ksRequest = $ksRequest;
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->modifiersPool       = $modifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);

        // Get Collection
        $this->collection          = $ksCollectionFactory->create();
        // get website
        $this->collection->addWebsiteNamesToResult();
        // Get all values for Collection
        $this->collection->addAttributeToSelect('*');

        //get seller id
        $this->collection->joinField(
            'ks_seller_id',
            'ks_product_details',
            'ks_seller_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get parent product ID
        $this->collection->joinField(
            'ks_parent_product_id',
            'ks_product_details',
            'ks_parent_product_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get seller id
        $this->collection->joinField(
            'ks_product_stage',
            'ks_product_details',
            'ks_product_stage',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get id
        $this->collection->joinField(
            'id',
            'ks_product_details',
            'id',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get qty
        $this->collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            [],
            'left'
        );

        $ksParentProductId = $this->ksRequest->getParam('product_id');
        if ($ksParentProductId) {
            $this->collection->addFieldToFilter('ks_parent_product_id', $ksParentProductId);
        } else {
            $this->collection->addFieldToFilter('ks_parent_product_id', '-1');
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        $data = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];

        /** @var ModifierInterface $modifier */
        foreach ($this->modifiersPool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        
        /** @var ModifierInterface $modifier */
        foreach ($this->modifiersPool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }
        
        return $meta;
    }
}
