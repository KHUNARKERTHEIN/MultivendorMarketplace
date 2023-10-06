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
 * Class KsAfterMemoSave
 * @package Ksolves\MultivendorMarketplace\Observer
 */
class KsAfterMemoSave implements ObserverInterface
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
        $ksCreditmemo = $ksObserver->getEvent()->getCreditmemo();
        $this->ksOrderHelper->setKsSellerCreditmemo($ksCreditmemo);
    }
}
