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

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Delete store view.
 */
class DeleteStorePost extends \Magento\Backend\Controller\Adminhtml\System\Store implements HttpPostActionInterface
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
     * Delete store view post action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ksItemId = $this->getRequest()->getParam('item_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $ksRedirectResult */
        $ksRedirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!($ksModel = $this->_objectManager->create(\Magento\Store\Model\Store::class)->load($ksItemId))) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again.'));
            return $ksRedirectResult->setPath('adminhtml/*/');
        }
        if (!$ksModel->isCanDelete()) {
            $this->messageManager->addErrorMessage(__('This store view cannot be deleted.'));
            return $ksRedirectResult->setPath('adminhtml/*/editStore', ['store_id' => $ksModel->getId()]);
        }

        if (!$this->_backupDatabase()) {
            return $ksRedirectResult->setPath('*/*/editStore', ['store_id' => $ksItemId]);
        }

        try {

            // delete the seller store url rewrite
            $this->ksDeleteStoreUrlRedirect($ksItemId);

            $ksModel->delete();

            $this->messageManager->addSuccessMessage(__('You deleted the store view.'));
            return $ksRedirectResult->setPath('adminhtml/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager
                ->addExceptionMessage($e, __('Unable to delete the store view. Please try again later.'));
        }
        return $ksRedirectResult->setPath('adminhtml/*/editStore', ['store_id' => $ksItemId]);
    }

    /**
     * delete the seller store url rewrite in the current store view
     *
     * @param $ksStoreId
     * @return void
     */
    private function ksDeleteStoreUrlRedirect($ksStoreId)
    {
        $ksSellerCollection = $this->ksSellerCollectionFactory->create();
        foreach ($ksSellerCollection as $ksData) {
            $ksTargetPath ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksData->getKsSellerId().'/';

            $ksUrlRewriteModel = $this->ksUrlRewriteFactory->create();
            $ksUrlRewriteCollection = $ksUrlRewriteModel->getCollection()
                                ->addFieldToFilter('target_path', $ksTargetPath)
                                ->addFieldToFilter('store_id', $ksStoreId);
            if ($ksUrlRewriteCollection->getSize() != 0) {
                foreach ($ksUrlRewriteCollection as $ksData) {
                    $ksData->delete();
                }
            }
        }
    }
}
