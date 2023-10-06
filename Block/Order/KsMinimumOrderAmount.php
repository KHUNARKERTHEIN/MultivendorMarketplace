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

namespace Ksolves\MultivendorMarketplace\Block\Order;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCheckoutHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * KsMinimumOrderAmount block
 */
class KsMinimumOrderAmount extends \Magento\Framework\View\Element\Template
{
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
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsCheckoutHelper
     */
    protected $ksCheckoutHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $ksPriceCurrencyInterface;

    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface  $ksStoreManager
     * @param KsSellerHelper  $ksSellerHelper
     * @param KsCheckoutHelper  $ksCheckoutHelper
     * @param PriceCurrencyInterface $ksPriceCurrencyInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $ksContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $ksScopeConfig,
        \Magento\Store\Model\StoreManagerInterface  $ksStoreManager,
        KsSellerHelper $ksSellerHelper,
        KsCheckoutHelper $ksCheckoutHelper,
        PriceCurrencyInterface $ksPriceCurrencyInterface,
        array $data = []
    ) {
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCheckoutHelper = $ksCheckoutHelper;
        $this->ksPriceCurrencyInterface = $ksPriceCurrencyInterface;
        parent::__construct($ksContext, $data);
    }

    /**
     * @return Integer
     */
    public function getKsSellerId()
    {
        if ($this->ksSellerHelper->ksIsSeller()) {
            return $this->ksSellerHelper->getKsCustomerId();
        }
        return 0;
    }

    /**
     * @return Integer
     */
    public function getKsMinimumOrderAmt()
    {
        return $this->ksCheckoutHelper->getKsMinimumOrderAmt($this->getKsSellerId());
    }

    /**
     * @return String
     */
    public function getKsMinimumOrderMessage()
    {
        return $this->ksCheckoutHelper->getKsMinimumOrderMessage($this->getKsSellerId());
    }

    /**
     * @return Integer
     */
    public function ksCanEditAmt()
    {
        return $this->ksCheckoutHelper->getKsAllowSellerSetMinAmt();
    }

    /**
     * @return String
     */
    public function getKsCurrencySymbol()
    {
        return $this->ksStoreManager->getStore()->getBaseCurrency()->getCurrencySymbol();
    }
}
