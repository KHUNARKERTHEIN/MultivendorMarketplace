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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as ksInitializationHelper;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductTypes;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Catalog\Model\Product\Copier;
use Magento\Framework\App\ObjectManager;

/**
 * Save Controller Class
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var ksInitializationHelper
     */
    protected $ksInitializationHelper;

    /**
     * @var TypeTransitionManager
     */
    protected $ksProductTypeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var ProductFactory
     */
    protected $ksProductFactory;

    /**
     * @var KsSellerProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var ConfigurableProduct
     */
    protected $ksConfigurableProductType;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var Copier
     */
    protected $ksProductCopier;

    /**
     * @var DateTime
     */
    protected $ksDate;

    /**
     * @var ksStage
     */
    protected $ksStage = [];

    protected $ksObjectManager;

    /**
     * Save constructor.
     * @param Context $ksContext
     * @param KsSellerHelper $ksSellerHelper
     * @param ksInitializationHelper $ksInitializationHelper
     * @param TypeTransitionManager $ksProductTypeManager
     * @param ProductRepositoryInterface $ksProductRepository
     * @param ProductFactory $ksProductFactory
     * @param KsDataHelper $ksDataHelper
     * @param KsSellerProductFactory $ksSellerProductFactory
     * @param ConfigurableProduct $ksConfigurableProductType
     * @param KsSellerFactory $ksSellerFactory
     * @param Copier $productCopier
     */
    public function __construct(
        Context $ksContext,
        KsSellerHelper $ksSellerHelper,
        ksInitializationHelper $ksInitializationHelper,
        TypeTransitionManager $ksProductTypeManager,
        ProductRepositoryInterface $ksProductRepository,
        ProductFactory $ksProductFactory,
        KsDataHelper $ksDataHelper,
        KsSellerProductFactory $ksSellerProductFactory,
        ConfigurableProduct $ksConfigurableProductType,
        KsSellerFactory $ksSellerFactory,
        Copier $productCopier,
        DateTime $ksDate
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksInitializationHelper = $ksInitializationHelper;
        $this->ksProductTypeManager = $ksProductTypeManager;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksProductFactory = $ksProductFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksConfigurableProductType = $ksConfigurableProductType;
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksProductCopier = $productCopier;
        $this->ksDate = $ksDate;
        $this->ksObjectManager = ObjectManager::getInstance();
        parent::__construct($ksContext);
    }

    /**
     * Execute Action to save the product
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        $ksResultRedirect = $this->resultRedirectFactory->create();
        // check for seller
        if ($ksIsSeller) {
            $ksProductData = $this->getRequest()->getPostValue();
            $ksParentProductId = $this->getRequest()->getParam('parent_id');
            $ksStoreId = $this->getRequest()->getParam('store', 0);
            $ksRedirectBack = $this->getRequest()->getParam('back');
            if ($ksProductData) {
                try {
                    $ksParentProduct = $this->ksProductRepository->getById($ksParentProductId, true, $ksStoreId);

                    if ($ksParentProduct->getTypeId() == "downloadable") {
                        if (!array_key_exists('downloadable', $ksProductData)) {
                            $this->messageManager->addErrorMessage(
                                __('please add links for downloadable product.')
                            );
                            return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                        }
                    }

                    if ($ksParentProduct->getTypeId() == "configurable") {
                        if (!array_key_exists('variations-matrix', $ksProductData)) {
                            $this->messageManager->addErrorMessage(
                                __('please add variations for configurable product.')
                            );
                            return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                        }
                    }

                    //condiontional array for configurable product
                    if (array_key_exists('variations-matrix', $ksProductData)) {
                        $ksVaritionMatrix = $ksProductData['variations-matrix'];
                        $ksVaritionArray = [];
                        foreach ($ksVaritionMatrix as $key => $ksMatrix) {
                            $ksMatrix["variationKey"] = $key;
                            if ($this->getRequest()->getParam('parent_id') && $this->getRequest()->getParam('id')) {
                                $ksMatrix["newProduct"] = 0;
                            } else {
                                $ksMatrix["newProduct"] = 1;
                            }
                            
                            $ksVaritionArray[]= $ksMatrix;
                            $this->ksStage[] = $ksMatrix['stage'];
                        }

                        $this->getRequest()->setPostValue('configurable-matrix-serialized', json_encode($ksVaritionArray));
                        $this->getRequest()->setPostValue('new-variations-attribute-set-id', $ksParentProduct->getAttributeSetId());
                    }
                    if (array_key_exists('associated_product_ids', $ksProductData)) {
                        if ($this->getRequest()->getParam('id')) {
                            $ksAssociated = $ksProductData['associated_product_ids'];
                            $this->getRequest()->setPostValue('associated_product_ids_serialized', json_encode($ksAssociated));
                            $this->getRequest()->setPostValue('new-variations-attribute-set-id', $ksParentProduct->getAttributeSetId());
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
                    
                    if ($this->getRequest()->getParam('parent_id') && !$this->getRequest()->getParam('id')) {
                        $ksParentProduct->unsetData('quantity_and_stock_status');

                        $ksNewProduct = $this->ksProductCopier->copy($ksParentProduct);

                        $this->getRequest()->setPostValue('id', $ksNewProduct->getId());

                        $ksProductValue = $this->getRequest()->getPostValue('product');
                        $ksProductValue['website_ids']= $ksNewProduct->getWebsiteIds();
                        $ksProductValue['url_key']= $ksNewProduct->getUrlKey();
                        $this->getRequest()->setPostValue('product', $ksProductValue);
                    }
                    
                    $ksProduct = $this->build($this->getRequest());
                    $ksProduct = $this->ksInitializationHelper->initialize($ksProduct);

                    $this->ksProductTypeManager->processProduct($ksProduct);
                    if (isset($ksProductData['product'][$ksProduct->getIdFieldName()])) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The product was unable to be saved. Please try again.')
                        );
                    }
                    
                    $ksProduct->save();

                    $this->saveKsSellerProduct($ksProduct->getId(), $this->getRequest()->getParam('back'));

                    if ($ksProduct->getTypeId()==ConfigurableProduct::TYPE_CODE) {
                        $this->saveKsAssociateProductToSeller($ksProduct, $this->getRequest()->getParam('back'));
                    }

                    $this->messageManager->addSuccessMessage(__('You saved the product.'));
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __($e->getMessage())
                    );

                    //for redirecting url
                    return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addErrorMessage(
                        __($e->getMessage())
                    );

                    //for redirecting url
                    return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
                }
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        if ($ksRedirectBack === 'new') {
            $ksResultRedirect->setPath(
                'multivendor/pricecomparison/viewproduct'
            );
        } else {
            $ksResultRedirect->setPath(
                'multivendor/pricecomparison/productlist'
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
     * save associated product to seller
     * @param Product
     */
    protected function saveKsAssociateProductToSeller($ksProduct)
    {
        $ksAssociateproducts = [];
        $ksAssociatedProductCollection = $this->ksConfigurableProductType
                                        ->getUsedProductCollection($ksProduct)
                                        ->setFlag('has_stock_status_filter', true);
        $ksStage = $this->ksStage;
        $ksIndex = 0;
        foreach ($ksAssociatedProductCollection as $ksAssociatedProduct) {
            $this->saveKsSellerProduct($ksAssociatedProduct->getEntityId(), 0, $ksStage[$ksIndex++]);
        }
    }

    /**
     * save seller product
     * @param int $ksProductId
     */
    private function saveKsSellerProduct($ksProductId, string $ksStatus, $ksStage = null)
    {
        if ($ksStatus=='new' || $ksStatus=='close' || $ksStatus=='save') {
            $ksPendingStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED;
            $ksApprovalStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED;
            $ksPendingUpdateStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED;
        } else {
            $ksPendingStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING;
            $ksApprovalStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
            $ksPendingUpdateStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING_UPDATE;
        }

        try {
            $ksSellerId = $this->ksSellerHelper->getKsCustomerId();

            $ksProductAutoApproval = $this->ksSellerFactory->create()
                ->load($ksSellerId, 'ks_seller_id')
                ->getKsProductAutoApproval();

            //get product collection
            $ksSellerProductCollection = $this->ksSellerProductFactory->create()->getCollection()
                ->addFieldToFilter('ks_product_id', $ksProductId);
            //check size
            if ($ksSellerProductCollection->getSize()) {
                foreach ($ksSellerProductCollection as $ksProduct) {
                    $ksId = $ksProduct->getId();
                }
                $ksCollection = $this->ksSellerProductFactory->create()->load($ksId);
                $ksCollection->setKsSellerId($ksSellerId);
                if ($ksProductAutoApproval) {
                    $ksCollection->setKsProductApprovalStatus($ksApprovalStatus);
                } else {
                    if ($ksCollection->getKsProductApprovalStatus()) {
                        if (!$this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_edit_approval_required')) {
                            $ksCollection->setKsProductApprovalStatus($ksApprovalStatus);
                        } else {
                            $ksCollection->setKsProductApprovalStatus($ksPendingUpdateStatus);
                        }
                    } else {
                        $ksCollection->setKsProductApprovalStatus($ksPendingStatus);
                    }
                }
                $ksCollection->setKsRejectionReason("");
                $ksCollection->setKsParentProductId($this->getRequest()->getParam('parent_id'));
                if ($ksStage != null) {
                    $ksCollection->setKsProductStage($ksStage);
                } else {
                    $ksCollection->setKsProductStage($this->getRequest()->getParam('ks_product_stage'));
                }
                $ksCollection->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksCollection->save();

            //get seller id
            } elseif (isset($ksSellerId) && $ksSellerId != '') {
                $ksCollection = $this->ksSellerProductFactory->create();
                $ksCollection->setKsProductId($ksProductId);
                $ksCollection->setKsSellerId($ksSellerId);
                if ($ksProductAutoApproval) {
                    $ksCollection->setKsProductApprovalStatus($ksApprovalStatus);
                } else {
                    if (!$this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_approval_required')) {
                        $ksCollection->setKsProductApprovalStatus($ksApprovalStatus);
                    } else {
                        $ksCollection->setKsProductApprovalStatus($ksPendingStatus);
                    }
                }
                $ksCollection->setKsRejectionReason("");
                $ksCollection->setKsParentProductId($this->getRequest()->getParam('parent_id'));
                if ($ksStage != null) {
                    $ksCollection->setKsProductStage($ksStage);
                } else {
                    $ksCollection->setKsProductStage($this->getRequest()->getParam('ks_product_stage'));
                }
                $ksCollection->setKsCreatedAt($this->ksDate->gmtDate());
                $ksCollection->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksCollection->save();
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
