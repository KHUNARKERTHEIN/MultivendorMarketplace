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
namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\AssignProduct;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 *  KsAdminAssignedProductDataProvider DataProvider Class
 */
class KsAdminAssignedProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

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
     * @var \Magento\Framework\App\Request\Http
     */
    private $ksRequest;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
    * @var StoreManagerInterface
    */
    protected $ksStoreManager;


    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param Http $ksRequest
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param DataPersistorInterface $ksDataPersistor
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $ksRequest,
        CollectionFactory $ksCollectionFactory,
        DataPersistorInterface $ksDataPersistor,
        StoreManagerInterface $ksStoreManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection          = $ksCollectionFactory->create();
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->ksStoreManager      = $ksStoreManager;
        $this->modifiersPool       = $modifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);
        $this->ksRequest          = $ksRequest;
        $this->ksDataPersistor    = $ksDataPersistor;
        // get website
        $this->collection->addWebsiteNamesToResult();
        $this->collection->addAttributeToSelect('*');
        $ksStoreId = $ksRequest->getParam('store') ? $ksRequest->getParam('store') : 0;
        $this->collection->setStoreId($ksStoreId);
        //get seller id
        $this->collection->joinField(
            'ks_seller_id',
            'ks_product_details',
            'ks_seller_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get approval status
        $this->collection->joinField(
            'ks_product_approval_status',
            'ks_product_details',
            'ks_product_approval_status',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get parent product id
        $this->collection->joinField(
            'ks_parent_product_id',
            'ks_product_details',
            'ks_parent_product_id',
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

        //get condition
        $this->collection->joinField(
            'ks_product_stage',
            'ks_product_details',
            'ks_product_stage',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //get ks_is_admin_assigned_product
        $this->collection->joinField(
            'ks_is_admin_assigned_product',
            'ks_product_details',
            'ks_is_admin_assigned_product',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        //join rejection reason
        $this->collection->joinField(
            'ks_rejection_reason',
            'ks_product_details',
            'ks_rejection_reason',
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

        // Get Seller Id
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
        // Get Seller Id from DataPersistor
        $ksDataSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        // Terinary Operator to find seller id
        $ksCurrentSellerId = $ksSellerId ? $ksSellerId : $ksDataSellerId;
        $this->collection->addAttributeToFilter('ks_parent_product_id', ['eq' => 0]);
        $this->collection->addFieldToFilter('ks_is_admin_assigned_product', 1);
        $this->collection->addFieldToFilter('ks_seller_id', $ksCurrentSellerId);

        $ksStoreId = $this->ksRequest->getParam('ks_store_id');
        if ($ksStoreId) {
            $ksStore = $this->ksStoreManager->getStore($ksStoreId);
            $this->collection->addStoreFilter($ksStore);
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
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
