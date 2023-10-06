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
 * KsTopSellers Block class
 */
class KsTopSellers extends Template
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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Template\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param array $ksData = []
     */
    public function __construct(
        Template\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        array $ksData = []
    ) {
        $this->ksStoreManager = $ksStoreManager;
        $this->ksSellerCollection = $ksSellerCollection;
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
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Top Sellers'));
        if ($this->getKsSellerCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ks-top-seller-pager'
            )->setAvailableLimit([20 => 20, 25 => 25, 30 =>30, 35 => 35, 40 => 40])
                ->setShowPerPage(true)->setCollection(
                    $this->getKsSellerCollection()
                );
            $this->setChild('pager', $pager);
            $this->getKsSellerCollection()->load();
        }
        return $this;
    }
 
    /**
     * Returns seller collection
     * @return array
     */
    public function getKsSellerCollection()
    {
        $ksPage = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $ksPageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest(
        )->getParam('limit') : 20;

        $ksCollection = $this->ksSellerCollection->create()->addFieldToFilter('ks_store_status', \Ksolves\MultivendorMarketplace\Model\KsSellerStore::KS_STATUS_ENABLED)->addFieldToFilter('ks_seller_status', \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_APPROVED);

        if($this->ksSellerHelper->getKsCustomerConfigScope() == 1){
            $ksCustomerTable = $ksCollection->getTable('customer_entity');
            $ksCollection->getSelect()->joinLeft(
                $ksCustomerTable. ' as kc',
                'ks_seller_id = kc.entity_id',
                array('website_id'=>'website_id'))
            ->where('website_id = ' . $this->ksStoreManager->getStore()->getWebsiteId());
        }

        if ($this->getPostValue()) {
            $ksCollection->addFieldToFilter('ks_store_name', ['like' => '%'.$this->getPostValue().'%']);
        }

        $ksCollection->setOrder('ks_report_count', 'ASC');
        $ksCollection->setPageSize($ksPageSize);
        $ksCollection->setCurPage($ksPage);

        return $ksCollection;
    }

    /**
     * Return pager html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
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
     * Get delete action url for favourite seller
     *
     * @return url
     */
    public function getKsDeleteAction()
    {
        return $this->getUrl('multivendor/favouriteSeller/delete', ['_secure' => true]);
    }

    /**
     * Return search product name
     *
     * @return string
     */
    public function getPostValue()
    {
        return $this->getRequest()->getParam('s');
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
