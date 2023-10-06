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
use Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests;

/**
 * Save Controller class
 */
class Save extends \Magento\Framework\App\Action\Action
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
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory
     */
    protected $ksSellerCategoriesCollection;

    /**
     * @var  KsCategoryRequests
     */
    protected $ksCategoryHelper;
 
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection
     * @param KsCategoryRequests $ksCategoryHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory $ksSellerCategoriesCollection,
        KsCategoryRequests $ksCategoryHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerCategoriesCollection = $ksSellerCategoriesCollection;
        $this->ksCategoryHelper = $ksCategoryHelper;
        parent::__construct($ksContext);
    }

    /**
     * Vendor Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get data
                $ksPostData = $this->getRequest()->getPostValue();
                //check exist data
                if (array_key_exists("ks_category_id", $ksPostData)) {
                    //get seller id
                    $ksSellerId = $this->getRequest()->getPostValue('ks_seller_id');
                    //get category id
                    $ksCategoryId = $this->getRequest()->getPostValue('ks_category_id');
                    //get store id
                    $ksStoreId = $this->getRequest()->getPostValue('ks_store_id');
                    //get model data
                    $ksSellerCategoriesCollection = $this->ksSellerCategoriesCollection->create()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_category_id', $ksCategoryId)->getFirstItem();
                    //check exist data
                    if (array_key_exists("ks-category-enabled", $ksPostData)) {
                        //save data
                        $ksSellerCategoriesCollection->setKsCategoryStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_ENABLED)->save();
                        //assign category from Product
                        $this->ksCategoryHelper->ksAssignProductInCategory($ksSellerId,$ksCategoryId);
                    } else {
                        //save data
                        $ksSellerCategoriesCollection->setKsCategoryStatus(\Ksolves\MultivendorMarketplace\Model\KsCategoryRequestsAllowed::KS_STATUS_DISABLED)->save();
                        //Unassign category from Product
                        $this->ksCategoryHelper->ksUnAssignProductCategory(array($ksCategoryId),$ksSellerId);
                    }
                    $this->messageManager->addSuccess(__('A product category has been saved successfully.'));
                }
            } catch (\Exception $e) {
                $ksMessage = __($e->getMessage());
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
