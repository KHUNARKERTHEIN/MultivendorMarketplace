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

namespace Ksolves\MultivendorMarketplace\Controller\Product;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ksInitializationHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Copier;
use Magento\Catalog\Model\Product\Type as ProductTypes;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Ksolves\MultivendorMarketplace\Model\KsProduct;
use Magento\Framework\Locale\CurrencyInterface;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsSellerCategories\CollectionFactory as KsSellerCategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Relation;

/**
 * Save Controller class
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
    * XML Path
    */
    public const XML_PATH_PRODUCT_REQUEST_MAIL = 'ks_marketplace_catalog/ks_product_settings/ks_request_email';

    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var TypeTransitionManager
     */
    protected $ksProductTypeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var ksInitializationHelper
     */
    protected $ksInitializationHelper;

    /**
     * @var ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var KsSellerProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var Product\Copier
     */
    protected $ksProductCopier;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductHelper
     */
    protected $ksProductHelper;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $ksCategoryLinkManagement;

    /**
     * @var LoggerInterface
     */
    protected $ksLogger;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var ConfigurableProduct
     */
    protected $ksConfigurableProductType;

    /**
     * @var Escaper
     */
    private $ksEscaper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Magento\Catalog\Model\CategoryFactory
     */
    protected $ksCategoryFactory;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests
     */
    protected $ksCategoryHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper
     */
    protected $ksFavSellerHelper;

    /**
     * @var  \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory
     */
    protected $ksProductCategoryCollectionFactory;

    /**
     * @var KsSellerCategoryCollectionFactory
     */
    protected $ksSellerCategoryCollection;

    /**
     * @var Relation
     */
    protected $ksRelationProcessor;

    /**
     * @var StoreManagerInterface
     */
    protected $ksStoreManager;

    /**
     * @var CurrencyInterface
     */
    protected $ksLocaleCurrency;

    protected $ksObjectManager;

    /**
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param ksInitializationHelper $ksInitializationHelper
     * @param TypeTransitionManager $ksProductTypeManager
     * @param ProductRepositoryInterface $ksProductRepository
     * @param ProductFactory $ksProductFactory
     * @param KsSellerProductFactory $ksSellerProductFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsProductHelper $ksProductHelper
     * @param Copier $productCopier
     * @param CategoryLinkManagementInterface $ksCategoryLinkManagement
     * @param LoggerInterface $ksLogger
     * @param DataPersistorInterface $ksDataPersistor
     * @param ConfigurableProduct $ksConfigurableProductType
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param Magento\Catalog\Model\CategoryFactory $ksCategoryFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper
     * @param Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory
     * @param KsSellerCategoryCollectionFactory $ksSellerCategoryCollection
     * @param CurrencyInterface $ksLocaleCurrency
     * @param Relation $ksRelationProcessor
     * @param StoreManagerInterface $ksStoreManager = null
     * @param Escaper $ksEscaper = null
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        KsSellerHelper $ksSellerHelper,
        ksInitializationHelper $ksInitializationHelper,
        TypeTransitionManager $ksProductTypeManager,
        ProductRepositoryInterface $ksProductRepository,
        ProductFactory $ksProductFactory,
        KsSellerProductFactory $ksSellerProductFactory,
        KsDataHelper $ksDataHelper,
        KsProductHelper $ksProductHelper,
        Copier $productCopier,
        LoggerInterface $ksLogger,
        DataPersistorInterface $ksDataPersistor,
        ConfigurableProduct $ksConfigurableProductType,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Magento\Catalog\Model\CategoryFactory $ksCategoryFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsCategoryRequests $ksCategoryHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\ResourceModel\KsProductCategoryBackup\CollectionFactory $ksProductCategoryCollectionFactory,
        KsSellerCategoryCollectionFactory $ksSellerCategoryCollection,
        CurrencyInterface $ksLocaleCurrency,
        Relation $ksRelationProcessor,
        CategoryLinkManagementInterface $ksCategoryLinkManagement = null,
        StoreManagerInterface $ksStoreManager = null,
        Escaper $ksEscaper = null
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksInitializationHelper = $ksInitializationHelper;
        $this->ksProductTypeManager = $ksProductTypeManager;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksProductCopier = $productCopier;
        $this->ksObjectManager = ObjectManager::getInstance();
        $this->ksCategoryLinkManagement = $ksCategoryLinkManagement ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
        $this->ksLogger = $ksLogger;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksConfigurableProductType = $ksConfigurableProductType;
        $this->ksStoreManager = $ksStoreManager ?: ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->ksProductTypeHelper     = $ksProductTypeHelper;
        $this->ksEmailHelper           = $ksEmailHelper;
        $this->ksCategoryFactory       = $ksCategoryFactory;
        $this->ksCategoryHelper        = $ksCategoryHelper;
        $this->ksFavSellerHelper       = $ksFavSellerHelper;
        $this->ksEscaper = $ksEscaper ?: ObjectManager::getInstance()
        ->get(\Magento\Framework\Escaper::class);
        $this->ksProductCategoryCollectionFactory = $ksProductCategoryCollectionFactory;
        $this->ksLocaleCurrency        = $ksLocaleCurrency;
        $this->ksSellerCategoryCollection = $ksSellerCategoryCollection;
        $this->ksRelationProcessor = $ksRelationProcessor;
        parent::__construct($ksContext);
    }

    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();

        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksProductAttributeSetId = $this->getRequest()->getParam('set');
        $ksProductTypeId = $this->getRequest()->getParam('type');
        $ksProductId = $this->getRequest()->getParam('id');
        $ksStoreId = $this->getRequest()->getParam('store', 0);
        $ksRedirectBack = $this->getRequest()->getParam('back');
        $ksCurrentCode=$this->ksStoreManager->getStore()->getCode();

        $ksStore = $this->ksStoreManager->getStore($ksStoreId);
        $this->ksStoreManager->setCurrentStore($ksStore->getCode());
        // check for seller
        if ($ksIsSeller == 1) {
            $ksProductData = $this->getRequest()->getPostValue();
            $ksSellerId = $this->ksDataHelper->getKsCustomerId();
            if ($ksProductData) {
                try {
                    $ksProductArray = $this->getRequest()->getPostValue('product');

                    $ksProductCategoryIds = isset($ksProductArray['category_ids']) ? $ksProductArray['category_ids'] : [];
                    $ksCollection = $this->ksProductCategoryCollectionFactory->create()->addFieldToFilter('ks_product_id',$ksProductId);

                    foreach($ksCollection as $ksItem){
                        $ksCollection = $this->ksSellerCategoryCollection->create()
                        ->addFieldToFilter('ks_seller_id', $ksSellerId)
                        ->addFieldToFilter('ks_category_id',$ksItem->getKsCategoryId());
                        if($ksCollection->getSize() > 0){
                            if (($ksKey = array_search($ksItem->getKsCategoryId(),$ksProductCategoryIds)) !== false) {
                                unset($ksProductCategoryIds[$ksKey]);
                            } else {
                                $ksItem->delete();
                            }
                        }
                    }
                    if(isset($ksProductArray['category_ids'])){
                        $ksProductArray['category_ids'] = $ksProductCategoryIds;
                        $this->getRequest()->setPostValue('product',$ksProductArray);
                    }

                    //condiontional array for configurable product
                    if (array_key_exists('variations-matrix', $ksProductData)) {
                        $ksVaritionMatrix = $ksProductData['variations-matrix'];
                        $ksVaritionArray = [];
                        foreach ($ksVaritionMatrix as $key => $ksMatrix) {
                            $ksMatrix["variationKey"] = $key;
                            $ksVaritionArray[]= $ksMatrix;
                        }
                        $this->getRequest()->setPostValue('configurable-matrix-serialized', json_encode($ksVaritionArray));
                        $this->getRequest()->setPostValue('new-variations-attribute-set-id', $ksProductData['product']['attribute_set_id']);
                    }
                    if (array_key_exists('associated_product_ids', $ksProductData)) {
                        $ksAssociated = $ksProductData['associated_product_ids'];

                        $this->getRequest()->setPostValue('associated_product_ids_serialized', json_encode($ksAssociated));
                        if (isset($ksProductData['product']['attribute_set_id'])) {
                            $this->getRequest()->setPostValue('new-variations-attribute-set-id', $ksProductData['product']['attribute_set_id']);
                        }
                    }

                    //downloadable array for downloadable product
                    if (array_key_exists('downloadable', $ksProductData)) {
                        // unserialize the file array of downloadable product
                        foreach ($ksProductData['downloadable'] as $ksParentKey => $ksDownloadable) {
                            foreach ($ksDownloadable as $ksChildKey => $ksValue) {
                                $ksFileData = $this->ksObjectManager->create('Magento\Framework\Serialize\Serializer\Json')->unserialize($ksValue['file']);
                                $ksProductData['downloadable'][$ksParentKey][$ksChildKey]['file'] = $ksFileData;
                                if (array_key_exists('sample', $ksValue)) {
                                    $ksSampleFileData = $this->ksObjectManager->create('Magento\Framework\Serialize\Serializer\Json')->unserialize($ksValue['sample']['file']);
                                    $ksProductData['downloadable'][$ksParentKey][$ksChildKey]['sample']['file'] = $ksSampleFileData;
                                }
                            }
                        }
                        $this->getRequest()->setPostValue('downloadable', $ksProductData['downloadable']);
                    }

                    $ksProduct = $this->build($this->getRequest());

                    $ksProduct = $this->ksInitializationHelper->initialize($ksProduct);

                    $this->ksProductTypeManager->processProduct($ksProduct);
                    if (isset($ksProductData['product'][$ksProduct->getIdFieldName()])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product was unable to be saved. Please try again.')
                        );
                    }

                    $ksOriginalSku = $ksProduct->getSku();
                    $ksCanSaveCustomOptions = $ksProduct->getCanSaveCustomOptions();
                    $ksProduct->save();
                    $this->ksHandleImageRemoveError($ksProductData, $ksProduct->getId());
                    $this->ksCategoryLinkManagement->assignProductToCategories(
                        $ksProduct->getSku(),
                        $ksProduct->getCategoryIds()
                    );
                    $ksProductId = $ksProduct->getId();
                    $ksProductAttributeSetId = $ksProduct->getAttributeSetId();
                    $ksProductTypeId = $ksProduct->getTypeId();

                    $ksExtendedData = $ksProductData;
                    $ksExtendedData['can_save_custom_options'] = $ksCanSaveCustomOptions;
                    $this->ksCopyToStores($ksExtendedData, $ksProductId);

                    $this->ksRemoveLinkedItem($ksProduct, $ksProductData);
                    $this->ksRemoveAssociateItem($ksProductId, $ksProductData);

                    if ($ksProduct->getSku() != $ksOriginalSku) {
                        $this->messageManager->addNoticeMessage(
                            __(
                                'SKU for product %1 has been changed to %2.',
                                $this->ksEscaper->escapeHtml($ksProduct->getName()),
                                $this->ksEscaper->escapeHtml($ksProduct->getSku())
                            )
                        );
                    }
                    $this->_eventManager->dispatch(
                        'controller_action_catalog_product_save_entity_after',
                        ['controller' => $this, 'product' => $ksProduct]
                    );

                    $this->ksStoreManager->setCurrentStore($ksCurrentCode);

                    //save product to seller table
                    if ($ksProductId) {
                        $this->ksSendEmailAction($ksProductId, $this->getRequest()->getParam('back'));

                        //Email functionality for followers
                        $ksProductType = $ksProduct->getTypeId();
                        $ksOldSpecialPrice = $ksProduct->getOrigData('special_price');
                        $ksNewSpecialPrice = $ksProduct->getSpecialPrice();
                        $ksSellerId = $this->ksDataHelper->getKsCustomerId();

                        if (!$this->getRequest()->getParam('id')) {
                            $ksEditState = \Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmail::KS_NEW_PRODUCT;
                            $this->ksFavSellerHelper->ksSaveFavSellerEmailData($ksSellerId, $ksProductId, $ksOldSpecialPrice, $ksProductType, $ksEditState);
                        } elseif ($ksNewSpecialPrice != 0 && $ksOldSpecialPrice != $ksNewSpecialPrice) {
                            $ksEditState = \Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmail::KS_EDIT_PRODUCT;
                            $this->ksFavSellerHelper->ksSaveFavSellerEmailData($ksSellerId, $ksProductId, $ksOldSpecialPrice, $ksProductType, $ksEditState);
                        }
                    }

                    if ($ksRedirectBack === 'duplicate') {
                        $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
                        $ksProduct->unsetData('quantity_and_stock_status');
                        $ksNewProduct = $this->ksProductCopier->copy($ksProduct);
                        $this->checkUniqueAttributes($ksProduct);
                        $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
                    }
                    $this->messageManager->addSuccessMessage(__('You saved the product.'));
                    $this->ksDataPersistor->clear('ks_seller_product');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->ksStoreManager->setCurrentStore($ksCurrentCode);
                    $this->ksLogger->critical($e);
                    $this->messageManager->addExceptionMessage($e);
                    $this->ksDataPersistor->set('ks_seller_product', $ksProductData);
                    $ksRedirectBack = $ksProductId ? true : 'new';
                } catch (\Exception $e) {
                    $this->ksStoreManager->setCurrentStore($ksCurrentCode);
                    $this->ksLogger->critical($e);
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->ksDataPersistor->set('ks_seller_product', $ksProductData);
                    $ksRedirectBack = $ksProductId ? true : 'new';
                }
            }
        } else {
            $ksResultRedirect->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }


        if ($ksRedirectBack === 'new') {
            $ksResultRedirect->setPath(
                'multivendor/product/new',
                ['set' => $ksProductAttributeSetId, 'type' => $ksProductTypeId]
            );
        } elseif ($ksRedirectBack=='duplicate' && isset($ksNewProduct)) {
            $ksResultRedirect->setPath(
                'multivendor/product/edit',
                ['id' => $ksNewProduct->getEntityId()]
            );
        } elseif ($ksRedirectBack=='close' || $ksRedirectBack=='save') {
            $ksResultRedirect->setPath(
                'multivendor/product/index',
            );
        } else {
            $ksResultRedirect->setPath(
                'multivendor/product/edit',
                [   'id' => $ksProductId,
                    'store' => $ksStoreId
                ]
            );
        }

        return $ksResultRedirect;
    }

    /**
     * @param $ksRequest
     * @return \Magento\Catalog\Model\Product
     */
    public function build(RequestInterface $ksRequest)
    {
        $ksProductId = (int) $ksRequest->getParam('id');
        $ksStoreId = $ksRequest->getParam('store', 0);
        $ksAttributeSetId = (int) $ksRequest->getParam('set');
        $ksTypeId = $ksRequest->getParam('type');

        if ($ksProductId) {
            try {
                $ksProduct = $this->ksProductRepository->getById($ksProductId, true, $ksStoreId);
            } catch (\Exception $e) {
                $ksProduct = $this->createEmptyProduct(ProductTypes::DEFAULT_TYPE, $ksAttributeSetId, $ksStoreId);
            }
        } else {
            $ksProduct = $this->createEmptyProduct($ksTypeId, $ksAttributeSetId, $ksStoreId);
        }

        if ($ksRequest->has('attributes')) {
            $ksAttributes = $ksRequest->getParam('attributes');
            if (!empty($ksAttributes)) {
                $ksProduct->setTypeId(ConfigurableProduct::TYPE_CODE);
                $this->ksConfigurableProductType->setUsedProductAttributes($ksProduct, $ksAttributes);
            } else {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            }
        }

        return $ksProduct;
    }

    /**
     * @param int $ksTypeId
     * @param int $attributeSetId
     * @param int $ksStoreId
     * @return \Magento\Catalog\Model\Product
     */
    private function createEmptyProduct($ksTypeId, $ksAttributeSetId, $ksStoreId): Product
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $ksProduct = $this->ksProductFactory->create();
        $ksProduct->setData('_edit_mode', true);

        if ($ksTypeId !== null) {
            $ksProduct->setTypeId($ksTypeId);
        }

        if ($ksStoreId !== null) {
            $ksProduct->setStoreId($ksStoreId);
        }

        if ($ksAttributeSetId) {
            $ksProduct->setAttributeSetId($ksAttributeSetId);
        }

        return $ksProduct;
    }

    /**
     * save seller product
     * @param int $ksProductId
     */
    protected function ksSendEmailAction($ksProductId, string $ksStatus)
    {
        try {
            //get product collection
            $ksSellerProductCollection = $this->ksSellerProductFactory->create()->getCollection()
                ->addFieldToFilter('ks_product_id', $ksProductId);

            if ($ksSellerProductCollection->getSize()) {
                $ksId = $ksSellerProductCollection->getFirstItem()->getId();
                $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
                $ksCollection = $this->ksSellerProductFactory->create()->load($ksId);

                if (($ksCollection->getKsProductApprovalStatus()==KsProduct::KS_STATUS_PENDING_UPDATE || $ksCollection->getKsProductApprovalStatus()==KsProduct::KS_STATUS_PENDING)) {
                    $ksProduct = $this->ksProductRepository->getById($ksProductId, true, 0);
                    $this->ksSendEmailProductRequest($ksProduct);
                }

                if ($ksCollection->getKsProductApprovalStatus()==KsProduct::KS_STATUS_APPROVED) {
                    $this->ksEmailHelper->ksSendEmailProductApprove($ksSellerId, array($ksProductId));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }

    /**
     * Notify customer when image was not deleted in specific case.
     *
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $ksPostData
     * @param int $ksProductId
     * @return void
     */
    private function ksHandleImageRemoveError($ksPostData, $ksProductId)
    {
        if (isset($ksPostData['product']['media_gallery']['images'])) {
            $ksRemovedImagesAmount = 0;
            foreach ($ksPostData['product']['media_gallery']['images'] as $ksImage) {
                if (!empty($ksImage['removed'])) {
                    $ksRemovedImagesAmount++;
                }
            }
            if ($ksRemovedImagesAmount) {
                $ksExpectedImagesAmount = count($ksPostData['product']['media_gallery']['images']) - $ksRemovedImagesAmount;
                $ksProduct = $this->ksProductRepository->getById($ksProductId);
                $ksImages = $ksProduct->getMediaGallery('images');
                if (is_array($ksImages) && $ksExpectedImagesAmount != count($ksImages)) {
                    $this->messageManager->addNoticeMessage(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

    /**
     * Do copying data to stores
     *
     * @param array $ksData
     * @param int $ksProductId
     *
     * @return void
     */
    protected function ksCopyToStores($ksData, $ksProductId)
    {
        if (!empty($ksData['product']['copy_to_stores'])) {
            foreach ($ksData['product']['copy_to_stores'] as $ksWebsiteId => $ksGroup) {
                if (isset($ksData['product']['website_ids'][$ksWebsiteId])
                    && (bool)$ksData['product']['website_ids'][$ksWebsiteId]) {
                    foreach ($ksGroup as $ksStore) {
                        $this->ksCopyToStore($ksData, $ksProductId, $ksStore);
                    }
                }
            }
        }
    }

    /**
     * Do copying data to stores
     *
     * If the 'copy_from' field is not specified in the input data,
     * the store fallback mechanism will automatically take the admin store's default value.
     *
     * @param array $ksData
     * @param int $ksProductId
     * @param array $store
     */
    private function ksCopyToStore($ksData, $ksProductId, $ksStore)
    {
        if (isset($ksStore['copy_from'])) {
            $ksCopyFrom = $ksStore['copy_from'];
            $ksCopyTo = (isset($ksStore['copy_to'])) ? $ksStore['copy_to'] : 0;
            if ($ksCopyTo) {
                $this->ksObjectManager->create(\Magento\Catalog\Model\Product::class)
                    ->setStoreId($ksCopyFrom)
                    ->load($ksProductId)
                    ->setStoreId($ksCopyTo)
                    ->setCanSaveCustomOptions($ksData['can_save_custom_options'])
                    ->setCopyFromView(true)
                    ->save();
            }
        }
    }

    /**
     * Check unique attributes and add error to message manager
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    private function checkUniqueAttributes(\Magento\Catalog\Model\Product $product)
    {
        $uniqueLabels = [];
        foreach ($product->getAttributes() as $attribute) {
            if ($attribute->getIsUnique() && $attribute->getIsUserDefined()
                && $product->getData($attribute->getAttributeCode()) !== null
            ) {
                $uniqueLabels[] = $attribute->getDefaultFrontendLabel();
            }
        }
        if ($uniqueLabels) {
            $uniqueLabels = implode('", "', $uniqueLabels);
            $this->messageManager->addErrorMessage(__('The value of attribute(s) "%1" must be unique', $uniqueLabels));
        }
    }

    /**
     * remove linked item after remove from product data
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    public function ksRemoveLinkedItem(\Magento\Catalog\Model\Product $ksProduct, $ksProductData)
    {
        $ksRelatedItemArr = [];
        $ksUpsellItemArr = [];
        $ksCrossSellItemArr = [];

        if (array_key_exists('links', $ksProductData)) {
            $ksLinkData = $ksProductData['links'];

            if (array_key_exists('related', $ksLinkData)) {
                foreach ($ksLinkData['related'] as $item) {
                    $ksRelatedItemArr[]= $item['id'];
                }
            }

            if (array_key_exists('upsell', $ksLinkData)) {
                foreach ($ksLinkData['upsell'] as $item) {
                    $ksUpsellItemArr[]= $item['id'];
                }
            }

            if (array_key_exists('crosssell', $ksLinkData)) {
                foreach ($ksLinkData['crosssell'] as $item) {
                    $ksCrossSellItemArr[]= $item['id'];
                }
            }
        }

        //remove related product which out of stock after unassign from product
        foreach ($ksProduct->getRelatedLinkCollection() as $ksRelatedItem) {
            if (!in_array($ksRelatedItem->getLinkedProductId(), $ksRelatedItemArr)) {
                $ksRelatedItem->delete();
            }
        }

        //remove upsell product which out of stock after unassign from product
        foreach ($ksProduct->getUpSellLinkCollection() as $ksUpSellItem) {
            if (!in_array($ksUpSellItem->getLinkedProductId(), $ksUpsellItemArr)) {
                $ksUpSellItem->delete();
            }
        }

        //remove CrossSell product which out of stock after unassign from product
        foreach ($ksProduct->getCrossSellLinkCollection() as $ksCrossSellItem) {
            if (!in_array($ksCrossSellItem->getLinkedProductId(), $ksCrossSellItemArr)) {
                $ksCrossSellItem->delete();
            }
        }
    }

    /**
     * remove associate linked item after remove from product data
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    public function ksRemoveAssociateItem($ksProductId, $ksProductData)
    {
        $ksGroupedItemArr = [];

        if (array_key_exists('links', $ksProductData)) {
            $ksLinkData = $ksProductData['links'];

            if (array_key_exists('associated', $ksLinkData)) {
                foreach ($ksLinkData['associated'] as $item) {
                    $ksGroupedItemArr[]= $item['id'];
                }
            }
        }

        $ksLinkProductCollection = $this->ksProductHelper
            ->getKsAssociateLinkCollection()
            ->addFieldToFilter('product_id', $ksProductId);

        //remove associated product which out of stock after unassign from product
        foreach ($ksLinkProductCollection as $ksLinkProductItem) {
            if (!in_array($ksLinkProductItem->getLinkedProductId(), $ksGroupedItemArr)) {
                $this->ksRelationProcessor->removeRelations(
                    $ksLinkProductItem->getProductId(),
                    $ksLinkProductItem->getLinkedProductId()
                );

                $ksLinkProductItem->delete();
            }
        }
    }

    /**
     * Send Mail to Admin when Seller Request for New Product
     */
    public function ksSendEmailProductRequest($ksProduct)
    {
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            self::XML_PATH_PRODUCT_REQUEST_MAIL,
            $this->ksDataHelper->getKsCurrentStoreView()
        );
        if ($ksEmailEnabled != "disable") {
            $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_settings/ks_email_sender'
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
            //Get seller info
            $ksSellerInfo = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);
            //Get Receiver Info
            $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_settings/ks_product_admin_email_option';
            $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_settings/ks_product_admin_email';
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            $ksReceiverInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
            //Get store id
            $ksStoreId = $this->getRequest()->getParam('store', 0);
            $ksCategoryName = '';
            $ksCategory = '';
            $ksCategoryIds = $ksProduct->getCategoryIds();
            if (!empty($ksCategoryIds)) {
                foreach ($ksCategoryIds as $ksCategoryId) {
                    $ksCategory.= $this->ksCategoryHelper->getKsCategoryNameWithParent($ksCategoryId, $ksStoreId) . ',';
                }
                $ksCategoryName.= substr($ksCategory, 0, -1);
            }
            $ksStore = $this->ksStoreManager->getStore(0);
            $ksCurrency = $this->ksLocaleCurrency->getCurrency($ksStore->getBaseCurrencyCode());
            $ksTemplateVariable = [];
            $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverInfo['name']);
            $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerInfo['name']);
            $ksTemplateVariable['ksProductName'] = $ksProduct->getName();
            $ksTemplateVariable['ksCategory'] = ($ksCategoryName) ? $ksCategoryName : 'N/A';
            $ksTemplateVariable['ksSku'] = $ksProduct->getSku();
            $ksTemplateVariable['ksPrice'] = $ksCurrency->toCurrency(sprintf("%f", $ksProduct->getPrice()));
            $ksTemplateVariable['ksDescription'] = ($ksProduct->getDescription()) ? strip_tags($ksProduct->getDescription()) : 'N/A';
            // Send Mail
            $this->ksEmailHelper->ksSendEmail(
                $ksEmailEnabled,
                $ksTemplateVariable,
                $ksSenderInfo,
                $ksReceiverInfo
            );
        }
        $this->messageManager->addSuccessMessage(__("A product request has been send successfully."));
    }
}
