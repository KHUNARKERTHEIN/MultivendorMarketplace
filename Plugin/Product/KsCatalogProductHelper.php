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

namespace Ksolves\MultivendorMarketplace\Plugin\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class KsCatalogProductHelper
 * @package Ksolves\MultivendorMarketplace\Plugin\Product
 */
class KsCatalogProductHelper
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * KsCatalogProductHelper constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Catalog\Helper\Product $ksSubject
     * @param $product
     * @param string $where
     * @return false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCanShow(\Magento\Catalog\Helper\Product $ksSubject, callable $proceed, $product, $where = 'catalog')
    {
        if (!$this->ksProductHelper->ksSellerProductFilter($product->getId())) {
            return false;
        } else {
            return $proceed($product, $where = 'catalog');
        }
    }
}
