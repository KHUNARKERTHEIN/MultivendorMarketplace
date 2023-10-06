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

namespace Ksolves\MultivendorMarketplace\Block\Adminhtml\PriceComparison\Tabs;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\VariationMatrix;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsSellerProductCollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * KsConfigurableProduct block
 */
class KsConfigurableProduct extends \Magento\Backend\Block\Template
{
    protected $_template = 'Ksolves_MultivendorMarketplace::product/ks_configurable.phtml';

    /*
    * @var \Magento\Catalog\Helper\Image
    */
    protected $ksHelperImage;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

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
     * @var $ConfigurableProductMatrix
     */
    private $ksConfigurableProductMatrix;

    /**
     * @var $ConfigurableProductAttributes
     */
    private $ksConfigurableProductAttributes;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface 
     */
    protected $ksLocaleCurrency;
    
    /**
     * @var KsSellerProductCollectionFactory
     */
    protected $ksSellerProductCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Catalog\Helper\Image $ksHelperImage
     * @param Configurable $ksConfigurableProductType
     * @param VariationMatrix $ksConfigurableProductVariationMatrix
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency
     * @param ProductRepositoryInterface $ksProductRepositoryInterface
     * @param StockRegistryInterface $ksStockRegistryInterface
     * @param KsProductHelper $ksProductHelper
     * @param array                         $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Catalog\Helper\Image $ksHelperImage,
        Configurable $ksConfigurableProductType,
        VariationMatrix $ksConfigurableProductVariationMatrix,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Framework\Locale\CurrencyInterface $ksLocaleCurrency,
        ProductRepositoryInterface $ksProductRepositoryInterface,
        StockRegistryInterface $ksStockRegistryInterface,
        KsSellerProductCollectionFactory $ksSellerProductCollectionFactory,
        KsProductHelper $ksProductHelper,
        array $data = []
    ) {
        parent::__construct($ksContext, $data);
        $this->ksHelperImage = $ksHelperImage;
        $this->ksConfigurableProductType = $ksConfigurableProductType;
        $this->ksConfigurableProductVariationMatrix = $ksConfigurableProductVariationMatrix;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksLocaleCurrency = $ksLocaleCurrency;
        $this->ksProductRepositoryInterface = $ksProductRepositoryInterface;
        $this->ksStockRegistryInterface = $ksStockRegistryInterface;
        $this->ksSellerProductCollectionFactory = $ksSellerProductCollectionFactory;
        $this->ksProductHelper = $ksProductHelper;
    }

    /**
     * Return product
     *
     * @return object
     */
    public function getKsSellerProduct()
    {
        return $this->ksCoreRegistry->registry('ks_product_modal');
    }

    /**
     * Return get associated product ids.
     *
     * @return array
     */
    public function getKsAssociatedProductIds()
    {
        $ksProductIds = [];
        $ksChildren = $this->getKsSellerProduct()
                      ->getTypeInstance()
                      ->getUsedProducts($this->getKsSellerProduct());
        foreach ($ksChildren as $ksChild) {
            $ksProductIds[] = $ksChild->getEntityId();
        }
        return $ksProductIds;
    }


    /**
     * Return currency symbol.
     *
     * @return string
     */
    public function getKsCurrencySymbol()
    {
        return $this->ksLocaleCurrency->getCurrency($this->_storeManager->getStore()->getBaseCurrencyCode())->getSymbol();
    }

    /**
     * Retrieve all possible attribute values combinations.
     *
     * @return array
     */
    public function getKsConfigurableProductVariationMatrix()
    {
        return $this->ksConfigurableProductVariationMatrix
        ->getVariations($this->getKsConfigurableAttributes());
    }

    /**
     * Get Configurable attributes data.
     *
     * @return array
     */
    protected function getKsConfigurableAttributes()
    {
        if (!$this->hasData('attributes')) {
            $ksConfAttributes = (array) $this->ksConfigurableProductType
            ->getConfigurableAttributesAsArray($this->getKsSellerProduct());

            $this->setAttributes($ksConfAttributes);
        }

        return $this->getAttributes();
    }

    /**
     * get all list of Configurable associated products.
     *
     * @return array
     */
    protected function getKsConfigurableAssociatedProducts()
    {
        $ksUsedProductAttributes = $this->ksConfigurableProductType
        ->getUsedProductAttributes(
            $this->getKsSellerProduct()
        );
        $ksProductByUsedAttributes = [];
        foreach ($this->_getKsConfigurableAssociatedProducts() as $ksProduct) {
            $ksKeys = [];
            foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                /* @var $confAttribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
                $ksKeys[] = $ksProduct->getData($ksConfAttribute->getAttributeCode());
            }
            $ksProductByUsedAttributes[implode('-', $ksKeys)] = $ksProduct;
        }

        return $ksProductByUsedAttributes;
    }

    /**
     * @return array
     */
    protected function _getKsConfigurableAssociatedProducts()
    {
        $ksProduct = $this->getKsSellerProduct();
        $ksAssociatedProductIds = $this->getKsSellerProduct()->getKsAssociatedProductIds();
        if ($ksAssociatedProductIds === null) {
            return $this->ksConfigurableProductType->getUsedProducts($ksProduct);
        }
        $ksProducts = [];
        foreach ($ksAssociatedProductIds as $ksAssociatedProductId) {
            try {
                $ksProducts[] = $this->ksProductRepositoryInterface->getById($ksAssociatedProductId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
        }

        return $ksProducts;
    }

    /**
     * @return array|null
     */
    public function getKsSellerProductMatrix()
    {
        if ($this->ksConfigurableProductMatrix === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->ksConfigurableProductMatrix;
    }

    /**
     * Get PriceComparison Product Id
     * @param  $ksProductId
     * @return int
     */
    public function getKsPriceComparisonProductId($ksProductId)
    {
        $ksId = $this->ksSellerProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksProductId)->getFirstItem()->getId();
        return $ksId;
    }

    /**
     * get product attribute
     * @return array|null
     */
    public function getKsSellerProductAttributes()
    {
        if ($this->ksConfigurableProductAttributes === null) {
            $this->prepareConfigurableProductVariation();
        }

        return $this->ksConfigurableProductAttributes;
    }

    /**
     * get configurable product variation
     * @return array|null
     */
    protected function prepareConfigurableProductVariation()
    {
        $ksConfigurableProductVariations = $this->getKsConfigurableProductVariationMatrix();
        $ksConfigurableProductMatrix = [];
        $ksConfAttributes = [];
        if ($ksConfigurableProductVariations) {
            $ksUsedProductAttributes = $this->ksConfigurableProductType->getUsedProductAttributes(
                $this->getKsSellerProduct()
            );
            $ksProductByUsedAttributes = $this->getKsConfigurableAssociatedProducts();
            foreach ($ksConfigurableProductVariations as $ksConfigurableProductVariation) {
                $ksConfAttributeValues = [];
                foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                    $ksConfAttributeValues[$ksConfAttribute->getAttributeCode()] =
                    $ksConfigurableProductVariation[
                        $ksConfAttribute->getId()
                    ]['value'];
                }
                $ksKey = implode('-', $ksConfAttributeValues);
                if (isset($ksProductByUsedAttributes[$ksKey])) {
                    $ksProduct = $ksProductByUsedAttributes[$ksKey];
                    $ksPrice = $ksProduct->getPrice();
                    $ksConfigurableProductVariationOptions = [];
                    foreach ($ksUsedProductAttributes as $ksConfAttribute) {
                        $ksConfAttributeId = $ksConfAttribute->getAttributeId();
                        $ksConfAttributeCode = $ksConfAttribute->getAttributeCode();
                        if (!isset($ksConfAttributes[$ksConfAttributeId])) {
                            $ksConfAttributes[$ksConfAttributeId] = [
                                'code' => $ksConfAttributeCode,
                                'label' => $ksConfAttribute->getStoreLabel(),
                                'id' => $ksConfAttributeId,
                                'position' => $ksConfAttribute->getPosition(),
                                'chosen' => [],
                            ];
                            foreach ($ksConfAttribute->getOptions() as $ksOption) {
                                $ksOptionValue = $ksOption->getValue();
                                if (!empty($ksOptionValue)) {
                                    $ksConfAttributes[$ksConfAttributeId]['options'][
                                        $ksOption->getValue()
                                    ] = [
                                        'attribute_code' => $ksConfAttributeCode,
                                        'attribute_label' => $ksConfAttribute->getStoreLabel(0),
                                        'id' => $ksOption->getValue(),
                                        'label' => $ksOption->getLabel(),
                                        'value' => $ksOption->getValue(),
                                    ];
                                }
                            }
                        }
                        $ksOptionId = $ksConfigurableProductVariation[$ksConfAttribute->getId()]['value'];
                        $ksConfigurableProductVariationOption = [
                            'attribute_code' => $ksConfAttributeCode,
                            'attribute_label' => $ksConfAttribute->getStoreLabel(0),
                            'id' => $ksOptionId,
                            'label' => $ksConfigurableProductVariation[$ksConfAttribute->getId()]['label'],
                            'value' => $ksOptionId,
                        ];
                        $ksConfigurableProductVariationOptions[] = $ksConfigurableProductVariationOption;
                        $ksConfAttributes[$ksConfAttributeId]['chosen'][$ksOptionId] =
                        $ksConfigurableProductVariationOption;
                    }

                    $ksProductQty = $this->ksStockRegistryInterface->getStockItem(
                        $ksProduct->getId(),
                        $ksProduct->getStore()->getWebsiteId()
                    )->getQty();

                    $ksConfigurableProductMatrix[] = [
                        'productId' => $ksProduct->getId(),
                        'images' => [
                            'preview' => $this->ksHelperImage->init(
                                $ksProduct,
                                'product_thumbnail_image'
                            )->getUrl(),
                        ],
                        'sku' => $ksProduct->getSku(),
                        'name' => $ksProduct->getName(),
                        'quantity' => $ksProductQty,
                        'price' => $ksPrice,
                        'options' => $ksConfigurableProductVariationOptions,
                        'weight' => $ksProduct->getWeight(),
                        'status' => $ksProduct->getStatus(),
                        'stage' => $this->ksProductHelper->getKsProductCondition($ksProduct->getId()),
                        'productUrl' => $this->getUrl('multivendor/pricecomparison/edit', ['id' => $this->getKsPriceComparisonProductId($ksProduct->getId())])
                    ];
                }
            }
        }
        $this->ksConfigurableProductMatrix = $ksConfigurableProductMatrix;
        $this->ksConfigurableProductAttributes = array_values($ksConfAttributes);
    }
}
