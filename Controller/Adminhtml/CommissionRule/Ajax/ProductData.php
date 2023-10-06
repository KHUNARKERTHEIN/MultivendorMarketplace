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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ProductData Controller
 */
class ProductData extends \Magento\Backend\App\Action
{
    /**
     * @var ksProductCollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var ksProductLoader
     */
    protected $ksProductLoader;

    /**
     * @var ksCatalogRuleFactory
     */
    protected $ksCatalogRuleFactory;

    /**
     * @var ksProductRepository
     */
    protected $ksProductRepository;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var ksRatesCollection
     */
    protected $ksRatesCollection;

    /**
     * Initialize Controller
     *
     * @param Context $ksContext
     * @param \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $ksRatesCollection
     * @param DataPersistorInterface $ksDataPersistor
     * @param \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory
     * @param \Magento\Catalog\Model\ProductFactory $ksProductLoader
     * @param \Magento\Catalog\Model\ProductRepository $ksProductRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        \Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection $ksRatesCollection,
        DataPersistorInterface $ksDataPersistor,
        \Magento\CatalogRule\Model\RuleFactory $ksCatalogRuleFactory,
        \Magento\Catalog\Model\ProductFactory $ksProductLoader,
        \Magento\Catalog\Model\ProductRepository $ksProductRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $ksProductCollectionFactory
    ) {
        $this->ksRatesCollection = $ksRatesCollection;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksCatalogRuleFactory = $ksCatalogRuleFactory;
        $this->ksProductLoader = $ksProductLoader;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        // get product id
        $ksProductId = $this->getRequest()->getParam('ksProductId');
        $ksTaxRateId = $this->getRequest()->getParam('ksRate');
        $ksRate = 0;
        if ($ksTaxRateId != null) {
            if ($this->ksRatesCollection->addFieldToFilter('tax_calculation_rate_id', $ksTaxRateId)->getData()[0]['rate']) {
                $ksRate = $this->ksRatesCollection->addFieldToFilter('tax_calculation_rate_id', $ksTaxRateId)->getData()[0]['rate'];
            }
        }

        if (array_key_exists('ksAttributesData', $this->getRequest()->getParams())) {
            parse_str($this->getRequest()->getParam('ksAttributesData'), $ksAttributesData);
            $ksAttributes = $ksAttributesData['super_attribute'];
            $ksAttrSku='';
            $ksParentSku = $this->ksProductLoader->create()->load($ksProductId)->getSku();
            foreach ($ksAttributes as $key => $value) {
                $ksAttrSku = $ksAttrSku . '-'. $value;
            }
            $ksChildSku = $ksParentSku . $ksAttrSku;
            $ksChildProduct = $this->ksProductRepository->get($ksChildSku);
            $ksProductId = $ksChildProduct->getId();
        }

        // get product collection
        $ksProductList = $ksCollection = $this->ksProductCollectionFactory->addFinalPrice()->addAttributeToSelect('*')->addFieldToFilter('entity_id', $ksProductId);
        $ksProduct = null;

        if (count($ksProductList->getData()) > 0) {
            $ksProduct = $ksProductList->getData()[0];
            $ksProduct['price'] = number_format($ksProduct['price'], 2);
        }

        $ksProductRecord = $this->ksProductLoader->create()->load($ksProductId);
        $ks_discounted_price = $this->ksCatalogRuleFactory->create()->calcProductPriceRule(
            $ksProductRecord
            ->setStoreId(1)
            ->setCustomerGroupId(1),
            $ksProductRecord->getPrice()
        );
        $ksProductPrice = 0;

        if ($ks_discounted_price == null) {
            if ($ksProduct != null && isset($ksProduct['final_price'])) {
                $ks_discounted_price = $ksProduct['final_price'];
            } else {
                $ks_discounted_price = 0;
            }
        }
        
        if ($ksProduct != null && isset($ksProduct['price'])) {
            $ksProductPrice =  $ksProduct['price'];
        } else {
            $ksProductPrice = $ksProductRecord->getPrice();
            $ksProduct = $ksProductRecord->getData();
            $ks_discounted_price = $ksProductRecord->getFinalPrice();
        }
        if($ksProductPrice){
            $ksProductPrice = (float) str_replace(',', '', $ksProductPrice);
        }
        if($ks_discounted_price){
            $ks_discounted_price = (float) str_replace(',', '', $ks_discounted_price);
        }

        // set response data
        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksProduct, 'discount'=> number_format(($ksProductPrice - $ks_discounted_price), 2),
            'Rate' => number_format($ksRate, 2), 'price'=> $ksProductPrice, 'discounted_price'=>$ks_discounted_price
        ]);
        return $ksResponse;
    }
}
