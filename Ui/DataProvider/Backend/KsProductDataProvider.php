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

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Store\Model\Store;

/**
 * Class KsProductDataProvider
 */
class KsProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $ksAddFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $ksAddFilterStrategies;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var PoolInterface
     */
    private $ksModifiersPool;

    /**
     * @var KsCommissionRuleFactory
     */
    protected $ksCommissionRuleFactory;

    /**
     * @var ResourceConnection
     */
    protected $ksResource;

    /**
     * @param string $name
     * @param string $ksPrimaryFieldName
     * @param string $ksRequestFieldName
     * @param CollectionFactory $ksCollectionFactory
     * @param KsCommissionRuleFactory $ksCommissionRuleFactory
     * @param DataPersistorInterface $ksDataPersistor
     * @param PoolInterface|null $ksModifiersPool
     * @param ResourceConnection $ksResource
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $ksAddFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $ksAddFilterStrategies
     * @param array $ksMeta
     * @param array $data
     */
    public function __construct(
        $name,
        $ksPrimaryFieldName,
        $ksRequestFieldName,
        CollectionFactory $ksCollectionFactory,
        KsCommissionRuleFactory $ksCommissionRuleFactory,
        DataPersistorInterface $ksDataPersistor,
        PoolInterface $ksModifiersPool,
        ResourceConnection $ksResource,
        array $ksAddFieldStrategies = [],
        array $ksAddFilterStrategies = [],
        array $ksMeta = [],
        array $data = []
    ) {
        parent::__construct($name, $ksPrimaryFieldName, $ksRequestFieldName, $ksMeta, $data);

        $this->ksAddFieldStrategies = $ksAddFieldStrategies;
        $this->ksAddFilterStrategies = $ksAddFilterStrategies;
        $this->ksModifiersPool = $ksModifiersPool;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksCommissionRuleFactory = $ksCommissionRuleFactory;
        $this->ksResource = $ksResource;

        $KsCommissionRuleModel = $this->ksCommissionRuleFactory->create()->load($this->ksDataPersistor->get('ks_commission_rule_id'));

        $this->collection = $ksCollectionFactory->create();

        //get qty
        $this->collection->joinField(
            'qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            [],
            'left'
        );

        // get website
        $this->collection->addWebsiteNamesToResult();

        //fetch product by commission rule id
        $this->collection->joinField(
            'ks_product_id',
            'ks_commissionrule_product_indexer',
            'ks_product_id',
            'ks_product_id=entity_id',
            [],
            'inner'
        );

        $this->collection->getSelect()->where('ks_commission_rule_id = ?', $this->ksDataPersistor->get('ks_commission_rule_id'));

        //remove other produt which associate priority commission rule
        $ksRemoveProductIds = [];
        $ksRuleData = $this->ksCommissionRuleFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('ks_priority', ['lt' => $KsCommissionRuleModel->getKsPriority()])
                        ->addFieldToFilter('id', ['neq' => $KsCommissionRuleModel->getId()]);

        $ksSameRules = $this->ksCommissionRuleFactory->create()->getCollection()
                        ->addFieldToFilter('ks_priority', ['eq' => $KsCommissionRuleModel->getKsPriority()])
                        ->addFieldToFilter('id', ['neq' => $KsCommissionRuleModel->getId()]);

        foreach ($ksSameRules as $ksSameRule) {
            if (strtotime($ksSameRule->getKsCreatedAt()) > strtotime($KsCommissionRuleModel->getKsCreatedAt())) {
                $ksRemoveProductIds = array_merge($ksRemoveProductIds, $this->getKsOtherRuleProducts($ksSameRule->getId()));
            }
        }

        foreach ($ksRuleData as $ksValue) {
            $ksRemoveProductIds = array_merge($ksRemoveProductIds, $this->getKsOtherRuleProducts($ksValue['id']));
        }

        if (!empty($ksRemoveProductIds)) {
            $this->collection->addFieldToFilter('entity_id', ['nin' => $ksRemoveProductIds]);
        }

        $this->collection->setStoreId(Store::DEFAULT_STORE_ID);
    }

    /**
     * Get Other Rule Products
     * @param  $ksRuleId
     * @return array
     */
    public function getKsOtherRuleProducts($ksRuleId)
    {
        $ksConnection = $this->ksResource->getConnection();
        $ksCommissionRuleProductTable = $this->ksResource->getTableName('ks_commissionrule_product_indexer');
        $ksSelect = $ksConnection->select('*')
            ->from($ksCommissionRuleProductTable)
            ->where('ks_commission_rule_id =?', $ksRuleId);
        $ksData = $ksConnection->fetchAll($ksSelect);

        $ksproductIds = [];
        foreach ($ksData as $key => $value) {
            $ksproductIds[] = $value['ks_product_id'];
        }
        return $ksproductIds;
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
        $ksItems =  $this->getCollection()->toArray();

        $ksData = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($ksItems),
        ];

        /** @var ModifierInterface $modifier */
        foreach ($this->ksModifiersPool->getModifiersInstances() as $ksModifier) {
            $ksData = $ksModifier->modifyData($ksData);
        }
        return $ksData;
    }

    /**
     * Add field to select
     *
     * @param string|array $ksField
     * @param string|null $alias
     * @return void
     */
    public function addField($ksField, $alias = null)
    {
        if (isset($this->ksAddFieldStrategies[$ksField])) {
            $this->ksAddFieldStrategies[$ksField]->addField($this->getCollection(), $ksField, $alias);
        } else {
            parent::addField($ksField, $alias);
        }
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $ksFilter)
    {
        if (isset($this->ksAddFilterStrategies[$ksFilter->getField()])) {
            $this->ksAddFilterStrategies[$ksFilter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $ksFilter->getField(),
                    [$ksFilter->getConditionType() => $ksFilter->getValue()]
                );
        } else {
            parent::addFilter($ksFilter);
        }
    }

    /**
     * @inheritdoc
     * @since 103.0.0
     */
    public function getMeta()
    {
        $ksMeta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->ksModifiersPool->getModifiersInstances() as $ksModifier) {
            $ksMeta = $ksModifier->modifyMeta($ksMeta);
        }

        return $ksMeta;
    }
}
