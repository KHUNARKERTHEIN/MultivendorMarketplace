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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * PriceComparisonProductList Controller class
 */
class PriceComparisonProductList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * Initialize constructor.
     * @param Context $context
     * @param KsProductHelper $ksProductHelper
     * @param LayoutFactory $ksResultLayoutFactory
     * @param ProductRepositoryInterface $ksProductRepository
     */
    public function __construct(
        Context $context,
        KsProductHelper $ksProductHelper,
        LayoutFactory $ksResultLayoutFactory,
        ProductRepositoryInterface $ksProductRepository
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        $this->ksProductRepository = $ksProductRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\LayoutFactory
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue('product')) {

            // Get product id
            $ksProductId = $this->getRequest()->getPostValue('product');

            $ksResultPage = $this->ksResultLayoutFactory->create();

            $block = $ksResultPage->getLayout()->getBlock('ks_multivendormarketplace_price_comparsion')
            ->setData('ks_product_collection', $this->ksProductHelper->ksPriceComparisonProductListCollection($ksProductId));

            $block = $ksResultPage->getLayout()->getBlock('ks_multivendormarketplace_price_comparsion')
            ->setData('ks_product_type', $this->getKsParentProductType($ksProductId));
            $block = $ksResultPage->getLayout()->getBlock('ks_multivendormarketplace_price_comparsion')
            ->setData('ks_current_product_id', $ksProductId);

            return $ksResultPage;
        }
    }

    /**
     * get product type
     * @param $ksProductId
     * @return string
     */
    public function getKsParentProductType($ksProductId)
    {
        $ksProduct = $this->ksProductRepository->getById($ksProductId, true, 0);
        $ksProduct->getTypeId();
        return $ksProduct->getTypeId();
    }
}
