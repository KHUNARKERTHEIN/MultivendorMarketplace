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

namespace Ksolves\MultivendorMarketplace\Block\Account;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsBecomeSeller block class
 */
class KsBecomeSeller extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $ksCollectionFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param CollectionFactory $ksCollectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        CollectionFactory $ksCollectionFactory,
        KsSellerHelper $ksSellerHelper,
        array $ksData = []
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Get Seller Details
     * @return collection
     */
    public function getKsSellerDetails()
    {
        $ksCustomerId = $this->ksSellerHelper->getKsCustomerIdFromSessionFactory();
        $ksSellerCollection = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksCustomerId);
        return $ksSellerCollection;
    }

    /**
     * Check State of Seller
     * @return  int
     */
    public function ksCheckSellerState()
    {
        $ksSellerStatus = 1;
        $ksSellerCollection = $this->getKsSellerDetails();
        if ($ksSellerCollection->getSize()) {
            $ksSellerStatus = $ksSellerCollection->getFirstItem()->getKsSellerStatus();
        }
        return $ksSellerStatus;
    }

    /**
     * Get Store Details
     * @return array
     */
    public function ksGetStoreDetails()
    {
        $ksSellerCollection = $this->getKsSellerDetails();
        $ksStoreDetails = [];
        if ($ksSellerCollection->getSize()) {
            $ksStoreDetails['store-name'] = $ksSellerCollection->getFirstItem()->getKsStoreName();
            $ksStoreDetails['store-url'] = $ksSellerCollection->getFirstItem()->getKsStoreUrl();
            $ksStoreDetails['reason'] = $ksSellerCollection->getFirstItem()->getKsRejectionReason();
        }
        return $ksStoreDetails;
    }
}
