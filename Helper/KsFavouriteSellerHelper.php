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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory as KsSellerStoreCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory as KsProductCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmailFactory as KsFavSellerEmailFactory;

/**
 * KsFavouriteSellerHelper Class
 */
class KsFavouriteSellerHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksCustomerSession;

    /**
     * @var KsSellerStoreCollectionFactory
     */
    protected $ksSellerStoreCollectionFactory;

    /**
     * @var KsFavouriteSellerCollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * @var KsProductCollectionFactory
     */
    protected $ksProductCollectionFactory;

    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $ksCustomerCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $ksCurrencyFactory;

    /**
     * @var KsFavSellerEmailFactory
     */
    protected $ksFavSellerEmailFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $ksRequestInterface;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param \KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory
     * @param KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory
     * @param KsProductCollectionFactory $ksProductCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData
     * @param KsSellerCollectionFactory $ksSellerCollectionFactory
     * @param Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param Magento\Catalog\Model\ProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Magento\Directory\Model\CurrencyFactory
     * @param KsFavSellerEmailFactory $ksFavSellerEmailFactory
     * @param \Magento\Framework\App\RequestInterface $ksRequestInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Magento\Customer\Model\SessionFactory $ksCustomerSession,
        KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory,
        ksFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerCollectionFactory,
        KsProductCollectionFactory $ksProductCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksHelperData,
        KsSellerCollectionFactory $ksSellerCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Magento\Directory\Model\CurrencyFactory $ksCurrencyFactory,
        KsFavSellerEmailFactory $ksFavSellerEmailFactory,
        \Magento\Framework\App\RequestInterface $ksRequestInterface
    ) {
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksSellerStoreCollectionFactory = $ksSellerStoreCollectionFactory;
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksProductCollectionFactory = $ksProductCollectionFactory;
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        $this->ksHelperData = $ksHelperData;
        $this->ksCustomerCollectionFactory = $ksCustomerCollectionFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksCurrencyFactory = $ksCurrencyFactory;
        $this->ksFavSellerEmailFactory = $ksFavSellerEmailFactory;
        $this->ksRequestInterface = $ksRequestInterface;
        parent::__construct($ksContext);
    }

    /**
     * Get seller name by seller id
     *
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerName($ksSellerId)
    {
        $ksSeller = $this->ksCustomerFactory->create()->load($ksSellerId);
        $ksSellerName = $ksSeller->getName();
        return $ksSellerName;
    }

    /**
     * Get seller store name by seller id
     *
     * @param int $ksSellerId
     * @return string
     */
    public function getKsStoreName($ksSellerId)
    {
        return $this->ksSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem()->getKsStoreName();
    }

    /**
     * Get customer name by email
     * @param  string $ksEmail
     * @return string
     */
    public function getKsNameByEmail($ksEmail)
    {
        $ksWebsiteId = $this->ksStoreManager->getWebsite()->getWebsiteId();
        $ksCustomerData = $this->ksCustomerFactory->create()->setWebsiteId($ksWebsiteId)->loadByEmail($ksEmail);
        return $ksCustomerData->getName();
    }

    /**
     * Get current customer id
     *
     * @return int
     */
    public function getKsCustomerId()
    {
        return $this->ksCustomerSession->create()->getCustomer()->getId();
    }

    /**
     * Get seller store information
     *
     * @param int $ksSellerId
     * @param int $ksStoreId
     * @return array
     */
    public function getKsStoreInfo($ksSellerId, $ksStoreId)
    {
        $ksStoreCollection = $this->ksSellerStoreCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId)->getFirstItem();
        $ksStoreInfo['logo'] = $ksStoreCollection->getKsStoreLogo();
        $ksStoreInfo['banner'] = $ksStoreCollection->getKsStoreBanner();
        return $ksStoreInfo;
    }

    /**
     * Get current seller id and return 0 if seller is admin
     *
     * @param int $ksProductCurrentId
     * @return int
     */
    public function getKsCurrentSeller($ksProductCurrentId)
    {
        $ksProductCollection = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_product_id', $ksProductCurrentId);
        $ksCurrentSellerId = 0;
        foreach ($ksProductCollection as $ksSellerId) {
            $ksCurrentSellerId = $ksSellerId->getKsSellerId();
        }
        return $ksCurrentSellerId;
    }

    /**
     * Get customer details
     *
     * @param  int $ksId
     * @return string array
     */
    public function getKsCustomerAccountInfo($ksId)
    {
        $ksCustomerCollection = $this->ksCustomerCollectionFactory->create()->addFieldToFilter('entity_id', $ksId);
        $ksEmail = [];
        foreach ($ksCustomerCollection as $ksCustomer) {
            $ksEmail['email'] = $ksCustomer->getEmail();
            $ksEmail['name'] = $ksCustomer->getName();
        }
        return $ksEmail;
    }

    /**
     * Check module enabled
     *
     * @return boolean
     */
    public function isKsEnabled()
    {
        return $this->ksHelperData->getKsConfigValue('ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_favourite_seller_enable', $this->ksHelperData->getKsCurrentStoreView());
    }

    /**
     * Favourite Seller Remove
     *
     * @param  [int] $ksCustomerId
     * @param  [int] $ksSellerId
     * @return array
     */
    public function getKsFavouriteSellerRemove($ksCustomerId, $ksSellerId)
    {
        return $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_customer_id', $ksCustomerId)->addFieldToFilter('ks_seller_id', $ksSellerId);
    }
    
    /**
     * Check favourite seller exist
     *
     * @param int $ksCustomer
     * @param int $ksSeller
     * @return bool
     */
    public function isKsSellerAdded($ksCustomer, $ksSeller)
    {
        $ksModel = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSeller)->addFieldToFilter('ks_customer_id', $ksCustomer);
        if ($ksModel->getData()) {
            return false;
        }
        return true;
    }

    /**
     * Get all followers of a seller
     *
     * @param  [int] $ksSellerId
     * @return [int] array
     */
    public function getKsAllFollowers($ksSellerId)
    {
        $ksFollowersCollection = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksFollowersIds = [];
        foreach ($ksFollowersCollection as $ksCollection) {
            $ksFollowersIds[] = $ksCollection->getKsCustomerId();
        }
        return $ksFollowersIds;
    }

    /**
     * Get product details by product id
     *
     * @param  [int] $ksProductId
     * @param  [int] $ksStoreId
     * @return [mixed] array
     */
    public function getKsProductDetails($ksProductId, $ksStoreId)
    {
        $ksProductCollection = $this->ksProductFactory->create()->setStoreId($ksStoreId)->load($ksProductId);
        $ksProDetails = [];

        $ksProDetails['name'] = $ksProductCollection->getName();
        $ksProDetails['price'] = $ksProductCollection->getPrice();
        $ksProDetails['special_price'] = $ksProductCollection->getSpecialPrice();
        $ksProDetails['description'] = is_null($ksProductCollection->getDescription()) ? "" : trim(strip_tags($ksProductCollection->getDescription()));
        if ($ksProDetails['description'] == "") {
            $ksProDetails['description'] = "N/A";
        }
        return $ksProDetails;
    }

    /**
     * Get current currency symbol
     *
     * @param  [int] $ksStoreId
     */
    public function getKsCurrencySymbol($ksStoreId)
    {
        $ksCurrencyCode = $this->ksStoreManager->getStore($ksStoreId)->getBaseCurrencyCode();
        $ksCurrency = $this->ksCurrencyFactory->create()->load($ksCurrencyCode)->getCurrencySymbol();
        return $ksCurrency ? $ksCurrency : $ksCurrencyCode;
    }

    /**
     * Get all categories names that are applied on a product
     *
     * @param  [int] $ksProductId
     * @param  [int] $ksStoreId
     * @return [string]
     */
    public function getKsProductAllCatNames($ksProductId, $ksStoreId)
    {
        $ksCatCollection = $this->ksProductFactory->create()->load($ksProductId);
        $ksCategoryIds = $ksCatCollection->getCategoryIds();

        $ksCatIds = [];
        if ($ksCategoryIds) {
            $ksCatIds = array_unique($ksCategoryIds);
        }

        $ksCategoryName = '';
        $ksCategory = '';
        if (isset($ksCatIds)) {
            foreach ($ksCatIds as $ksCategoryId) {
                $ksCategory.= $this->ksCategoryHelper->getKsCategoryNameWithParent($ksCategoryId, $ksStoreId) . ',';
            }
            $ksCategoryName.= substr($ksCategory, 0, -1);
        }
        if ($ksCategoryName == "") {
            $ksCategoryName = "N/A";
        }
        return $ksCategoryName;
    }

    /**
     * Get Customer email preference
     *
     * @param  [int] $ksFollowerId
     * @param  [int] $ksSellerId
     * @return array
     */
    public function getKsCustomerEmailPreference($ksFollowerId, $ksSellerId)
    {
        $ksCollection = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_customer_id', $ksFollowerId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        $ksEmail = [];
        $ksEmail['seller_new_product'] = $ksCollection->getKsSellerNewProductAlert();
        $ksEmail['seller_price_alert'] = $ksCollection->getKsSellerPriceAlert();
        $ksEmail['customer_new_product'] = $ksCollection->getKsCustomerNewProductAlert();
        $ksEmail['customer_price_alert'] = $ksCollection->getKsCustomerPriceAlert();
        return $ksEmail;
    }

    /**
     * Check website id when product edited
     *
     * @param [int] $ksFollowerId
     * @param [int] $ksMassIds [array]
     * @param [int] $ksSellerId
     */
    public function ksCheckProductCustomerWebsiteIds($ksFollowerId, $ksMassIds, $ksSellerId)
    {
        $ksAccountSharingScope = $this->ksHelperData->getKsConfigValue('customer/account_share/scope');
        $ksPriceScope = $this->ksHelperData->getKsConfigValue('catalog/price/scope');

        $ksFollowerCollection = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_customer_id', $ksFollowerId)->getFirstItem();
        $ksFollowerWebsiteId = $ksFollowerCollection->getKsWebsiteId();
        $ksFollowerStoreId = $ksFollowerCollection->getKsStoreId();
        $ksProIds = [];

        //0 for global and 1 for website
        switch (true) {
            case $ksAccountSharingScope == 1 && $ksPriceScope == 1:
                foreach ($ksMassIds as $ksId) {
                    $ksCollection = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem();
                    $ksProductWebsiteId = $ksCollection->getKsWebsiteId();
                    $ksProductId = $ksCollection->getKsProductId();
                    $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
                    $ksIsSaleable = $ksProduct->isSaleable();

                    if (($ksFollowerWebsiteId == $ksProductWebsiteId) || ($ksProductWebsiteId == 0 && $this->ksCheckDefaultValue($ksProduct, $ksFollowerStoreId, $ksFollowerWebsiteId)) == "true" && $ksIsSaleable == 1) {
                        $ksProIds[] = $ksProductId;
                    }
                }
                break;

            case $ksAccountSharingScope == 0 && $ksPriceScope == 1:
                foreach ($ksMassIds as $ksId) {
                    $ksCollection = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem();
                    $ksProductWebsiteId = $ksCollection->getKsWebsiteId();
                    $ksProductId = $ksCollection->getKsProductId();

                    $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
                    $ksProductInWebsiteId = $ksProduct->getWebsiteIds();
                    $ksIsSaleable = $ksProduct->isSaleable();

                    if ($ksProductWebsiteId == $ksFollowerWebsiteId && $ksIsSaleable == 1) {
                        $ksProIds[] = $ksProductId;
                    } elseif ($ksProductWebsiteId == 0 && $this->ksCheckDefaultValue($ksProduct, $ksFollowerStoreId, $ksFollowerWebsiteId) == "true" && $ksIsSaleable == 1) {
                        $ksProIds[] = $ksProductId;
                    }
                }
                break;

            default:
                foreach ($ksMassIds as $ksId) {
                    $ksCollection = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem();
                    $ksProductWebsiteId = $ksCollection->getKsWebsiteId();
                    $ksProductId = $ksCollection->getKsProductId();

                    $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
                    $ksProductInWebsiteId = $ksProduct->getWebsiteIds();
                    $ksIsSaleable = $ksProduct->isSaleable();

                    if (in_array($ksFollowerWebsiteId, $ksProductInWebsiteId) && $ksIsSaleable == 1) {
                        $ksProIds[] = $ksProductId;
                    }
                }
        }
        return array_unique($ksProIds);
    }

    /**
     * Check whether special price changed on other store view
     *
     * @param  [mixed] $ksProduct
     * @param  [int] $ksFollowerStoreId
     * @param  [int] $ksFollowerWebsiteId
     * @return [boolean]
     */
    public function ksCheckDefaultValue($ksProduct, $ksFollowerStoreId, $ksFollowerWebsiteId)
    {
        $ksStoreViewPrice = $ksProduct->setStoreId($ksFollowerStoreId)->getSpecialPrice();
        $ksAllStoreViewPrice = $ksProduct->setStoreId(0)->getSpecialPrice();

        $ksProductInWebsiteId = $ksProduct->getWebsiteIds();

        if ($ksAllStoreViewPrice == $ksStoreViewPrice && in_array($ksFollowerWebsiteId, $ksProductInWebsiteId)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check Website ids for new products
     *
     * @param  [int] $ksFollowerId
     * @param  [int] $ksMassIds [array]
     * @param  [int] $ksSellerId
     */
    public function ksCheckNewProductWebsites($ksFollowerId, $ksMassIds, $ksSellerId)
    {
        $ksCustomerWebsiteId = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_customer_id', $ksFollowerId)->getFirstItem()->getKsWebsiteId();
        $ksProIds = [];

        foreach ($ksMassIds as $ksId) {
            $ksProductId = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem()->getKsProductId();
            $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
            $ksProductInWebsiteIds = $ksProduct->getWebsiteIds();
            $ksIsSaleable = $ksProduct->isSaleable();

            if (in_array($ksCustomerWebsiteId, $ksProductInWebsiteIds) && $ksIsSaleable == 1) {
                $ksProIds[] = $ksProductId;
            }
        }
        
        return array_unique($ksProIds);
    }

    /**
     * Save data in ks_favourite_seller_email table
     *
     * @param  [int] $ksSellerId
     * @param  [int] $ksProductId
     * @param  [float] $ksOldPrice
     * @param  [string] $ksProductType
     * @param  [int] $ksEditState
     */
    public function ksSaveFavSellerEmailData($ksSellerId, $ksProductId, $ksOldPrice, $ksProductType, $ksEditState)
    {
        if ($this->isKsEnabled() && ($ksProductType == "simple" || $ksProductType == "virtual" || $ksProductType == "downloadable")) {
            $ksStoreId = $this->ksRequestInterface->getParam('store', 0);
            $ksId = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('ks_product_id', $ksProductId)->addFieldToFilter('ks_store_id', $ksStoreId)->getFirstItem()->getId();
            $ksWebsiteId = $this->ksStoreManager->getStore($ksStoreId)->getWebsiteId();

            if (!$ksId) {
                $ksModel = $this->ksFavSellerEmailFactory->create();
                $ksModel->setKsProductId($ksProductId);
                $ksModel->setKsStoreId($ksStoreId);
                $ksModel->setKsWebsiteId($ksWebsiteId);
            } else {
                $ksModel = $this->ksFavSellerEmailFactory->create()->load($ksId);
            }
            
            $ksModel->setKsSellerId($ksSellerId);
            $ksModel->setKsProductState($ksEditState);
            $ksModel->setKsProductOldSpecialPrice($ksOldPrice);
            $ksModel->setKsIsEmailSent(\Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmail::KS_STATUS_PENDING);
            $ksModel->save();
        }
    }

    /**
     * Get follower store id
     *
     * @param  [int] $ksSellerId
     * @param  [int] $ksFollowerId
     */
    public function getKsFollowerStoreId($ksSellerId, $ksFollowerId)
    {
        return $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_customer_id', $ksFollowerId)->getFirstItem()->getKsStoreViewId();
    }
}
