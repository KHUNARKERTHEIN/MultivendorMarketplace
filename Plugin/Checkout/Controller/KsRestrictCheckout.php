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

namespace Ksolves\MultivendorMarketplace\Plugin\Checkout\Controller;

use Ksolves\MultivendorMarketplace\Helper\KsCheckoutHelper;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Controller\Index\Index;

/**
 * KsRestrictCheckout class
 */
class KsRestrictCheckout
{
    /**
     * @var KsCheckoutHelper
     */
    protected $ksCheckoutHelper;

    /**
     * @var Cart
     */
    protected $ksCartHelper;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $ksUrlFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $ksResultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;
    
    /**
     * @param KsCheckoutHelper $ksCheckoutHelper
     * @param Cart $ksCartHelper
     * @param \Magento\Framework\UrlFactory $ksUrlFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksSalesOrder
     */
    public function __construct(
        KsCheckoutHelper $ksCheckoutHelper,
        Cart $ksCartHelper,
        UrlFactory $ksUrlFactory,
        RedirectFactory $ksResultRedirectFactory,
        ManagerInterface $ksMessageManager
    ) {
        $this->ksCheckoutHelper = $ksCheckoutHelper;
        $this->ksCartHelper = $ksCartHelper;
        $this->ksUrlFactory = $ksUrlFactory;
        $this->ksResultRedirectFactory = $ksResultRedirectFactory;
        $this->ksMessageManager = $ksMessageManager;
    }
    
    /**
     * Collect totals for all the sellers whose products are present in the cart
     *
     * @param Index $ksSubject
     * @param \Closure $ksProceed
     * @return $ksProceed()
     */
    public function aroundExecute(
        Index $ksSubject,
        \Closure $ksProceed
    ) {
        $ksFlag = false;
        if ($this->ksCheckoutHelper->getKsIsMinimumAmtEnabled() && $this->ksCheckoutHelper->getKsIsStoreMinimumAmtEnabled()) {
            $ksQuote = $this->ksCartHelper->getCart()->getQuote();
            $ksCartItems = $ksQuote->getAllItems();
            $ksSellerTotals = [];
            $ksSellerTotals = $this->ksCheckoutHelper->getKsSellerTotals($ksCartItems);
            foreach ($ksSellerTotals as $ksSellerId => $ksTotal) {
                if (!$this->ksCheckoutHelper->ksValidateSellerTotal($ksSellerId, $ksTotal)) {
                    $ksFlag = true;
                }
            }
        }
        if ($ksFlag) {
            $ksCartUrl = $this->ksUrlFactory->create()->getUrl('checkout/cart/', ['_secure' => true]);
            $resultRedirect = $this->ksResultRedirectFactory->create();
            return $resultRedirect->setUrl($ksCartUrl);
        }
        return $ksProceed();
    }
}
