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

use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory as KsSellerCollectionFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderItemFactory;
use Ksolves\MultivendorMarketplace\Model\KsSalesOrderFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerStore\CollectionFactory as KsSellerStoreCollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\ScopeInterface;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;
use Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * KsSellerHelper Class
 */
class KsSellerHelper extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var KsSellerCollectionFactory
     */
    protected $ksSellerStoreCollectionFactory;

    /**
    * @var KsSalesOrderItemCollectionFactory
    */
    protected $ksOrderItemFactory;

    /**
     * @var KsSalesOrderFactory
     */
    protected $ksSalesOrderFactory;

    /**
     * @var TypeListInterface
     */
    protected $ksCacheTypeList;

    /**
     * @var Pool
     */
    protected $ksCacheFrontendPool;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $ksEscaper;

    /**
     * @var SessionFactory
     */
    protected $ksSessionFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $ksUrlRewriteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsCategoryRequestsAllowedFactory
     */
    protected $ksCategoryRequestsAllowedFactory;

    /**
     * @var KsSellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;

    /**
     * @var KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory
     */
    protected $ksTypeFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $ksCoreSession;

    /**
     * AbstractData constructor
     *
     * @param \Magento\Framework\App\Helper\Context $ksContext
     * @param \Magento\Customer\Model\CustomerFactory $ksCustomerFactory
     * @param \Magento\Customer\Model\Session $ksCustomerSession
     * @param KsSellerCollectionFactory $ksSellerCollectionFactory
     * @param KsSalesOrderItemFactory $ksOrderItemFactory
     * @param KsSalesOrderFactory $ksSalesOrderFactory
     * @param KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory
     * @param \Magento\Framework\Escaper $ksEscaper
     * @param SessionFactory $ksSessionFactory
     * @param TypeListInterface $ksCacheTypeList
     * @param Pool $ksCacheFrontendPool
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory $ksTypeFactory,
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $ksUrlRewriteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory
     * @param KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param KsCategoryRequests $ksCategoryHelper
     * @param SessionManagerInterface $ksCoreSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $ksContext,
        \Magento\Customer\Model\CustomerFactory $ksCustomerFactory,
        \Magento\Customer\Model\Session $ksCustomerSession,
        KsSellerCollectionFactory $ksSellerCollectionFactory,
        KsSalesOrderItemFactory $ksOrderItemFactory,
        KsSalesOrderFactory $ksSalesOrderFactory,
        KsSellerStoreCollectionFactory $ksSellerStoreCollectionFactory,
        \Magento\Framework\Escaper $ksEscaper,
        SessionFactory $ksSessionFactory,
        TypeListInterface $ksCacheTypeList,
        Pool $ksCacheFrontendPool,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $ksUrlRewriteFactory,
        \Ksolves\MultivendorMarketplace\Model\KsProductTypeFactory $ksTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory,
        KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        KsCategoryRequests $ksCategoryHelper,
        SessionManagerInterface $ksCoreSession
    ) {
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksCustomerSession = $ksCustomerSession;
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        $this->ksOrderItemFactory = $ksOrderItemFactory;
        $this->ksSalesOrderFactory = $ksSalesOrderFactory;
        $this->ksSellerStoreCollectionFactory = $ksSellerStoreCollectionFactory;
        $this->ksEscaper = $ksEscaper;
        $this->ksCacheTypeList = $ksCacheTypeList;
        $this->ksCacheFrontendPool = $ksCacheFrontendPool;
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        $this->ksSessionFactory = $ksSessionFactory;
        $this->ksUrlRewriteFactory = $ksUrlRewriteFactory;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksCategoryRequestsAllowedFactory = $ksCategoryRequestsAllowedFactory;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksTypeFactory = $ksTypeFactory;
        $this->ksCoreSession = $ksCoreSession;
        parent::__construct($ksContext);
    }

    /**
     *  get seller name by seller id
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerName($ksSellerId)
    {
        $ksSeller = $this->ksCustomerFactory->create()->load($ksSellerId);
        $ksSellerName   = $ksSeller->getName();
        return $ksSellerName;
    }

    /**
     * Return Customer id.
     *
     * @return int
     */
    public function getKsCustomerId()
    {
        return $this->ksCustomerSession->getCustomer()->getId();
    }

    /**
     * Check current customer is Seller
     *
     * @return bool|0|1
     */
    public function ksIsSeller()
    {
        $ksSellerStatus = 0;
        $ksSellerId = $this->getKsCustomerId();
        // Check Seller Login is Enable or Not
        $ksLoginEnable = $this->ksDataHelper->getKsConfigLoginAndRegistrationSetting('ks_enable_seller_login');

        // get value from session to check the login from backend
        $ksIsSellerLoginFromAdmin = $this->ksCoreSession->getKsIsLoginFromAdmin();

        if ($ksLoginEnable || $ksIsSellerLoginFromAdmin) {
            $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
                            
            if ($ksSellerCollection->getSize() > 0) {
                foreach ($ksSellerCollection as $ksData) {
                    if ($ksData->getKsSellerStatus() == 1) {
                        $ksSellerStatus = $ksData->getKsSellerStatus();
                    }
                }
            }
        }

        return $ksSellerStatus;
    }

    /**
     * return seller website Id by seller id
     * @param int $ksSellerId
     * @return int
     */
    public function getksSellerWebsiteId($ksSellerId)
    {
        $ksSeller = $this->ksCustomerFactory->create()->load($ksSellerId);
        $ksWebsiteId   = $ksSeller->getWebsiteId();
        return $ksWebsiteId;
    }

    /**
     * return seller product request allowed or not according to seller id
     * @param int $ksSellerId
     * @return int
     */
    public function getksProductRequestAllowed($ksSellerId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksProductTypeRequestStatus = 0;
        if ($ksSellerCollection->getSize() > 0) {
            foreach ($ksSellerCollection as $ksData) {
                if ($ksData->getKsSellerStatus() == 1) {
                    $ksProductTypeRequestStatus = $ksData->getKsSellerProducttypeRequestStatus();
                }
            }
        }
        return $ksProductTypeRequestStatus;
    }

    /**
     * return pending seller count
     * @return int
     */
    public function getKsPendingSellerCount()
    {
        $ksPendingStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_PENDING;
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_status', $ksPendingStatus);
        return $ksSellerCollection->getSize();
    }

    /**
     * Check the seller order item
     * @param $productId
     * @return bool
     */
    public function KsIsSellerOrderItem($ks_productId, $ks_orderId)
    {
        $ks_collection = $this->ksOrderItemFactory->create()->getCollection()
        ->addFieldToFilter('ks_product_id', $ks_productId)
        ->addFieldToFilter('ks_order_id', $ks_orderId)
        ->addFieldToFilter('ks_seller_id', $this->getKsCustomerId());
        if ($ks_collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
    * Check the seller order
    * @param $ks_orderId
    * @return bool
    */
    public function KsIsSellerOrder($ks_orderId)
    {
        $ks_collection = $this->ksSalesOrderFactory->create()->getCollection()
        ->addFieldToFilter('ks_order_id', $ks_orderId)
        ->addFieldToFilter('ks_seller_id', $this->getKsCustomerId());
        if ($ks_collection->getSize()) {
            return true;
        }
        return false;
    }

    /**
     * Get Seller Store Logo for Store Collection
     * @param int $ksSellerId
     * @param int $ksStoreId
     * @return string
     */
    public function getKsSellerStoreLogo($ksSellerId, $ksStoreId)
    {
        // Get Collection according to seller Id and Store Id
        $ksSellerStoreCollection = $this->ksSellerStoreCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId)
                            ->addFieldToFilter('ks_store_id', $ksStoreId);
        $ksSellerStoreLogo = "";
        if ($ksSellerStoreCollection->getSize() >0) {
            foreach ($ksSellerStoreCollection as $ksStore) {
                $ksSellerStoreLogo = $ksStore->getKsStoreLogo();
            }
        } else {
            $ksSellerStoreCollection = $this->ksSellerStoreCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId)
                            ->addFieldToFilter('ks_store_id', 0);
            foreach ($ksSellerStoreCollection as $ksStore) {
                $ksSellerStoreLogo = $ksStore->getKsStoreLogo();
            }
        }

        return $ksSellerStoreLogo;
    }

    /**
     * Get Seller Store Name for Store Collection
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerStoreName($ksSellerId)
    {
        // Get Collection according to seller Id
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksSellerStoreName = "";
        foreach ($ksSellerCollection as $ksSeller) {
            $ksSellerStoreName = $ksSeller->getKsStoreName();
        }
        return $ksSellerStoreName;
    }

    /**
     * Get Seller Store Status for Store Collection
     * @param int $ksSellerId
     * @return string
     */
    public function getKsSellerStoreStatus($ksSellerId)
    {
        // Get Collection according to seller Id
        $ksSellerDetails = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        if ($ksSellerDetails->getSize()>0) {
            return $ksSellerDetails->getFirstItem()->getKsStoreStatus();
        } else {
            return 0;
        }
    }

    /**
     * add default seller config data
     *
     * @return void
     */
    public function setKsSellerConfigData($ksSellerId)
    {
        $ksCollection = $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId);
        if (count($ksCollection) == 0) {
            $ksModel = $this->ksSellerConfigFactory->create();
            $ksData=[
                "ks_seller_id"               => $ksSellerId,
                "ks_show_banner"             => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_show_recently_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_recently_products_text"  => $this->ksEscaper->escapeHtml('Recently Added Products'),
                "ks_recently_products_count" => 10,
                "ks_show_best_products"      => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_best_products_text"      => $this->ksEscaper->escapeHtml('Best Selling Products'),
                "ks_best_products_count"     => 10,
                "ks_show_discount_products"  => \Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED,
                "ks_discount_products_text"  => $this->ksEscaper->escapeHtml('Most Discounted Products'),
                "ks_discount_products_count" => 10
            ];
            $ksModel->addData($ksData)->save();
        }
    }

    /**
     * @return void
     */
    public function ksFlushCache()
    {
        $ksTypes = [
            'config',
            'layout',
            'block_html',
            'collections',
            'reflection',
            'db_ddl',
            'eav',
            'config_integration',
            'config_integration_api',
            'full_page',
            'translate',
            'config_webservice'
        ];

        foreach ($ksTypes as $ksType) {
            $this->ksCacheTypeList->cleanType($ksType);
        }
        foreach ($this->ksCacheFrontendPool as $ksCacheFrontend) {
            $ksCacheFrontend->getBackend()->clean();
        }
    }

    /**
     * return seller product attributerequest allowed or not according to seller id
     * @param int $ksSellerId
     * @return int
     */
    public function getksProductAttributeRequestAllowed($ksSellerId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksRequestStatus = 0;
        if ($ksSellerCollection->getSize() > 0) {
            foreach ($ksSellerCollection as $ksData) {
                $ksRequestStatus = $ksData->getKsProductAttributeRequestAllowedStatus();
            }
        }
        return $ksRequestStatus;
    }

    /**
     * return seller product attribute request auto approval allowed or not according to seller id
     * @param int $ksSellerId
     * @return int
     */
    public function getksProductAttributeAutoAppoval($ksSellerId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        $ksRequestStatus = 0;
        if ($ksSellerCollection->getSize() > 0) {
            foreach ($ksSellerCollection as $ksData) {
                $ksRequestStatus = $ksData->getKsProductAttributeAutoApprovalStatus();
            }
        }
        return $ksRequestStatus;
    }

    /**
     * Get All Seller List
     *
     * @return array
     */
    public function getKsSellerList()
    {
        $ksSellerList = [];
        $ksSellerCollection = $this->ksSellerCollectionFactory->create();
        foreach ($ksSellerCollection as $ksSellerRecord) {
            $ksSellerList[] = $ksSellerRecord->getKsSellerId();
        }
        return $ksSellerList;
    }

    /**
     * Get Yes/No values for product attributes
     *
     * @param bool $ksValue
     * @return string
     */
    public function getKsYesNoValue($ksValue)
    {
        if ($ksValue == 1) {
            return "Yes";
        } else {
            return "No";
        }
    }


    /**
     * Get Store values
     *
     * @param bool $ksValue
     * @return string
     */
    public function getKsStoreValues($ksValue)
    {
        if ($ksValue == 0) {
            return "Store View";
        } elseif ($ksValue == 1) {
            return "Global";
        } else {
            return "Web Site";
        }
    }

    /**
     * Get Filterable values
     *
     * @param bool $ksValue
     * @return string
     */
    public function getKsFilterableValues($ksValue)
    {
        if ($ksValue == 0) {
            return "No";
        } elseif ($ksValue == 1) {
            return "Filterable (with results)";
        } else {
            return "Filterable (no results)";
        }
    }

    /**
     * Check current customer is Seller
     *
     * @return bool|0|1
     */
    public function ksIsSellerApproved($ksSellerId)
    {
        $ksSellerStatus = 0;
        $ksSellerCollection = $this->ksSellerCollectionFactory->create()
                            ->addFieldToFilter('ks_seller_id', $ksSellerId);
        if ($ksSellerCollection->getSize() > 0) {
            foreach ($ksSellerCollection as $ksData) {
                if ($ksData->getKsSellerStatus() == 1) {
                    $ksSellerStatus = $ksData->getKsSellerStatus();
                }
            }
        }
        return $ksSellerStatus;
    }

    /**
     * Get current customer id from sessonfactory
     *
     * @return int
     */
    public function getKsCustomerIdFromSessionFactory()
    {
        return $this->ksSessionFactory->create()->getCustomer()->getId();
    }

    /**
     * Get customer email from id
     *
     * @return string
     */
    public function getKsCustomerEmail($ksId)
    {
        $ksSeller = $this->ksCustomerFactory->create()->load($ksId);
        $ksSellerEmail   = $ksSeller->getEmail();
        return $ksSellerEmail;
    }

    /**
     * get seller profile redirect url
     * @param $ksTargetPath
     * @param $ksRequestPath
     * @return void
     */
    public function getKsSellerProfileUrl($ksSellerId)
    {
        $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksSellerId.'/';

        return $this->getRequestedPageUrl($ksTargetPathUrl);
    }

    /**
     * get seller store requested url
     * @param $ksTargetPath
     * @return void
     */
    public function getRequestedPageUrl($ksTargetPath)
    {
        $ksUrlRewriteModel = $this->ksUrlRewriteFactory->create();
        $ksUrlRewriteCollection= $ksUrlRewriteModel->getCollection()
                                ->addFieldToFilter('target_path', $ksTargetPath)
                                ->addFieldToFilter('store_id', $this->ksStoreManager->getStore()->getId());
        if ($ksUrlRewriteCollection->getFirstItem()->getId()) {
            $ksUrl= $ksUrlRewriteCollection->getFirstItem()->getRequestPath();
        } else {
            $ksUrl = 0;
        }
        return $ksUrl;
    }

    /**
     * save seller store redirect url
     * @param $ksTargetPath
     * @param $ksRequestPath
     * @return void
     */
    public function ksSellerStoreUrlRedirect($ksTargetPath, $ksRequestPath)
    {
        $ksCollection = $this->ksUrlRewriteFactory->create()->getCollection()->addFieldToFilter('target_path', $ksTargetPath);
        if ($ksCollection->getSize() == 0) {
            foreach ($this->ksStoreManager->getStores() as $ksStore) {
                $ksUrlRewriteModel = $this->ksUrlRewriteFactory->create();
                $ksUrlRewriteModel->setStoreId($ksStore->getStoreId());
                $ksUrlRewriteModel->setIsSystem(0);
                $ksUrlRewriteModel->setIdPath(rand(1, 100000));
                $ksUrlRewriteModel->setTargetPath($ksTargetPath);
                $ksUrlRewriteModel->setRequestPath($ksRequestPath);
                $ksUrlRewriteModel->setIsAutogenerated(1);
                $ksUrlRewriteModel->save();
            }
        }
    }

    /**
     * delete seller store redirect url
     * @param $ksTargetPath
     * @return void
     */
    public function ksRedirectUrlDelete($ksTargetPath)
    {
        $ksUrlRewriteModel = $this->ksUrlRewriteFactory->create();

        $ksUrlRewriteCollection = $ksUrlRewriteModel->getCollection()->addFieldToFilter('target_path', $ksTargetPath);
        if ($ksUrlRewriteCollection->getSize() != 0) {
            foreach ($ksUrlRewriteCollection as $ksData) {
                $ksData->delete();
            }
        }
    }

    /**
     * Return customer account share scope
     * @return boolean
     */
    public function getKsCustomerConfigScope()
    {
        return $this->scopeConfig->getValue(
            'customer/account_share/scope',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return show sell page or not
     * @return boolean
     */
    public function getKsShowSellPage()
    {
        return $this->scopeConfig->getValue(
            'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_show_sell_page',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return seller model
     * @return object
     */
    public function ksSaveConfigurationValue($ksModel)
    {
        $ksProductApproval = $this->ksDataHelper->getKsConfigProductSetting('ks_admin_approval') ? 0 : 1 ;
        $ksTypeApproval = $this->ksDataHelper->getKsConfigSellerProductTypeSetting('ks_admin_approval_type_status') ? 0 : 1;
        $ksAttributeEnable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_seller_enable_custom_attribute_status');
        $ksAttributeApproval = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_attribute_status') ? 0 : 1;
        $ksModel->setKsProductAutoApproval($ksProductApproval);
        if ($ksAttributeEnable) {
            $ksModel->setKsProductAttributeRequestAllowedStatus($ksAttributeEnable);
            $ksModel->setKsProductAttributeAutoApprovalStatus($ksAttributeApproval);
        }

        if ($ksTypeApproval) {
            $ksModel->setKsSellerProducttypeRequestStatus(1);
            $ksModel->setKsProducttypeAutoApprovalStatus($ksTypeApproval);
        }
        return $ksModel;
    }

    /**
     * assign category to seller
     * @param boolean $ksCustomerId
     */
    public function ksSetCategoryConfiguration($ksCustomerId)
    {
        $ksCategoryApproval = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_requires_admin_approval') ? 0 : 1 ;

        try {
            //set seller categories from Configuration
            $this->ksSellerCategorySave($ksCustomerId);

            //set seller category Auto Approval from Configuration
            $ksCategoryRequestsCollection = $this->ksCategoryRequestsAllowedFactory->create()->getCollection()
                ->addFieldToFilter('ks_seller_id', $ksCustomerId);

            if ($ksCategoryRequestsCollection->getSize() <= 0) {
                $ksCategoryRequestsModel = $this->ksCategoryRequestsAllowedFactory->create();
                $ksCategoryRequestsModel->setKsSellerId($ksCustomerId);
                $ksCategoryRequestsModel->setKsIsRequestsAllowed(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);
                $ksCategoryRequestsModel->setKsIsAutoApproved($ksCategoryApproval);
                $ksCategoryRequestsModel->setKsIsInit(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED);
                $ksCategoryRequestsModel->save();
            }
        } catch (\Magento\Framework\Validator\Exception $ksException) {
        }
    }

    /**
     * assign category to seller
     * @param boolean $ksCustomerId
     */
    public function ksSellerCategorySave($ksCustomerId)
    {
        $ksSelectCategoryIds = $this->ksCategoryHelper->getKsAllCategoriesIds(0);

        $ksSellerIsInit = $this->ksCategoryRequestsAllowedFactory->create()->getCollection()
                ->addFieldToFilter('ks_seller_id', $ksCustomerId)
                ->addFieldToFilter('ks_is_init', 1);

        if ($ksSellerIsInit->getSize()==0) {
            foreach ($ksSelectCategoryIds as $ksCategoryId) {
                $ksSellerCategoryCol = $this->ksSellerCategoriesFactory->create()->getCollection()
                ->addFieldToFilter('ks_seller_id', $ksCustomerId)
                ->addFieldToFilter('ks_category_id', $ksCategoryId);

                if ($ksSellerCategoryCol->getSize()==0) {
                    $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
                    $ksData = [
                           "ks_seller_id" => $ksCustomerId,
                           "ks_category_id" => $ksCategoryId,
                           "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
                          ];

                    $ksCategoriesFactory->addData($ksData)->save();
                }
            }
        }
    }

    /**
     * Get Login as Seller Footer Text
     * @return string
     */
    public function getKsSellerLoginFooterText()
    {
        return $this->ksDataHelper->getKsConfigLoginAndRegistrationSetting('ks_seller_login_footer_title');
    }

    /**
     * Add Product Type to Seller Id When Seller Register
     * @param  $ksSellerId [description]
     * @return void
     */
    public function ksAddProductTypeInSellerTable($ksSellerId)
    {
        //Getting Collection of Seller Product Type
        $ksProductTypeCollection = $this->ksTypeFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', 0);
        // Intialize array
        $ksTypeArr = [];
        // Assigned Value
        $ksAssignStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_ASSIGNED;
        // Enable Value
        $ksEnableStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
        // Check Collection has size
        if ($ksProductTypeCollection->getSize()) {
            $ksTypeArr = $ksProductTypeCollection->getColumnValues('ks_product_type');
        }
        if (!empty($ksTypeArr)) {
            $ksSellerType = $this->ksTypeFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId);
            if ($ksSellerType->getSize() == 0) {
                foreach ($ksTypeArr as $ksType) {
                    $ksTypeModel = $this->ksTypeFactory->create();
                    $ksTypeModel->setKsSellerId($ksSellerId)
                      ->setKsProductType($ksType)
                      ->setKsProductTypeStatus($ksEnableStatus)
                      ->setKsRequestStatus($ksAssignStatus)
                      ->save();
                }
            }
        }
    }
}
