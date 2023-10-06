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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * KsEarningCalculator Block class
 */
class KsEarningCalculator extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\CommissionRuleFactory $ksRuleFactory
     */
    protected $ksRuleModel;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var $ksCollection
     */
    protected $ksCollection;

    /**
     * @var $ksCollection
     */
    protected $ksCalculationSource;

    /**
     * @var $ksSellerDetails
     */
    protected $ksSellerDetails;

    /**
     * @var $ksDate
     */
    protected $ksDate;

    /**
     * @var $ksCollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

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
    * @param KsDataHelper $ksDataHelper
    * @param KsProductHelper $ksProductHelper
    * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory
    * @param DataPersistorInterface $ksDataPersistor
    * @param \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory
    * @param CollectionFactory $ksCollectionFactory
    * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory
    * @param \Magento\Catalog\Model\ProductFactory $ksProductLoader
    * @param CurrencyFactory $ksCurrencyFactory
    * @param array $ksData
    */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksRuleFactory,
        DataPersistorInterface $ksDataPersistor,
        \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory,
        CollectionFactory $ksCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Customer\Model\Session $ksSession,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Magento\Catalog\Model\ProductFactory $ksProductLoader,
        \Ksolves\MultivendorMarketplace\Model\Source\CommissionRule\KsCalculationBaseOption $ksCalculationSource,
        \Ksolves\MultivendorMarketplace\Model\KsSeller $ksSellerDetails,
        CurrencyFactory $ksCurrencyFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        array $ksData = []
    ) {
        $this->ksSession = $ksSession;
        $this->ksSellerDetails = $ksSellerDetails;
        $ksStrDate = $ksDate;
        $this->ksDate = $ksStrDate;
        $this->ksCalculationSource = $ksCalculationSource;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCatalogRuleFactory = $ksCatalogRuleFactory;
        $this->ksRuleModel = $ksRuleFactory;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksProductLoader = $ksProductLoader;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Return product id
     * @return id
    */
    public function ksGetProductData()
    {
        return $this->ksRegistry->registry('product')->getId();
    }

    /**
     * Retrieve currency symbol
     * @return string
     */
    public function getKsCurrentCurrency()
    {
        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode);
        return $ksCurrency->getCurrencySymbol();
    }

    /**
    * Return $ksCalculationSource
    * @return array
    */
    public function ksGetCalculationBaseSource()
    {
        return $this->ksCalculationSource->toOptionArray();
    }

    /**
     * Return Rule data
     * @return array
    */
    public function ksGetRule()
    {
        $ksIds = [];
        $ksProductId = $this->ksGetProductData();
        $ksSellerId = $this->ksSession->getCustomer()->getId();//get id of customer
        $ksSellerWebsite = $this->ksSession->getCustomer()->getWebsiteId();
        $ksWebsites = null;
        $ksProductTypes = null;
        $ksProduct = null;

        if ($ksSellerId > 0) {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()
                ->addFieldToFilter('ks_status', 1)
                ->addFieldToFilter('ks_seller_id', ['in' => [$ksSellerId, 0]]);
        } else {
            $this->ksCollection = $this->ksRuleModel->create()->getCollection()
                ->addFieldToFilter('ks_status', 1)
                ->addFieldToFilter('ks_seller_id', ['in' => [0]]);
        }

        if ($ksProductId != null) {
            $ksProduct = $this->ksProductLoader->create()->load($ksProductId);
        }
        if ($ksProduct) {
            $ksWebsites = $ksProduct->getWebsiteIds();
            $ksProductTypes = $ksProduct->getTypeId();
        }
        if ($ksWebsites == null) {
            $ksWebsites[] = $ksSellerWebsite;
        } elseif (!is_array($ksWebsites)) {
            $ksWebsites[] = $ksWebsites;
        }

        foreach ($this->ksCollection as $ksRuleItem) {
            if ($ksProductId != null) {
                $ksRuleRecord = $this->ksRuleModel->create()->load($ksRuleItem->getData('id'));
                if ($ksRuleRecord->validate($this->ksProductLoader->create()->load($ksProductId))) {
                    $ksIds[] = $ksRuleItem->getData('id');
                }
            } else {
                $ksIds[] = $ksRuleItem->getData('id');
            }
        }
        $this->ksGetWebsiteFilter($ksIds, $ksProductTypes, $ksWebsites);
        if (count($this->ksCollection->getData()) > 0) {
            $ksRuleData = $this->ksCollection->getData()[0];
            if (isset($ksRuleData['ks_status'])) {
                if ((!$ksRuleData['ks_status']) && ($ksRuleData['id'] == 1)) {
                    $ksRuleData['ks_commission_value'] = 0.00;
                    return $ksRuleData;
                }
            }
            return $this->ksCollection->getData()[0];
        } else {
            throw  new \Exception('No rules are applied. Please Create new or update old rules.');
        }
    }

    /**
     * Return Rule data based on website type
     * @return collection
    */
    public function ksGetWebsiteFilter($ksIds, $ksProductTypes, $ksWebsites)
    {
        $this->ksCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter('id', ['in'=>$ksIds])
            ->setOrder('ks_priority', 'asc');

        $ksProductTypeId = $this->ksCollectionFactory->create()
            ->addFieldToFilter('id', ['in'=>$ksIds])
            ->addFieldToFilter(
                'ks_product_type',
                ['nlike'=> '%'.$ksProductTypes.'%']
            )->getColumnValues('id');
        $ksProductTypeId[] = '';
        $this->ksCollection->addFieldToFilter(
            'id',
            ['nin'=> $ksProductTypeId]
        );

        $ksSellerWebsite = $this->ksSession->getCustomer()->getWebsiteId();
        $ksWebsiteIds = $this->ksCollectionFactory->create()
            ->addFieldToFilter('id', ['in'=>$ksIds])
            ->addFieldToFilter(
                'ks_website',
                ['nlike'=> '%'.$ksSellerWebsite.'%']
            )->getColumnValues('id');
        $ksWebsiteIds[] = '';
        $this->ksCollection->addFieldToFilter(
            'id',
            ['nin'=> $ksWebsiteIds]
        );

        $ksAllWebsiteProductTypesRulesCollection = $this->ksCollectionFactory->create()
            ->addFieldToFilter('ks_website', null)
            ->addFieldToFilter('ks_product_type', null)
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
        foreach ($this->ksCollection->getData() as $key => $ksItem) {
            $ksIds[] = $ksItem['id'];
        }
        if (count($ksIds) > 0) {
            $ksProductId = $this->ksGetProductData();
            if ($ksProductId != null) {
                $ksProduct = $this->ksProductLoader->create()->load($ksProductId);
            }
            $ksSellerId = $this->ksSession->getCustomer()->getId();
            $this->ksSellerDetails = $this->ksSellerDetails->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId);
            $ksSellerGroups = $this->ksSellerDetails->getData()[0]['ks_seller_group_id'];
            if ($ksProductId == null) {
                $this->ksCollection = $this->ksCollectionFactory->create()
                    ->addFieldToFilter('id', ['in' => $ksIds])
                    ->setOrder('ks_priority', 'asc');
            } else {
                $this->ksCollection = $this->ksCollectionFactory->create()
                    ->addFieldToFilter('id', ['in' => $ksIds])
                    ->addFieldToFilter(
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
                        ]
                    )->addFieldToFilter(
                        'ks_end_date',
                        [
                            ['gteq'=> $this->ksDate->gmtDate()],
                            ['null' => true]
                        ]
                    )->setOrder('ks_priority', 'asc')->setOrder('ks_created_at', 'desc');
                if (count($this->ksCollection->getData()) == 0) {
                    $this->ksCollection = $this->ksCollectionFactory->create()->addFieldToFilter('id', 1);
                }
            }
        } else {
            $this->ksCollection = $this->ksCollectionFactory->create()->addFieldToFilter('id', 1);
        }
    }

    /**
     * Return applied price
     * @return price
    */
    public function ksGetAppliedPrice($ksTax, $ksPrice)
    {
        $ksProductId = $this->ksGetProductData();
        $ksProductRecord = $this->ksProductLoader->create()->load($ksProductId);
        $ksProductList = $ksCollection = $this->ksProductCollectionFactory->addFinalPrice()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', $ksProductId);
        if (count($ksProductList->getData()) > 0) {
            $ksProduct = $ksProductList->getData()[0];
        }
        $ks_discounted_price = $this->ksCatalogRuleFactory->create()->calcProductPriceRule(
            $ksProductRecord
            ->setCustomerGroupId($this->ksSession->getCustomer()->getGroupId()),
            $ksProductRecord->getPrice()
        );
        $ksDiscount = 0;
        if ($ks_discounted_price != null) {
            $ksDiscount = $ksProduct['price'] - $ks_discounted_price;
        }

        $ksRule = $this->ksGetRule();
        if ($ksRule['ks_calculation_baseon'] == 1) {
            $ksAppliedPrice = $ksPrice + $ksTax;
        } elseif ($ksRule['ks_calculation_baseon'] == 2) {
            $ksAppliedPrice = $ksPrice;
        } elseif ($ksRule['ks_calculation_baseon'] == 3) {
            $ksAppliedPrice = $ksPrice + $ksTax -$ksDiscount;
        } else {
            $ksAppliedPrice = $ksPrice - $ksDiscount;
        }
    }
}
