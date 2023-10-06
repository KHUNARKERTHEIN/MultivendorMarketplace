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
use \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * KsBecomeSellerLink block class
 */
class KsBecomeSellerLink extends \Magento\Framework\View\Element\Html\Link\Current
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
     * @param \Magento\Framework\App\DefaultPathInterface $ksDefaultPath
     * @param CollectionFactory $ksCollectionFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param array $ksData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Framework\App\DefaultPathInterface $ksDefaultPath,
        CollectionFactory $ksCollectionFactory,
        KsSellerHelper $ksSellerHelper,
        array $ksData = []
    ) {
        $this->ksCollectionFactory = $ksCollectionFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext, $ksDefaultPath, $ksData);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $ksCustomerId = $this->ksSellerHelper->getKsCustomerIdFromSessionFactory();
        $ksSellerCollection = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksCustomerId)->addFieldToFilter('ks_seller_status', ['eq' => 1]);
        // check customer who is not registered as seller
        if (!$ksSellerCollection->getSize()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Check State of Seller
     * @return  int
     */
    public function ksCheckSellerState()
    {
        $ksSellerStatus = 1;
        $ksCustomerId = $this->ksSellerHelper->getKsCustomerIdFromSessionFactory();
        $ksSellerCollection = $this->ksCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksCustomerId);
        if ($ksSellerCollection->getSize()) {
            $ksSellerStatus = $ksSellerCollection->getFirstItem()->getKsSellerStatus();
        }
        return $ksSellerStatus;
    }
}
