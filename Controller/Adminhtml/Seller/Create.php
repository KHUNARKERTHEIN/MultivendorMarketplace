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

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Metadata\Form;
use Magento\Customer\Model\Metadata\FormFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\Error;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\File\Uploader;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsSellerSitemapFactory as KsSellerSitemapCollectionFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Class Create Controller
 */
class Create extends \Magento\Backend\App\Action
{
    public const KS_IMAGE_TMP_PATH = 'ksolves/tmp/multivendor';

    public const KS_IMAGE_PATH = 'ksolves/multivendor';

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $ksCustomerDataFactory;

    /**
     * @var AccountManagementInterface
     */
    protected $ksCustomerAccountManagement;


    /**
     * @var FormFactory
     */
    protected $ksFormFactory;

    /**
     * @var ObjectFactory
     */
    protected $ksObjectFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var DataObjectHelper
     */
    protected $ksDataObjectHelper;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $ksMediaDirectory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $ksCoreFileStorageDatabase;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /*
    *  @var KsDataHelper
    */
    protected $ksDataHelper;

    /**
     * @var KsSellerSitemapCollectionFactory
     */
    protected $ksSellerSitemapCollectionFactory;

    /**
     * @var KsSellerHelper
     */
    protected $KsSellerHelper;

    /**
     * @var StoreWebsiteRelationInterface
     */
    protected $ksStoreWebsiteRelation;

    /**
     * Initialize Group Controller
     *
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory
     * @param Registry $ksCoreRegistry
     * @param CustomerInterfaceFactory $ksCustomerDataFactory
     * @param AccountManagementInterface $ksCustomerAccountManagement
     * @param StoreManagerInterface $ksStoreManager
     * @param FormFactory $ksFormFactory
     * @param ObjectFactory $ksObjectFactory
     * @param KsSellerFactory $ksSellerFactory
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param DataObjectHelper $ksDataObjectHelper
     * @param Filesystem $ksFilesystem
     * @param Database $ksCoreFileStorageDatabase
     * @param DateTime $ksDate
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerHelper $KsSellerHelper
     * @param StoreWebsiteRelationInterface $ksStoreWebsiteRelation
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory,
        Registry $ksCoreRegistry,
        CustomerInterfaceFactory $ksCustomerDataFactory,
        AccountManagementInterface $ksCustomerAccountManagement,
        StoreManagerInterface $ksStoreManager,
        FormFactory $ksFormFactory,
        ObjectFactory $ksObjectFactory,
        KsSellerFactory $ksSellerFactory,
        KsSellerStoreFactory $ksSellerStoreFactory,
        DataObjectHelper $ksDataObjectHelper,
        Filesystem $ksFilesystem,
        Database $ksCoreFileStorageDatabase,
        DateTime $ksDate,
        KsDataHelper $ksDataHelper,
        KsSellerHelper $KsSellerHelper,
        StoreWebsiteRelationInterface $ksStoreWebsiteRelation
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerSitemapCollectionFactory = $ksSellerSitemapCollectionFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksCustomerDataFactory = $ksCustomerDataFactory;
        $this->ksCustomerAccountManagement = $ksCustomerAccountManagement;
        $this->ksStoreManager = $ksStoreManager;
        $this->ksFormFactory = $ksFormFactory;
        $this->ksObjectFactory = $ksObjectFactory;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksDataObjectHelper = $ksDataObjectHelper;
        $this->ksMediaDirectory = $ksFilesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->ksCoreFileStorageDatabase = $ksCoreFileStorageDatabase;
        $this->ksDate = $ksDate;
        $this->ksDataHelper = $ksDataHelper;
        $this->KsSellerHelper = $KsSellerHelper;
        $this->ksStoreWebsiteRelation = $ksStoreWebsiteRelation;
        parent::__construct($ksContext);
    }

    /**
     * Reformat customer account data to be compatible with customer service interface
     *
     * @return array
     */
    protected function ksExtractCustomerData()
    {
        $ksCustomerData = [];
        $ksWebsite = $this->getRequest()->getPost('website_id');
        if ($this->getRequest()->getPost('customer')) {
            $ksAdditionalAttributes = [
                CustomerInterface::DEFAULT_BILLING,
                CustomerInterface::DEFAULT_SHIPPING,
                'confirmation',
                'sendemail_store_id',
                'extension_attributes',
            ];

            $ksCustomerData = $this->ksExtractData(
                'adminhtml_customer',
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $ksAdditionalAttributes,
                'customer'
            );
        }

        if (isset($customerData['disable_auto_group_change'])) {
            $ksCustomerData['disable_auto_group_change'] = (int) filter_var(
                $ksCustomerData['disable_auto_group_change'],
                FILTER_VALIDATE_BOOLEAN
            );
        }

        if ($ksCustomerData) {
            $ksCustomerData['website_id'] = $ksWebsite ? $ksWebsite : 1;
        }

        return $ksCustomerData;
    }

