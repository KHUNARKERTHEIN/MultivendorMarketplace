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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Ksolves\MultivendorMarketplace\Model\KsSeller;

/**
 * KsCustomerLoginObserver Observer Class
 */
class KsCustomerLoginObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $ksCoreSession;

    /**
     * @param CollectionFactory $ksCollectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     */
    public function __construct(
        CollectionFactory $ksCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Magento\Framework\Session\SessionManagerInterface $ksCoreSession
    ) {
        $this->ksCollectionFactory    = $ksCollectionFactory;
        $this->ksMessageManager       = $ksMessageManager;
        $this->ksCoreSession       = $ksCoreSession;
    }

    /**
     * customer login event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->ksCoreSession->start();
            // get value from session to check the customer create his account or login
            $ksIsCustomerRegister = $this->ksCoreSession->getIsCustomerRegister();
            // check sesssion value
            if (!$ksIsCustomerRegister) {
                $ksCustomer = $observer->getEvent()->getCustomer();
                // get logged in customer id
                $ksCustomerId = $ksCustomer->getId();
                // get seller collection by seller id
                $ksSellerCollection = $this->ksCollectionFactory->create()
                                        ->addFieldToFilter('ks_seller_id', $ksCustomerId);
                // check the current customer is seller or not
                if ($ksSellerCollection->getSize()) {
                    foreach ($ksSellerCollection as $ksData) {
                        // check seller status
                        if ($ksData->getKsSellerStatus() == KsSeller::KS_STATUS_PENDING) {
                            // msg for pending seller
                            $this->ksMessageManager->addNotice(__('Your seller account creation request is in pending
                            state. Please contact admin for more information.'));
                        } elseif ($ksData->getKsSellerStatus() == KsSeller::KS_STATUS_REJECTED) {
                            // msg for rejected seller
                            $this->ksMessageManager->addError(__('Your seller account has been rejected.
                            Please contact admin.'));
                        }
                    }
                }
            }
            // unset the session
            $this->ksCoreSession->unsMessage();
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
    }
}
