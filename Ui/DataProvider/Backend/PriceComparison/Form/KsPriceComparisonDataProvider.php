<?php
declare(strict_types=1);
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Ui\DataProvider\Backend\PriceComparison\Form;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsProductCollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * KsPriceComparisonDataProvider DataProvider class
 */
class KsPriceComparisonDataProvider extends AbstractDataProvider
{
    /**
     * @var Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var Magento\Framework\App\Request\Http
     */
    protected $ksRequest;

    /**
     * @var array
     */
    protected $ksLoadedData;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    protected $collection;

    protected $meta;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Http $ksRequest
     * @param KsProductCollectionFactory $ksCollectionFactory
     * @param ProductRepositoryInterface $ksProductRepository
     * @param StoreManagerInterface $ksStoreManager
     * @param ScopeConfigInterface $ksScopeConfig
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $ksRequest,
        KsProductCollectionFactory $ksCollectionFactory,
        ProductRepositoryInterface $ksProductRepository,
        StoreManagerInterface $ksStoreManager,
        ScopeConfigInterface $ksScopeConfig,
        array $meta = [],
        array $data = []
    ) {
        $ksId                       = $ksRequest->getParam('id');
        $ksCollection               = $ksCollectionFactory->create()->addFieldToFilter('id', $ksId);
        $this->ksRequest            = $ksRequest;
        $this->collection           = $ksCollection;
        $this->ksStoreManager       = $ksStoreManager;
        $this->ksProductRepository  = $ksProductRepository;
        $this->ksScopeConfig        = $ksScopeConfig;
        $this->meta = $this->prepareMeta($this->meta);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Prepares Meta
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get product
     * @return array
     */
    public function getKsProduct()
    {
        $ksItems = $this->collection->getItems();

        foreach ($ksItems as $ksItem) {
            $ksProductId = $ksItem->getKsProductId();
        }

        $ksStoreId = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        return $this->ksProductRepository->getById($ksProductId, true, $ksStoreId);
    }

    /**
     * Get data
     * @return array
     */
    public function getData()
    {
        if (isset($this->ksLoadedData)) {
            return $this->ksLoadedData;
        }

        $ksItems = $this->collection->getItems();

        foreach ($ksItems as $ksItem) {
            $this->ksLoadedData[$ksItem->getId()]['ks_id'] = $ksItem->getId();
            // get product stage
            $ksProductStage = (int)$ksItem->getKsProductStage();
            $ksProductId = $ksItem->getKsProductId();
        }

        $ksStoreId = $this->ksRequest->getParam('store') ? $this->ksRequest->getParam('store') : 0;
        // get product by id
        $ksProduct = $this->getKsProduct();

        $ksProductData = $ksProduct->getData();

        // unset entity id field
        unset($ksProductData['entity_id']);

        // set the data in product baseic fields
        $this->ksLoadedData[$ksItem->getId()]['set'] = $ksProduct->getAttributeSetId();
        $this->ksLoadedData[$ksItem->getId()]['type'] = $ksProduct->getTypeId();
        $this->ksLoadedData[$ksItem->getId()]['ks_product_id'] = $ksProduct->getEntityId();
        $this->ksLoadedData[$ksItem->getId()]['store'] = $ksStoreId;
        $this->ksLoadedData[$ksItem->getId()]['product'] = $ksProductData;
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data'] = $ksProduct->getExtensionAttributes()->getStockItem()->getData();
        if ($ksProduct->getTypeId() == "configurable") {
            $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['qty'] =  '';
            $ksData['qty'] =  '';
        } else {
            $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['qty'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getQty();
            $ksData['qty'] =  (float)$ksProduct->getExtensionAttributes()->getStockItem()->getQty();
        }
        
        $ksData['is_in_stock'] = (int)$ksProduct->getExtensionAttributes()->getStockItem()->getIsInStock();
        $this->ksLoadedData[$ksItem->getId()]['product']['quantity_and_stock_status'] = $ksData;
        $this->ksLoadedData[$ksItem->getId()]['product']['ks_product_stage'] = $ksProductStage;
        $this->ksLoadedData[$ksItem->getId()]['product']['product_has_weight'] = (int)$ksProduct->getTypeInstance()->hasWeight();
        $this->ksLoadedData[$ksItem->getId()]['product']['website_ids'] = $ksProduct->getWebsiteIds();

        // set the price data into the fields
        if ($ksProduct->getPrice()) {
            $this->ksLoadedData[$ksItem->getId()]['product']['price'] = number_format((float)$ksProduct->getPrice(), 2);
        }
        
        if ($ksProduct->getSpecialPrice()) {
            $this->ksLoadedData[$ksItem->getId()]['product']['special_price'] = number_format((float)$ksProduct->getSpecialPrice(), 2);
        }

        if ($ksProduct->getCost()) {
            $this->ksLoadedData[$ksItem->getId()]['product']['cost'] = number_format((float)$ksProduct->getCost(), 2);
        }
        
        if ($ksProduct->getMsrp()) {
            $this->ksLoadedData[$ksItem->getId()]['product']['msrp'] = number_format((float)$ksProduct->getMsrp(), 2);
        }

        // set the weight data into the fields
        if ($ksProduct->getWeight()) {
            $this->ksLoadedData[$ksItem->getId()]['product']['weight'] = (float)$ksProduct->getWeight();
        }
               
        // set the adavanced quantity data into the fields
        
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['min_qty'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getMinQty();
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['min_sale_qty'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getMinSaleQty();
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['notify_stock_qty'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getNotifyStockQty();
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['qty_increments'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getQtyIncrements();
        $this->ksLoadedData[$ksItem->getId()]['product']['stock_data']['max_sale_qty'] = (float)$ksProduct->getExtensionAttributes()->getStockItem()->getMaxSaleQty();

        // return data
        return $this->ksLoadedData;
    }

    /**
     * Get meta
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $meta = $this->ksPriceFieldConfig($meta);
        $meta = $this->ksAdvancePriceMapField($meta);
        $ksProduct = $this->getKsProduct();
        if ($ksProduct->getTypeId() == "configurable") {
            $ksConfigVisibleValue = true;
            $ksDisabled = true;
        } else {
            $ksConfigVisibleValue = false;
            $ksDisabled = false;
        }

        if ($ksProduct->getTypeId() == "downloadable") {
            $ksDowloadVisibleValue = true;
        } else {
            $ksDowloadVisibleValue = false;
        }
        // disable the field in configurable product case
        $meta['ks_configurable_product_tab']['arguments']['data']['config']['visible'] = $ksConfigVisibleValue;
        $meta['ks_downloadable_product_tab']['arguments']['data']['config']['visible'] = $ksDowloadVisibleValue;
        $meta['product']['children']['price']['arguments']['data']['config']['disabled'] = $ksDisabled;
        $meta['product']['children']['qty']['arguments']['data']['config']['disabled'] = $ksDisabled;
        // add the weight unit in weight field
        $meta['product']['children']['weight']['arguments']['data']['config']['addafter'] = $this->ksStoreManager->getStore()->getConfig('general/locale/weight_unit');

        return $meta;
    }

    /**
     * set the price field config
     * Get meta
     * @return array
     */
    public function ksPriceFieldConfig($meta)
    {
        // add the currency sign in price fields
        $meta['product']['children']['price']['arguments']['data']['config']['addbefore'] = $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['special_price']['arguments']['data']['config']['addbefore'] = $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['cost']['arguments']['data']['config']['addbefore'] = $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['tier_price']['children']['price']['arguments']['data']['config']['addbefore'] = $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();

        // set the price scopelabel according to config
        if ($this->ksScopeConfig->getValue('catalog/price/scope')) {
            $ksScopeLabel = '[website]';
        } else {
            $ksScopeLabel = '[global]';
        }
        $meta['product']['children']['price']['arguments']['data']['config']['scopeLabel'] = $ksScopeLabel;
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['special_price']['arguments']['data']['config']['scopeLabel'] = $ksScopeLabel;
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['cost']['arguments']['data']['config']['scopeLabel'] = $ksScopeLabel;

        return $meta;
    }

    /**
     * visible or hide Minimum Advertised Price in advance pricing
     * Get meta
     * @return array
     */
    public function ksAdvancePriceMapField($meta)
    {
        // check for Map enable or disable
        if ($this->ksScopeConfig->getValue('sales/msrp/enabled')) {
            $ksFieldVisible = true;
        } else {
            $ksFieldVisible = false;
        }

        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['msrp']['arguments']['data']['config']['addbefore'] = $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();

        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['msrp']['arguments']['data']['config']['visible'] = $ksFieldVisible;
        $meta['product']['children']['ks_advance_pricing_modal']['children']['ks_advance_pricing_form']['children']['msrp_display_actual_price_type']['arguments']['data']['config']['visible'] = $ksFieldVisible;

        return $meta;
    }
}
