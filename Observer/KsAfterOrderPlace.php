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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class KsAfterOrderPlace
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsAfterOrderPlace implements ObserverInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper
     */
    protected $ksOrderHelper;

    public function __construct(
        \Ksolves\MultivendorMarketplace\Helper\KsOrderHelper $ksOrderHelper
    ) {
        $this->ksOrderHelper         = $ksOrderHelper;
    }

    /**
     * @param Observer $ksObserver
     */
    public function execute(Observer $ksObserver)
    {
        if ($ksObserver->getEvent()->getOrder()) {
            $ksOrder = $ksObserver->getEvent()->getOrder();
            $this->ksOrderHelper->setKsOrderData($ksOrder);
            //save invoices if created due to online payment
            if ($ksOrder->hasInvoices()) {
                foreach ($ksOrder->getInvoiceCollection() as $ksInvoice) {
                    $this->ksOrderHelper->setKsSellerInvoice($ksInvoice);
                }
            }
        } elseif ($ksObserver->getEvent()->getOrders()) {
            foreach ($ksObserver->getEvent()->getOrders() as $ksOrder) {
                $this->ksOrderHelper->setKsOrderData($ksOrder);
                //save invoices if created due to online payment
                if ($ksOrder->hasInvoices()) {
                    foreach ($ksOrder->getInvoiceCollection() as $ksInvoice) {
                        $this->ksOrderHelper->setKsSellerInvoice($ksInvoice);
                    }
                }
            }
        }
    }
}
