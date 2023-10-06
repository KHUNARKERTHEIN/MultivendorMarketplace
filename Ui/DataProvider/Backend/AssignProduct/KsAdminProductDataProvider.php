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
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class KsAdminProductDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend
 */
class KsAdminProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\ksDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\ksSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var PoolInterface
     */
    private $modifiersPool;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
    * @var StoreManagerInterface
    */
    protected $ksStoreManager;

    /**
     * @var Request
     */
    protected $ksRequest;

    /**
     * @var Seller Id
     */
    protected $ksSellerId;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param KsProductHelper $ksProductHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param RequestInterface $ksRequest
     * @param StoreManagerInterface $ksStoreManager
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
        KsProductHelper $ksProductHelper,
        KsDataHelper $ksDataHelper,
        KsSellerHelper $ksSellerHelper,
        DataPersistorInterface $ksDataPersistor,
        RequestInterface $ksRequest,
        StoreManagerInterface $ksStoreManager,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = [],
        PoolInterface $modifiersPool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection          = $ksCollectionFactory->create();
        $this->ksProductHelper     = $ksProductHelper;
        $this->ksDataHelper        = $ksDataHelper;
        $this->ksSellerHelper      = $ksSellerHelper;
        $this->ksDataPersistor     = $ksDataPersistor;
        $this->ksRequest           = $ksRequest;
        $this->ksStoreManager      = $ksStoreManager;
        $this->addFieldStrategies  = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->modifiersPool       = $modifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);

        // get website
        $this->collection->addWebsiteNamesToResult();

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

        //get ks_email id
        $this->collection->joinField(
            'ks_email',
            'ks_product_details',
            'ks_email',
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

        //get category
        $this->collection->joinField(
            'category_id',
            'catalog_category_product',
            'category_id',
            'product_id=entity_id',
            [],
            'left'
        );
        // Get Seller Id from DataPersistor
        $ksCurrentSellerId = $this->ksDataPersistor->get('ks_assign_product_seller_id');
        $this->ksSellerId = $ksCurrentSellerId;
        $ksStoreId = $this->ksRequest->getParam('ks_store_id');
        $ksSellerWebsiteId = $this->ksSellerHelper->getksSellerWebsiteId($ksCurrentSellerId);
        // Filter According to the Seller Website
        if ($this->ksDataHelper->getKsConfigValue('customer/account_share/scope')) {
            $this->collection->addWebsiteFilter($ksSellerWebsiteId);
        }
        if ($ksStoreId) {
            $ksStore = $this->ksStoreManager->getStore($ksStoreId);
            $this->collection->addStoreFilter($ksStore);
        }
        // Get Allowed Product Type
        $ksAllowedType = $this->ksProductHelper->getKsSellerProductType($ksCurrentSellerId);

        // Get Allowed Product Attribute Set Id
        $ksAttributeSetId = $this->ksDataHelper->getKsDefaultAttributes();

        // Create Product Array
        $ksProductId = [0];
        $ksCollection = $this->collection->getData();
        foreach ($ksCollection as $ksRecord) {
            if ($ksRecord['ks_seller_id'] || $ksRecord['ks_email']) {
                $ksProductId[] = $ksRecord['entity_id'];
            }
        }
        $this->collection->addFieldtoFilter(
            'entity_id',
            ['nin' => $ksProductId]
        )->addFieldToFilter(
            'type_id',
            ['in' => $ksAllowedType]
        )->addFieldToFilter(
            'attribute_set_id',
            ['in' => $ksAttributeSetId]
        )->getSelect()->group('entity_id');
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
        $ksItems = $this->getCollection()->toArray();

        foreach ($ksItems as $ksKey => $ksValue) {
            if ($this->ksSellerId) {
                $ksItems[$ksKey]['ks_seller_id'] = $this->ksSellerId;
            }
        }

        $data = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($ksItems),
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
