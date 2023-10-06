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

namespace Ksolves\MultivendorMarketplace\Block\PriceComparison;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsSellerProductCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Catalog\Helper\Image;
use Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Configurable\Variations\Config\KsMatrix;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * KsConfigurableProductTab Block Class
 */
class KsConfigurableProductTab extends KsMatrix
{

    /**
     * @var KsSellerProductCollectionFactory
     */
    protected $ksSellerProductCollectionFactory;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @param Context                       ksContext
     * @param Registry                      $ksRegistry
     * @param StoreManagerInterface         $ksStoreManager
     * @param CurrencyInterface             $ksLocaleCurrency
     * @param Image                         $ksHelperImage
     * @param Configurable                  $ksConfigurableProductType
     * @param VariationMatrix               $ksConfigurableProductVariationMatrix
     * @param ProductRepositoryInterface    $ksProductRepositoryInterface
     * @param StockRegistryInterface        $ksStockRegistryInterface
     * @param array                         $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        StoreManagerInterface $ksStoreManager,
        CurrencyFactory $ksCurrencyFactory,
        CurrencyInterface    $ksLocaleCurrency,
        Image $ksHelperImage,
        Configurable $ksConfigurableProductType,
        VariationMatrix $ksConfigurableProductVariationMatrix,
        KsSellerProductCollectionFactory $ksSellerProductCollectionFactory,
        ProductRepositoryInterface $ksProductRepositoryInterface,
        StockRegistryInterface $ksStockRegistryInterface,
        KsProductHelper $ksProductHelper,
        array $ksData = []
    ) {
        $this->ksSellerProductCollectionFactory = $ksSellerProductCollectionFactory;
        $this->ksProductHelper = $ksProductHelper;
        parent::__construct($ksContext, $ksRegistry, $ksStoreManager, $ksCurrencyFactory, $ksLocaleCurrency, $ksHelperImage, $ksConfigurableProductType, $ksConfigurableProductVariationMatrix, $ksProductRepositoryInterface, $ksStockRegistryInterface, $ksData);
    }

    /**
     * get product
     * @return object
     */
    public function getKsProduct()
    {
        if ($this->getRequest()->getParam('parent_id') && !$this->getRequest()->getParam('id')) {
            $ksParentProduct = $this->ksProductRepositoryInterface->getById($this->getRequest()->getParam('parent_id'), true, 0);
            return $ksParentProduct;
        } else {
            return $this->ksRegistry->registry('product');
        }
    }

    /**
     * get product variations
     * @return array|null
     */
    protected function prepareConfigurableProductVariation()
    {
        $ksConfigurableProductVariations = $this->getConfigurableProductVariationMatrix();
        $ksConfigurableProductMatrix = [];
        $ksConfAttributes = [];

        if ($ksConfigurableProductVariations) {
            $ksUsedProductAttributes = $this->ksConfigurableProductType->getUsedProductAttributes(
                $this->getKsProduct()
            );
            $ksProductByUsedAttributes = $this->getConfigurableAssociatedProducts();
            foreach ($ksConfigurableProductVariations as $ksConfigurableProductVariation) {
                $ksConfAttributeValues = [];
                foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                    $ksConfAttributeValues[$ksConfAttribute->getAttributeCode()] =
                    $ksConfigurableProductVariation[$ksConfAttribute->getId()]['value'];
                }
                $key = implode('-', $ksConfAttributeValues);
                if (isset($ksProductByUsedAttributes[$key])) {
                    $ksProduct = $ksProductByUsedAttributes[$key];

                    $ksConfigurableProductVariationOptions = [];

                    foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                        $confAttributeId = $ksConfAttribute->getAttributeId();
                        $confAttributeCode = $ksConfAttribute->getAttributeCode();
                        if (!isset($ksConfAttributes[$confAttributeId])) {
                            $ksConfAttributes[$confAttributeId] = [
                                'code' => $confAttributeCode,
                                'label' => $ksConfAttribute->getStoreLabel(),
                                'id' => $confAttributeId,
                                'position' => $ksConfAttribute->getPosition(),
                                'chosen' => [],
                            ];
                            foreach ($ksConfAttribute->getOptions() as $option) {
                                $optionValue = $option->getValue();
                                if (!empty($optionValue)) {
                                    $ksConfAttributes[$confAttributeId]['options'][
                                        $option->getValue()
                                    ] = [
                                        'attribute_code' => $confAttributeCode,
                                        'attribute_label' => $ksConfAttribute->getStoreLabel(0),
                                        'id' => $option->getValue(),
                                        'label' => $option->getLabel(),
                                        'value' => $option->getValue(),
                                    ];
                                }
                            }
                        }
                        $ksOptionId = $ksConfigurableProductVariation[$ksConfAttribute->getId()]['value'];
                        $ksConfigurableProductVariationOption = [
                            'attribute_code' => $confAttributeCode,
                            'attribute_label' => $ksConfAttribute->getStoreLabel(0),
                            'id' => $ksOptionId,
                            'label' => $ksConfigurableProductVariation[$ksConfAttribute->getId()]['label'],
                            'value' => $ksOptionId,
                        ];
                        $ksConfigurableProductVariationOptions[] = $ksConfigurableProductVariationOption;
                        $ksConfAttributes[$confAttributeId]['chosen'][] = $ksConfigurableProductVariationOption;
                    }

                    $ksProductQty = $this->ksStockRegistryInterface->getStockItem(
                        $ksProduct->getId(),
                        $ksProduct->getStore()->getWebsiteId()
                    )->getQty();

                    $ksConfigurableProductMatrix[] = [
                        'productId' => $ksProduct->getId(),
                        'images' => [
                            'preview' => $this->ksHelperImage->init($ksProduct, 'product_thumbnail_image')->getUrl(),
                        ],
                        'sku' => $ksProduct->getSku(),
                        'name' => $ksProduct->getName(),
                        'quantity' => $ksProductQty,
                        'price' => $ksProduct->getPrice(),
                        'stage' =>$this->ksProductHelper->getKsProductCondition($ksProduct->getId()),
                        'options' => $ksConfigurableProductVariationOptions,
                        'weight' => $ksProduct->getWeight(),
                        'status' => $ksProduct->getStatus(),
                        'ks_product_stage' => $this->getKsProductStage($ksProduct),
                    ];
                }
            }
        }
        $this->ksConfigurableProductMatrix = $ksConfigurableProductMatrix;
        $this->ksConfigurableProductAttributes = array_values($ksConfAttributes);
    }

    /**
     * get product stage
     * @return array|null
     */
    public function getKsProductStage($ksProduct)
    {
        $ksProductCollection = $this->ksSellerProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksProduct->getId());

        foreach ($ksProductCollection as $key => $ksData) {
            return $ksData->getKsProductStage();
        }
    }

    /**
     * @return boolean
     */
    public function getKsIsProductEdit()
    {
        if ($this->getRequest()->getParam('id')) {
            return true;
        } else {
            return false;
        }
    }
}
