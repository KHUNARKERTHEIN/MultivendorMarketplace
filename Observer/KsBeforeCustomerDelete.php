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
namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Class KsBeforeProductSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsBeforeCustomerDelete implements ObserverInterface
{

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var  KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * KsBeforeProductSave constructor.
     * @param KsProductHelper $ksProductHelper
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        KsProductHelper $ksProductHelper,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksProductHelper = $ksProductHelper;
        $this->ksSellerHelper  = $ksSellerHelper;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        try {
            $ksCustomer = $ksObserver->getCustomer();
            $ksCustomerId = $ksCustomer->getId();
            $ksCustomerEmail = $ksCustomer->getEmail();
            $this->ksProductHelper->ksChangeProductStatus($ksCustomerId, $ksCustomerEmail);

            //delete seller store url rewrite
            $ksTargetPathUrl = "multivendor/sellerprofile/sellerprofile/seller_id/".$ksCustomerId.'/';
            $this->ksSellerHelper->ksRedirectUrlDelete($ksTargetPathUrl);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
