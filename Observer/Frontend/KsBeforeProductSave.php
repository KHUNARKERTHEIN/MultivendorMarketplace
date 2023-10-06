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

namespace Ksolves\MultivendorMarketplace\Observer\Frontend;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Product;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;

/**
 * Class KsBeforeProductSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsBeforeProductSave implements ObserverInterface
{
    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
    * @var KsProductHelper
    */
    protected $ksProductHelper;

    /**
     * @var KsSellerProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * KsBeforeProductSave constructor.
     * @param Http $ksRequest
      * @param KsSellerHelper $ksSellerHelper
      * @param KsDataHelper $ksDataHelper
      * @param KsProductHelper $ksProductHelper
      * @param KsSellerProductFactory $ksSellerProductFactory
     */
    public function __construct(
        Http $ksRequest,
        KsSellerHelper $ksSellerHelper,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        KsSellerProductFactory $ksSellerProductFactory
    ) {
        $this->ksRequest            = $ksRequest;
        $this->ksSellerHelper       = $ksSellerHelper;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksProductHelper      = $ksProductHelper;
        $this->ksSellerProductFactory  = $ksSellerProductFactory;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        try {
            if (!$this->ksRequest->getParam('parent_id')) {
                $ksProduct = $ksObserver->getProduct();
                $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
                $this->checkAllowedProduct($ksProduct, $ksSellerId);
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    protected function checkAllowedProduct(Product $ksProduct, int $ksSellerId)
    {
        $ksSellerWebsite = $this->ksSellerHelper->getksSellerWebsiteId($ksSellerId);
        if (!$ksProduct->getWebsiteIds()) {
            $ksProduct->setWebsiteIds([0=>$ksSellerWebsite]);
        }
        $ksCheckSellerWebsite = $this->ksProductHelper->ksCheckSellerProductWebsite($ksProduct->getWebsiteIds(), $ksSellerWebsite);

        $ksCustomerScope = $this->ksDataHelper->getKsConfigCustomerScopeField("scope");

        if ($ksCheckSellerWebsite || !$ksCustomerScope) {
            if (!$this->ksProductHelper->ksIsProductTypeAllowed($ksProduct->getTypeId(), $ksSellerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This product type is not allowed to you.')
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This website product is not allowed for you.')
            );
        }

        $ksProductCount=$this->ksSellerProductFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('ks_seller_id', $ksSellerId)
            ->addFieldToFilter('ks_parent_product_id', ['eq' => 0])
            ->getSize();

        // check for has value
        if ($this->ksDataHelper->getKsConfigProductSetting('ks_seller_product_limit') != null) {
            // check for product id
            if (!$this->ksRequest->getParam('id')) {
                // check for product limit
                if ($ksProductCount >= $this->ksDataHelper->getKsConfigProductSetting('ks_seller_product_limit')) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Reached product limit.')
                    );
                }
            }
        }
    }
}
