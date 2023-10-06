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

namespace Ksolves\MultivendorMarketplace\Plugin\Checkout;

use Ksolves\MultivendorMarketplace\Helper\KsCheckoutHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\UrlFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Controller\Index\Index;

/**
 * KsOnepageLink class
 */
class KsOnepageLink
{
    /**
     * @var KsCheckoutHelper
     */
    protected $ksCheckoutHelper;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

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
     * @param KsSellerHelper $ksSellerHelper
     * @param Cart $ksCartHelper
     * @param \Magento\Framework\UrlFactory $ksUrlFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksSalesOrder
     */
    public function __construct(
        KsCheckoutHelper $ksCheckoutHelper,
        KsSellerHelper $ksSellerHelper,
        Cart $ksCartHelper,
        UrlFactory $ksUrlFactory,
        RedirectFactory $ksResultRedirectFactory,
        ManagerInterface $ksMessageManager
    ) {
        $this->ksCheckoutHelper = $ksCheckoutHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCartHelper = $ksCartHelper;
        $this->ksUrlFactory = $ksUrlFactory;
        $this->ksResultRedirectFactory = $ksResultRedirectFactory;
        $this->ksMessageManager = $ksMessageManager;
    }
 
    public function afterIsDisabled(
        \Magento\Checkout\Block\Onepage\Link $ksSubject,
        $ksResult
    ) {
        if ($this->ksCheckoutHelper->getKsIsMinimumAmtEnabled() && $this->ksCheckoutHelper->getKsIsStoreMinimumAmtEnabled()) {
            $ksQuote = $this->ksCartHelper->getCart()->getQuote();
            $ksCartItems = $ksQuote->getAllItems();
            $ksSellerTotals = [];
            $ksSellerTotals = $this->ksCheckoutHelper->getKsSellerTotals($ksCartItems);
            /*check seller totals*/
            foreach ($ksSellerTotals as $ksSellerId => $ksTotal) {
                if (!$this->ksCheckoutHelper->ksValidateSellerTotal($ksSellerId, $ksTotal)) {
                    $this->ksMessageManager->addNotice($this->ksCheckoutHelper->getKsMinimumOrderMessage($ksSellerId));
                    $ksResult = true;
                }
            }
        }
        return $ksResult;
    }
}
