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

namespace Ksolves\MultivendorMarketplace\Controller\CategoryType;

use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as KsProductCollection;

/**
 * Request Controller class
 */
class Request extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory
     */
    protected $ksCategoryRequestsFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory
     */
    protected $ksCategoryRequestsCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $ksTimezone;
    
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryCollectionFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDateTime;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var  KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory
     */
    protected $ksCategoryRequestsAllowedCollection;

    /**
     * @var\Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory
     */
    protected $ksSellerCategoriesFactory;

    /**
     * @var KsProductCollection
     */
    protected $ksCatalogProductFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $ksTimezone
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime
     * @param \Magento\Catalog\Model\CategoryFactory $ksCategoryCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param KsCategoryRequests $ksCategoryHelper
     * @param Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory
     * @param KsProductCollection $ksCatalogProductFactory
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsFactory $ksCategoryRequestsFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequests\CollectionFactory $ksCategoryRequestsCollection,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface  $ksTimezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDateTime,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        KsCategoryRequests $ksCategoryHelper,
        \Ksolves\MultivendorMarketplace\Model\KsProductFactory $ksProductFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCategoryRequestsAllowed\CollectionFactory $ksCategoryRequestsAllowedCollection,
        \Ksolves\MultivendorMarketplace\Model\KsSellerCategoriesFactory $ksSellerCategoriesFactory,
        KsProductCollection $ksCatalogProductFactory,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCategoryRequestsFactory = $ksCategoryRequestsFactory;
        $this->ksCategoryRequestsCollection = $ksCategoryRequestsCollection;
        $this->ksTimezone = $ksTimezone;
        $this->ksDateTime = $ksDateTime;
        $this->ksCategoryCollectionFactory = $ksCategoryCollectionFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksCategoryHelper = $ksCategoryHelper;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksCategoryRequestsAllowedCollection = $ksCategoryRequestsAllowedCollection;
        $this->ksSellerCategoriesFactory = $ksSellerCategoriesFactory;
        $this->ksCatalogProductFactory = $ksCatalogProductFactory;
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Vendor Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $this->ksRequestCategory();
                //for redirecting url
                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            } catch (\Exception $e) {
                $ksMessage = __($e->getMessage());
                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
    
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function ksRequestCategory()
    {
        //get category request status
        $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_PENDING;
        //get category id
        $ksCatId = $this->getRequest()->getParam('ks_category_id');
        //get seller id
        $ksSellerId = $this->getRequest()->getParam('ks_seller_id');
        //get store id
        $ksStoreId = $this->getRequest()->getParam('ks_store_id');
        //get requests allowed collection data
        $ksRequestsAllowedCollection = $this->ksCategoryRequestsAllowedCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
        if ($ksRequestsAllowedCollection->getKsIsAutoApproved()) {
            //get category request status
            $ksCategoryStatus = \Ksolves\MultivendorMarketplace\Model\KsCategoryRequests::KS_STATUS_APPROVED;
            //rejection reason
            $ksRejectionReason = "";
            //get collecion data
            $ksCategoryCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId)->getFirstItem();
            //get model data
            $ksCategoriesFactory = $this->ksSellerCategoriesFactory->create();
            $ksData = [
               "ks_seller_id" => $ksSellerId,
               "ks_category_id" => $ksCatId,
               "ks_category_status" => \Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED
            ];
            //add data
            $ksCategoriesFactory->addData($ksData)->save();
            if(!empty($ksCategoryCollection->getData())){
                //set data
                $ksCategoryCollection->setKsRejectionReason($ksRejectionReason);
                $ksCategoryCollection->setKsRequestStatus($ksCategoryStatus);
                $ksCategoryCollection->save();
            }
            //assign category to product
            $this->ksCategoryHelper->ksAssignProductInCategory($ksSellerId, $ksCatId);

            $this->messageManager->addSuccess(__('A product category request has been sent successfully.'));

            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_categories/ks_category_request_approval_email',
                $this->ksDataHelper->getKsCurrentStoreView()
            );

            if ($ksEmailEnabled != "disable") {
                //Get Sender Info
                $ksSender = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_catalog/ks_product_categories/ks_email_sender',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                //Get Receiver Info
                $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                //save category id in array
                $ksMassCatIds = [];
                $ksMassCatIds[] = $ksCatId;
                //Template variables
                $ksTemplateVariable = [];
                $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                $ksTemplateVariable['ksCatIds'] = $ksMassCatIds;
                $ksTemplateVariable['ksSellerId'] = $ksSellerId;
                // Send Mail
                $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
            }
        } else {
            //get collection data
            $ksCatReqCollection = $this->ksCategoryRequestsCollection->create()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId);
            //rejection reason
            $ksRejectionReason = "";
            //get model data
            $ksCategoryCollection = $this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksCatId);
            //get product collection from category
            $ksProductCollection = $this->ksCatalogProductFactory->create()->addAttributeToSelect('*');
            $ksProductCollection->addCategoriesFilter(['eq' => $ksCatId]);
            //get seller disabled category collection data
            $ksSellerCatCol = $this->ksSellerCategoriesFactory->create()->getCollection()->addFieldToFilter('ks_category_id', $ksCatId)->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_category_status',0);
            //get global product count
            $ksGlobalProductCount = $ksProductCollection->getSize();
            //get seller product count
            $ksSellerProductCount =  $ksProductCollection->addFieldToFilter('entity_id', ['in' => $this->getKsProductIds()])->getSize();
            $ksCount = count($this->getKsBackupProductIds($ksCatId));
            $ksGlobalProductCount += $this->getKsBackupGlobalCount($ksCatId);
            $ksSellerProductCount += $ksCount;


            $ksParents = array_map('intval', explode('/', $ksCategoryCollection->getPath()));
            $ksCategoryName ='';
            foreach ($ksParents as $ksParent) {
                // category id not equal to root category
                if ($ksParent!=1 && $ksParent!=$ksCategoryCollection->getId()) {
                    $ksCat=$this->ksCategoryCollectionFactory->create()->setStoreId($ksStoreId)->load($ksParent);
                    $ksCategoryName .= $ksCat->getName() . ' >> ';
                }
            }
            $ksCategoryName .= $ksCategoryCollection->getName();
            //check collection data
            if (count($ksCatReqCollection)!=0) {
                $ksCatRequest = $ksCatReqCollection->getFirstItem();
                $ksCatReqFactory = $this->ksCategoryRequestsFactory->create();
                $ksCatReqFactory->load($ksCatRequest->getId());
                $ksCatReqFactory->setKsRequestStatus($ksCategoryStatus);
                $ksCatReqFactory->setKsGlobalProductCount($ksGlobalProductCount);
                $ksCatReqFactory->setKsSellerProductCount($ksSellerProductCount);
                $ksCatReqFactory->setKsRequestedOn($this->ksTimezone
                                            ->date(new \DateTime($this->ksDateTime->date()))
                                            ->format('Y-m-d H:i:s'));
                //save data
                $ksCatReqFactory->save();
            } else {
                $ksCatReqFactory = $this->ksCategoryRequestsFactory->create();
                $ksData=[
                    "ks_seller_id" => $ksSellerId,
                    "ks_category_id" => $ksCatId,
                    "ks_category_name" => $ksCategoryName,
                    "ks_rejection_reason" => $ksRejectionReason,
                    "ks_request_status" => $ksCategoryStatus,
                    "ks_requested_on" => $this->ksTimezone
                                            ->date(new \DateTime($this->ksDateTime->date()))
                                            ->format('Y-m-d H:i:s'),
                    "ks_global_product_count" => $ksGlobalProductCount,
                    "ks_seller_product_count" => $ksSellerProductCount
                ];
                //add data
                $ksCatReqFactory->addData($ksData)->save();
            }
            
            $this->messageManager->addSuccess(__('A product category request has been sent successfully.'));

            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_categories/ks_category_request_email',
                $this->ksDataHelper->getKsCurrentStoreView()
            );

            if ($ksEmailEnabled != "disable") {
                //Get Sender Info
                $ksSellerInfo = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                //Get Receiver Info
                $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_categories/ks_category_admin_email_option';
                $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_categories/ks_category_admin_email';
                $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                $ksReceiverInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                $ksCategoryDetails=$this->ksCategoryHelper->getKsCategoryDetails($ksCatId, $ksSellerId);
                //Get Sender Info
                $ksSender = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_catalog/ks_product_categories/ks_email_sender',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                //Template variables
                $ksTemplateVariable = [];
                $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverInfo['name']);
                $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerInfo['name']);
                $ksTemplateVariable['ks_category_name'] = $ksCategoryName;
                $ksTemplateVariable['ks_global_count'] = $ksCategoryDetails['ks_global_product_count'];
                $ksTemplateVariable['ks_seller_count'] = $ksCategoryDetails['ks_seller_product_count'];

                if ($ksCategoryDetails['ks_category_image'] == "") {
                    $ksTemplateVariable['ks_category_image'] = "";
                } else {
                    $ksTemplateVariable['ks_category_image'] = $this->ksCategoryHelper->getKsCategoryImageUrl(substr($ksCategoryDetails['ks_category_image'], 1));
                }
                $ksTemplateVariable['ks_category_desc'] = $ksCategoryDetails['ks_category_description'];
                // Send Mail
                $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverInfo);
            }
        }
    }

    /**
     * Return product id
     *
     * @return array
     */
    public function getKsProductIds()
    {
        $ksProductId =[];
        $ksProductCollection = $this->ksProductFactory->create()->getCollection()
                            ->addFieldToFilter('ks_seller_id', $this->getRequest()->getParam('ks_seller_id'))
                            ->addFieldToFilter('ks_product_approval_status', KsProduct::KS_STATUS_APPROVED)
                            ->addFieldToFilter('ks_parent_product_id', 0);

        foreach ($ksProductCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     * Return backup product ids
     * 
     * @return array
     */
    public function getKsBackupProductIds($ksCategoryId)
    {
        $ksProductId =[];
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId)->addFieldToFilter('ks_product_id',['in' => $this->getKsProductIds()]);
        foreach ($ksProductCategoryCollection as $ksProduct) {
            $ksProductId[] = $ksProduct->getKsProductId();
        }
        return $ksProductId;
    }

    /**
     * Return backup product ids count
     * 
     * @return int
     */
    public function getKsBackupGlobalCount($ksCategoryId)
    {
        $ksProductCategoryCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_category_id',$ksCategoryId);
        return $ksProductCategoryCollection->getSize();
    }
}
