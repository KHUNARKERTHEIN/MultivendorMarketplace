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

namespace Ksolves\MultivendorMarketplace\Block\FavouriteSeller;

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollectionFactory;

/**
 * KsShowData Block Class
 */
class KsShowData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $ksImageHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var KsFavouriteSellerCollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor
     *
     * @param Magento\Framework\View\Element\Template\Context $ksContext
     * @param Magento\Catalog\Helper\Image $ksImageHelper
     * @param Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param Magento\Framework\Registry $ksRegistry
     * @param KsSellerCollectionFactory $ksSellerCollectionFactory
     * @param KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $ksContext,
        \Magento\Catalog\Helper\Image $ksImageHelper,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Framework\Registry $ksRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData,
        KsSellerCollectionFactory $ksSellerCollectionFactory,
        ksFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        array $data = []
    ) {
        $this->ksImageHelper = $ksImageHelper;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksRegistry = $ksRegistry;
        $this->ksHelperData = $ksHelperData;
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksFavSellerHelper = $ksFavSellerHelper;
        $this->ksSellerHelper    = $ksSellerHelper;
        parent::__construct($ksContext, $data);
    }

    /**
     * Get delete action
     *
     * @return url
     */
    public function getKsDeleteAction()
    {
        return $this->getUrl('multivendor/favouriteSeller/delete', ['_secure' => true]);
    }

    /**
     * Get product image url
     *
     * @param int $ksProductId
     * @return url
     */
    public function getKsProductImageUrl($ksProductId)
    {
        $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
        $ksUrl = $this->ksImageHelper->init($ksProduct, 'product_small_image')->getUrl();
        return $ksUrl;
    }

    /**
     * Get product url
     *
     * @param int $ksProductId
     * @return url
     */
    public function getKsProductUrl($ksProductId)
    {
        $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
        $ksUrl = $ksProduct->getProductUrl();
        return $ksUrl;
    }

    /**
     * Get product name
     *
     * @param int $ksProductId
     * @return string
     */
    public function getKsProductName($ksProductId)
    {
        $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
        $ksName = $ksProduct->getName();
        return $ksName;
    }

    /**
     * Get seller logo/banner url
     *
     * @param string $ksName
     * @return url
     */
    public function getKsStoreManagerData($ksName)
    {
        if ($ksName == "") {
            return $this->ksImageHelper->getDefaultPlaceholderUrl('small_image');
        } else {
            return $this->ksStoreManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) .'ksolves/multivendor/'.$ksName;
        }
    }

    /**
     * Get current product id
     *
     * @return int
     */
    public function getKsCurrentProduct()
    {
        $ksCurrentProduct = $this->ksRegistry->registry('current_product');
        $ksCurrentProductId = $ksCurrentProduct->getId();
        return $ksCurrentProductId;
    }

    /**
     * get enable status of report seller
     * @return int
     */
    public function isReportEnabled()
    {
        return $this->ksHelperData->getKsConfigValue(
            'ks_marketplace_report/ks_report_seller/ks_report_seller_enable',
            $this->ksHelperData->getKsCurrentStoreView()
        );
    }

    /**
     * Get product visibility
     *
     * @param  int $ksProductId
     * @return int
     */
    public function getKsProductVisibility($ksProductId)
    {
        return $this->ksProductFactory->create()->load($ksProductId)->getVisibility();
    }

    /**
     * Prepare Layout for Pagination
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getKsFavSellerPaginationData($this->ksFavSellerHelper->getKsCustomerId())) {
            $ksPager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ks-fav-seller-pager'
            )->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
            ->setShowPerPage(true)->setCollection(
                $this->getKsFavSellerPaginationData($this->ksFavSellerHelper->getKsCustomerId())
            );
            $this->setChild('pager', $ksPager);//sub html of pager
            $this->getKsFavSellerPaginationData($this->ksFavSellerHelper->getKsCustomerId())->load();
        }
        return $this;
    }

    /**
     * Get Pager Html
     * @return page
     */
    public function getKsPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Seller Collection
     * @return array
     */
    public function getKsFavSellerPaginationData($ksCustomerId)
    {
        $ksPage = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $ksPageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;

        $ksCollection = $this->getFavSellerData($ksCustomerId);
        if ($ksCollection) {
            $ksCollection->setPageSize($ksPageSize);
            $ksCollection->setCurPage($ksPage);
        }
        return $ksCollection;
    }

    /**
     * Get favourite seller collection
     *
     * @param int $ksCustomerId
     * @return mixed
     */
    public function getFavSellerData($ksCustomerId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()->addFieldToFilter('ks_seller_status', 1)->addFieldToFilter('ks_store_status', 1);
        $ksApprovedSeller = [];
        foreach ($ksSellerCollection as $key) {
            $ksApprovedSeller[] = $key->getKsSellerId();
        }

        $ksFavouriteSellerCollection = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_customer_id', $ksCustomerId)->addFieldToFilter('ks_seller_id', ['in' =>$ksApprovedSeller]);
        return $ksFavouriteSellerCollection;
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
