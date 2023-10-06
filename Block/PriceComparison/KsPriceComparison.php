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

use Magento\Framework\View\Element\Template;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Product;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Downloadable\Api\SampleRepositoryInterface;
use Magento\Downloadable\Api\LinkRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * KsPriceComparison Block class
 */
class KsPriceComparison extends Template
{
    /**
     * @var KsDataHelper $ksDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksProductHelper;

    /**
     * @var Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Downloadable\Api\SampleRepositoryInterface
     */
    protected $ksSampleRepository;

    /**
     * @var \Magento\Downloadable\Api\LinkRepositoryInterface
     */
    protected $ksLinkRepository;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var StockRegistryInterface
     */
    protected $ksStockRegistry;
 
    /**
     * KsPriceComparison constructor.
     * @param Template\Context $ksContext
     * @param KsDataHelper $ksDataHelper
     * @param Registry $ksRegistry
     * @param KsSellerHelper $ksSellerHelper
     * @param KsProductHelper $ksProductHelper
     * @param SampleRepositoryInterface $ksSampleRepository
     * @param LinkRepositoryInterface $ksLinkRepository
     * @param LoggerInterface $ksLogger
     * @param array $data
     */
    public function __construct(
        Template\Context $ksContext,
        KsDataHelper $ksDataHelper,
        Registry $ksRegistry,
        KsSellerHelper $ksSellerHelper,
        KsProductHelper $ksProductHelper,
        SampleRepositoryInterface $ksSampleRepository,
        LinkRepositoryInterface $ksLinkRepository,
        LoggerInterface $ksLogger,
        StockRegistryInterface $ksStockRegistry,
        array $data = []
    ) {
        $this->ksDataHelper = $ksDataHelper;
        $this->ksRegistry  = $ksRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSampleRepository = $ksSampleRepository;
        $this->ksLinkRepository = $ksLinkRepository;
        $this->ksLogger = $ksLogger;
        $this->ksStockRegistry = $ksStockRegistry;
        parent::__construct($ksContext, $data);
    }

    /**
     * Check Price Range Allowed
     * @return bool
     */
    public function getKsPriceRangeAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_minimum_price_on_product_page');
    }

    /**
     * Check Seller logo allowed
     * @return bool
     */
    public function getKsSellerLogoAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_seller_logo');
    }

    /**
     * Check Product Condition Allowed
     * @return bool
     */
    public function getKsProductConditionAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_product_condition');
    }

    /**
     * Check Product Rating Allowed
     * @return bool
     */
    public function getKsProductRatingAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_product_review_rating');
    }

    /**
     * Check Product Image Allowed
     * @return bool
     */
    public function getKsProductImageAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_product_image');
    }

    /**
     * Check Product Description Allowed
     * @return bool
     */
    public function getKsProductDescriptionAllowed()
    {
        return $this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_show_product_description');
    }

    /**
     * Get Current Product Price Comparison Product
     * @return Collection
     */
    public function getKsCurrentPagePriceComparisonProduct()
    {
        // Get Current Product From Registry
        $ksProduct = $this->ksRegistry->registry('current_product');

        $ksCollection = $this->ksProductHelper->ksPriceComparisonProductListCollection($ksProduct->getId());

        return $ksCollection;
    }

    /**
     * Get Minimum Price of Price Comparison Product
     * @return string
     */
    public function getKsLowestPriceComparison($ksCollection)
    {
        // Get Current Product From Registry
        $ksProduct = $this->ksRegistry->registry('current_product');

        $ksPriceCollection = [number_format((float)$ksProduct->getPrice(), 2)];
        foreach ($ksCollection as $ksProduct) {
            $ksPriceCollection[] = number_format((float)$ksProduct->getPrice(), 2);
        }
        return min($ksPriceCollection);
    }

    /**
     * Get Logo of Store
     * @param int $ksSellerid
     * @return string
     */
    public function getKsSellerStoreLogo($ksSellerId)
    {
        $ksImageDirectory = 'ksolves/multivendor/';
        $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();

        $ksStoreLogo = $this->ksSellerHelper->getKsSellerStoreLogo($ksSellerId, $ksStoreId);
        $ksSellerStoreLogo = "";
        if ($ksStoreLogo) {
            $ksImagePath = $this->ksDataHelper->getKsStoreMediaUrl();
            $ksSellerStoreLogo = $ksImagePath.$ksImageDirectory.$ksStoreLogo;
        }
        return $ksSellerStoreLogo;
    }

    /**
     * Get Name of Store
     * @param int $ksSellerid
     * @return string
     */
    public function getKsSellerStoreName($ksSellerId)
    {
        return $this->ksSellerHelper->getKsSellerStoreName($ksSellerId);
    }

    /**
     * get downloadable product sample data
     * @return arrar
     */
    public function getKsSampleData(string $ksProductSku)
    {
        $ksSamples = [];
        try {
            $ksSamples = $this->ksSampleRepository->getList($ksProductSku);
        } catch (NoSuchEntityException $exception) {
            $this->ksLogger->error($exception->getMessage());
        }
        return $ksSamples;
    }

    /**
     * get downloadable product links data
     * @return arrar
     */
    public function getKsLinksData(string $ksProductSku)
    {
        $ksSamples = [];
        try {
            $ksSamples = $this->ksLinkRepository->getList($ksProductSku);
        } catch (NoSuchEntityException $exception) {
            $this->ksLogger->error($exception->getMessage());
        }
        return $ksSamples;
    }

    /**
     * get current product
     * @return arrar
     */
    public function getKsCurrentCurrency()
    {
        return $this->ksProductHelper->getKsCurrentCurrencySymbol();
    }

    /**
     * get product default qty for add to cart
     * @param ksProduct
     * @return int
     */
    public function getKsProductDefaultQty($ksProduct)
    {
        $ksStockItem = $this->ksStockRegistry->getStockItem($ksProduct->getId(), $ksProduct->getStore()->getWebsiteId());
        $ksMinSaleQty = $ksStockItem->getMinSaleQty();
        $ksQty = $ksMinSaleQty > 0 ? $ksMinSaleQty : null;
        $ksConfig = $ksProduct->getPreconfiguredValues();
        $ksConfigQty = $ksConfig->getQty();
        if ($ksConfigQty > $ksQty) {
            $ksQty = $ksConfigQty;
        }
        return $ksQty;
    }

    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getKsQuantityValidators()
    {
        $ksValidators = [];
        $ksValidators['required-number'] = true;
        return $ksValidators;
    }
}
