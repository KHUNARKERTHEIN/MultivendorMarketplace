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

namespace Ksolves\MultivendorMarketplace\Controller\Product\Ajax;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;

/**
 * KsEaringCalculator Controller class
 */
class KsEarningCalculator extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\CommissionRuleFactory $ksRuleFactory
     */
    protected $ksRuleModel;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSeller
     */
    protected $ksSellerDetails;

    /**
     * @var $ksCollection
     */
    protected $ksCollection;

    /**
     * @var $ksSession
     */
    protected $ksSession;

    /**
     * @var $ksDate
     */
    protected $ksDate;

    /**
     * @var $ksCollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory 
     */
    protected $ksCatalogRuleFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $ksProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductLoader;

    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksProductLoader
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Customer\Model\Session $ksSession
     * @param \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerDetails
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
     * @param CollectionFactory $ksCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductLoader,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Customer\Model\Session $ksSession,
        \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerDetails,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        CollectionFactory $ksCollectionFactory
    ) {
        $this->ksRegistry = $ksRegistry;
        $this->ksCatalogRuleFactory = $ksCatalogRuleFactory;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksRuleModel = $ksRuleFactory;
        $this->ksProductLoader = $ksProductLoader;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSession = $ksSession;
        $this->ksSellerDetails = $ksSellerDetails;
        $ksStrDate = $ksDate;
        $this->ksDate = $ksStrDate;
        $this->ksCollectionFactory = $ksCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\Controller\ResultFactory json
     */
    public function execute()
    {
        $ksGetRule = $this->getRequest()->getParam('ksGetRulesData');
        $ksPrice = (float)$this->getRequest()->getParam('ksPrice');
        $ksTax = (float)$this->getRequest()->getParam('ksTax');
        $ksTaxType = $this->getRequest()->getParam('ksTaxType');

        if ($ksGetRule != true) {
            $ksResponse = $this->ksPriceCalculation($ksPrice, $ksTaxType, $ksTax);
        } else {
            $ksProduct = $this->ksProductLoader->create();
            $ksProductFormData = $this->getRequest()->getParam('ksProductForm');
            foreach ($ksProductFormData as $key => $value) {
                if (stripos($value['name'], 'product[') !== false && (substr_count($value['name'], '[') <= 2 || stripos($value['name'], '[]') === true)) {
                    $ksIndex = ltrim(ltrim($value['name'], 'product'), '[');
                    if (substr_count($value['name'], '[') == 2) {
                        $ksIndex = rtrim($ksIndex, '][]');
                        if ($ksProduct->getData($ksIndex) != null) {
                            $ksProduct->setData($ksIndex, [$ksProduct->getData($ksIndex), $value['value']]);
                        } else {
                            $ksProduct->setData($ksIndex, $value['value']);
                        }
                    } else {
                        $ksIndex = rtrim($ksIndex, ']');
                        $ksProduct->setData($ksIndex, $value['value']);
                    }
                } elseif ($value['name'] ==  'ks_product_type') {
                    $ksProduct->setData('type', $value['value']);
                }
            }
            $ksRule = $this->ksGetRule($ksProduct);
            // set response data
            $ksResponse = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'ksRule' => $ksRule
            ]);
        }

        return $ksResponse;
    }

    /**
     * Return Rule
     * @return array
     */
    public function ksGetRule($ksProduct)
    {
        $ksIds = [];
        $ksSellerId = $this->ksSession->getCustomer()->getId();//get id of customer
        $ksSellerWebsite = $this->ksSession->getCustomer()->getWebsiteId();
        $this->ksSellerDetails = $this->ksSellerDetails->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId);
        //Your Website Id
        $ksCurrentWebsiteId = $this->ksStoreManager->getStore()->getWebsiteId();

        if ($ksSellerId > 0) {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('ks_status', 1)->addFieldToFilter('ks_seller_id', ['in' => [$ksSellerId, 0]])->setOrder('ks_created_at', 'desc');
        } else {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('ks_status', 1);
        }

        foreach ($this->ksCollection as $ksRuleItem) {
            $ksRuleRecord = $this->ksRuleModel->create()->load($ksRuleItem->getData('id'));
            if ($ksRuleRecord->validate($ksProduct)) {
                $ksIds[] = $ksRuleItem->getData('id');
            }
        }
        $ksWebsites = $ksProduct->getWebsiteIds();
        $ksProductTypes = $ksProduct->getType();
        $ksSellerGroups = $this->ksSellerDetails->getData()[0]['ks_seller_group_id'];

        if ($ksWebsites == null) {
            $ksWebsites[] = $ksSellerWebsite;
        }
        $this->ksCollection = $this->ksCollection->addFieldToFilter('id', ['in'=>$ksIds])->setOrder('ks_priority', 'asc')->setOrder('ks_created_at', 'desc');
        $ksWebsitesFilter = [];
        $ksProductFilter = [];
        $ksSellerGroupFilter = [];
        if (is_array($ksSellerGroups)) {
            foreach ($ksSellerGroups as $key => $value) {
                $ksSellerGroupFilter[] = ['finset' => $value];
            }
        } else {
            $ksSellerGroupFilter[] = ['finset' => $ksSellerGroups];
        }
        if (is_array($ksProductTypes)) {
            foreach ($ksProductTypes as $key => $value) {
                $ksProductFilter[] = ['finset' => $value];
            }
        } else {
            $ksProductFilter[] = ['finset' => $ksProductTypes];
        }

        $this->ksCollection->addFieldToFilter(
            'ks_product_type',
            [
                $ksProductFilter
            ]
        );

        if (is_array($ksWebsites)) {
            foreach ($ksWebsites as $key => $value) {
                $ksWebsitesFilter[] = ['finset' => $value];
            }
        } else {
            $ksWebsitesFilter[] = ['finset' => $ksWebsites];
        }

        $this->ksCollection->addFieldToFilter(
            'ks_website',
            [
                $ksWebsitesFilter
            ]
        );

        $ksAllWebsiteProductTypesRulesCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter('ks_website', null)
            ->addFieldToFilter('ks_product_type', null)
            ->addFieldToFilter('id', ['in' => $ksIds]);
        $ksAllSellerRulesCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter('ks_seller_id', ['in' => [$ksSellerId, 0]])
            ->addFieldToFilter('id', ['in' => $ksIds]);
        $ksAllWebsiteRulesCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter('ks_website', null)
            ->addFieldToFilter(
                'ks_product_type',
                ['like'=> '%'.$ksProductTypes.'%']
            )->addFieldToFilter('id', ['in' => $ksIds]);
        $ksAllProductTypesRulesCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter(
                'ks_website',
                ['like'=> '%'.$ksSellerWebsite.'%']
            )
            ->addFieldToFilter('ks_product_type', null)
            ->addFieldToFilter('id', ['in' => $ksIds]);
        $ksIds = [];
        foreach ($ksAllWebsiteProductTypesRulesCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }
        foreach ($ksAllWebsiteRulesCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }
        foreach ($ksAllProductTypesRulesCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }
        foreach ($ksAllSellerRulesCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }
        foreach ($this->ksCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }

        if (count($ksIds) > 0) {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('id', ['in' => $ksIds])->addFieldToFilter(
                'ks_seller_group',
                [
                    ['null' => true],
                    ['finset' => $ksSellerGroups]
                ]
            )->addFieldToFilter(
                'ks_min_price',
                [
                    ['lteq'=> $ksProduct->getPrice()],
                    ['eq'=>0]
                ]
            )->addFieldToFilter(
                'ks_max_price',
                [
                    ['gteq'=> $ksProduct->getPrice()],
                    ['eq'=>0]
                ]
            )->addFieldToFilter(
                'ks_start_date',
                [
                    ['lteq'=> $this->ksDate->gmtDate()],
                    ['null' => true]
                ],
            )->addFieldToFilter(
                'ks_end_date',
                [
                    ['gteq'=> $this->ksDate->gmtDate()],
                    ['null' => true]
                ]
            )->setOrder('ks_priority', 'asc')->setOrder('ks_created_at', 'desc');

            if (count($this->ksCollection->getData()) == 0) {
                $this->ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('id', 1);
            }
        } else {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()->addFieldToFilter('id', 1);
        }
        $ksRuleData = $this->ksCollection->getData()[0];

        if (isset($ksRuleData['ks_status'])) {
            if ((!$ksRuleData['ks_status']) && ($ksRuleData['id'] == 1)) {
                $ksRuleData['ks_commission_value'] = 0.00;
                return $ksRuleData;
            }
        }
        return $this->ksCollection->getData()[0];
    }

    /**
     * Return price
     * @return price
     */
    public function ksPriceCalculation($ksPrice, $ksTaxType, $ksTax)
    {
        $ksCalculationBaseOn = $this->getRequest()->getParam('ks_calculation_baseon');
        $ksProductId = (int)$this->getRequest()->getParam('ksProductId');
        if ($ksProductId != null) {
            $ksProductRecord = $this->ksProductLoader->create()->load($ksProductId);
            $ksProductRecord->setPrice($ksPrice);
            $ksProductList = $ksCollection = $this->ksProductCollectionFactory->addFinalPrice()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $ksProductId);
            if (count($ksProductList->getData()) > 0) {
                $ksProduct = $ksProductList->getData()[0];
            }
        } else {
            $ksProduct = $this->ksProductLoader->create();
            $ksProductFormData = $this->getRequest()->getParam('ksProductForm');
            foreach ($ksProductFormData as $key => $value) {
                if (stripos($value['name'], 'product[') !== false && (substr_count($value['name'], '[') <= 2 || stripos($value['name'], '[]') === true)) {
                    $ksIndex = ltrim(ltrim($value['name'], 'product'), '[');
                    if (substr_count($value['name'], '[') == 2) {
                        $ksIndex = rtrim($ksIndex, '][]');
                        if ($ksProduct->getData($ksIndex) != null) {
                            $ksProduct->setData($ksIndex, [$ksProduct->getData($ksIndex), $value['value']]);
                        } else {
                            $ksProduct->setData($ksIndex, $value['value']);
                        }
                    } else {
                        $ksIndex = rtrim($ksIndex, ']');
                        $ksProduct->setData($ksIndex, $value['value']);
                    }
                } elseif ($value['name'] ==  'type') {
                    $ksProduct->setData('type', $value['value']);
                }
            }
            $ksProduct['price'] = $ksPrice;
            $ksProduct['entity_id'] = 1;
            $ksProductRecord = $ksProduct;
        }

        $ks_discounted_price = $this->ksCatalogRuleFactory->create()->calcProductPriceRule(
            $ksProductRecord
            ->setCustomerGroupId($this->ksSession->getCustomer()->getGroupId()),
            $ksProductRecord->getPrice()
        );
        $ksAppliedPrice = 0;
        $ksDiscount=0;
        if ($ks_discounted_price != null) {
            $ksDiscount = $ksProductRecord->getPrice() - $ks_discounted_price;
        }

        if ($ksCalculationBaseOn == 1) {
            if ($ksTaxType == 'fixed') {
                $ksAppliedPrice = $ksPrice + $ksTax;
            } else {
                $ksAppliedPrice = $ksPrice + ($ksPrice * $ksTax)/100;
            }
        } elseif ($ksCalculationBaseOn == 2) {
            $ksAppliedPrice = $ksPrice;
        } elseif ($ksCalculationBaseOn == 3) {
            if ($ksTaxType == 'fixed') {
                $ksAppliedPrice = $ksPrice + $ksTax -$ksDiscount;
            } else {
                $ksAppliedPrice = $ksPrice + ((($ksPrice-$ksDiscount)*$ksTax)/100) -$ksDiscount;
            }
        } else {
            $ksAppliedPrice = $ksPrice - $ksDiscount;
        }
        // set response data
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'appliedprice' => $ksAppliedPrice, 'discounted' => $ks_discounted_price, 'discount'=> $ksDiscount
            ]);
    }
}
