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

namespace Ksolves\MultivendorMarketplace\Model\Indexer;

use Magento\Catalog\Model\Product;
use Ksolves\MultivendorMarketplace\Model\KsCommissionRule;
use Ksolves\MultivendorMarketplace\Model\KsCommissionRule\Rule\Collection as KsCommissionRuleCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory as KsCommissionRuleCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsProductCollection;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Commission rule index builder
 *
 */
class IndexBuilder
{
    /**
    * @var \Magento\Framework\App\ResourceConnection
    */
    protected $ksResource;

    /**
     * @var KsCommissionRuleCollectionFactory
     */
    protected $ksCommissionCollection;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory $ksProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var int
     */
    protected $ksBatchCount;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $ksConnection;

    /**
     * @var KsProductCollection
     */
    protected $ksProductCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     * @param KsCommissionRuleCollectionFactory $ksCommissionCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param KsProductCollection $ksProductCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
     * @param int $batchCount
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $ksResource,
        KsCommissionRuleCollectionFactory $ksCommissionCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        KsProductCollection $ksProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        $ksBatchCount = 1000
    ) {
        $this->ksResource = $ksResource;
        $this->ksConnection = $ksResource->getConnection();
        $this->ksCommissionCollection = $ksCommissionCollectionFactory->create();
        $this->ksProductFactory = $ksProductFactory;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksBatchCount = $ksBatchCount;
        $this->ksDate = $ksDate;
    }

    /**
     * Get products by ids
     *
     * @param array $ksProductIds
     * @return ProductInterface[]
     */
    public function getKsProducts(array $ksProductIds)
    {
        $ksProductCollection = $this->ksProductFactory->create()->getCollection()
            ->setStore($this->ksStoreManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID))
            ->addattributetoselect("price")
            ->addAttributeToFilter('entity_id', ['in'=> $ksProductIds])
            ->addAttributeToFilter('type_id', ['nin'=> ['bundle', 'grouped']]);

        //get seller id
        $ksProductCollection->joinField(
            'ks_seller_id',
            'ks_product_details',
            'ks_seller_id',
            'ks_product_id=entity_id',
            [],
            'inner'
        );
        $ksProductCollection->getSelect()
        ->where('ks_product_approval_status!=? ', KsProduct:: KS_STATUS_NOT_SUBMITTED)
        ->where('at_ks_seller_id.ks_seller_id!=? ', null);


        //get seller group id
        $ksSellerTable = $ksProductCollection->getTable('ks_seller_details');
        $ksProductCollection->getSelect()->join(
            $ksSellerTable. ' as ks_seller',
            'at_ks_seller_id.ks_seller_id = ks_seller.ks_seller_id',
            [
                'ks_seller_group_id' => 'ks_seller_group_id',
            ]
        );

        return $ksProductCollection;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     * @return void
     * @api
     */
    public function reindexById($id)
    {
        try {
            $this->ksCleanProductIndex([$id]);

            $ksProducts = $this->getKsProducts([$id]);
            $ksRows = [];
            $ksCommissionRules = $this->ksCommissionCollection->addFieldToFilter('ks_status', 1);
            foreach ($ksProducts as $ksProduct) {
                $ksRows = $this->ksApplyRules($ksCommissionRules, $ksProduct);
            }

            $this->ksAddIndexData($ksRows);
        } catch (\Exception $e) {
            //$this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Commission rule indexing failed. See details in exception log.'.$e->getMessage())
            );
        }
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->ksCleanProductIndex([$ids]);
            $ksProducts = $this->getKsProducts([$ids]);

            $ksCommissionRules = $this->ksCommissionCollection->addFieldToFilter('ks_status', 1);

            $ksRows= [];
            foreach ($ksProducts as $ksProduct) {
                $ksRows = array_merge($ksRows, $this->ksApplyRules($ksCommissionRules, $ksProduct));
            }

            $this->ksAddIndexData($ksRows);
        } catch (\Exception $e) {
            //$this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Commission rule indexing failed. See details in exception log.'.$e->getMessage())
            );
        }
    }

    /**
     * Clean product index
     *
     * @param array $productIds
     * @return void
     */
    private function ksCleanProductIndex(array $ksProductIds): void
    {
        $ksWhere = ['ks_product_id IN (?)' => $ksProductIds];
        $ksCommissionRuleProductTable = $this->ksResource->getTableName('ks_commissionrule_product_indexer');
        $this->ksConnection->delete($ksCommissionRuleProductTable, $ksWhere);
    }

    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            //$this->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Commission rule indexing failed. See details in exception log.".$e->getMessage())
            );
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        $ksCommissionRuleProductTable = $this->ksResource->getTableName('ks_commissionrule_product_indexer');
        $this->ksConnection->truncateTable($ksCommissionRuleProductTable);

        $ksCommissionCollection = $this->ksCommissionCollection->addFieldToFilter('ks_status', 1);

        foreach ($this->ksCommissionCollection as $ksCommissionRule) {
            if ($ksCommissionRule->getKsStartDate() && $ksCommissionRule->getKsStartDate() > $this->ksDate->gmtDate()) {
                continue;
            }

            if ($ksCommissionRule->getKsEndDate() && $ksCommissionRule->getKsEndDate() < $this->ksDate->gmtDate()) {
                continue;
            }

            $ksProductCollection = $this->ksProductFactory->create()->getCollection()
            ->setStore($this->ksStoreManager->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID))
            ->addattributetoselect("price")
            ->addAttributeToFilter('type_id', ['nin'=> ['bundle', 'grouped']]);

            //get seller id
            $ksProductCollection->joinField(
                'ks_seller_id',
                'ks_product_details',
                'ks_seller_id',
                'ks_product_id=entity_id',
                [],
                'inner'
            );
            $ksProductCollection->getSelect()
            ->where('ks_product_approval_status!=? ', KsProduct:: KS_STATUS_NOT_SUBMITTED)
            ->where('at_ks_seller_id.ks_seller_id!=? ', null);

            //filter commission rule by product type
            if ($ksCommissionRule->getKsProductType()) {
                $ksProductType = explode(',', $ksCommissionRule->getKsProductType());
                $ksProductCollection->addAttributeToFilter('type_id', ['in'=> $ksProductType]);
            }

            //filter commission rule by website
            if ($ksCommissionRule->getKsWebsite()) {
                $ksWebsiteIds = explode(',', $ksCommissionRule->getKsWebsite());
                $ksProductCollection = $ksProductCollection->addWebsiteFilter($ksWebsiteIds);
            }

            //filter commission rule by seller id
            if ($ksCommissionRule->getKsSellerId() > 0) {
                $ksProductCollection->addFieldToFilter('ks_seller_id', $ksCommissionRule->getKsSellerId());
            }

            if ($ksCommissionRule->getKsRuleType()==1 && $ksCommissionRule->getKsSellerGroup()) {
                $ksSellerGroups = explode(',', $ksCommissionRule->getKsSellerGroup());
                //get seller group id
                $ksSellerTable = $ksProductCollection->getTable('ks_seller_details');
                $ksProductCollection->getSelect()->join(
                    $ksSellerTable. ' as ks_seller',
                    'at_ks_seller_id.ks_seller_id = ks_seller.ks_seller_id',
                    [
                        'ks_seller_group_id' => 'ks_seller_group_id',
                    ]
                )->where('ks_seller_group_id IN (?)', $ksSellerGroups);
            }

            $ksMinPrice = $ksCommissionRule->getKsMinPrice();
            $ksMaxPrice = $ksCommissionRule->getKsMaxPrice();

            $ksRows = [];

            //validate by rule condition
            $ksMatchingProductIds = $ksCommissionRule->getMatchingProductIds();

            if (!empty($ksMatchingProductIds)) {
                foreach ($ksProductCollection as $ksProduct) {
                    if (array_key_exists($ksProduct->getId(), $ksMatchingProductIds)) {
                        if ($ksMatchingProductIds[$ksProduct->getId()]) {
                            if ($ksMinPrice > 0 && $ksProduct->getPrice() < $ksMinPrice
                                && $ksProduct->getTypeId()!="configurable") {
                                continue;
                            }

                            if ($ksMaxPrice  > 0 && $ksProduct->getPrice()> $ksMaxPrice
                                && $ksProduct->getTypeId()!="configurable") {
                                continue;
                            }

                            $ksRuleId = (int) $ksCommissionRule->getId();

                            $ksRows[] = [
                                    'ks_commission_rule_id' => $ksRuleId,
                                    'ks_product_id' => $ksProduct->getId(),
                                ];
                        }
                    }
                }
            }

            $this->ksAddIndexData($ksRows);
        }
    }

    /**
    * Apply rules
    *
    * @param KsCommissionRule $ksCommissionRule
    * @param Product $ksProduct
    * @return array
    */
    protected function ksApplyRules($ksCommissionRuleCollection, Product $ksProduct)
    {
        $ksRows = [];
        $ksProductSellerId = $ksProduct->getKsSellerId();

        foreach ($ksCommissionRuleCollection as $ksCommissionRule) {
            if ($ksCommissionRule->getKsStartDate() && $ksCommissionRule->getKsStartDate() > $this->ksDate->gmtDate()) {
                continue;
            }

            if ($ksCommissionRule->getKsEndDate() && $ksCommissionRule->getKsEndDate() < $this->ksDate->gmtDate()) {
                continue;
            }

            //filter commission rule by seller id
            if ($ksProductSellerId && $ksCommissionRule->getKsSellerId() > 0
                && $ksCommissionRule->getKsSellerId()!= $ksProductSellerId) {
                continue;
            }

            if ($ksCommissionRule->getKsRuleType()==1 && $ksCommissionRule->getKsSellerGroup()
                 && $ksProductSellerId >0) {
                $ksSellerGroups = explode(',', $ksCommissionRule->getKsSellerGroup());

                $ksSellerGroupId = $ksProduct->getKsSellerGroupId();
                if (!in_array($ksSellerGroupId, $ksSellerGroups)) {
                    continue;
                }
            }

            $ksMinPrice = $ksCommissionRule->getKsMinPrice();
            $ksMaxPrice = $ksCommissionRule->getKsMaxPrice();

            if ($ksMinPrice > 0 && $ksProduct->getPrice() < $ksMinPrice
                && $ksProduct->getTypeId()!="configurable") {
                continue;
            }

            if ($ksMaxPrice  > 0 && $ksProduct->getPrice()> $ksMaxPrice
                && $ksProduct->getTypeId()!="configurable") {
                continue;
            }

            //filter commission rule by product type
            if ($ksCommissionRule->getKsProductType()) {
                $ksProductType = explode(',', $ksCommissionRule->getKsProductType());
                if (!in_array($ksProduct->getTypeId(), $ksProductType)) {
                    continue;
                }
            }

            //filter commission rule by website
            if ($ksCommissionRule->getKsWebsite()) {
                $ksWebsiteIds = explode(',', $ksCommissionRule->getKsWebsite());
                if (empty(array_intersect($ksProduct->getWebsiteIds(), $ksWebsiteIds))) {
                    continue;
                }
            }

            //validate by rule condition
            $ksMatchingProductIds = $ksCommissionRule->getMatchingProductIds();

            if (!empty($ksMatchingProductIds)) {
                if (array_key_exists($ksProduct->getId(), $ksMatchingProductIds)) {
                    if ($ksMatchingProductIds[$ksProduct->getId()]) {
                        $ksRows[] = [
                            'ks_commission_rule_id' => (int) $ksCommissionRule->getId(),
                            'ks_product_id' => $ksProduct->getId(),
                        ];
                    }
                }
            }
        }

        return $ksRows;
    }

    /**
    * Add Indexer Table Data
    *
    * @param array $ksRows
    * @return void
    */
    protected function ksAddIndexData($ksRows)
    {
        $ksCommissionRuleProductTable = $this->ksResource->getTableName('ks_commissionrule_product_indexer');

        if (count($ksRows) == $this->ksBatchCount) {
            $this->ksConnection->insertMultiple($ksCommissionRuleProductTable, $ksRows);
            $ksRows = [];
        }

        if ($ksRows) {
            $this->ksConnection->insertMultiple($ksCommissionRuleProductTable, $ksRows);
        }
    }
}