    /**
     * Perform customer data filtration based on form code and form object
     *
     * @param string $formCode The code of EAV form to take the list of attributes from
     * @param string $entityType entity type for the form
     * @param string[] $additionalAttributes The list of attribute codes to skip filtration for
     * @param string $scope scope of the request
     * @return array
     */
    protected function ksExtractData(
        $ksFormCode,
        $ksEntityType,
        $ksAdditionalAttributes = [],
        $ksScope = null
    ) {
        $ksAttributeValues = [];

        $ksMetadataForm = $this->ksFormFactory->create(
            $ksEntityType,
            $ksFormCode,
            $ksAttributeValues,
            false,
            Form::DONT_IGNORE_INVISIBLE
        );

        $ksFormData = $ksMetadataForm->extractData($this->getRequest(), $ksScope);
        $ksFormData = $ksMetadataForm->compactData($ksFormData);

        // Initialize additional attributes
        /** @var DataObject $object */
        $object = $this->ksObjectFactory->create(['data' => $this->getRequest()->getPostValue()]);
        $ksRequestData = $object->getData($ksScope);
        foreach ($ksAdditionalAttributes as $ksAttributeCode) {
            $ksFormData[$ksAttributeCode] = isset($ksRequestData[$ksAttributeCode]) ? $ksRequestData[$ksAttributeCode] : false;
        }

        // Unset unused attributes
        $ksFormAttributes = $ksMetadataForm->getAttributes();
        foreach ($ksFormAttributes as $ksAttribute) {
            /** @var AttributeMetadataInterface $attribute */
            $ksAttributeCode = $ksAttribute->getAttributeCode();
            if ($ksAttribute->getFrontendInput() != 'boolean'
                && $ksFormData[$ksAttributeCode] === false
            ) {
                unset($ksFormData[$ksAttributeCode]);
            }
        }

        if (empty($ksFormData['extension_attributes'])) {
            unset($ksFormData['extension_attributes']);
        }

        return $ksFormData;
    }


