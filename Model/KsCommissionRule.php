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

namespace Ksolves\MultivendorMarketplace\Model;

use Magento\Rule\Model\AbstractModel;
use Ksolves\MultivendorMarketplace\Model\Indexer\CommissionRule\KsCommissionRuleProcessor;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;

/**
 * KsCommissionRule Model class
 */
class KsCommissionRule extends AbstractModel
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $ksCondProdCombineF;

    /**
    * @var KsCommissionRuleProcessor
    */
    protected $ksCommissionRuleProcessor;

    /**
     * matched product Ids
     *
     * @var array
     */
    protected $ksProductIds;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $ksProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
    * @var ConditionsToCollectionApplier
    */
    private $ksConditionsToCollectionApplier;

    /**
    * @var \Magento\Framework\Model\ResourceModel\Iterator
    */
    private $ksResourceIterator;

    /**
    * @var \Magento\Framework\App\ResourceConnection
    */
    private $ksResourceConnection;

    /**
     * @param \Magento\Framework\Model\Context                               $KsContext
     * @param \Magento\Framework\Registry                                    $KsRegistry
     * @param \Magento\Framework\Data\FormFactory                            $ksFormFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $ksLocaleDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource        $KsResource
     * @param \Magento\Framework\Data\Collection\AbstractDb                  $ksResourceCollection
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $ksCondProdCombineF
     * @param KsCommissionRuleProcessor $ksCommissionRuleProcessor
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksProductCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param ConditionsToCollectionApplier $ksConditionsToCollectionApplier
     * @param \Magento\Framework\Model\ResourceModel\Iterator $ksResourceIterator
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Framework\App\ResourceConnection $ksResourceConnection,
     * @param array  $ksData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $KsContext,
        \Magento\Framework\Registry $KsRegistry,
        \Magento\Framework\Data\FormFactory $ksFormFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksLocaleDate,
        \Magento\CatalogRule\Model\Rule\Condition\CombineFactory $ksCondProdCombineF,
        KsCommissionRuleProcessor $ksCommissionRuleProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksProductCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        ConditionsToCollectionApplier $ksConditionsToCollectionApplier,
        \Magento\Framework\Model\ResourceModel\Iterator $ksResourceIterator,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\App\ResourceConnection $ksResourceConnection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $ksResource = null,
        \Magento\Framework\Data\Collection\AbstractDb $ksResourceCollection = null,
        array $ksData = []
    ) {
        $this->ksCondProdCombineF = $ksCondProdCombineF;
        $this->ksCommissionRuleProcessor = $ksCommissionRuleProcessor;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksConditionsToCollectionApplier = $ksConditionsToCollectionApplier;
        $this->ksResourceIterator = $ksResourceIterator;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksResourceConnection = $ksResourceConnection;
        parent::__construct($KsContext, $KsRegistry, $ksFormFactory, $ksLocaleDate, $ksResource, $ksResourceCollection, $ksData);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule');
        $this->setIdFieldName('id');
    }

    public const CACHE_TAG = 'ks_commission_rule';

    /**
     * @var string
     */
    protected $_cacheTag = 'ks_commission_rule';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'ks_commission_rule';

    /**
     * Get rule condition combine model instance
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->ksCondProdCombineF->create();
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Get rule condition product combine model instance
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->ksCondProdCombineF->create();
    }

    /**
     * @inheritdoc
     *
     * @return $this
     */
    public function afterSave()
    {
        $ksProductIds = array_keys($this->getMatchingProductIds());

        $this->ksCleanCommissionRuleIndex($this->getId());

        if (!empty($ksProductIds) && is_array($ksProductIds)) {
            $this->ksCommissionRuleProcessor->getIndexer()->reindexList($ksProductIds);
        }

        return parent::afterSave();
    }

    /**
    * Clean product index
    *
    * @param int $ksRuleId
    * @return void
    */
    private function ksCleanCommissionRuleIndex($ksRuleId): void
    {
        $ksWhere = ['ks_commission_rule_id=?' => $ksRuleId];
        $ksCommissionRuleProductTable = $this->ksResourceConnection->getTableName('ks_commissionrule_product_indexer');
        $this->ksResourceConnection->getConnection()->delete($ksCommissionRuleProductTable, $ksWhere);
    }

    /**
     * @inheritdoc
     *
     * @return $this
     */
    public function afterDelete()
    {
        $this->ksCleanCommissionRuleIndex($this->getId());
        return parent::afterDelete();
    }

    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->ksProductIds === null) {
            $this->ksProductIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $ksProductCollection = $this->ksProductCollectionFactory->create()
            ->setStore($this->ksStoreManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID));

            $this->getConditions()->collectValidatedAttributes($ksProductCollection);

            if ($this->ksCanPreMapProducts()) {
                $ksProductCollection = $this->ksConditionsToCollectionApplier
                    ->applyConditionsToCollection($this->getConditions(), $ksProductCollection);
            }

            $this->ksResourceIterator->walk(
                $ksProductCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->ksProductFactory->create()
                ]
            );
        }

        return $this->ksProductIds;
    }

    /**
     * Check if we can use mapping for rule conditions
     *
     * @return bool
     */
    private function ksCanPreMapProducts()
    {
        $ksConditions = $this->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$ksConditions || !$ksConditions->getConditions()) {
            return false;
        }

        return true;
    }

    /**
     * Callback function for product matching
     *
     * @param array $ksArgs
     * @return void
     */
    public function callbackValidateProduct($ksArgs)
    {
        $ksProduct = clone $ksArgs['product'];
        $ksProduct->setData($ksArgs['row']);

        $ksResults = [];

        $ksResults = $this->getConditions()->validate($ksProduct);

        $this->ksProductIds[$ksProduct->getId()] = $ksResults;
    }
}
