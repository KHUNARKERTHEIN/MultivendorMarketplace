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
use Magento\Framework\App\RequestInterface;

/**
 * Class KsSellerCommissionProvider
 */
class KsSellerCommissionProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @var \Magento\Framework\App\RequestInterface,
     */
    private $ksRequest;


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
        RequestInterface $ksRequest,
        array $ksAddFieldStrategies = [],
        array $ksAddFilterStrategies = [],
        array $ksMeta = [],
        array $data = [],
        PoolInterface $ksModifiersPool = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $ksMeta, $data);
        $this->ksRuleModel = $ksRuleFactory->create();
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksSellerModel = $ksSellerModelFactory->create();
        $this->ksAddFieldStrategies = $ksAddFieldStrategies;
        $this->ksAddFilterStrategies = $ksAddFilterStrategies;
        $this->ksModifiersPool = $ksModifiersPool ?: ObjectManager::getInstance()->get(PoolInterface::class);
        $this->ksRequest = $ksRequest;
        //Getting Seller Id from Grid
        $ksSellerId = $this->ksRequest->getParam('ks_seller_id');
        // Get Seller Id from DataPersistor
        $ksDataSellerId = $this->ksDataPersistor->get('ks_current_seller_id');
        // Terinary Operator to find seller id
        $ksCurrentSellerId = $ksSellerId ? $ksSellerId : $ksDataSellerId;

        $ksGlobalCollection = $this->ksRuleModel->getCollection()->addFieldToFilter('ks_rule_type', 1);
        $ksCollection = $this->ksRuleModel->getCollection()->addFieldToFilter('ks_rule_type', 2)->addFieldToFilter('ks_seller_id', $ksCurrentSellerId);
        $ksIds = [];
        foreach ($ksGlobalCollection as $ksRecord) {
            $ksIds[] =$ksRecord->getId();
        }
        foreach ($ksCollection as $ksRecord) {
            $ksIds[] =$ksRecord->getId();
        }

        $this->collection = $ksCollectionFactory->create()->addFieldToFilter('id', ['in'=>$ksIds]);
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
