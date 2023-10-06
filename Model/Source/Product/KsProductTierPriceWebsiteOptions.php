<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves
 * @copyright Copyright (c) Ksolves India Limited (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Model\Source\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Directory\Helper\Data;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * KsProductCondition Model Class
 */
class KsProductTierPriceWebsiteOptions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $ksDirectoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * Initialize constructor
     * @param StoreManagerInterface $ksStoreManager
     * @param Registry $ksRegistry
     * @param ProductRepositoryInterface $ksProductRepository
     * @param Data $ksDirectoryHelper
     */
    public function __construct(
        StoreManagerInterface $ksStoreManager,
        Registry $ksRegistry,
        ProductRepositoryInterface $ksProductRepository,
        Data $ksDirectoryHelper
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksCoreRegistry = $ksRegistry;
        $this->ksDirectoryHelper = $ksDirectoryHelper;
        $this->ksProductRepository     = $ksProductRepository;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $ksWebsites = [
            [
                'label' => __('All Websites') . ' [' . $this->ksDirectoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $ksProductId = $this->ksCoreRegistry->registry('ks_product_id');
        $ksProduct = $this->ksProductRepository->getById($ksProductId, true, 0);

        if (!$this->ksIsScopeGlobal() && $ksProduct->getStoreId()) {
            /** @var \Magento\Store\Model\Website $ksWebsite */
            $ksWebsite = $this->getStore()->getWebsite();

            $ksWebsites[] = [
                'label' => $ksWebsite->getName() . '[' . $ksWebsite->getBaseCurrencyCode() . ']',
                'value' => $ksWebsite->getId(),
            ];
        } elseif (!$this->ksIsScopeGlobal()) {
            $ksWebsitesList = $this->ksStoreManager->getWebsites();
            $ksProductWebsiteIds = $ksProduct->getWebsiteIds();
            foreach ($ksWebsitesList as $ksWebsite) {
                /** @var \Magento\Store\Model\Website $ksWebsite */
                if (!in_array($ksWebsite->getId(), $ksProductWebsiteIds)) {
                    continue;
                }
                $ksWebsites[] = [
                    'label' => $ksWebsite->getName() . '[' . $ksWebsite->getBaseCurrencyCode() . ']',
                    'value' => $ksWebsite->getId(),
                ];
            }
        }

        return $ksWebsites;
    }

    /**
     * Check tier_price attribute scope is global
     *
     * @return bool
     */
    private function ksIsScopeGlobal()
    {
        $ksProductId = $this->ksCoreRegistry->registry('ks_product_id');
        $ksProduct = $this->ksProductRepository->getById($ksProductId, true, 0);
        return $ksProduct->getResource()->getAttribute(ProductAttributeInterface::CODE_TIER_PRICE)->isScopeGlobal();
    }
}
