<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright(c) Ksolves India Limited(https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\SellerLocator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * SellerLocator View Controller Class.
 */
class View extends Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
     */
    protected $ksHelperData;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory
     */
    protected $ksProductCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     */
    protected $ksSellerCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $ksCustomerFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $ksCatalogCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory
     */
    protected $ksSellerLocatorFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Block\Frontend\SellerLocator\KsSellerLocatorView $ksSellerLocatorView
     */
    protected $ksSellerLocatorView;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $ksScopeConfig;

    /**
     * SellerLocator View Constructor
     *
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksCatalogCollection
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorFactory
     * @param \Ksolves\MultivendorMarketplace\Block\Frontend\SellerLocator\KsSellerLocatorView $ksSellerLocatorView
     * @param \Magento\Framework\Registry $ksRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $ksStoreManager
     * @param ScopeConfigInterface $ksScopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerLocatorHelper $ksHelperData,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProduct\CollectionFactory $ksProductCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollection,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $ksCustomerFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $ksCatalogCollection,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorFactory,
        \Ksolves\MultivendorMarketplace\Block\Frontend\SellerLocator\KsSellerLocatorView $ksSellerLocatorView,
        \Magento\Framework\Registry $ksRegistry,
        \Magento\Store\Model\StoreManagerInterface $ksStoreManager,
        ScopeConfigInterface $ksScopeConfig
    ) {
        $this->ksHelperData = $ksHelperData;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksProductCollection = $ksProductCollection;
        $this->ksSellerCollection = $ksSellerCollection;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksCustomerFactory = $ksCustomerFactory;
        $this->ksCatalogCollection = $ksCatalogCollection;
        $this->ksSellerLocatorFactory = $ksSellerLocatorFactory;
        $this->ksSellerLocatorView = $ksSellerLocatorView;
        $this->ksRegistry = $ksRegistry;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksScopeConfig = $ksScopeConfig;
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

            //Get Sellers
            $ksSellerData = $this->getKsAllSellers();

            //Get Sellers Id
            $ksSellerIdArray = $this->getKsFilterSellerId($ksSellerData);

            //Get Customer Collection
            $ksCollection = $this->getKsCustomerCollection();

            if ($this->ksScopeConfig->getValue('customer/account_share/scope')) {
                //Get Current Website Id
                $ksWebsiteId = $this->ksStoreManager->getStore()->getWebsiteId();

                //Get Customer Id of Current Website
                $ksCustomerId = $ksCollection->addFieldToFilter('website_id', $ksWebsiteId)->getColumnValues('entity_id');

                $ksSellerIdArray = array_intersect($ksSellerIdArray, $ksCustomerId);

                if ($ksSellerIdArray) {
                    $ksSellerData->addFieldToFilter('ks_seller_id', $ksSellerIdArray);
                }
            }

            if (!empty($ksFilterData)) {
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
                if (($ksLatitude || $ksLongitude) && $ksStoreName==null && $ksProductName==null && ($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Location
                    $ksSellerStore = $ksSellerData;
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                        $this->setKsDataRegistry($ksSellerStore);
                    }
                } elseif (($ksLatitude || $ksLongitude) && $ksStoreName && $ksProductName==null && ($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Store Name & Location Both
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromSellerName = $this->getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromSellerName, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromSellerName = $this->getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromSellerName, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif (($ksLatitude || $ksLongitude) && $ksStoreName== null && $ksProductName && ($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Product Name & Location Both
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductName = $this->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductName = $this->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif (($ksLatitude || $ksLongitude) && $ksStoreName && $ksProductName && ($ksDistance==null || $ksDistance==0)) {
                    //Filter Seller By Store Name, Product Name & Location.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductSeller = $this->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductSeller = $this->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdsFromLocation = $this->getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdsFromLocation);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude)  && $ksStoreName==null && $ksProductName==null) {
                    //Filter Seller By Location & Distance.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerStore = $this->getKsFilterSellerByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance, $ksSellerData);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerStore = $this->getKsFilterSellerByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance, $ksSellerData);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude)  && $ksStoreName && $ksProductName==null) {
                    //Filter Seller By Store Name, Location & Distance.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromSellerName = $this->getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromSellerName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromSellerName = $this->getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromSellerName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude) && $ksProductName && $ksStoreName==null) {
                    //Filter Seller By Product Name, Location & Distance.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductName = $this->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductName = $this->getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductName, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                } elseif ($ksDistance!=0 && ($ksLatitude && $ksLongitude)  && $ksStoreName && $ksProductName) {
                    //ALL :Filter Seller By Store Name, Product Name, Location & Distance all.
                    if (isset($ksData['sortby'])) {
                        //When user change sort by
                        $ksSellerIdsFromProductSeller = $this->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                    } else {
                        //Set Autofilled Distance(NeartoFar)
                        $ksSellerIdsFromProductSeller = $this->getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData);
                        $ksSellerIdByDistance = $this->getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance);
                        $ksSellerStore = $this->getKsFilterSellerByCommonId($ksSellerIdsFromProductSeller, $ksSellerIdByDistance);
                        $this->setKsDataRegistry($ksSellerStore);
                        $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, 'Distance(NeartoFar)');
                        $this->ksRegistry->register('ks_sellerLocator_sortby', 'Distance(NeartoFar)');
                    }
                }
            } else {
                $ksSellerStore = $ksSellerData;
                $this->setKsDataRegistry($ksSellerStore);
            }

            //Sort By Filter
            try {
                if ($ksData['sortby'] != 'Distance(NeartoFar)' && $ksData['sortby'] != 'Distance(FartoNear)') {
                    $this->ksRegistry->register('ks_sellerLocator_sortby', $ksData['sortby']);
                } else {
                    $this->getKsNearFarSellersFromCenter($ksSellerStore, $ksLatitude, $ksLongitude, $ksData['sortby']);
                    $this->ksRegistry->register('ks_sellerLocator_sortby', $ksData['sortby']);
                }
            } catch (\Exception $e) {
                $ksCollection = $this->ksSellerCollection->create()->addFieldToFilter('ks_seller_status', 1);
            }
        
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Seller Locator'));

            return $ksResultPage;
        }
    }

    /**
     * Set Sellers in Registry
     * @param array $ksSellerStore
     */
    public function setKsDataRegistry($ksSellerStore)
    {
        $this->ksRegistry->register('ks_sellerLocator_filter', $ksSellerStore);
    }

    /**
     * Get Customer Collection
     * @return array
     */
    public function getKsCustomerCollection()
    {
        return $this->ksCustomerFactory->create();
    }

    /**
     * Get Filter Sellers By Store Name
     * @param mixed $ksStoreName,$ksSellerData
     * @return array
     */
    public function getKsFilterSellerByStoreName($ksStoreName, $ksSellerData)
    {
        $ksCustomerCollection = $ksSellerData->addFieldToFilter('ks_store_name', ['like' =>$ksStoreName.'%']);
        $ksCustomerId= $this->getKsFilterSellerId($ksCustomerCollection);
        $ksSellerStore = $ksSellerData->addFieldToFilter('ks_seller_id', ['in' => $ksCustomerId])->addFieldToFilter('ks_seller_status', 1);
        return $ksSellerStore;
    }


    /**
     * Get all approved sellers who provide location
     * @return array
     */
    public function getKsAllSellers()
    {
        $ksSeller = $this->ksSellerLocatorFactory->create()->addFieldToFilter('ks_latitude', ['like'=>"_%"])->addFieldToFilter('ks_longitude', ['like'=>"_%"]);
        $ksCustomerId= $this->getKsFilterSellerId($ksSeller);
        $ksSellerStore = $this->ksSellerCollection->create()->addFieldToFilter('ks_store_status', 1)->addFieldToFilter('ks_seller_id', ['in' => $ksCustomerId]);
        return $ksSellerStore;
    }

    /**
     * Get Filter Sellers Id By Store Name
     * @param mixed $ksStoreName,$ksSellerData
     * @return array
     */
    public function getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData)
    {
        $ksSellerStore = $this->getKsFilterSellerByStoreName($ksStoreName, $ksSellerData);
        $ksSeller = $ksSellerStore->getData();
        $ksSellerId=[];
        foreach ($ksSeller as $ksRecord) {
            $ksSellerId[] = $ksRecord['ks_seller_id'];
        }
        return $ksSellerId;
    }

    /**
     * Get Filter Sellers By Product Name
     * @param mixed $ksProductName,$ksSellerIdArray
     * @return array
     */
    public function getKsFilterSellerByProductName($ksProductName, $ksSellerIdArray)
    {
        $ksCollection = $this->ksCatalogCollection->create()->addAttributeToSelect('*')->addFieldToFilter('status', 1)->addAttributeToFilter('name', ['like' =>$ksProductName]);
        $ksProductId = $this->getKsFilterCustomerId($ksCollection);
        $ksProductSellerCollection = $this->ksProductCollection->create()->addFieldToFilter('ks_product_id', ['in' => $ksProductId])->addFieldToFilter('ks_product_approval_status', 1)->addFieldToFilter('ks_seller_id', ['in' => $ksSellerIdArray]);
        return $ksProductSellerCollection;
    }

    /**
     * Get Filter Sellers Id By Product Name
     * @param mixed $ksProductName,$ksSellerIdArray
     * @return array
     */
    public function getKsFilterSellerIdByProductName($ksProductName, $ksSellerIdArray)
    {
        $ksProductSellerCollection = $this->getKsFilterSellerByProductName($ksProductName, $ksSellerIdArray);
        $ksSellerIdsFromProductName = $this->getKsFilterSellerId($ksProductSellerCollection);
        return $ksSellerIdsFromProductName;
    }

    /**
     * Get Filter Sellers Id By Store Name & Product Name
     * @param mixed $ksStoreName,$ksProductName,$ksSellerData
     * @return array
     */
    public function getKsFilterSellerIdBySellerProductName($ksStoreName, $ksProductName, $ksSellerData)
    {
        $ksSellerIdsFromSellerName = $this->getKsFilterSellerIdByStoreName($ksStoreName, $ksSellerData);
        $ksProductSellerCollection = $this->getKsFilterBySellerProductName($ksProductName, $ksSellerIdsFromSellerName);
        $ksSellerIdsFromProductSeller = $this->getKsFilterSellerId($ksProductSellerCollection);
        return $ksSellerIdsFromProductSeller;
    }

    /**
     * Get Filter Sellers By Store Name & Product Name
     * @param mixed $ksProductName,$ksSellerId
     * @return array
     */
    public function getKsFilterBySellerProductName($ksProductName, $ksSellerId)
    {
        $ksCollection = $this->ksCatalogCollection->create()->addAttributeToSelect('*')->addFieldToFilter('status', 1)->addAttributeToFilter('name', ['like' =>$ksProductName]);
        $ksProductId = $this->getKsFilterCustomerId($ksCollection);
        $ksProductSellerCollection = $this->ksProductCollection->create()->addFieldToFilter('ks_product_id', ['in' => $ksProductId])->addFieldToFilter('ks_product_approval_status', 1)->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId]);
        return $ksProductSellerCollection;
    }

    /**
     * Get Filter Sellers By Customer Location
     * @param mixed $ksLatitude,$ksLongitude,$ksSellerData
     * @return array
     */
    public function getKsFilterByLocation($ksSellerData, $ksAddress)
    {
        $ksSeller = $this->ksSellerLocatorFactory->create()->addFieldToFilter('ks_location', ['like' =>'%'.$ksAddress.'%']);
        $ksSellerId = $this->getKsFilterSellerId($ksSeller);
        $ksSellerStore = $ksSellerData->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId])->addFieldToFilter('ks_seller_status', 1);
        return $ksSellerStore;
    }

    /**
     * Get Filter Sellers Id By Location
     * @param mixed $ksLatitude,$ksLongitude,$ksSellerIdArray
     * @return array
     */
    public function getKsFilterSellerIdByLocation($ksSellerIdArray, $ksAddress)
    {
        $ksSellerData = $this->ksSellerLocatorFactory->create()->addFieldToFilter('ks_location', ['like' =>'%'.$ksAddress.'%'])->addFieldToFilter('ks_seller_id', ['in' => $ksSellerIdArray]);
        $ksSellerIdsFromLocation = $this->getKsFilterSellerId($ksSellerData);
        return $ksSellerIdsFromLocation;
    }

    /**
     * Get Filter Sellers By Commom Id of Applied Condition
     * @param array $ksSellerIdsFromName,$ksSellerIdsFromLocation
     * @return array
     */
    public function getKsFilterSellerByCommonId($ksSellerIdsFromName, $ksSellerIdsFromLocation)
    {
        $ksSellerIds = [];
        $ksSellerIds = array_intersect($ksSellerIdsFromName, $ksSellerIdsFromLocation);
        $ksSellerStore = $this->ksSellerCollection->create()->addFieldToFilter('ks_seller_id', ['in' => $ksSellerIds])->addFieldToFilter('ks_seller_status', 1);
        return $ksSellerStore;
    }

    /**
     * Get Filter Sellers within Range
     * @param mixed $ksLatitude,$ksLongitude,$ksUnit,$ksDistance
     * @return array
     */
    public function getKsFilterSellerByDistanceAdmin($ksLatitude, $ksLongitude, $ksUnit, $ksDistance)
    {
        $ksSellerData = $this->ksSellerLocatorFactory->create();
        $ksSellers = [];
        $ksSellerId = [];
        foreach ($ksSellerData as $ksSellerCollection) {
            $ksSellerLatitude = $ksSellerCollection->getKsLatitude();
            $ksSellerLongitude = $ksSellerCollection->getKsLongitude();
            $ksSellerWithinRange = $this->getKsSellersWithinRange($ksLatitude, $ksLongitude, $ksSellerLatitude, $ksSellerLongitude, $ksUnit);
            $ksSellers[$ksSellerCollection->getKsSellerId()]=$ksSellerWithinRange;
        }
        foreach ($ksSellers as $ksKey => $ksValue) {
            if ($ksValue <= $ksDistance) {
                $ksSellerId[] = $ksKey;
            }
        }
        $ksSellerStore = $this->ksSellerCollection->create()->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId])->addFieldToFilter('ks_seller_status', 1);
        return $ksSellerStore;
    }

    /**
     * Get Nearest and farthest Sellers from Center Point
     * @param float $ksCenterLatitude,$ksCenterLongitude
     * @return array
     */
    public function getKsNearFarSellersFromCenter($ksFilterSeller, $ksCenterLatitude, $ksCenterLongitude, $ksDistanceSortBy)
    {
        $ksSellerId=[];
        $ksFilterSellers = $ksFilterSeller->getData();
        foreach ($ksFilterSellers as $ksRecord) {
            $ksSellerId[] = $ksRecord['ks_seller_id'];
        }
        
        $ksSellerData = $this->ksSellerLocatorFactory->create()->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId]);
        $ksSellerIds = [];
        $ksSellers = [];
        $ksSellerCollection = $ksSellerData->getData();
        foreach ($ksSellerCollection as $ksCollection) {
            $ksSellerDistanceFromCenter = $this->getKsSellerDistanceFromCenter($ksCenterLatitude, $ksCenterLongitude, $ksCollection['ks_latitude'], $ksCollection['ks_longitude']);
            $ksSellers[$ksCollection['ks_seller_id']]=$ksSellerDistanceFromCenter;
        }
        if ($ksDistanceSortBy == 'Distance(FartoNear)') {
            arsort($ksSellers);
            foreach ($ksSellers as $ksKey => $ksValue) {
                $ksSellerIds[] = $ksKey;
            }
        }

        if ($ksDistanceSortBy == 'Distance(NeartoFar)') {
            asort($ksSellers);
            
            foreach ($ksSellers as $ksKey => $ksValue) {
                $ksSellerIds[] = $ksKey;
            }
        }
        $this->ksRegistry->register('ks_seller_id_near_far', $ksSellerIds);
    }

    /**
     * Get Filter Sellers within Range
     * @param mixed $ksLatitude,$ksLongitude,$ksUnit,$ksDistance,$ksSeller
     * @return array
     */
    public function getKsFilterSellerByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance, $ksSeller)
    {
        $ksSellerData = $this->ksSellerLocatorFactory->create();
        $ksSellers = [];
        $ksSellerId = [];
        foreach ($ksSellerData as $ksSellerCollection) {
            $ksSellerLatitude = $ksSellerCollection->getKsLatitude();
            $ksSellerLongitude = $ksSellerCollection->getKsLongitude();
            $ksSellerWithinRange = $this->getKsSellersWithinRange($ksLatitude, $ksLongitude, $ksSellerLatitude, $ksSellerLongitude, $ksUnit);
            $ksSellers[$ksSellerCollection->getKsSellerId()]=$ksSellerWithinRange;
        }
        foreach ($ksSellers as $ksKey => $ksValue) {
            if ($ksValue <= $ksDistance) {
                $ksSellerId[] = $ksKey;
            }
        }
        $ksSellerStore = $ksSeller->addFieldToFilter('ks_seller_id', ['in' => $ksSellerId])->addFieldToFilter('ks_seller_status', 1);
        return $ksSellerStore;
    }

    /**
     * Get Filter Sellers Id within Range
     * @param mixed $ksLatitude,$ksLongitude,$ksUnit,$ksDistance
     * @return array
     */
    public function getKsFilterSellerIdByDistance($ksLatitude, $ksLongitude, $ksUnit, $ksDistance)
    {
        $ksSellerData = $this->ksSellerLocatorFactory->create();
        $ksSellers = [];
        $ksSellerId = [];
        foreach ($ksSellerData as $ksSellerCollection) {
            $ksSellerLatitude = $ksSellerCollection->getKsLatitude();
            $ksSellerLongitude = $ksSellerCollection->getKsLongitude();
            $ksSellerWithinRange = $this->getKsSellersWithinRange($ksLatitude, $ksLongitude, $ksSellerLatitude, $ksSellerLongitude, $ksUnit);
            $ksSellers[$ksSellerCollection->getKsSellerId()]=$ksSellerWithinRange;
        }
        foreach ($ksSellers as $ksKey => $ksValue) {
            if ($ksValue <= $ksDistance) {
                $ksSellerId[] = $ksKey;
            }
        }
        return $ksSellerId;
    }

    /**
     * Get Filter Customer Id
     * @param array $ksCollection
     * @return array
     */
    public function getKsFilterCustomerId($ksCollection)
    {
        $ksCustomerId=[];
        foreach ($ksCollection as $ksCollection) {
            $ksCustomerId[] = $ksCollection->getEntityId();
        }
        return $ksCustomerId;
    }

    /**
     * Get Filter Seller Id
     * @param array $ksCollection
     * @return array
     */
    public function getKsFilterSellerId($ksCollection)
    {
        $ksSellerId=[];
        foreach ($ksCollection as $ksRecord) {
            $ksSellerId[] = $ksRecord->getKsSellerId();
        }
        return $ksSellerId;
    }

    /**
     * Calculate Distance Between two sellers to find all sellers of given Range
     * @param array $ksLatitudeInitial, $ksLongitudeInitial, $ksLatitudeFinal, $ksLongitudeFinal, $ksUnit, $ksDecimals
     * @return float
     */
    public function getKsSellersWithinRange($ksLatitudeInitial, $ksLongitudeInitial, $ksLatitudeFinal, $ksLongitudeFinal, $ksUnit, $ksDecimals = 2)
    {
        // Calculate the distance in degrees
        $ksDegrees = rad2deg(acos((sin(deg2rad($ksLatitudeInitial))*sin(deg2rad($ksLatitudeFinal)))
            + (cos(deg2rad($ksLatitudeInitial))*cos(deg2rad($ksLatitudeFinal))*cos(deg2rad($ksLongitudeInitial-$ksLongitudeFinal)))));
        
        // Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
        switch ($ksUnit) {
            case 'km':
                $ksDistance = $ksDegrees * 111.13384;
                // 1 degree = 111.13384 km, based
                // on the average diameter of the Earth (12,735 km)
                break;
            case 'miles':
                $ksDistance = $ksDegrees * 69.05482;
                // 1 degree = 69.05482 miles, based on
                // the average diameter of the Earth (7,913.1 miles)
                break;
        }
        return round($ksDistance, $ksDecimals);
    }

    /**
     * Calculate all Sellers Distance from center point to find nearest and farthest sellers to center location of Map
     * @param array $ksCenterLatitude, $ksCenterLongitude, $ksSellerLatitude, $ksSellerLongitude, $ksDecimals
     * @return float
     */
    public function getKsSellerDistanceFromCenter($ksCenterLatitude, $ksCenterLongitude, $ksSellerLatitude, $ksSellerLongitude, $ksDecimals = 2)
    {
        // Calculate the distance in degrees
        $ksDegrees = rad2deg(acos((sin(deg2rad($ksCenterLatitude))*sin(deg2rad($ksSellerLatitude)))
            + (cos(deg2rad($ksCenterLatitude))*cos(deg2rad($ksSellerLatitude))*cos(deg2rad($ksCenterLongitude-$ksSellerLongitude)))));
        
        // Convert the distance in degrees to the unit(km)
        $ksDistance = $ksDegrees * 111.13384;
        // 1 degree = 111.13384 km, based
        // on the average diameter of the Earth (12,735 km)
        return round($ksDistance, $ksDecimals);
    }
}
