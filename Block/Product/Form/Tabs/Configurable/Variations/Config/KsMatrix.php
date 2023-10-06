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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Configurable\Variations\Config;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * KsMatrix
 */
class KsMatrix extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Registry
     */
    protected $ksRegistry;

    /*
    * @var Image
    */
    protected $ksHelperImage;

    /**
     * @var Configurable
     */
    protected $ksConfigurableProductType;

    /**
     * @var VariationMatrix
     */
    protected $ksConfigurableProductVariationMatrix;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepositoryInterface;

    /**
     * @var StockRegistryInterface
     */
    protected $ksStockRegistryInterface;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var CurrencyInterface
     */
    protected $ksLocaleCurrency;

    /**
     * @var array
     */
    protected $ksConfigurableProductAttributes;

    /**
     * @var array
     */
    protected $ksConfigurableProductMatrix;

    /**
     * @param Context                       ksContext
     * @param Registry                      $ksRegistry
     * @param StoreManagerInterface         $ksStoreManager
     * @param CurrencyFactory               $ksCurrencyFactory
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
        CurrencyInterface $ksLocaleCurrency,
        Image $ksHelperImage,
        Configurable $ksConfigurableProductType,
        VariationMatrix $ksConfigurableProductVariationMatrix,
        ProductRepositoryInterface $ksProductRepositoryInterface,
        StockRegistryInterface $ksStockRegistryInterface,
        array $ksData = []
    ) {
        parent::__construct($ksContext, $ksData);
        $this->ksRegistry = $ksRegistry;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksLocaleCurrency    = $ksLocaleCurrency;
        $this->ksHelperImage = $ksHelperImage;
        $this->ksConfigurableProductType = $ksConfigurableProductType;
        $this->ksConfigurableProductVariationMatrix = $ksConfigurableProductVariationMatrix;
        $this->ksProductRepositoryInterface = $ksProductRepositoryInterface;
        $this->ksStockRegistryInterface = $ksStockRegistryInterface;
    }

    public function getKsProduct()
    {
        return $this->ksRegistry->registry('product');
    }

    /**
     * Retrieve all possible attribute values combinations.
     *
     * @return array
     */
    public function getConfigurableProductVariationMatrix()
    {
        return $this->ksConfigurableProductVariationMatrix->getVariations($this->getConfigurableAttributes());
    }

    /**
     * @param array $initData
     *
     * @return string
     */
    public function getKsVariationStepsWizard($initData)
    {
        /** @var \Magento\Ui\Block\Component\StepsWizard $variationWizardBlock */
        $ksVariationWizardBlock = $this->getChildBlock('ks-variation-steps-wizard');
        if ($ksVariationWizardBlock) {
            $ksVariationWizardBlock->setInitData($initData);

            return $ksVariationWizardBlock->toHtml();
        }

        return '';
    }

    /**
     * Get Configurable attributes data.
     *
     * @return array
     */
    protected function getConfigurableAttributes()
    {
        if (!$this->hasData('attributes')) {
            $ksConfAttributes = (array) $this->ksConfigurableProductType
            ->getConfigurableAttributesAsArray($this->getKsProduct());

            $ksProductData = (array) $this->getRequest()->getParam('product');
            if (isset($ksProductData['configurable_attributes_data'])) {
                $configurableAttributeData = $ksProductData['configurable_attributes_data'];
                foreach ($ksConfAttributes as $key => $ksConfAttribute) {
                    if (isset($configurableAttributeData[$key])) {
                        $ksConfAttributes[$key] = array_replace_recursive(
                            $ksConfAttribute,
                            $configurableAttributeData[$key]
                        );
                        $ksConfAttributes[$key]['values'] = array_merge(
                            isset($ksConfAttribute['values']) ? $ksConfAttribute['values'] : [],
                            isset($configurableAttributeData[$key]['values'])
                            ? array_filter($configurableAttributeData[$key]['values'])
                            : []
                        );
                    }
                }
            }
            $this->setAttributes($ksConfAttributes);
        }

        return $this->getAttributes();
    }

    /**
     * get all list of Configurable associated products.
     *
     * @return array
     */
    protected function getConfigurableAssociatedProducts()
    {
        $ksUsedProductAttributes = $this->ksConfigurableProductType
        ->getUsedProductAttributes($this->getKsProduct());

        $ksProductByUsedAttributes = [];
        foreach ($this->_getConfigurableAssociatedProducts() as $ksProduct) {
            $keys = [];
            foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                /* @var $confAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $keys[] = $ksProduct->getData($ksConfAttribute->getAttributeCode());
            }
            $ksProductByUsedAttributes[implode('-', $keys)] = $ksProduct;
        }

        return $ksProductByUsedAttributes;
    }

    /**
     * @return array
     */
    protected function _getConfigurableAssociatedProducts()
    {
        $ksAssociateproducts = [];
        $ksAssociatedProductCollection = $this->ksConfigurableProductType
                                        ->getUsedProductCollection($this->getKsProduct())
                                        ->setFlag('has_stock_status_filter', true);

        foreach ($ksAssociatedProductCollection as $ksAssociatedProduct) {
            try {
                if ($ksAssociatedProduct->getData('has_options')) {
                    continue;
                }
                $ksAssociateproducts[] = $this->ksProductRepositoryInterface->getById($ksAssociatedProduct->getEntityId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
        }

        return $ksAssociateproducts;
    }

    /**
     * @return array|null
     */
    public function getKsProductMatrix()
    {
        if ($this->ksConfigurableProductMatrix === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->ksConfigurableProductMatrix;
    }

    /**
     * @return array|null
     */
    public function getKsProductAttributes()
    {
        if ($this->ksConfigurableProductAttributes === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->ksConfigurableProductAttributes;
    }

    protected function prepareConfigurableProductVariation()
    {
        $ksConfigurableProductVariations = $this->getConfigurableProductVariationMatrix();
        $ksConfigurableProductMatrix = [];
        $ksConfAttributes = [];

        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksStore = $this->ksStoreManager->getStore($ksStoreId);
        $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());

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
                        'price' => number_format((float)$ksProduct->getPrice(), 2, ".", ''),
                        'pricewithcurrency' => $ksCurrency->toCurrency(sprintf("%f", $ksProduct->getPrice())),
                        'options' => $ksConfigurableProductVariationOptions,
                        'weight' => ($ksProduct->getWeight()) ? number_format((float)$ksProduct->getWeight(), 2, ".", "") : $ksProduct->getWeight(),
                        'status' => $ksProduct->getStatus(),
                        'was_changed'=> 0
                    ];
                }
            }
        }
        $this->ksConfigurableProductMatrix = $ksConfigurableProductMatrix;
        $this->ksConfigurableProductAttributes = array_values($ksConfAttributes);
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
}