    /**
     * execute action
     */
    public function execute()
    {
        $ksPostData = $this->getRequest()->getPostValue();


        if ($ksPostData) {
            try {
                $ksStoreUrlList = $this->ksSellerFactory->create()->getCollection()
                                ->addFieldToFilter('ks_store_url', $ksPostData['seller']['ks_store_url']);
                if ($ksStoreUrlList->getSize()) {
                    $this->messageManager->addError(__("Store URL already exists."));
                    $ksResultRedirect = $this->resultRedirectFactory->create();
                    $ksResultRedirect->setPath($this->_redirect->getRefererUrl(), ['_current' => true]);
                    return $ksResultRedirect;
                }

                $ksExistCustomerId =0 ;
                if (array_key_exists("data", $ksPostData)) {
                    if (array_key_exists("ks_seller_id", $ksPostData['data'])) {
                        $ksExistCustomerId = $ksPostData['data']['ks_seller_id'];
                    }
                }


                if (!$ksExistCustomerId) {
                    //create new customer
                    $ksCustomer = $this->ksCustomerDataFactory->create();
                    $ksCustomerData = $customerData = $this->ksExtractCustomerData();

                    if (array_key_exists('website_id', $ksCustomerData)) {
                        $kswebsiteId = $ksCustomerData['website_id'];
                    }
                    if (!array_key_exists('website_id', $ksCustomerData)) {
                        $kswebsiteId = $this->ksStoreManager->getDefaultStoreView()->getWebsiteId();
                        $ksCustomerData['website_id'] = $kswebsiteId;
                    }

                    $ksStoreIds = $this->ksStoreWebsiteRelation->getStoreByWebsiteId($kswebsiteId);

                    if (empty($ksStoreIds)) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __("The Store View selected for sending Welcome email from is not related to the seller's associated website.")
                        );
                    }

                    $ksDefaultStoreId = $this->ksStoreManager->getWebsite($kswebsiteId)->getDefaultStore()->getId();

                    $ksCustomerData['sendemail_store_id'] = $ksDefaultStoreId;

                    $this->ksDataObjectHelper->populateWithArray(
                        $ksCustomer,
                        $ksCustomerData,
                        CustomerInterface::class
                    );

                    // After save
                    $this->_eventManager->dispatch(
                        'adminhtml_customer_prepare_save',
                        ['customer' => $ksCustomer, 'request' => $this->getRequest()]
                    );

                    $ksCustomer = $this->ksCustomerAccountManagement->createAccount($ksCustomer);

                    $ksCustomerId = $ksCustomer->getId();


                    // After save
                    $this->_eventManager->dispatch(
                        'adminhtml_customer_save_after',
                        ['customer' => $ksCustomer, 'request' => $this->getRequest()]
                    );
                } else {
                    $ksCustomerId = $ksExistCustomerId;
                }

                if ($ksCustomerId) {
                    $ksSellerData = $this->getRequest()->getPost('seller');
                    $ksSellerData['ks_seller_id'] = $ksCustomerId;
                    $ksSellerData['ks_company_country'] = $ksPostData['country_id'];

                    if ($ksSellerData['ks_store_available_countries']) {
                        $ksSellerData['ks_store_available_countries'] = implode(',', $ksSellerData['ks_store_available_countries']);
                    }
                    $ksSellerModel = $this->ksSellerFactory->create();
                    try {
                        $ksSellerModel->setData($ksSellerData);

                        // check seller status to set rejection reason and store status
                        if ($ksSellerData['ks_seller_status'] == 1) {
                            $ksSellerModel->setData('ks_rejection_reason', " ");
                        } elseif ($ksSellerData['ks_seller_status'] == 0) {
                            $ksSellerModel->setData('ks_rejection_reason', " ");

                            // check store status
                            if ($ksSellerData['ks_store_status'] != 0) {
                                $this->messageManager->addError(__('Store of pending seller can not be enabled.'));
                                $ksSellerModel->setData('ks_store_status', 0);
                            }
                        } elseif ($ksSellerData['ks_seller_status'] == 2) {
                            // check store status
                            if ($ksSellerData['ks_store_status'] != 0) {
                                $this->messageManager->addError(__('Store of rejected seller can not be enabled.'));
                                $ksSellerModel->setData('ks_store_status', 0);
                            }
                        }
                        $ksSellerModel = $this->KsSellerHelper->ksSaveConfigurationValue($ksSellerModel);
                        $ksSellerModel->setKsCreatedAt($this->ksDate->gmtDate());
                        $ksSellerModel->setKsUpdatedAt($this->ksDate->gmtDate());
                        $ksSellerModel->save();
                        $this->messageManager->addSuccessMessage(__('A new seller has been created successfully.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Something went wrong while creating the seller'));
                    }

                    //save seller store details
                    $ksSellerStoreData = $this->getRequest()->getPost('store');
                    $ksSellerStoreData['ks_seller_id'] = $ksCustomerId;

                    if (!empty($ksSellerStoreData)) {
                        $this->ksAddSellerStoreInformation($ksSellerStoreData);
                    }

                    // url rewrite for seller store
                    $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksCustomerId.'/';
                    $ksRequestedPathUrl ="multivendor/".$ksPostData['seller']['ks_store_url'];

                    $this->KsSellerHelper->ksSellerStoreUrlRedirect($ksTargetPathUrl, $ksRequestedPathUrl);

                    //Category table entry
                    $this->KsSellerHelper->ksSetCategoryConfiguration($ksCustomerId);

                    //Save Product Type
                    $this->KsSellerHelper->ksAddProductTypeInSellerTable($ksCustomerId);

                    //ks_seller_sitemap table data entry
                    $this->ksSetSitemapData($ksCustomerId);
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while creating the seller.' . $ksException->getMessage()));
            } catch (\Magento\Framework\Validator\Exception $ksException) {
                $ksMessages = $ksException->getMessages();
                if (empty($ksMessages)) {
                    $ksMessages = $ksException->getMessage();
                }
                $this->ksAddSessionErrorMessages($ksMessages);
            } catch (AbstractAggregateException $ksException) {
                $ksErrors = $ksException->getErrors();
                $ksMessages = [];
                foreach ($ksErrors as $ksError) {
                    $ksMessages[] = $ksError->getMessage();
                }
                $this->ksAddSessionErrorMessages($ksMessages);
            } catch (LocalizedException $ksException) {
                $this->ksAddSessionErrorMessages($ksException->getMessage());
            } catch (\Exception $ksException) {
                $this->messageManager->addExceptionMessage(
                    $ksException,
                    __($ksException->getMessage())
                );
            }
        }

        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksResultRedirect->setPath('*/*/');
        return $ksResultRedirect;
    }

    /**
     * Save Seller Store Information
     * @param array ksSellerStoreData
     * @param array ksFiles
     */
    public function ksAddSellerStoreInformation($ksSellerStoreData)
    {
        $ksSellerStoreModel = $this->ksSellerStoreFactory->create();

        //save store logo
        if (isset($ksSellerStoreData['ks_store_logo'][0]['name']) && isset($ksSellerStoreData['ks_store_logo'][0]['tmp_name'])) {
            $ksStoreLogo = $this->ksMoveFileFromTmp($ksSellerStoreData['ks_store_logo'][0]['name']);
            $ksSellerStoreData['ks_store_logo'] = $ksStoreLogo;
        } elseif (isset($ksSellerStoreData['ks_store_logo'][0]['name'])) {
            $ksSellerStoreData['ks_store_logo'] = $ksSellerStoreData['ks_store_logo'][0]['name'];
        } else {
            $ksSellerStoreData['ks_store_logo'] = '';
        }

        //save store banner
        if (isset($ksSellerStoreData['ks_store_banner'][0]['name']) && isset($ksSellerStoreData['ks_store_banner'][0]['tmp_name'])) {
            $ksStoreBanner = $this->ksMoveFileFromTmp($ksSellerStoreData['ks_store_banner'][0]['name']);
            $ksSellerStoreData['ks_store_banner'] = $ksStoreBanner;
        } elseif (isset($ksSellerStoreData['ks_store_banner'][0]['name'])) {
            $ksSellerStoreData['ks_store_banner'] = $ksSellerStoreData['ks_store_banner'][0]['name'];
        } else {
            $ksSellerStoreData['ks_store_banner'] = '';
        }

        try {
            $ksSellerStoreModel->setData($ksSellerStoreData);
            $ksSellerStoreModel->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while saving the seller store data.'.$e->getMessage()));
        }
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
     * Add errors messages to session.
     *
     * @param array|string $messages
     * @return void
     *
     */
    protected function ksAddSessionErrorMessages($ksMessages)
    {
        $ksMessages = (array)$ksMessages;
        $ksSession = $this->_getSession();

        $ksCallback = function ($ksError) use ($ksSession) {
            if (!$ksError instanceof Error) {
                $ksError = new Error($ksError);
            }
            $this->messageManager->addMessage($ksError);
        };
        array_walk_recursive($ksMessages, $ksCallback);
    }

    /**
     * Add default entry in ks_seller_sitemap table
     * @param $ksSellerId
     * @return void
     * */

    public function ksSetSitemapData($ksSellerId)
    {
        // assign default Store id as "0"
        $ksStoreId=0;
        $ksSitemapModel=$this->ksSellerSitemapCollectionFactory->create();
        $ksSitemapModel->setData('ks_seller_id', $ksSellerId);
        $ksSitemapModel->setData('ks_included_sitemap_profile', $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ksStoreId));
        $ksSitemapModel->setData('ks_included_sitemap_product', $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ksStoreId));
        $ksSitemapModel->save();
    }
}
