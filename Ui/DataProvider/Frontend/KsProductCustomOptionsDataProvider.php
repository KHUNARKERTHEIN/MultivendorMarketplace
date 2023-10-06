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
namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\Option\Repository as ProductOptionRepository;
use Magento\Catalog\Model\Product\Option\Value as ProductOptionValueModel;

/**
 * Class KsProductCustomOptionsDataProvider
 * @package Ksolves\MultivendorMarketplace\Ui\DataProvider\Frontend
 */
class KsProductCustomOptionsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var \Magento\Customer\Model\Session
     */
    protected $ksSession;

    /**
     * @var RequestInterface
     */
    protected $ksRequest;

    /**
     * @var ProductOptionRepository
     */
    protected $ksRroductOptionRepository;

    /**
     * @var ProductOptionValueModel
     */
    protected $ksProductOptionValueModel;

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
     * @param RequestInterface $ksRequest
     * @param ProductOptionRepository $ksRroductOptionRepository
     * @param ProductOptionValueModel $ksProductOptionValueModel
     * @param Session $ksSession
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        RequestInterface $ksRequest,
        ProductOptionRepository $ksRroductOptionRepository,
        ProductOptionValueModel $ksProductOptionValueModel,
        Session $ksSession,
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
        $this->ksSession           = $ksSession;
        $this->ksRequest           = $ksRequest;
        $this->ksRroductOptionRepository = $ksRroductOptionRepository;
        $this->ksProductOptionValueModel = $ksProductOptionValueModel;

        $this->collection->setStoreId(Store::DEFAULT_STORE_ID);

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

        //get qty
        $this->collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left'
        );

        $this->collection->setFlag('has_stock_status_filter');

        $this->collection->addFieldToFilter('ks_seller_id', $this->ksSession->getCustomerId());
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $currentProductId = (int)$this->ksRequest->getParam('current_product_id');

            if (0 !== $currentProductId) {
                $this->getCollection()->getSelect()->where('e.entity_id != ?', $currentProductId);
            }

            try {
                $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
                $linkField = $entityMetadata->getLinkField();
            } catch (\Exception $e) {
                $linkField = 'entity_id';
            }

            $this->getCollection()->getSelect()->distinct()->join(
                ['opt' => $this->getCollection()->getTable('catalog_product_option')],
                'opt.product_id = e.' . $linkField,
                null
            );
            $this->getCollection()->load();

            /** @var ProductInterface $product */
            foreach ($this->getCollection() as $product) {
                $options = [];

                /** @var ProductOption|DataObject $option */
                foreach ($this->ksRroductOptionRepository->getProductOptions($product) as $option) {
                    $option->setData(
                        'values',
                        $this->ksProductOptionValueModel->getValuesCollection($option)->toArray()['items']
                    );

                    $options[] = $option->toArray();
                }

                $product->setOptions($options);
            }
        }

        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
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
