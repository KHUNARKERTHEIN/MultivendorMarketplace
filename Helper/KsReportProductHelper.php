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
 * KsReportProductHelper class
 */
class KsReportProductHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KS_STATUS_ENABLED = 1;
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProduct
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory $ksReasonFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProduct $ksProductFactory
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsReportProductReasonsFactory $ksReasonFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($ksContext);
        $this->ksReasonFactory = $ksReasonFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * report product reasons list
     *
     * @return int
     */
    public function getKsProductReportReasons()
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
     * set product report count
     * @param integer $ksProductId
     */
    public function setKsSellerReportCount($ksProductId)
    {
        $ksProductDetails = $this->ksProductFactory->create()->getCollection()->addFieldToFilter('ks_product_id', $ksProductId)->getFirstItem();
        if ($ksProductDetails) {
            $this->ksProductFactory->create()->load($ksProductDetails->getId())->setKsReportCount($ksProductDetails->getKsReportCount()+1)->save();
        }
    }
}
