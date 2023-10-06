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

namespace Ksolves\MultivendorMarketplace\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * KsCommissionHelper class
 */
class KsCommissionHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    protected $ksResource;

    /**
    * @var \Magento\Store\Model\WebsiteFactory
    */
    protected $ksWebsiteFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\commissionRuleFactory
     */
    protected $ksCommissionRuleFactory;

    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProduct
     */
    protected $ksSellerProduct;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @param Context $ksContext
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRuleFactory
     */
    public function __construct(
        Context $ksContext,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        CollectionFactory $ksCollectionFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        ResourceConnection $ksResource,
        \Ksolves\MultivendorMarketplace\Model\KsProduct $ksSellerProduct,
        \Magento\Store\Model\WebsiteFactory $ksWebsiteFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRuleFactory
    ) {
        parent::__construct($ksContext);
        $this->ksDate = $ksDate;
        $this->ksResource = $ksResource;
        $this->ksWebsiteFactory = $ksWebsiteFactory;
        $this->ksCollectionFactory  = $ksCollectionFactory;
        $this->ksSellerProduct = $ksSellerProduct;
        $this->ksCommissionRuleFactory = $ksCommissionRuleFactory;
        $this->ksProductFactory = $ksProductFactory;
    }

    /**
     * product count by commisson rule apply
     *
     * @param  int $ksCommissionRuleId
     * @return int
     */
    public function ksProductCountByCommissionRule($ksCommissionRuleId)
    {
        $ksProductCollection = $this->ksProductFactory->create()->getCollection();

        //fetch product by commission rule id
        $ksProductCollection->joinField(
            'ks_product_id',
            'ks_commissionrule_product_indexer',
            'ks_product_id',
            'ks_product_id=entity_id',
            [],
            'inner'
        );

        $ksProductCollection->getSelect()->where('ks_commission_rule_id = ?', $ksCommissionRuleId);

        //remove other produt which associate priority commission rule
        $KsCommissionRuleModel = $this->ksCommissionRuleFactory->create()->load($ksCommissionRuleId);

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
            $ksProductCollection->addFieldToFilter('entity_id', ['nin' => $ksRemoveProductIds]);
        }

        $ksProductCount = $ksProductCollection->getSize();
        return $ksProductCount;
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
     * get all active commission rule
     * @param  int $ksSellerId
     * @param  int $ksProductId
     * @param  int $websiteId
     * @param  int $ksStoreId
     * @return object
     */
    public function getKsCommissionRuleList($ksSellerId, $ksProductId, $websiteId, $ksStoreId)
    {
        $ksProduct = $this->ksProductFactory->create()->setStoreId($ksStoreId)->load($ksProductId);
        $ksProductType = $ksProduct->getTypeId();
        $ksSellerGroupId =1;

        $ksCommissionRuleList = $this->ksCommissionRuleFactory->create()->getCollection()
                    ->addFieldToFilter('ks_status', 1)
                    ->addFieldToFilter(
                        'ks_website',
                        [
                            ['null'=>true],
                            ['finset'=>$websiteId]
                        ]
                    )
                    ->addFieldToFilter(
                        'ks_seller_group',
                        [
                            ['null' => true],
                            ['finset' => $ksSellerGroupId]
                        ]
                    )
                    ->addFieldToFilter(
                        'ks_product_type',
                        [
                            ['null' => true],
                            ['finset' => $ksProductType]
                        ]
                    )->addFieldToFilter(
                        'ks_seller_id',
                        [
                            ['finset' => $ksSellerId],
                            ['eq' => 0]
                        ]
                    );

        return  $ksCommissionRuleList;
    }

    /**
     * Validate rule condition with product id
     *
     * @param  int $ksProductId
     * @param  object $ksCommissionRule
     * @return boolean
     */
    public function ksRuleConditionByProduct($ksProductId, $ksCommissionRule)
    {
        $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
        if ($ksCommissionRule->validate($ksProduct)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Algorithm for calculating price by rule
     *
     * @param  string $ksActionOperator
     * @param  int $ksRuleAmount
     * @param  float $ksPrice
     * @return float|int
     */
    public function ksCalcPriceRule($ksActionOperator, $ksRuleAmount, $ksPrice)
    {
        $ksPriceRule = 0;
        switch ($ksActionOperator) {
            case 'to_fixed':
                $ksPriceRule = min($ksRuleAmount, $ksPrice);
                break;
            case 'to_percent':
                $ksPriceRule = $ksPrice * $ksRuleAmount / 100;
                break;
        }
        return $ksPriceRule;
    }

    /**
     * Get Commission Price by Decimal||Round
     *
     * @param  int $ksPrice
     * @param  int $ksPriceRoundoff
     * @return float|int
     */
    public function ksCheckCommissionPriceRoundoff($ksPrice, $ksPriceRoundoff)
    {
        if ($ksPriceRoundoff) {
            return round($ksPrice);
        } else {
            return $ksPrice;
        }
    }
}
