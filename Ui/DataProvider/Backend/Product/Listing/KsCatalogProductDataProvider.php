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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\Product\Listing;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class KsCatalogProductDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend
 */
class KsCatalogProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequest;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $modifiersPool
     * @param \Magento\Framework\App\RequestInterface $ksRequest
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory
     * @param \Magento\Customer\Model\SessionFactory $ksSession
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,        
        \Magento\Framework\App\RequestInterface $ksRequest,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductFactory,
        \Magento\Customer\Model\SessionFactory $ksSession,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksSellerProductFactory,
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
        $this->modifiersPool       = $modifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);
        $this->ksRequest           = $ksRequest;
        $this->ksSellerProductFactory   = $ksSellerProductFactory;


        $ksStoreId = $ksRequest->getParam('store', Store::DEFAULT_STORE_ID);
        $this->collection->setStoreId($ksStoreId);

        // get website
        $this->collection->addWebsiteNamesToResult();

        $this->collection->addAttributeToSelect('*');

        $ksComparisonProductData = $ksProductFactory->create()->addFieldToFilter('ks_parent_product_id', array('neq' => 0))->getData();
        $ksNotSubmittedProductData = $ksProductFactory->create()->addFieldToFilter('ks_product_approval_status', 4)->getData();
        $ksProductData = array_merge($ksComparisonProductData, $ksNotSubmittedProductData);

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

        //get qty
        $this->collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            [],
            'left'
        );

        $this->collection->setFlag('has_stock_status_filter');

        $ksProductId = $this->ksRequest->getParam('current_product_id');
        $ksNameSpace = $ksRequest->getParam('namespace');
        $ksNameSpaceArray = array('product_listing','ks_marketplace_product_listing','ks_marketplace_product_pendinglisting','ks_marketplace_seller_product_listing');

        if ($ksProductId || !in_array($ksNameSpace, $ksNameSpaceArray)) {
            if (!$ksSession->create()->getId()) {
                $ksSellerId = $this->ksSellerProductFactory->create()
                    ->load($this->ksRequest->getParam('current_product_id'), 'ks_product_id')
                    ->getKsSellerId();
                if ($ksSellerId) {
                    $this->collection->addFieldToFilter('ks_seller_id', $ksSellerId);
                } else {
                    $this->collection->addFieldToFilter(
                        'ks_seller_id',
                        array('null' => true)
                    );
                }
            }
        }

        if ($ksSession->create()->getId()) {
            $this->collection->addFieldToFilter('ks_seller_id', $ksSession->create()->getId());
        }

        foreach ($ksProductData as $ksProduct) {
            $this->collection->addFieldToFilter('entity_id', [
                'neq' => $ksProduct['ks_product_id']
            ]);
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
