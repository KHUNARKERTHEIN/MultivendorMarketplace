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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoriesCollection;
use Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowedFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory as KsCategoryRequestsAllowedCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\Http;
use Magento\Framework\File\Uploader;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;

/**
 * Class Save Controller
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * XML Path
     */
    const KS_IMAGE_TMP_PATH = 'ksolves/tmp/multivendor';
    const KS_IMAGE_PATH = 'ksolves/multivendor';
    const XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_rejection_email_template';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $ksCoreFileStorageDatabase;

    /**
     * @var KsSellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;

    /**
     * @var KsSellerCategoriesCollection
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var KsCategoryRequestsAllowedFactory
     */
    protected $ksCategoryRequestsAllowedFactory;

    /**
     * @var KsCategoryRequestsAllowedCollection
     */
    protected $ksCategoryRequestsAllowedCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory
     */
    protected $ksCategoryRequestsCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory
     */
    protected $ksSellerConfigFactory;

    /*
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $ksCacheManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $KsDataHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductHelper;

    /**
     * @var  KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $ksAttributeFactory;
    
    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory
     */
    protected $ksProductTypeFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerSitemapFactory
     */
    protected $ksSellerSitemapFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $ksFileUploaderFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory 
     */
    protected $ksSellerLocatorCollection;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerLocatorFactory
     */
    protected $ksSellerLocatorFactory;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    protected $ksMediaDirectory;

    /**
     * @var Http
     */
    protected $ksRequest;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory
     * @param \Magento\Framework\Filesystem $ksFilesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection
     * @param KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param KsSellerCategoriesCollection $ksSellerCategoriesCollection
     * @param KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection
     * @param KsCategoryRequestsAllowedCollection $ksCategoryRequestsAllowedCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerLocatorFactory $ksSellerLocatorFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory
     * @param \Magento\Framework\App\Cache\Manager $ksCacheManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param Http $ksRequest
     * @param Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper
     * @param KsCategoryRequests $ksCategoryHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $ksAttributeFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory $ksProductTypeFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerSitemapFactory $ksSellerSitemapFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory,
        \Magento\Framework\Filesystem $ksFilesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $ksCoreFileStorageDatabase,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection,
        KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        KsSellerCategoriesCollection $ksSellerCategoriesCollection,
        KsCategoryRequestsAllowedFactory $ksCategoryRequestsAllowedFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerLocator\CollectionFactory $ksSellerLocatorCollection,
        KsCategoryRequestsAllowedCollection $ksCategoryRequestsAllowedCollection,
        \Ksolves\MultivendorMarketplace\Model\KsSellerLocatorFactory $ksSellerLocatorFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigFactory,
        \Magento\Framework\App\Cache\Manager $ksCacheManager,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        Http $ksRequest,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductHelper $ksProductHelper,
        KsCategoryRequests $ksCategoryHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $ksAttributeFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductType\CollectionFactory $ksProductTypeFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerSitemapFactory $ksSellerSitemapFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksCoreFileStorageDatabase = $ksCoreFileStorageDatabase;
        $this->ksFileUploaderFactory = $fileUploaderFactory;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection; 
        $this->ksCategoryRequestsAllowedFactory = $ksCategoryRequestsAllowedFactory;
        $this->ksSellerLocatorCollection = $ksSellerLocatorCollection;
        $this->ksCategoryRequestsAllowedCollection = $ksCategoryRequestsAllowedCollection;
        $this->ksSellerLocatorFactory = $ksSellerLocatorFactory;
        $this->ksSellerConfigFactory = $ksSellerConfigFactory;
        $this->ksCacheManager = $ksCacheManager;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksRequest= $ksRequest;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksProductTypeFactory = $ksProductTypeFactory;
        $this->ksSellerSitemapFactory  = $ksSellerSitemapFactory;
        parent::__construct($ksContext);
    }

    /**
     * Retrieve path
     *
     * @param string $ksPath
     * @param string $ksImageName
     *
     * @return string
     */
    public function getKsFilePath($ksPath, $ksImageName)
    {
        return rtrim($ksPath, '/') . '/' . ltrim($ksImageName, '/');
    }

    /**
     * Checking file for moving and move it
     *
     * @param string $ksImageName
     *
     * @return string
     *
     * @throws LocalizedException
     */
    public function ksMoveFileFromTmp($ksImageName)
    {
        $ksBaseTmpPath = self::KS_IMAGE_TMP_PATH;
        $ksBasePath = self::KS_IMAGE_PATH;

        $ksBaseImagePath = $this->getKsFilePath(
            $ksBasePath,
            Uploader::getNewFileName(
                $this->ksMediaDirectory->getAbsolutePath(
                    $this->getKsFilePath($ksBasePath, $ksImageName)
                )
            )
        );
        $ksBaseTmpImagePath = $this->getKsFilePath($ksBaseTmpPath, $ksImageName);

        try {
            $this->ksCoreFileStorageDatabase->copyFile(
                $ksBaseTmpImagePath,
                $ksBaseImagePath
            );
            $this->ksMediaDirectory->renameFile(
                $ksBaseTmpImagePath,
                $ksBaseImagePath
            );
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }
        return str_replace('ksolves/multivendor/', '', $ksBaseImagePath);
    }

    /**
     * execute action
     * Save action
     */
    public function execute()
    {
        $ksData = [];
        $ksSellerStoreEntityId = '';
        $ksSellerSitemapEntityId = '';
        $ks_store_sitemap_id=0;
        // get form data
        $ksPostData = $this->getRequest()->getPostValue();
        // get the url to redirect to seller list or pending approval seller list
        if (str_contains($this->_redirect->getRefererUrl(), 'multivendor/seller/pendingedit')) {
            $ksRedirectUrl = '*/*/sellerpendinglist';
        } else {
            $ksRedirectUrl = '*/*/';
        }
        $this->KsSellerLocationSave($ksPostData);
        //set categories data
        $this->KsCategoryDataSave($ksPostData);
        //save seller config data
        $this->KsSellerConfigDataSave($ksPostData);

        unset($ksPostData['ks_seller_account_details_tab']['ks_approval_section']['ks_created_at']);
        if ($ksPostData) {
            if (array_key_exists('ks_store_id', $ksPostData['ks_seller_account_details_tab']['ks_public_profile_section'])) {
                $ks_store_sitemap_id=$ksPostData['ks_seller_account_details_tab']['ks_public_profile_section']['ks_store_id'];
            }
        }
        if (!array_key_exists('ks_sitemap_section', $ksPostData['ks_seller_account_details_tab'])) {
            $ksPostData['ks_seller_account_details_tab']['ks_sitemap_section']= ['ks_included_sitemap_profile' => $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ks_store_sitemap_id) ,'ks_included_sitemap_product' => $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ks_store_sitemap_id)];
        }
        // merge data from all tabs of seller edit form
        $ksData = array_merge($ksData, $ksPostData['ks_seller_account_details_tab']['ks_public_profile_section'], $ksPostData['ks_seller_account_details_tab']['ks_company_details_section'], $ksPostData['ks_seller_account_details_tab']['ks_approval_section'], $ksPostData['ks_customer_support_tab'], $ksPostData['ks_seo_details_tab'], $ksPostData['ks_seller_product_type_tab'], $ksPostData['ks_seller_product_tab'], $ksPostData['ks_seller_product_attribute_tab'], $ksPostData['ks_seller_account_details_tab']['ks_sitemap_section']);

        // To check Social media tab is visible
        if (isset($ksPostData['ks_social_media_tab'])) {
            $ksData = array_merge($ksData, $ksPostData['ks_social_media_tab']);
        }
        // To check store_policies_details media tab is visible
        if (isset($ksPostData['ks_store_policies_details_tab'])) {
            $ksData = array_merge($ksData, $ksPostData['ks_store_policies_details_tab']);
        }
        $ksSellerData = $this->ksSellerFactory->create()
        ->getCollection()
        ->addFieldToFilter('id', $ksData['id'])
        ->getData();

        // check data
        if ($ksData) {
            $ksId = $ksData['id'];
            $ksStoreId = 0;
            if (array_key_exists("ks_store_id", $ksData)) {
                $ksStoreId = $ksData['ks_store_id'];
            }

            // Check for Product Attribute Request then Auto Approval will be given
            if (!($ksData['ks_product_attribute_request_allowed_status'])) {
                unset($ksData['ks_product_attribute_auto_approval_status']);
            }

            // Check for Product Type Request then Auto Approval will be given
            if (!($ksData['ks_seller_producttype_request_status'])) {
                $ksData['ks_producttype_auto_approval_status'] = 0;
            }

            try {
                if ($ksSellerData[0]['ks_store_url']!=$ksData['ks_store_url']) {
                    $ksStoreUrlList = $this->ksSellerFactory->create()->getCollection()
                    ->addFieldToFilter('ks_store_url', $ksData['ks_store_url']);

                    if ($ksStoreUrlList->getSize() > 0) {
                        throw new LocalizedException(__('Store URL already exists.'));
                    }

                    // target url seller store
                    $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksData['ks_seller_id'].'/';
                    //delete seller store url rewrite
                    $this->ksSellerHelper->ksRedirectUrlDelete($ksTargetPathUrl);
                    // request url seller store
                    $ksRequestedPathUrl ="multivendor/".$ksData['ks_store_url'];
                    // url rewrite for seller store
                    $this->ksSellerHelper->ksSellerStoreUrlRedirect($ksTargetPathUrl, $ksRequestedPathUrl);
                }

                //get seller collection
                $ksSellerModel = $this->ksSellerFactory->create();

                //get seller store collection
                $ksSellerStoreModel = $this->ksSellerStoreFactory->create();

                //get seller_sitemap collection
                $ksSellerSitemapModel = $this->ksSellerSitemapFactory->create();

                // check id
                if ($ksData['id']) {
                    // load seller model class
                    $ksSellerModel = $ksSellerModel->load($ksData['id']);

                    $ksStoreStatusBefore = $ksSellerModel->getKsStoreStatus();

                    //Email functionality
                    $ksSellerStatus = $ksData['ks_seller_status'];
                    $ksRejectReason = $ksData['ks_rejection_reason'];
                    if ($ksSellerModel->getKsSellerStatus() != $ksSellerStatus) {
                        if ($ksSellerStatus == 1) {
                            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_seller/ks_seller_settings/ks_seller_approved_templates');
                            if ($ksEmailEnabled != "disable") {
                                $this->ksSendSellerEmail($ksEmailEnabled, $ksData['ks_seller_id'], $ksSellerModel, $ksRejectReason);
                            }
                        } elseif ($ksSellerStatus == 2) {
                            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_seller/ks_seller_settings/ks_seller_rejection_templates');
                            if ($ksEmailEnabled != "disable") {
                                $this->ksSendSellerEmail($ksEmailEnabled, $ksData['ks_seller_id'], $ksSellerModel, $ksRejectReason);
                            }
                        }
                    }
                    // check store id
                    if (isset($ksData['ks_store_id'])) {
                        $ksSellerStoreCollection = $ksSellerStoreModel->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])->addFieldToFilter('ks_store_id', $ksData['ks_store_id']);
                    } else {
                        $ksSellerStoreCollection = $ksSellerStoreModel->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])->addFieldToFilter('ks_store_id', 0);
                    }

                    foreach ($ksSellerStoreCollection as $ksValue) {
                        $ksSellerStoreEntityId = $ksValue->getId();
                        // load seller store model class
                        $ksSellerStoreModel = $ksSellerStoreModel->load($ksSellerStoreEntityId);
                    }
                }
                if ($ksData['ks_store_available_countries']) {
                    $ksData['ks_store_available_countries'] = implode(',', $ksData['ks_store_available_countries']);
                }
                $ksData['ks_company_country'] = $ksData['country_id'];

                $ksSellerModel->setData($ksData);

                // check seller status to set rejection reason and store status
                if ($ksData['ks_seller_status'] == 1) {
                    $ksSellerModel->setData('ks_rejection_reason', " ");
                } elseif ($ksData['ks_seller_status'] == 0) {
                    $ksSellerModel->setData('ks_rejection_reason', " ");

                    // check store status
                    if ($ksData['ks_store_status'] != 0) {
                        $this->messageManager->addError(__('Store of pending seller can\'t be enabled.'));
                        $ksSellerModel->setData('ks_store_status', 0);
                    }
                } elseif ($ksData['ks_seller_status'] == 2) {
                    // check store status
                    if ($ksData['ks_store_status'] != 0) {
                        $this->messageManager->addErrorMessage(__('Store of rejected seller can\'t be enabled.'));
                        $ksSellerModel->setData('ks_store_status', 0);
                    }
                }

                // If Attribute Request is Disable
                if (!($ksData['ks_product_attribute_request_allowed_status'])) {
                    // Send the Seller Id to Function for Rejecting the pending attribute
                    $this->ksRejectSellerAttribute($ksData['ks_seller_id']);
                }

                if (!($ksData['ks_seller_producttype_request_status'])) {
                    // Send the Seller Id to Function for Rejecting the pending product type
                    $this->ksRejectSellerProductType($ksData['ks_seller_id']);
                }

                $ksSellerModel->save();

                // unset id
                unset($ksData['id']);

                //save store logo
                if (isset($ksData['ks_store_logo'][0]['name']) && isset($ksData['ks_store_logo'][0]['tmp_name'])) {
                    $ksStoreLogo = $this->ksMoveFileFromTmp($ksData['ks_store_logo'][0]['name']);
                    $ksData['ks_store_logo'] = $ksStoreLogo;
                } elseif (isset($ksData['ks_store_logo'][0]['name'])) {
                    $ksData['ks_store_logo'] = $ksData['ks_store_logo'][0]['name'];
                } else {
                    $ksData['ks_store_logo'] = '';
                }

                //save store banner
                if (isset($ksData['ks_store_banner'][0]['name']) && isset($ksData['ks_store_banner'][0]['tmp_name'])) {
                    $ksStoreBanner = $this->ksMoveFileFromTmp($ksData['ks_store_banner'][0]['name']);
                    $ksData['ks_store_banner'] = $ksStoreBanner;
                } elseif (isset($ksData['ks_store_banner'][0]['name'])) {
                    $ksData['ks_store_banner'] = $ksData['ks_store_banner'][0]['name'];
                } else {
                    $ksData['ks_store_banner'] = '';
                }

                // If Store Id is not present means 0 then Change all the default values of other stores
                if (!array_key_exists('ks_store_id', $ksData)) {
                    $this->ksDefaultValueinOtherStore($ksData);
                    //assign default value to other stre in seller_Sitemap
                    $this->ksSitemapDefaultValueinOtherStore($ksData);
                // Else if store is not 0 then check value which is set as
                // default and set their value as default value
                } else {
                    $ksData = $this->getKsDefaultValue($ksPostData, $ksData);
                }
                $ksSellerStoreModel->setData($ksData);
                if ($ksSellerStoreEntityId) {
                    $ksSellerStoreModel->setData('id', $ksSellerStoreEntityId);
                }
                $ksSellerStoreModel->save();
                
                // Save sitemap tab data
                $ks_store_id= 0;
                if (array_key_exists('ks_store_id', $ksData)) {
                    $ks_store_id=$ksData['ks_store_id'];
                }
                $ksSellerSitemapCollection = $ksSellerSitemapModel->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])->addFieldToFilter('ks_store_id', $ks_store_id);
                if ($ksSellerSitemapCollection) {
                    foreach ($ksSellerSitemapCollection as $ksValue) {
                        $ksSellerSitemapEntityId = $ksValue->getId();
                        // load seller sitemap model class
                        $ksSellerSitemapModel = $ksSellerSitemapModel->load($ksSellerSitemapEntityId);
                    }
                }
            
                if (!array_key_exists('ks_included_sitemap_profile', $ksData)) {
                    $ksData['ks_included_sitemap_profile']= $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ks_store_sitemap_id);
                }
                if (!array_key_exists('ks_included_sitemap_product', $ksData)) {
                    $ksData['ks_included_sitemap_product']= $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ks_store_sitemap_id);
                }


                $ksSellerSitemapModel->setData($ksData);
                if ($ksSellerSitemapEntityId) {
                    $ksSellerSitemapModel->setData('id', $ksSellerSitemapEntityId);
                }
                $ksSellerSitemapModel->save();


                $this->ksCacheManager->clean($this->ksCacheManager->getAvailableTypes());
                $this->messageManager->addSuccess(__('You saved the seller'));

                if ($ksStoreStatusBefore != (int) $ksSellerModel->getKsStoreStatus()) {
                    $this->_eventManager->dispatch('ksseller_store_change_after');
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the seller.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addException($e, $e->getMessage());
                $ksResultRedirect = $this->resultRedirectFactory->create();
                if ($ksStoreId) {
                    return $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['id' => $ksId , 'seller_id' => $ksData['ks_seller_id'],
                        '_current' => true, 'store' => $ksStoreId]);
                }
                return $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['id' => $ksId , 'seller_id' => $ksData['ks_seller_id'], '_current' => true]);
            }

            $ksResultRedirect = $this->resultRedirectFactory->create();
            //check for `back` parameter
            if ($this->getRequest()->getParam('back')) {
                if ($ksStoreId) {
                    return $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['id' => $ksId , 'seller_id' => $ksData['ks_seller_id'],
                        '_current' => true, 'store' => $ksStoreId]);
                }
                return $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['id' => $ksId , 'seller_id' => $ksData['ks_seller_id'], '_current' => true]);
            }

            $ksResultRedirect->setPath($ksRedirectUrl);
            return $ksResultRedirect;
        } else {
            $this->messageManager->addException($e, __('Something went wrong while saving the seller.'));
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
            return $ksResultRedirect;
        }

        $ksResultRedirect = $this->resultRedirectFactory->create();
        //check for `back` parameter
        if ($this->getRequest()->getParam('back')) {
            return $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['id' => $ksId , 'seller_id' => $ksData['ks_seller_id'], '_current' => true]);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
        //for redirecting url
        $ksResultRedirect->setPath($ksRedirectUrl);
        return $ksResultRedirect;
    }

    /**
    * @param $ksPostData
    * @return void
    * @throws \Magento\Framework\Exception\LocalizedException
    */
    public function KsCategoryDataSave($ksPostData)
    {
        try {
            //get seller id
            $ksSellerId = $this->getRequest()->getPostValue('ks_seller_id');
            //get store id
            $ksStoreId = $this->getRequest()->getPostValue('ks_store_id');
            //get catgeory request is allowed
            $ksIsRequestsAllowed= $this->getRequest()->getPostValue('ks_is_requests_allowed');
            //get catgeory request auto approved
            $ksIsAutoApproved= $this->getRequest()->getPostValue('ks_is_auto_approved');
            //get category id
            $ksCatId = $this->getRequest()->getPostValue('ks_category_id');
            //get category enabled
            $ksCatEnabled = $this->getRequest()->getPostValue('ks-category-enabled');
            //check category id or category status
            if ($ksCatId !=null && $ksCatEnabled!=null) {
                //get seller category collection
                $ksSellerCategory = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_category_id', $ksCatId)->getFirstItem();
                $ksSellerFactory = $this->ksSellerCategoriesFactory->create();
                $ksSellerFactory->load($ksSellerCategory->getId());
                $ksSellerFactory->setData('ks_category_status', $ksCatEnabled)->save();
                if ($ksCatEnabled) {
                    //assign category from Product
                    $this->ksCategoryHelper->ksAssignProductInCategory($ksSellerId, $ksCatId);
                } else {
                    //Unassign category from Product
                    $this->ksCategoryHelper->ksUnAssignProductCategory(array($ksCatId), $ksSellerId);
                }
            }
            //get seller category collection
            $ksSellerCategories = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
            //get category request data
            $ksCategoryRequests = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_request_status', \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_PENDING);
            //get category request allowed model data
            $ksRequestsAllowedCollection = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
            //check category request allowed data
            if ($ksRequestsAllowedCollection != null) {
                foreach ($ksRequestsAllowedCollection as $ksRequestsAllowed) {
                    //get model data
                    $ksRequestsAllowedFactory = $this->ksCategoryRequestsAllowedFactory->create();
                    $ksRequestsAllowedFactory->load($ksRequestsAllowed->getId());
                    $ksRequestsAllowedFactory->setData('ks_is_requests_allowed', $ksIsRequestsAllowed);
                    if ($ksIsRequestsAllowed) {
                        $ksRequestsAllowedFactory->setData('ks_is_auto_approved', $ksIsAutoApproved);
                    }
                    $ksRequestsAllowedFactory->save();
                }
            }
            //check category request allowed toggle
            if (!$ksIsRequestsAllowed) {
                foreach ($ksCategoryRequests as $ksItem) {
                    //delete request data
                    $ksItem->delete();
                }
            }
            $this->setKsSellerCategories($ksPostData, $ksSellerCategories, $ksSellerId, $ksStoreId);
        } catch (\Exception $e) {
            $ksMessage = __($e->getMessage());
        }
    }

    /**
     * Save Seller Location Details
     * @param $ksPostData
     */
    public function KsSellerLocationSave($ksPostData)
    {
        $ksSellerId = $ksPostData['ks_seller_account_details_tab']['ks_public_profile_section']['ks_seller_id'];
        $ksSellerData = $this->getRequest()->getPostValue('sellerlocator');
        if (!empty($ksSellerData)) {
            $ksSellerData['ks_seller_id']= $ksSellerId;
            $ksSellerLocator = $this->ksSellerLocatorCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            $ksData = $ksSellerLocator->getData();
            $ksSellerFactory = $this->ksSellerLocatorFactory->create();
            if (!empty($ksData)) {
                $ksSellerFactory->load($ksSellerLocator->getId());
            }
            $ksSellerFactory->setKsLocation($ksSellerData['ks_location']);
            $ksSellerFactory->setKsLatitude($ksSellerData['ks_latitude']);
            $ksSellerFactory->setKsLongitude($ksSellerData['ks_longitude']);
            $ksSellerFactory->setKsSellerId($ksSellerData['ks_seller_id']);
            $ksSellerFactory->save();
        }
    }

    /**
     * @param $ksPostData
     * @param $ksSellerCategories
     * @param $ksSellerId
     * @param $ksStoreId
     * @return void
     */
    public function setKsSellerCategories($ksPostData, $ksSellerCategories, $ksSellerId, $ksStoreId)
    {
        //check ks_catgeories_ids exists
        if (array_key_exists("ks_categories_ids", $ksPostData)) {
            $ksMassCatIds = [];
            $ksCount = 0;
            $ksCatIds = $this->getRequest()->getPostValue('ks_categories_ids');
            $ksCategoryIds = array_map('intval', explode(',', $ksCatIds));
            $ksRootId = $this->getRequest()->getPostValue('ks_store_root_id');
            //check catgeory id
            if ($ksCatIds != null) {
                foreach ($ksSellerCategories as $ksSellerCategory) {
                    if (!in_array($ksSellerCategory->getKsCategoryId(), $ksCategoryIds) && $ksRootId != $ksSellerCategory->getKsCategoryId() && $this->ksCategoryHelper->getKsCategoryExist($ksSellerCategory->getKsCategoryId(), $ksStoreId)) {
                        $ksMassCatIds[] = $ksSellerCategory->getKsCategoryId();
                        $ksCount = 1;
                        //get category request model data
                        $ksCategoryRequests = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerCategory->getKsSellerId())->addFieldToFilter('ks_category_id', $ksSellerCategory->getKsCategoryId());
                        //check model data
                        if (count($ksCategoryRequests)!=0) {
                            $ksCatReq=$ksCategoryRequests->getFirstItem();
                            $ksRequestsFactory = $this->ksCategoryRequestsFactory->create();
                            $ksRequestsFactory->load($ksCatReq->getId());
                            $ksRequestsFactory->setKsRequestStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_UNASSIGNED)->save();
                        }
                        //delete model data
                        $ksSellerCategory->delete();
                    }
                }
                //Unassign category from Product
                $this->ksCategoryHelper->ksUnAssignProductCategory($ksMassCatIds, $ksSellerId);
            } else {
                $ksMassCatIds = [];
                //get seller category data
                $ksSellerCategories = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId);
                foreach ($ksSellerCategories as $ksSellerCategory) {
                    if ($ksRootId != $ksSellerCategory->getKsCategoryId() && $this->ksCategoryHelper->getKsCategoryExist($ksSellerCategory->getKsCategoryId(), $ksStoreId)) {
                        $ksMassCatIds[] = $ksSellerCategory->getKsCategoryId();
                        //get category request table
                        $ksCategoryRequests = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerCategory->getKsSellerId())->addFieldToFilter('ks_category_id', $ksSellerCategory->getKsCategoryId());
                        //check model data
                        if (count($ksCategoryRequests)!=0) {
                            $ksCatReq=$ksCategoryRequests->getFirstItem();
                            $ksRequestsFactory = $this->ksCategoryRequestsFactory->create();
                            $ksRequestsFactory->load($ksCatReq->getId());
                            $ksRequestsFactory->setKsRequestStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_UNASSIGNED)->save();
                        }
                        //delete model data
                        $ksSellerCategory->delete();
                    }
                }
                //Unassign category from Product
                $this->ksCategoryHelper->ksUnAssignProductCategory($ksMassCatIds, $ksSellerId);

                //email functionality
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_unassign_email', $this->ksDataHelper->getKsCurrentStoreView());
                if ($ksEmailEnabled != "disable") {
                    $this->ksSendCategoryEmail($ksEmailEnabled, $ksSellerId, $ksMassCatIds, $ksStoreId);
                }
            }
            //assign category
            $this->ksAssignNewCategory($ksCategoryIds, $ksSellerId, $ksStoreId);

            //email functionality
            if ($ksCount == 1) {
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_unassign_email', $this->ksDataHelper->getKsCurrentStoreView());
                if ($ksEmailEnabled != "disable") {
                    $this->ksSendCategoryEmail($ksEmailEnabled, $ksSellerId, $ksMassCatIds, $ksStoreId);
                }
            }
        }
    }

    /**
     * @param $ksPostData
     * @param $ksSellerId
     * @param $ksStoreId
     * @return void
     */
    public function ksAssignNewCategory($ksCategoryIds, $ksSellerId, $ksStoreId)
    {
        $ksMassCatIds = [];
        $ksCount = 0;
        foreach ($ksCategoryIds as $ksCategoryId) {
            //get seller category model data
            $ksSellerCategory = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_category_id', $ksCategoryId);
            //check model data
            if (count($ksSellerCategory)==0) {
                $ksMassCatIds[] = $ksCategoryId;
                $ksSellerFactory = $this->ksSellerCategoriesFactory->create();
                $ksData = [
                   "ks_seller_id" => $ksSellerId,
                   "ks_category_id" => $ksCategoryId,
                   "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
                ];
                $ksSellerFactory->addData($ksData)->save();
                $ksCount = 1;
                //get model data
                $ksCategoryRequests = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_category_id', $ksCategoryId);
                //check model data
                if (count($ksCategoryRequests)!=0) {
                    $ksCatReq=$ksCategoryRequests->getFirstItem();
                    $ksRequestsFactory = $this->ksCategoryRequestsFactory->create();
                    $ksRequestsFactory->load($ksCatReq->getId());
                    $ksRequestsFactory->setKsRequestStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_ASSIGNED);
                    $ksRequestsFactory->setKsRejectionReason("")->save();
                }
                $this->ksCategoryHelper->ksAssignProductInCategory($ksSellerId, $ksCategoryId);
            }
        }
        
        //email functionality
        if ($ksCount == 1) {
            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_categories/ks_category_assign_email', $this->ksDataHelper->getKsCurrentStoreView());
            if ($ksEmailEnabled != "disable") {
                $this->ksSendCategoryEmail($ksEmailEnabled, $ksSellerId, $ksMassCatIds, $ksStoreId);
            }
        }
    }

    /**
     * @param $ksPostData
     * @return void
     */
    public function KsSellerConfigDataSave($ksPostData)
    {
        if (array_key_exists('ks_seller_account_details_tab', $ksPostData) && array_key_exists('ks_seller_account_details_tab', $ksPostData)) {
            $ksSellerId = $ksPostData['ks_seller_account_details_tab']['ks_public_profile_section']['ks_seller_id'];
            $ksSellerData = $ksPostData['ks_homepage_tab'];
            $ksCollection =  $this->ksSellerConfigFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            $ksCollection->setKsShowBanner($ksSellerData['ks_show_banner']);
            $ksCollection->setKsShowRecentlyProducts($ksSellerData['ks_show_recently_products']);
            $ksCollection->setKsRecentlyProductsText($ksSellerData['ks_recently_products_text']);
            $ksCollection->setKsRecentlyProductsCount($ksSellerData['ks_recently_products_count']);
            $ksCollection->setKsShowBestProducts($ksSellerData['ks_show_best_products']);
            $ksCollection->setKsBestProductsText($ksSellerData['ks_best_products_text']);
            $ksCollection->setKsBestProductsCount($ksSellerData['ks_best_products_count']);
            $ksCollection->setKsShowDiscountProducts($ksSellerData['ks_show_discount_products']);
            $ksCollection->setKsDiscountProductsText($ksSellerData['ks_discount_products_text']);
            $ksCollection->setKsDiscountProductsCount($ksSellerData['ks_discount_products_count']);
            $ksCollection->save();
        }
    }

    /**
     * Send email to seller when an admin assign or unassign categories to seller
     * @param  [string] $ksTemplatePath
     * @param  [int] $ksSellerId
     * @param  [int] $ksCatId
     * @param  [int] $ksStoreId
     */
    public function ksSendCategoryEmail($ksTemplatePath, $ksSellerId, $ksMassCatIds, $ksStoreId)
    {
        //Get Sender Info
        $ksSender = $this->ksDataHelper->getKsConfigValue(
            'ks_marketplace_catalog/ks_product_categories/ks_email_sender',
            $this->ksDataHelper->getKsCurrentStoreView()
        );
        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
        //Get Receiver Info
        $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
        //Template variables
        $ksTemplateVariable = [];
        $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
        $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
        $ksTemplateVariable['ksStoreId'] = $ksStoreId;
        $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
        $ksTemplateVariable['ksSellerId'] = $ksSellerId;
        // Send Mail
        $this->ksEmailHelper->ksSendEmail($ksTemplatePath, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
    }

    /**
     * Send email to seller when an admin approves/rejects the seller
     *
     * @param [string] $ksTemplatePath
     * @param [int] $ksSellerId
     * @param [mixed] $ksSellerModel
     * @param [string] $ksRejectReason
     */
    public function ksSendSellerEmail($ksTemplatePath, $ksSellerId, $ksSellerModel, $ksRejectReason)
    {
        $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_seller/ks_seller_settings/ks_email_sender');
        $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
        $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);

        $ksTemplateVariable = [];
        $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
        $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];

        if (trim($ksRejectReason) == "") {
            $ksTemplateVariable['ksReason'] = "";
        } else {
            $ksTemplateVariable['ksReason'] = $ksRejectReason;
        }

        // Send Mail
        $this->ksEmailHelper->ksSendEmail($ksTemplatePath, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
    }

    /**
     * Get Default Value
     * @param  $ksPostData
     * @param  $ksData
     * @return array
     */
    public function getKsDefaultValue($ksPostData, $ksData)
    {
        // Check 'use_default' key exist in Array or not
        if (array_key_exists('use_default', $ksPostData)) {
            $ksPostData = $ksPostData['use_default'];
            //get seller store collection
            $ksSellerStoreModel = $this->ksSellerStoreFactory->create();
            $ksSellerStore = $ksSellerStoreModel->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem()->getData();
            $ksSellerSitemap=$this->ksSellerSitemapFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem()->getData();
            foreach ($ksPostData as $ksKey => $ksValue) {
                if ($ksKey!='ks_included_sitemap_profile' && $ksKey!='ks_included_sitemap_product') {
                    if ($ksValue) {
                        $ksData[$ksKey] = $ksSellerStore[$ksKey];
                    }
                } else {
                    if ($ksValue) {
                        $ksData[$ksKey] = $ksSellerSitemap[$ksKey];
                    }
                }
            }
        }
        return $ksData;
    }


    /**
     * Get Default Value in other Store from seller Sitemap
     * @param  $ksPostData
     * @return void
     */
    public function ksSitemapDefaultValueinOtherStore($ksData)
    {
        //get seller store collection
        $ksSellerSitemapModel=$this->ksSellerSitemapFactory->create();
        // Get Collection of Default Store
        $ksSellerSitemapStore = $ksSellerSitemapModel->getCollection()
                                            ->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])
                                            ->addFieldToFilter('ks_store_id', 0)
                                            ->getFirstItem()
                                            ->getData();
        // Get Collection of Other Store
        $ksSellerSitemapAllStore = $ksSellerSitemapModel->getCollection()
                                                ->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])
                                                ->addFieldToFilter('ks_store_id', ['neq' => 0])
                                                ->getData();
        if (!empty($ksSellerSitemapStore)) {
            // Iterate over all the store other then 0
            foreach ($ksSellerSitemapAllStore as $ksRecord) {
                // Initialize array
                $ksArray = [];
                // Iterate over store values
                foreach ($ksRecord as $ksKey => $ksValue) {
                    // these values are not available in $ksData so remove this.
                    if ($ksKey == 'id' ||
                        $ksKey == 'ks_created_at' ||
                        $ksKey == 'ks_updated_at' ||
                        $ksKey == 'ks_store_id') {
                        $ksArray[$ksKey] = $ksValue;
                        continue;
                    }
                    // If value is same means default change this with new
                    if ($ksValue == $ksSellerSitemapStore[$ksKey]) {
                        $ksArray[$ksKey] = $ksData[$ksKey];
                    // else leave it as it is
                    } else {
                        $ksArray[$ksKey] = $ksValue;
                    }
                }
                // Create new collection
                $ksSellerSitemapModel=$this->ksSellerSitemapFactory->create()->load($ksArray['id']);
                // Set Data
                $ksSellerSitemapModel->setData($ksArray);
                // Save the Data
                $ksSellerSitemapModel->save();
            }
        }
    }


    /**
     * Get Default Value in other Store
     * @param  $ksPostData
     * @return void
     */
    public function ksDefaultValueinOtherStore($ksData)
    {
        //get seller store collection
        $ksSellerStoreModel = $this->ksSellerStoreFactory->create();
        // Get Collection of Default Store
        $ksSellerStore = $ksSellerStoreModel->getCollection()
                                            ->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])
                                            ->addFieldToFilter('ks_store_id', 0)
                                            ->getFirstItem()
                                            ->getData();
        // Get Collection of Other Store
        $ksSellerAllStore = $ksSellerStoreModel->getCollection()
                                                ->addFieldToFilter('ks_seller_id', $ksData['ks_seller_id'])
                                                ->addFieldToFilter('ks_store_id', ['neq' => 0])
                                                ->getData();
        if (!empty($ksSellerStore)) {
            // Iterate over all the store other then 0
            foreach ($ksSellerAllStore as $ksRecord) {
                // Initialize array
                $ksArray = [];
                // Iterate over store values
                foreach ($ksRecord as $ksKey => $ksValue) {
                    if (!(isset($ksData[$ksKey]))) {
                        $ksArray[$ksKey] = $ksValue;
                        continue;
                    }
                    // If value is same means default change this with new
                    if ($ksValue == $ksSellerStore[$ksKey]) {
                        $ksArray[$ksKey] = $ksData[$ksKey];
                    // else leave it as it is
                    } else {
                        $ksArray[$ksKey] = $ksValue;
                    }
                }
                // Create new collection
                $ksSellerStoreModel = $this->ksSellerStoreFactory->create()->load($ksArray['id']);
                // Set Data
                $ksSellerStoreModel->setData($ksArray);
                // Save the Data
                $ksSellerStoreModel->save();
            }
        }
    }


    /**
     * Reject All the Product Type Which are pending make them rejected
     * @param $ksSellerId
     * @return void
     */
    public function ksRejectSellerProductType($ksSellerId)
    {
        // Reject Status
        $ksRejectStatus = \Ksolves\MultivendorMarketplace\Model\KsProductType::KS_REQUEST_STATUS_REJECTED;
        // Collection of Attributes of Seller which are pending new and pending update
        $ksTypeCollection = $this->ksProductTypeFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_request_status', ['eq' => 2]);
        $ksProductType = [];
        // Iterate the collection
        foreach ($ksTypeCollection as $ksCollection) {
            // Get the Id
            $ksTypeId = $ksCollection->getId();
            // Get Collection According to TypeId
            $ksAttributeModel = $this->ksProductTypeFactory->create()->addFieldToFilter('id', $ksTypeId)->getFirstItem();
            // update Status
            $ksAttributeModel->setKsProductTypeRejectionReason("");
            $ksAttributeModel->setKsRequestStatus($ksRejectStatus);
            $ksAttributeModel->save();
            $ksProductType[] = $ksCollection->getKsProductType();
        }
        
        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            'ks_marketplace_catalog/ks_product_type_settings/ks_seller_product_type_request_rejection_email',
            $ksStoreId
        );
        if ($ksEmailEnabled != "disable" && count($ksProductType)) {
            //Product types
            $ksMassProductType = implode(", ", $ksProductType);
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_type_settings/ks_email_sender',
                $ksStoreId
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductType'] = $ksMassProductType;
            $ksTemplateVariable['ksReason'] = "";
            // Send Mail
            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
        }
    }

    /**
     * Reject All the Attribute Which are pending update and pending rejected
     *
     * @param $ksSellerId
     * @return void
     */
    public function ksRejectSellerAttribute($ksSellerId)
    {
        // Reject Status
        $ksRejectStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        // Collection of Attributes of Seller which are pending new and pending update
        $ksAttributeColl = $this->ksAttributeFactory->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_attribute_approval_status', ['in' => [0, 3]]);
        // Iterate the collection
        foreach ($ksAttributeColl as $ksCollection) {
            // Get the Attribute Id
            $ksAttributeId = $ksCollection->getAttributeId();
            // Get Collection According to Attribute
            $ksAttributeModel = $this->ksAttributeFactory->create()->addFieldToFilter('attribute_id', $ksAttributeId)->getFirstItem();
            // update Status
            $ksAttributeModel->setKsAttributeRejectionReason("");
            $ksAttributeModel->setKsAttributeApprovalStatus($ksRejectStatus);
            $ksAttributeModel->save();

            //Email functionality
            $ksStoreId = $this->getRequest()->getParam('store', 0);
            $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_rejection_email_template', $ksStoreId);
            if ($ksEmailDisable != 'disable' && $ksAttributeModel->getKsAttributeApprovalStatus() == $ksRejectStatus) {
                $ksSellerDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                $ksSender = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email', $ksStoreId);
                
                $ksTemplateVariable = [];
                $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
                $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
                $ksTemplateVariable['ks-product-attribute-name'] = $ksAttributeModel->getFrontendLabel();
                $ksTemplateVariable['ks-attribute-code'] = $ksAttributeModel->getAttributeCode();
                $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsRequired());
                $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsUserDefined());
                $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsVisible());
                $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksAttributeModel->getIsGlobal());
                $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsSearchable());
                $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksAttributeModel->getIsFilterable());
                $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksAttributeModel->getIsComparable());
                $ksTemplateVariable['ks-rejection-reason'] = "";
                $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REJECTION_MAIL, $ksTemplateVariable, $ksSender, $ksSellerDetails, 1);
            }
        }
    }
}
