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
 
use Magento\Framework\View\Element\Template;

/**
 * KsSubmitQuery Block class
 */
class KsSubmitQuery extends Template
{

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
     */
    protected $ksSellerCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory
     */
    protected $ksBenefitsFactory;
    
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory
     */
    protected $ksHowItWorksFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory $ksHowItWorksFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param array $ksData = []
     */
    public function __construct(
        Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Model\KsBenefitsFactory $ksBenefitsFactory,
        \Ksolves\MultivendorMarketplace\Model\KsHowItWorksFactory $ksHowItWorksFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        array $ksData = []
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksBenefitsFactory = $ksBenefitsFactory;
        $this->ksHowItWorksFactory = $ksHowItWorksFactory;
        $this->ksSellerHelper       = $ksSellerHelper;
        parent::__construct($ksContext, $ksData);
    }
    
    /**
     * prepare layout
     *
     * @return this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
 
    /**
     * Returns action url for Green Enquire
     * @return string
     */
    public function getKsFormAction()
    {
        return $this->getUrl('multivendor/sellerprofile/post', ['_secure' => true]);
    }

    /**
     * Returns seller collection
     * @return array
     */
    public function getKsSellerCollection()
    {
        $ksCollection = $this->ksSellerCollection->create()->addFieldToFilter('ks_store_status',\Ksolves\MultivendorMarketplace\Model\KsSellerStore::KS_STATUS_ENABLED)->addFieldToFilter('ks_seller_status',\Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_APPROVED);

        if($this->ksSellerHelper->getKsCustomerConfigScope() == 1){
            $ksCustomerTable = $ksCollection->getTable('customer_entity');
            $ksCollection->getSelect()->joinLeft(
                $ksCustomerTable. ' as kc',
                'ks_seller_id = kc.entity_id',
                array('website_id'=>'website_id'))
            ->where('website_id = ' . $this->ksStoreManager->getStore()->getWebsiteId());
        }

        $ksCollection->setOrder('ks_report_count','ASC');
        $ksCollection->setPageSize(6);
        $ksCollection->setCurPage(1);
        return $ksCollection;
    }

    /**
     * Return store id
     *
     * @return int
     */
    public function getKsStoreId()
    {
        return $this->ksStoreManager->getStore()->getId();
    }
    
    /**
     * Return benefits collection
     *
     * @return array
     */
    public function getKsBenefitsCollection()
    {
        return $this->ksBenefitsFactory->create()->getCollection();
    }

    /**
     * Return how it works collection
     *
     * @return array
     */
    public function getKsHowItWorksCollection()
    {
        return $this->ksHowItWorksFactory->create()->getCollection();
    }

    /**
     * Get delete action url for favourite seller
     *
     * @return url
     */
    public function getKsDeleteAction()
    {
        return $this->getUrl('multivendor/favouriteSeller/delete', ['_secure' => true]);
    }

    /**
     * Get seller profile redirect url
     * @param $ksSellerId
     * @return url
     */
    public function getKsSellerProfileUrl($ksSellerId)
    {
        return $this->ksSellerHelper->getKsSellerProfileUrl($ksSellerId);
    }
}
