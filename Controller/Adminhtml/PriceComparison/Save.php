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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\PriceComparison;

use Magento\Catalog\Controller\Adminhtml\Product\Builder as KsProductBuilder;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as KsInitializationHelper;
use Magento\Catalog\Model\Product\TypeTransitionManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\Type as ProductTypes;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Model\KsProductFactory as KsSellerProductFactory;
use Magento\Framework\Serialize\Serializer\Json as KsJsonSerializer;

/**
 * Class Save Controller
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $ksProductBuilder;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     */
    protected $ksInitializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $ksProductTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $ksProductRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsProductFactory
     */
    protected $ksSellerProductFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $ksJsonSerializer;

    /**
     * Array $ksStage
     */
    protected $ksStage = [];

    /**
     * Initialize Save Controller
     *
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param KsProductBuilder $ksProductBuilder
     * @param KsInitializationHelper $ksInitializationHelper
     * @param TypeTransitionManager $ksProductTypeManager
     * @param ProductRepositoryInterface $ksProductRepository
     * @param KsSellerProductFactory $ksSellerProductFactory
     * @param KsJsonSerializer $ksJsonSerializer
     * @param KsDataHelper $ksDataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        KsProductBuilder $ksProductBuilder,
        KsInitializationHelper $ksInitializationHelper,
        TypeTransitionManager $ksProductTypeManager,
        ProductRepositoryInterface $ksProductRepository,
        KsSellerProductFactory $ksSellerProductFactory,
        KsJsonSerializer $ksJsonSerializer,
        KsDataHelper $ksDataHelper
    ) {
        $this->ksProductBuilder = $ksProductBuilder;
        $this->ksInitializationHelper = $ksInitializationHelper;
        $this->ksProductTypeManager = $ksProductTypeManager;
        $this->ksProductRepository = $ksProductRepository;
        $this->ksSellerProductFactory = $ksSellerProductFactory;
        $this->ksJsonSerializer = $ksJsonSerializer;
        $this->ksDataHelper = $ksDataHelper;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     * Save action
     */
    public function execute()
    {
        // get form data
        $ksPostData = $this->getRequest()->getPostValue();

        // check information for downloadable product
        if (array_key_exists('downloadable', $ksPostData)) {
            // unserialize the file array of downloadable product
            foreach ($ksPostData['downloadable'] as $ksParentKey => $ksDownloadable) {
                foreach ($ksDownloadable as $ksChildKey => $ksValue) {
                    $ksFileData = $this->ksJsonSerializer->unserialize($ksValue['file']);
                    $ksPostData['downloadable'][$ksParentKey][$ksChildKey]['file'] = $ksFileData;
                    if (array_key_exists('sample', $ksValue)) {
                        $ksSampleFileData = $this->ksJsonSerializer->unserialize($ksValue['sample']['file']);
                        $ksPostData['downloadable'][$ksParentKey][$ksChildKey]['sample']['file'] = $ksSampleFileData;
                    }
                }
            }
            $this->getRequest()->setPostValue('downloadable', $ksPostData['downloadable']);
        }

        // check information for configurable product
        if (array_key_exists('configurable-matrix', $ksPostData)) {
            $ksPost = [];

            $ksPost['configurable-matrix-serialized'] = json_encode($ksPostData['configurable-matrix']);

            $ksPost['associated_product_ids_serialized'] = json_encode($ksPostData['associated_product_ids']);

            $this->getRequest()->setPostValue('configurable-matrix-serialized', $ksPost['configurable-matrix-serialized']);
            $this->getRequest()->setPostValue('associated_product_ids_serialized', $ksPost['associated_product_ids_serialized']);
        }

        $this->getRequest()->setPostValue('id', $ksPostData['ks_product_id']);

        $ksResultRedirect = $this->resultRedirectFactory->create();

        // get the url to redirect to seller list or pending approval seller list
        if (str_contains($this->_redirect->getRefererUrl(), 'multivendor/pricecomparison/pendingedit')) {
            $ksRedirectUrl = '*/*/productpendinglist';
        } else {
            $ksRedirectUrl = '*/*/';
        }

        // check data
        if ($ksPostData) {
            $ksProduct = $this->ksProductBuilder->build($this->getRequest());

            $ksProduct = $this->ksInitializationHelper->initialize($ksProduct);

            $this->ksProductTypeManager->processProduct($ksProduct);

            if (isset($ksPostData['product'][$ksProduct->getIdFieldName()])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The product was unable to be saved. Please try again.')
                );
            }

            $ksProduct->save();

            $productId = $ksPostData['ks_product_id'];

            $ksProductId = $ksProduct->getId();
            try {
                if ($ksProductId) {
                    try {
                        $ksSellerProductModel = $this->ksSellerProductFactory->create()->load($this->getRequest()->getParam('ks_id'));
                        $ksSellerProductModel->setKsProductStage($ksPostData['product']['ks_product_stage']);
                        if ($this->ksDataHelper->getKsConfigPriceComparisonSetting('ks_product_edit_approval_required')) {
                            $ksSellerProductModel->setKsProductApprovalStatus(3);
                        }
                        $ksSellerProductModel->save();
                        $this->messageManager->addSuccess(__('You saved the product details.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the details.'));
                return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
            } catch (LocalizedException $e) {
                $this->messageManager->addException($e, $e->getMessage());
                return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
            }

            $ksResultRedirect->setPath($ksRedirectUrl);
            return $ksResultRedirect;
        } else {
            $this->messageManager->addException($e, __('Something went wrong while saving the details.'));
            return $ksResultRedirect->setPath($this->_redirect->getRefererUrl());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultRedirectFactory->create();
        //for redirecting url
        $ksResultRedirect->setPath($ksRedirectUrl);
        return $ksResultRedirect;
    }

    /**
     * @param $ksRequest
     * @return \Magento\Catalog\Model\Product
     */
    public function build(RequestInterface $ksRequest)
    {
        $ksProductId = (int) $ksRequest->getParam('ks_product_id');
        $ksAttributeSetId = (int) $ksRequest->getParam('set');
        $ksTypeId = $ksRequest->getParam('type');
        $ksStoreId = $ksRequest->getParam('store');

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
}
