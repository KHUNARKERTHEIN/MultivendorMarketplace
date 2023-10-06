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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class KsSellerProductCommissionProvider
 */
class KsSellerProductCommissionProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Commission collection
     *
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\Collection
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
     * @var \Ksolves\MultivendorMarketplace\Model\CommissionRuleFactory $ksRuleFactory
     */
    protected $ksRuleModel;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerModelFactory
     */
    protected $ksSellerModel;

    /**
     * @var PoolInterface
     */
    private $ksModifiersPool;

    /**
     * @var ksProductLoader
     */
    protected $ksProductLoader;

    /**
     * @var ksProductRepository
     */
    protected $ksProductRepository;

    /**
      * @var \Magento\Framework\Locale\CurrencyInterface
      */
    protected $ksLocaleCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;


    /**
     * @param string                                                        $name
     * @param string                                                        $primaryFieldName
     * @param string                                                        $requestFieldName
     * @param CollectionFactory                                             $ksCollectionFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]      $ksAddFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]     $ksAddFilterStrategies
     * @param array                                                         $ksMeta
     * @param array                                                         $data
     * @param DataPersistorInterface                                        $ksDataPersistor
     * @param PoolInterface|null                                            $ksModifiersPool
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ksCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerModelFactory,
        DataPersistorInterface $ksDataPersistor,
        \Magento\Catalog\Model\ProductFactory $ksProductLoader,
        \Magento\Catalog\Model\ProductRepository $ksProductRepository,
        \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        array $ksAddFieldStrategies = [],
        array $ksAddFilterStrategies = [],
        array $ksMeta = [],
        array $data = [],
        PoolInterface $ksModifiersPool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $ksMeta, $data);
        $this->ksRuleModel = $ksRuleFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksLocaleCurrency = $ksLocaleCurrency;
        $this->ksProductLoader = $ksProductLoader;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksSellerModel = $ksSellerModelFactory->create();
        $this->ksAddFieldStrategies = $ksAddFieldStrategies;
        $this->ksAddFilterStrategies = $ksAddFilterStrategies;
        $this->ksModifiersPool = $ksModifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);

        $ksSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        $ksProductId = $this->ksDataPersistor->get('ks_current_product_id');
        
        $ksAttributes = null;
        if (isset($this->ksDataPersistor->get('ks_attributes')['super_attribute'])) {
            $ksAttributes = $this->ksDataPersistor->get('ks_attributes')['super_attribute'];
        }
        
        $ksChildProduct = null;

        $ksGlobalCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('ks_status', 1)->addFieldToFilter('ks_rule_type', 1);
        $ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('ks_status', 1)->addFieldToFilter('ks_rule_type', 2)->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksIds = [];
        foreach ($ksGlobalCollection as $ksRecord) {
            $ksIds[] =$ksRecord->getId();
        }
        foreach ($ksCollection as $ksRecord) {
            $ksIds[] =$ksRecord->getId();
        }

        $this->collection = $ksCollectionFactory->create()->addFieldToFilter('id', ['in'=>$ksIds]);
        
        if ($ksProductId != null) {
            if ($ksAttributes != null) {
                $ksAttrSku= '';
                $ksParentSku = $this->ksProductLoader->create()->load($ksProductId)->getSku();
                foreach ($ksAttributes as $key => $value) {
                    $ksAttrSku = $ksAttrSku . '-'. $value;
                }
                $ksChildSku = $ksParentSku . $ksAttrSku;
                $ksChildProduct = $this->ksProductRepository->get($ksChildSku);
            }
        
            $ksIds = [];
            foreach ($this->collection as $ksRuleItem) {
                $ksRuleRecord = $this->ksRuleModel->create()->load($ksRuleItem->getData('id'));
                if ($ksChildProduct != null) {
                    if ($ksRuleRecord->validate($ksChildProduct)) {
                        $ksIds[] = $ksRuleItem->getData('id');
                    }
                } else {
                    if ($ksRuleRecord->validate($this->ksProductLoader->create()->load($ksProductId))) {
                        $ksIds[] = $ksRuleItem->getData('id');
                    }
                }
            }

            if ($ksChildProduct != null) {
                $this->ksProductLoader = $ksChildProduct;
            } else {
                $this->ksProductLoader = $this->ksProductLoader->create()->load($ksProductId);
            }
        }
        if ($ksProductId == null && $ksSellerId == null) {
            $ksIds = [];
        }
        $this->collection = $ksCollectionFactory->create()->addFieldToFilter('id', ['in'=>$ksIds])->setOrder('ks_priority', 'asc')->setOrder('ks_created_at', 'desc');
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
        
        $ksStore = $this->ksStoreManager->getStore();
        $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());
        $ksItems = $this->getCollection()->toArray();
        $ksTax = 0;
        $ksPrice = $this->ksDataPersistor->get('ks_price');
        $ksDiscount = $this->ksDataPersistor->get('ks_discount');
        $ksQuantity = $this->ksDataPersistor->get('ks_quantity');
        $ksTaxRate = $this->ksDataPersistor->get('ks_tax_rate');
        if ($ksPrice == null) {
            $ksPrice = 0;
        }
        if ($ksDiscount == null) {
            $ksDiscount = 0;
        }
        if ($ksQuantity == null) {
            $ksQuantity = 0;
        }
        if ($ksTaxRate == null) {
            $ksTaxRate = 0;
        }
        if ($ksQuantity > 0) {
            $ksDiscount = $ksDiscount/$ksQuantity;
        }
        $ksTax = (($ksPrice - $ksDiscount)*$ksTaxRate)/100;
        foreach ($ksItems['items'] as $key => $ksRecord) {
            $ksCommissionCost = $ksItems['items'][$key]['ks_commission_value'];
            if ($ksRecord['ks_calculation_baseon'] == 1) {
                if ($ksRecord['ks_commission_type'] == 'to_percent') {
                    $ksCommissionCost = (($ksPrice + $ksTax)*$ksItems['items'][$key]['ks_commission_value'])/100;
                }
                $ksAppliedPrice = $ksPrice + $ksTax;
            } elseif ($ksRecord['ks_calculation_baseon'] == 2) {
                if ($ksRecord['ks_commission_type'] == 'to_percent') {
                    $ksCommissionCost = (($ksPrice)*$ksRecord['ks_commission_value'])/100;
                }
                $ksAppliedPrice = $ksPrice;
            } elseif ($ksRecord['ks_calculation_baseon'] == 3) {
                if ($ksRecord['ks_commission_type'] == 'to_percent') {
                    $ksCommissionCost = (($ksPrice + $ksTax-$ksDiscount)*$ksRecord['ks_commission_value'])/100;
                }
                $ksAppliedPrice = $ksPrice + $ksTax -$ksDiscount;
            } elseif ($ksRecord['ks_calculation_baseon'] == 4) {
                if ($ksRecord['ks_commission_type'] == 'to_percent') {
                    $ksCommissionCost = (($ksPrice-$ksDiscount)*$ksRecord['ks_commission_value'])/100;
                }
                $ksAppliedPrice = $ksPrice - $ksDiscount;
            }

            $ksItems['items'][$key]['ks_commission_cost'] =$ksCurrency->toCurrency(sprintf("%f", $ksQuantity*$ksCommissionCost));
            $ksItems['items'][$key]['ks_applied_price'] = $ksCurrency->toCurrency(sprintf("%f", $ksQuantity*$ksAppliedPrice));
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' =>  $ksItems['items'],
        ];
    }

    
    /**
     * Add full text search filter to collection
     *
     * @param  Filter $ksFilter
     * @return void
     */
    public function addFilter(\Magento\Framework\Api\Filter $ksFilter): void
    {
        /**
        * @var GridCollection $ksCollection
        */
        $ksCollection = $this->getCollection();
        if ($ksFilter->getField() === 'fulltext') {
            $ksCollection->addFullTextFilter(trim($ksFilter->getValue()));
        } else {
            $ksCollection->addFieldToFilter(
                $ksFilter->getField(),
                [$ksFilter->getConditionType() => $ksFilter->getValue()]
            );
        }
    }
}
