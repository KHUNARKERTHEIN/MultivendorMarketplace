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

namespace Ksolves\MultivendorMarketplace\Helper;

use Magento\Framework\App\Helper\Context;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Magento\Backend\Model\Session;

/**
 * KsCheckoutHelper class
 */
class KsCheckoutHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var StoreManager
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $ksPriceHelper;

    /**
     * @var Session
     */
    protected $ksSession;

    /**
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     * @param KsSellerCollectionFactory $ksSellerCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param \Magento\Framework\Pricing\Helper\Data $ksPriceHelper
     * @param Session $ksSession
     */
    public function __construct(
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        KsSellerCollectionFactory $ksSellerCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        \Magento\Framework\Pricing\Helper\Data $ksPriceHelper,
        Session $ksSession
    ) {
        $this->ksDataHelper               = $ksDataHelper;
        $this->ksProductHelper            = $ksProductHelper;
        $this->ksSellerCollectionFactory  = $ksSellerCollectionFactory;
        $this->ksSellerHelper             = $ksSellerHelper;
        $this->ksScopeConfig              = $ksScopeConfig;
        $this->ksStoreManager             = $ksStoreManager;
        $this->ksSession                  = $ksSession;
        $this->ksPriceHelper              = $ksPriceHelper;
    }

    /**
     * Collect totals for all the sellers whose products are present in the cart
     *
     * @param Quote Items $ksItems
     * @return Integer
     */
    public function getKsSellerTotals($ksCartItems)
    {
        $ksSellerTotals = [];
        foreach ($ksCartItems as $ksItem) {
            if (($ksSellerId = $this->ksProductHelper->getKsSellerId($ksItem->getProductId())) && $this->getKsIsMinimumAmtEnabled()) {
                if (isset($ksSellerTotals[$ksSellerId])) {
                    $ksSellerTotals[$ksSellerId] = $ksSellerTotals[$ksSellerId] + $this->ksCalcTotal($ksItem);
                } else {
                    $ksSellerTotals[$ksSellerId] = $this->ksCalcTotal($ksItem);
                }
            }
        }
        return $ksSellerTotals;
    }

    /**
     * Collect totals for item based on tax and discount
     *
     * @param Quote Items $ksItems
     * @return Integer
     */
    public function ksCalcTotal($ksItem)
    {
        $ksTotalAmt = $ksItem->getBaseRowTotal();
        if ($this->getKsInclDiscountAmount()) {
            $ksTotalAmt = $ksItem->getBaseRowTotal() - $ksItem->getBaseDiscountAmount();
        }
        if ($this->getKsInclTaxAmount()) {
            $ksTotalAmt += $ksItem->getBaseTaxAmount();
        }
        return $ksTotalAmt;
    }

    /**
     * Validate that the total amount in cart for seller products is less than minimum order amount
     * @param Quote Items $ksItems
     * @return Integer
     */
    public function ksValidateSellerTotal($ksSellerId, $ksTotals)
    {
        $ksMinOrderAmt = $this->getKsMinimumOrderAmt($ksSellerId);
        return !($ksMinOrderAmt > $ksTotals);
    }

    /**
     * @return Boolean
     */
    public function getKsIsMinimumAmtEnabled()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_minimum_order_amount_settings_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * @return Boolean
     */
    public function getKsIsStoreMinimumAmtEnabled()
    {
        return $this->ksScopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }


    /**
     * Check if seller is allowed to set minimum order amount
     * @return Boolean
     */
    public function getKsAllowSellerSetMinAmt()
    {
        return $this->ksScopeConfig->isSetFlag(
            'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_allow_seller_set_minimum_order_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->ksStoreManager->getStore()->getId()
        );
    }

    /**
     * Get Seller specific minimum order amount or global amount based on configurations
     *
     * @param Integer $ksSellerId
     * @return Integer
     */
    public function getKsMinimumOrderAmt($ksSellerId)
    {
        if (!$this->getKsAllowSellerSetMinAmt() && $this->getKsIsMinimumAmtEnabled()) {
            return $this->ksScopeConfig->getValue(
                'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_default_minimum_order_amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->ksStoreManager->getStore()->getId()
            );
        } else {
            $ksMinAmount = $this->ksSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getKsMinOrderAmount();
            if (is_null($ksMinAmount)) {
                $ksMinAmount = $this->ksScopeConfig->getValue(
                    'ks_marketplace_sales/ks_minimum_order_amount_settings/ks_default_minimum_order_amount',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->ksStoreManager->getStore()->getId()
                );
            }
            return $ksMinAmount;
        }
    }

    /**
     * Get Seller specific minimum order message to be dispalyed in case of non fulfillment on minimum order amount
     *
     * @param Integer $ksSellerId
     * @return String
     */
    public function getKsMinimumOrderMessage($ksSellerId)
    {
        $ksMessage = $this->ksSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getKsMinOrderMessage();
        
        if (is_null($ksMessage) || !$this->getKsAllowSellerSetMinAmt()) {
            $ksMessage = $this->ksScopeConfig->getValue(
                'sales/minimum_order/description',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->ksStoreManager->getStore()->getId()
            );
        }
        if (!$ksMessage) {
            $ksMinimumAmount =  $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol().$this->getKsMinimumOrderAmt($ksSellerId);
            $ksMessage = __('Minimum order amount is '.$ksMinimumAmount);
        }
        return $ksMessage;
    }

    /**
     * @return Integer
     */
    public function getKsInclTaxAmount()
    {
        return $this->ksScopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->ksStoreManager->getStore()->getWebsiteId()
        );
    }

    /**
     * @return Integer
     */
    public function getKsInclDiscountAmount()
    {
        return $this->ksScopeConfig->isSetFlag(
            'sales/minimum_order/include_discount_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $this->ksStoreManager->getStore()->getWebsiteId()
        );
    }

    /**
     * @param Integer
     * @return Integer
     */
    public function getKsSellerId($ksProductId)
    {
        return $this->ksProductHelper->getKsSellerId($ksProductId);
    }
}
