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

/**
 * KsReportSellerHelper class
 */
class KsReportSellerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KS_STATUS_ENABLED = 1;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory $ksReasonFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory $ksReasonFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($ksContext);
        $this->ksReasonFactory = $ksReasonFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * report seller reasons list
     *
     * @return int
     */
    public function getKsSellerReportReasons()
    {
        $ksCollection = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('ks_reason_status', self::KS_STATUS_ENABLED);
        return $ksCollection;
    }

    /**
     * get current customer
     * @return Int
     */
    public function getKsCurrentCustomer()
    {
        if ($this->customerSession->getCustomer()->getId()) {
            return $this->customerSession->getCustomer();
        } else {
            return 0;
        }
    }

    /**
     * get current customer id
     * @return Int
     */
    public function getKsCurrentCustomerId()
    {
        if ($this->getKsCurrentCustomer()) {
            return $this->getKsCurrentCustomer()->getId();
        } else {
            return 0;
        }
    }

    /**
     * set seller report count
     * @param integer $ksSellerId
     */
    public function setKsSellerReportCount($ksSellerId)
    {
        $ksSellerDetails = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        if ($ksSellerDetails) {
            $this->ksSellerFactory->create()->load($ksSellerDetails->getId())->setKsReportCount($ksSellerDetails->getKsReportCount()+1)->save();
        }
    }
}
