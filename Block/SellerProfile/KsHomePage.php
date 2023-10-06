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

namespace Ksolves\MultivendorMarketplace\Block\SellerProfile;

/**
 * KsHomePage Block class
 */
class KsHomePage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param array $ksData = []
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        \Magento\Customer\Model\Session $ksCustomerSession,
        array $ksData = []
    ) {
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        $this->ksCustomerSession = $ksCustomerSession;
        parent::__construct($ksContext, $ksData);
    }

    /**
     * Return customer id
     *
     * @return int
     */
    public function getKsSellerId()
    {
        return $this->ksCustomerSession->getCustomer()->getId();
    }
    
    /**
     * Return seller config data
     *
     * @return string
     */
    public function getKsSellerConfigData()
    {
        $ksCollection = $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId());
        if (count($ksCollection) == 0) {
            $ksModel = $this->ksSellerConfigFactory->create();
            $ksData=[
                "ks_seller_id"               => $this->getKsSellerId(),
                "ks_show_banner"             => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_show_recently_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_recently_products_text"  => $this->escapeHtml('Recently Added Products'),
                "ks_recently_products_count" => 10,
                "ks_show_best_products"      => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_best_products_text"      => $this->escapeHtml('Best Selling Products'),
                "ks_best_products_count"     => 10,
                "ks_show_discount_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_discount_products_text"  => $this->escapeHtml('Most Discounted Products'),
                "ks_discount_products_count" => 10
            ];
            $ksModel->addData($ksData)->save();
        }
        return $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $this->getKsSellerId())->getFirstItem();
    }
}
