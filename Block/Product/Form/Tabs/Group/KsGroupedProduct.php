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

namespace Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Group;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as KsImageHelper;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class KsGroupedProduct
 * @package Ksolves\MultivendorMarketplace\Block\Product\Form\Tabs\Group
 */
class KsGroupedProduct extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $ksGroupedProduct;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var CurrencyInterface
     */
    protected $ksLocaleCurrency;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $ksAttributeSet;

    /**
     * @var KsImageHelper
     */
    protected $ksImageHelper;

    /**
     * @var StockRegistryInterface
     */
    protected $ksStockRegistryInterface;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var string
     */
    protected $_keyAssociatedProducts = '_cache_instance_associated_products';

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * KsGroupedProduct constructor.
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param \Magento\GroupedProduct\Model\Product\Type\Grouped $ksGroupedProduct
     * @param CurrencyInterface $ksLocaleCurrency
     * @param AttributeSetRepositoryInterface $ksAttributeSet
     * @param ProductRepositoryInterface $ksProductRepository
     * @param KsImageHelper $ksImageHelper
     * @param StoreManagerInterface $ksStoreManager
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $ksGroupedProduct,
        CurrencyInterface $ksLocaleCurrency,
        AttributeSetRepositoryInterface $ksAttributeSet,
        ProductRepositoryInterface $ksProductRepository,
        KsImageHelper $ksImageHelper,
        StoreManagerInterface $ksStoreManager,
        array $ksData = []
    ) {
        $this->ksGroupedProduct    = $ksGroupedProduct;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksStoreManager      = $ksStoreManager;
        $this->ksLocaleCurrency    = $ksLocaleCurrency;
        $this->ksAttributeSet      = $ksAttributeSet;
        $this->ksImageHelper       = $ksImageHelper;
        $this->ksProductHelper     = $ksProductHelper;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * @return array|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->getKsProduct());
        }
        $ksProduct = $this->getData('product');
        if ($ksProduct->getTypeInstance()->getStoreFilter($ksProduct) === null) {
            $ksProduct->getTypeInstance()->setStoreFilter(
                $this->_storeManager->getStore($ksProduct->getStoreId()),
                $ksProduct
            );
        }

        return $ksProduct;
    }

    /**
     * Retrieve store filter for associated products
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|\Magento\Store\Model\Store
     */
    public function getStoreFilter($product)
    {
        $cacheKey = '_cache_instance_store_filter';
        return $product->getData($cacheKey);
    }

    /**
    * Retrieve array of associated products
    *
    * @param \Magento\Catalog\Model\Product $product
    * @return array
    */
    public function getAssociatedProducts($product)
    {
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $ksProductLinkCollection = $this->ksProductHelper->getKsAssociateLinkCollection()
                ->addFieldToFilter('product_id', $product->getId());

            $product->setData($this->_keyAssociatedProducts, $ksProductLinkCollection);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }


    /**
     * Check required custom option has or not
     *
     * @param \Magento\Catalog\Model\Product $ksGroupedProduct
     * @return boolean
     */
    protected function ksCheckCustomOption($ksGroupedProduct)
    {
        $ksHasOption = false;
        $ksCustomOptions = $ksGroupedProduct->getOptions();
        if (is_array($ksCustomOptions)) {
            foreach ($ksGroupedProduct->getOptions() as $ksCustomOption) {
                if ($ksCustomOption->getIsRequire()) {
                    $ksHasOption = true;
                }
            }
        }
        return $ksHasOption;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getKsAssociateProduct()
    {
        $ksGroupedproductArr = [];
        $ksProduct = $this->getProduct();
        $ksGroupedProduct = $this->getAssociatedProducts($ksProduct);
        $ksPosition = 0;

        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksStore = $this->ksStoreManager->getStore($ksStoreId);
        $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());

        foreach ($ksGroupedProduct->getData() as $ksGroupedItem) {
            $ksGroupedProduct = $this->ksProductRepository->getById(
                $ksGroupedItem['linked_product_id'],
                true,
                $this->getRequest()->getParam('store', 0)
            );

            if ($this->ksCheckCustomOption($ksGroupedProduct)) {
                continue;
            }

            $ksImageUrl = $this->ksImageHelper->init($ksGroupedProduct, 'product_page_image_small')
                ->setImageFile($ksGroupedProduct->getImage()) // image,small_image,thumbnail
                ->resize(70)
                ->getUrl();

            $ksStatusAttr = $ksGroupedProduct->getResource()->getAttribute('status');
            $ksStatusText = '';
            if ($ksStatusAttr->usesSource()) {
                $ksStatusText = $ksStatusAttr->getSource()->getOptionText($ksGroupedProduct->getStatus());
            }


            $ksPrice = ($ksGroupedProduct->getPrice()) ? $ksCurrency->toCurrency(sprintf("%f", $ksGroupedProduct->getPrice())) : '';

            $ksGroupedproductArr[] = [
                'id' => $ksGroupedProduct->getId(),
                'thumbnail' => $ksImageUrl,
                'name' => $ksGroupedProduct->getName(),
                'status' => $ksStatusText,
                'attributeset' => $this->ksAttributeSet->get($ksGroupedProduct->getAttributeSetId())->getAttributeSetName(),
                'sku' => $ksGroupedProduct->getSku(),
                'price' => $ksPrice,
                'qty' => number_format((float)$ksGroupedItem['qty'], 0, ".", ""),
                'position' => $ksPosition
            ];
            $ksPosition++;
        }
        array_multisort(array_column($ksGroupedproductArr, "position"), SORT_ASC, $ksGroupedproductArr);
        return $ksGroupedproductArr;
    }
}
