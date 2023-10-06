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

namespace Ksolves\MultivendorMarketplace\Block\Checkout\Cart;

use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsSellerDetails Block Class
 */
class KsSellerDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * Constructor KsSellerDetails
     *
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper,
        array $data = []
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $data);
    }

    /**
     * Get Seller Id
     *
     * @return int
     */
    public function getKsSellerId()
    {
        $ksItem = $this->getParentBlock()->getItem();

        if ($ksItem->getProduct()) {
            return $this->ksProductHelper->getKsSellerId($ksItem->getProduct()->getId());
        }
        return 0;
    }

    /**
     * Get Seller Name
     *
     * @return String
     */
    public function getKsSellerName()
    {
        return $this->ksSellerHelper->getKsSellerName($this->getKsSellerId());
    }

    /**
     * Get Seller Url
     *
     * @return Url
     */
    public function getKsSellerUrl()
    {
        return $this->getBaseUrl()."multivendor/sellerprofile/sellerprofile/seller_id/".$this->getKsSellerId();
    }
}
