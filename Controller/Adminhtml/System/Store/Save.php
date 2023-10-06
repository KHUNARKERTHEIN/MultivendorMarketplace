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
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class Save
 *
 * Save controller for system entities such as: Store, StoreGroup, Website
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Store implements HttpPostActionInterface
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory
     */
    protected $ksSellerCollectionFactory;

    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $ksUrlRewriteFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollectionFactory
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $ksUrlRewriteFactory
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Framework\Filter\FilterManager $ksFilterManager
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSeller\CollectionFactory $ksSellerCollectionFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $ksUrlRewriteFactory,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Framework\Filter\FilterManager $ksFilterManager,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
    ) {
        $this->ksSellerCollectionFactory = $ksSellerCollectionFactory;
        $this->ksUrlRewriteFactory = $ksUrlRewriteFactory;
        parent::__construct($ksContext, $ksCoreRegistry, $ksFilterManager, $ksResultForwardFactory, $ksResultPageFactory);
    }
    /**
     * Process Website model save
     *
     * @param array $ksPostData
     * @return array
     */
    private function processWebsiteSave($ksPostData)
    {
        $ksPostData['website']['name'] = $this->filterManager->removeTags($ksPostData['website']['name']);
        $ksWebsiteModel = $this->_objectManager->create(\Magento\Store\Model\Website::class);
        if ($ksPostData['website']['website_id']) {
            $ksWebsiteModel->load($ksPostData['website']['website_id']);
        }
        $ksWebsiteModel->setData($ksPostData['website']);
        if ($ksPostData['website']['website_id'] == '') {
            $ksWebsiteModel->setId(null);
        }

        $ksWebsiteModel->save();
        $this->messageManager->addSuccessMessage(__('You saved the website.'));

        return $ksPostData;
    }

    /**
     * Process Store model save
     *
     * @param array $ksPostData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    private function processStoreSave($ksPostData)
    {
        /** @var \Magento\Store\Model\Store $storeModel */
        $ksStoreModel = $this->_objectManager->create(\Magento\Store\Model\Store::class);
        $ksPostData['store']['name'] = $this->filterManager->removeTags($ksPostData['store']['name']);
        if ($ksPostData['store']['store_id']) {
            $ksStoreModel->load($ksPostData['store']['store_id']);
        }
        $ksStoreModel->setData($ksPostData['store']);
        if ($ksPostData['store']['store_id'] == '') {
            $ksStoreModel->setId(null);
        }
        $ksGroupModel = $this->_objectManager->create(
            \Magento\Store\Model\Group::class
        )->load(
            $ksStoreModel->getGroupId()
        );
        $ksStoreModel->setWebsiteId($ksGroupModel->getWebsiteId());
        if (!$ksStoreModel->isActive() && $ksStoreModel->isDefault()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The default store cannot be disabled')
            );
        }

        $ksStoreModel->save();

        // check the store edit condition
        if ($ksPostData['store']['store_id'] == '') {
            $this->ksUpdateStoreUrlRedirect($ksStoreModel->getId());
        }
        
        $this->messageManager->addSuccessMessage(__('You saved the store view.'));

        return $ksPostData;
    }

    /**
     * Process StoreGroup model save
     *
     * @param array $ksPostData
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    private function processGroupSave($ksPostData)
    {
        $ksPostData['group']['name'] = $this->filterManager->removeTags($ksPostData['group']['name']);
        /** @var \Magento\Store\Model\Group $groupModel */
        $ksGroupModel = $this->_objectManager->create(\Magento\Store\Model\Group::class);
        if ($ksPostData['group']['group_id']) {
            $ksGroupModel->load($ksPostData['group']['group_id']);
        }
        $ksGroupModel->setData($ksPostData['group']);
        if ($ksPostData['group']['group_id'] == '') {
            $ksGroupModel->setId(null);
        }
        if (!$this->isSelectedDefaultStoreActive($ksPostData, $ksGroupModel)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('An inactive store view cannot be saved as default store view')
            );
        }
        $ksGroupModel->save();
        $this->messageManager->addSuccessMessage(__('You saved the store.'));

        return $ksPostData;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $ksRedirectResult */
        $ksRedirectResult = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost() && ($ksPostData = $this->getRequest()->getPostValue())) {
            if (empty($ksPostData['store_type']) || empty($ksPostData['store_action'])) {
                $ksRedirectResult->setPath('adminhtml/*/');
                return $ksRedirectResult;
            }
            try {
                switch ($ksPostData['store_type']) {
                    case 'website':
                        $ksPostData = $this->processWebsiteSave($ksPostData);
                        break;
                    case 'group':
                        $ksPostData = $this->processGroupSave($ksPostData);
                        break;
                    case 'store':
                        $ksPostData = $this->processStoreSave($ksPostData);
                        break;
                    default:
                        $ksRedirectResult->setPath('adminhtml/*/');
                        return $ksRedirectResult;
                }
                $ksRedirectResult->setPath('adminhtml/*/');
                return $ksRedirectResult;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_getSession()->setPostData($ksPostData);
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving. Please review the error log.')
                );
                $this->_getSession()->setPostData($ksPostData);
            }

            $ksRedirectResult->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return $ksRedirectResult;
        }
        $ksRedirectResult->setPath('adminhtml/*/');
        return $ksRedirectResult;
    }

    /**
     * Verify if selected default store is active
     *
     * @param array $ksPostData
     * @param \Magento\Store\Model\Group $ksGroupModel
     * @return bool
     */
    private function isSelectedDefaultStoreActive(array $ksPostData, \Magento\Store\Model\Group $ksGroupModel)
    {
        if (!empty($ksPostData['group']['default_store_id'])) {
            $defaultStoreId = $ksPostData['group']['default_store_id'];
            if (!empty($ksGroupModel->getStores()[$defaultStoreId]) &&
                !$ksGroupModel->getStores()[$defaultStoreId]->isActive()
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * update the seller store url rewrite in the current store view
     *
     * @param array $ksStoreId
     * @return void
     */
    private function ksUpdateStoreUrlRedirect($ksStoreId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create();
        foreach ($ksSellerCollection as $ksData) {
            $ksTargetPath ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksData->getKsSellerId().'/';
            $ksRequestPath ="multivendor/".$ksData->getKsStoreUrl();

            $ksUrlRewriteModel = $this->ksUrlRewriteFactory->create();
            $ksUrlRewriteModel->setStoreId($ksStoreId);
            $ksUrlRewriteModel->setIsSystem(0);
            $ksUrlRewriteModel->setIdPath(rand(1, 100000));
            $ksUrlRewriteModel->setEntityId(0);
            $ksUrlRewriteModel->setTargetPath($ksTargetPath);
            $ksUrlRewriteModel->setRequestPath($ksRequestPath);
            $ksUrlRewriteModel->setIsAutogenerated(1);
            $ksUrlRewriteModel->save();
        }
    }
}
