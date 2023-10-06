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
namespace Ksolves\MultivendorMarketplace\Controller\SellerLocator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Map Controller Class.
 */
class Map extends Action
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $ksContext;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;
    
    /**
     * @var CollectionFactory
     */
    protected $ksProductsCollection;

    /**
     * @var KsProductHelper
     */
    protected $ksProductHelper;

    /**
    * @var  RequestInterface
    */
    protected $ksRequestInterface;

    /**
    * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
    */
    protected $ksSellerCollection;

    /**
    * @var \Ksolves\MultivendorMarketplace\Controller\SellerLocator\View
    */
    protected $ksView;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory
     */
    protected $ksSellerLocatorFactory;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $ksStoreManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
     */
    protected $ksHelperData;
    
    /**
     * @param \Magento\Backend\Block\Template\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param Registry $ksRegistry
     * @param CollectionFactory $ksProductsCollection
     * @param KsProductHelper $ksProductHelper
     * @param RequestInterface $ksRequestInterface
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorFactory
     * @param \Ksolves\MultivendorMarketplace\Controller\SellerLocator\View $ksView
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param ScopeConfigInterface $ksScopeConfig
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksRegistry,
        CollectionFactory $ksProductsCollection,
        KsProductHelper $ksProductHelper,
        RequestInterface $ksRequestInterface,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorFactory,
        \Ksolves\MultivendorMarketplace\Controller\SellerLocator\View $ksView,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        ScopeConfigInterface $ksScopeConfig,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksRegistry = $ksRegistry;
        $this->ksProductsCollection = $ksProductsCollection;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksRequestInterface = $ksRequestInterface;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksSellerLocatorFactory = $ksSellerLocatorFactory;
        $this->ksView = $ksView; 
        $this->ksStoreManager = $ksStoreManager;
        $this->ksScopeConfig = $ksScopeConfig;
        $this->ksHelperData = $ksHelperData;
        parent::__construct($ksContext);
    }

    public function execute()
    {
        if (!($this->ksHelperData->isKsSLEnabled())) {
            return $this->resultRedirectFactory->create()->setPath(
                '',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            $ksFilterData = $this->getRequest()->getParams();
            $ksData = $ksFilterData;
            unset($ksFilterData['sortby']);
            unset($ksFilterData['view']);

            //Get all assigned sellers to product
            $ksSellerData = $this->getKsFilterSeller();

            //Get Sellers Id of assigned sellers to product
            $ksSellerIdArray = $this->ksView->getKsFilterSellerId($ksSellerData);

            //Get Customer Collection
            $ksCollection = $this->ksView->getKsCustomerCollection();

            if ($this->ksScopeConfig->getValue('customer/account_share/scope')) {
                //Get Current Website Id
                $ksWebsiteId = $this->ksStoreManager->getStore()->getWebsiteId();

                //Get Customer Id of Current Website
                $ksCustomerId = $ksCollection->addFieldToFilter('website_id', $ksWebsiteId)->getColumnValues('entity_id');

                $ksSellerIdArray = array_intersect($ksSellerIdArray, $ksCustomerId);

                $ksSellerData->addFieldToFilter('ks_seller_id', $ksSellerIdArray);
            }

            if (array_key_exists("store_name", $ksFilterData)) {
                //Get all get of form url
                $ksStoreName = $ksFilterData['store_name'];
                $ksProductName = $ksFilterData['product_name'];
                $ksFullAddress = $ksFilterData['address'];
                $ksLatitude = $ksFilterData['lat'];
                $ksLongitude = $ksFilterData['lng'];
                $ksDistance = $ksFilterData['distance'];
                $ksUnit = $ksFilterData['unit'];

                //Save all data url in form field
                $this->ksRegistry->register('ks_store_name', $ksStoreName);
                $this->ksRegistry->register('ks_product_name', $ksProductName);
                $this->ksRegistry->register('ks_distance', $ksDistance);
                $this->ksRegistry->register('ks_address', $ksFullAddress);
                $this->ksRegistry->register('ks_distance_unit', $ksUnit);

                $ksAddressFirstWord = explode(',', trim($ksFullAddress));
                $ksAddress  =$ksAddressFirstWord[0];
                if (($ksLatitude || $ksLongitude) && $ksStoreName== null && $ksProductName && ($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Product Name & Location Both
                    $ksSellerStore = $ksSellerData;
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $this->ksView->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', 'Distance(NeartoFar)');
                        $this->setKsDataRegistry($ksSellerStore);
                    }
                } elseif (($ksLatitude || $ksLongitude) && $ksStoreName && $ksProductName &&($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Store Name, Product Name & Location.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductSeller = $this->ksView->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->ksView->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductSeller = $this->ksView->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->ksView->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->ksView->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude) && $ksProductName && $ksStoreName==null) {
                    //Filter Seller By Product Name, Location & Distance.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductName = $this->ksView->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdByDistance = $this->ksView->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductName = $this->ksView->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdByDistance = $this->ksView->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->ksView->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude)  && $ksStoreName && $ksProductName) {
                    //ALL :Filter Seller By Store Name, Product Name, Location & Distance all.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductSeller = $this->ksView->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdByDistance = $this->ksView->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductSeller = $this->ksView->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdByDistance = $this->ksView->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->ksView->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->ksView->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', 'Distance(NeartoFar)');
                    }
                }
            } else {
                $ksSellerStore = $ksSellerData;
                $this->setKsDataRegistry($ksSellerStore);
            }

            if (array_key_exists("sortby", $ksData)) {
                if ($ksData['sortby'] != 'Distance(NeartoFar)' && $ksData['sortby'] != 'Distance(FartoNear)') {
                    $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', $ksData['sortby']);
                } else {
                    $this->ksView->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, $ksData['sortby']);
                    $this->ksRegistry->register('ks_sellerLocator_assigned_seller_sortby', $ksData['sortby']);
                }
            }

            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Seller Locator'));
            return $ksResultPage;
        }
    }

    /**
     * Set Assigned Sellers in Registry
     * @param array $ksSellerStore
     */
    public function setKsDataRegistry($ksSellerStore)
    {
        $this->ksRegistry->register('ks_sellerLocator_assigned_seller', $ksSellerStore);
    }

    /**
     * Get Filter Sellers
     * @return array
     */
    public function getKsFilterSeller()
    {
        $ksParentProductId = $this->ksRequestInterface->getParam('product_id');
        
        // Create Product Collection
        $ksCollection = $this->ksProductsCollection->create()->addAttributeToSelect('*')->addFieldToFilter('status', 1);

        //get parent product id
        $ksCollection->joinField(
            'ks_parent_product_id',
            'ks_product_details',
            'ks_parent_product_id',
            'ks_product_id=entity_id',
            [],
            'left'
        );

        // Remove Check Seller Status
        $ksCollection = $this->ksProductHelper->ksCheckSellerStatus($ksCollection);

        $ksCollection->addFieldToFilter('ks_seller_id', ['neq'=>''])->addFieldToFilter('ks_parent_product_id', ['eq'=> $ksParentProductId])->addFieldToFilter('ks_seller_status', 1)->addFieldToFilter('ks_store_status', 1)->addFieldToFilter('ks_product_approval_status', 1);

        $ksSellersFromProduct = $ksCollection->getData();
        $ksSellerId = [];

        foreach ($ksSellersFromProduct as $ksSellerFromProduct) {
            $ksSellerId[] = $ksSellerFromProduct['ks_seller_id'];
        }
            
        $ksSellerId[] = $this->ksRequestInterface->getParam('seller_id');

        $ksSeller = $this->ksSellerLocatorFactory->create()->addFieldToFilter('ks_latitude', ['like'=>"_%"])->addFieldToFilter('ks_longitude', ['like'=>"_%"])->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId]);
        $ksCustomerId= $this->ksView->getKsFilterSellerId($ksSeller);
        $ksSellerCollection = $this->ksSellerCollection->create()->addFieldToFilter('ks_store_status', 1)->addFieldToFilter('ks_seller_id', ['in' => $ksCustomerId]);
        return $ksSellerCollection;
    }
}
