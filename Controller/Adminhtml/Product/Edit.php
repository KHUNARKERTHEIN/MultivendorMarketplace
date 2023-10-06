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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 *  Edit product
 */
class Edit extends \Magento\Catalog\Controller\Adminhtml\Product implements HttpGetActionInterface
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $ksProductFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CollectionFactory $ksProductFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        CollectionFactory $ksProductFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->ksProductFactory = $ksProductFactory;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
        parent::__construct($context, $productBuilder);
    }

    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksStoreId = (int) $this->getRequest()->getParam('store', 0);
        $ksStore = $this->storeManager->getStore($ksStoreId);
        $this->storeManager->setCurrentStore($ksStore->getCode());
        $ksProductId = (int) $this->getRequest()->getParam('id');
        $ksProduct = $this->productBuilder->build($this->getRequest());

        if ($ksProductId && !$ksProduct->getEntityId()) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
            return $ksResultRedirect->setPath('catalog/*/');
        } elseif ($ksProductId === 0) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Invalid product id. Should be numeric value greater than 0'));
            return $ksResultRedirect->setPath('catalog/*/');
        }

        // Get Attribute Set Id
        $ksSetId = $this->getRequest()->getParam('set');
        if (isset($ksSetId)) {
            $ksAttributeId = $this->getKsProductAttributeSetId($ksProduct->getEntityId());
            if ($ksAttributeId) {
                if ($ksSetId != $ksAttributeId) {
                    $ksResultRedirect = $this->resultRedirectFactory->create();
                    return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                }
            }
        }

        $this->_eventManager->dispatch('catalog_product_edit_action', ['product' => $ksProduct]);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $ksResultPage = $this->resultPageFactory->create();
        $ksResultPage->addHandle('catalog_product_' . $ksProduct->getTypeId());
        $ksResultPage->setActiveMenu('Magento_Catalog::catalog_products');
        $ksResultPage->getConfig()->getTitle()->prepend(__('Products'));
        $ksResultPage->getConfig()->getTitle()->prepend($ksProduct->getName());

        if (!$this->storeManager->isSingleStoreMode()
            && ($ksSwitchBlock = $ksResultPage->getLayout()->getBlock('store_switcher'))
        ) {
            $ksSwitchBlock->setDefaultStoreName(__('Default Values'))
                ->setWebsiteIds($ksProduct->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        'catalog/*/*',
                        ['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
                    )
                );
        }
        return $ksResultPage;
    }

    /**
     * Get Product Attribute Set
     * @param  $ksProductId
     * @return int
     */
    private function getKsProductAttributeSetId($ksProductId)
    {
        $ksModel = $this->ksProductFactory->create()->addFieldToFilter('entity_id', $ksProductId);
        $ksAttributeId = 0;
        if ($ksModel->getSize()) {
            $ksAttributeId = $ksModel->getFirstItem()->getAttributeSetId();
        }
        return $ksAttributeId;
    }
}
