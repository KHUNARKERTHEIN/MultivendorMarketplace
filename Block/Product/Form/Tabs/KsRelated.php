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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as KsImageHelper;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * KsRelated block class
 */
class KsRelated extends \Ksolves\MultivendorMarketplace\Block\Product\Form\KsTabs
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $ksAttributeSet;

    /**
     * @var KsImageHelper
     */
    protected $ksImageHelper;

    /**
    * @var CurrencyInterface
    */
    protected $ksLocaleCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;


    /**
     * @param Context $ksContext
     * @param Registry $ksRegistry
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param DataPersistorInterface $ksDataPersistor
     * @param AttributeSetRepositoryInterface $ksAttributeSet
     * @param ProductRepositoryInterface $ksProductRepository
     * @param KsImageHelper $ksImageHelper
     * @param CurrencyInterface $ksLocaleCurrency
     * @param StoreManagerInterface $ksStoreManager
     * @param array $ksData
     */
    public function __construct(
        Context $ksContext,
        Registry $ksRegistry,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        DataPersistorInterface $ksDataPersistor,
        AttributeSetRepositoryInterface $ksAttributeSet,
        ProductRepositoryInterface $ksProductRepository,
        CurrencyInterface $ksLocaleCurrency,
        StoreManagerInterface $ksStoreManager,
        KsImageHelper $ksImageHelper,
        array $ksData = []
    ) {
        $this->ksProductRepository = $ksProductRepository;
        $this->ksAttributeSet = $ksAttributeSet;
        $this->ksImageHelper = $ksImageHelper;
        $this->ksLocaleCurrency    = $ksLocaleCurrency;
        $this->ksStoreManager      = $ksStoreManager;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext, $ksRegistry, $ksDataHelper, $ksProductHelper, $ksDataPersistor, $ksData);
    }

    /**
     * Check Enable to add Related products
     *
     * @return boolean
     */
    public function KsEnabledRelatedProduct()
    {
        return $this->ksDataHelper->getKsConfigProductSetting('ks_related_product');
    }

    /**
     * Check Enable to add Upsell products
     *
     * @return boolean
     */
    public function KsEnabledUpsellProduct()
    {
        return $this->ksDataHelper->getKsConfigProductSetting('ks_up_sell_product');
    }

    /**
     * Check Enable to add Crosssell products
     *
     * @return boolean
     */
    public function KsEnabledCrosssellProduct()
    {
        return $this->ksDataHelper->getKsConfigProductSetting('ks_cross_sell_product');
    }

    /**
     * Retrieve Related products
     *
     * @param string $ksLinkType
     * @return array
     */
    public function getKsLinkProducts($ksLinkType)
    {
        /** @var $ksProduct \Magento\Catalog\Model\Product */
        $ksProduct = $this->getKsProduct();

        if ($ksProduct->getId()) {
            if ($ksLinkType == "related") {
                $ksProductLinkCollection = $ksProduct->getRelatedLinkCollection();
            }
            if ($ksLinkType == "upsell") {
                $ksProductLinkCollection = $ksProduct->getUpSellLinkCollection();
            }
            if ($ksLinkType == "crosssell") {
                $ksProductLinkCollection = $ksProduct->getCrossSellLinkCollection();
            }
        } else {
            $ksProductLinkCollection = [];
        }


        $ksLinkedproductArr = [];

        foreach ($ksProductLinkCollection as $ksLinkItem) {
            $ksLinkproduct = $this->ksProductRepository->getById(
                $ksLinkItem->getLinkedProductId(),
                false,
                0
            );

            $ksImageUrl = $this->ksImageHelper->init($ksLinkproduct, 'product_page_image_small')
                ->setImageFile($ksLinkproduct->getImage()) // image,small_image,thumbnail
                ->resize(70)
                ->getUrl();

            $ksStatusAttr = $ksLinkproduct->getResource()->getAttribute('status');
            $ksStatusText = '';
            if ($ksStatusAttr->usesSource()) {
                $ksStatusText = $ksStatusAttr->getSource()->getOptionText($ksLinkproduct->getStatus());
            }

            $ksStore = $this->ksStoreManager->getStore(0);
            $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());
            $ksPrice = ($ksLinkproduct->getPrice()) ? $ksCurrency->toCurrency(sprintf("%f", $ksLinkproduct->getPrice())) : '';

            $ksLinkedproductArr[] = [
                'id' => $ksLinkproduct->getId(),
                'thumbnail' => $ksImageUrl,
                'name' => $ksLinkproduct->getName(),
                'status' => $ksStatusText,
                'attributeset' => $this->ksAttributeSet->get($ksLinkproduct->getAttributeSetId())->getAttributeSetName(),
                'sku' => $ksLinkproduct->getSku(),
                'price' => $ksPrice,
                'position' => $ksLinkItem->getPosition()
            ];
        }
        array_multisort(array_column($ksLinkedproductArr, "position"), SORT_ASC, $ksLinkedproductArr);
        return $ksLinkedproductArr;
    }
}
