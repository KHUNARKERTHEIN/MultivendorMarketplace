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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Product attribute save controller.
 * Save Controller Class
 */
class Save extends Attribute implements HttpPostActionInterface
{
    /**
     * @var ksBuildFactory
     */
    protected $ksBuildFactory;

    /**
     * @var ksFilterManager
     */
    protected $ksFilterManager;

    /**
     * @var Product
     */
    protected $ksProductHelper;

    /**
     * @var ksAttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * @var ksValidatorFactory
     */
    protected $ksValidatorFactory;

    /**
     * @var CollectionFactory
     */
    protected $ksGroupCollectionFactory;

    /**
     * @var ksLayoutFactory
     */
    private $ksLayoutFactory;

    /**
     * @var ksPresentation
     */
    private $ksPresentation;

    /**
     * @var FormData|null
     */
    private $ksFormDataSerializer;

    /**
     * @var AttributeCollection
     */
    protected $ksAttributeCollectionFactory;

    /**
     * @param Context $context
     * @param FrontendInterface $attributeLabelCache
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ksBuildFactory $ksBuildFactory
     * @param ksAttributeFactory $ksAttributeFactory
     * @param ksValidatorFactory $ksValidatorFactory
     * @param CollectionFactory $ksGroupCollectionFactory
     * @param AttributeCollection $ksAttributeCollectionFactory
     * @param ksFilterManager $ksFilterManager
     * @param Product $ksProductHelper
     * @param ksLayoutFactory $ksLayoutFactory
     * @param ksPresentation|null $ksPresentation
     * @param FormData|null $ksFormDataSerializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        FrontendInterface $attributeLabelCache,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        BuildFactory $ksBuildFactory,
        AttributeFactory $ksAttributeFactory,
        ValidatorFactory $ksValidatorFactory,
        CollectionFactory $ksGroupCollectionFactory,
        AttributeCollection $ksAttributeCollectionFactory,
        FilterManager $ksFilterManager,
        Product $ksProductHelper,
        LayoutFactory $ksLayoutFactory,
        Presentation $ksPresentation = null,
        FormData $ksFormDataSerializer = null
    ) {
        $this->ksBuildFactory = $ksBuildFactory;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksValidatorFactory = $ksValidatorFactory;
        $this->ksGroupCollectionFactory = $ksGroupCollectionFactory;
        $this->ksAttributeCollectionFactory = $ksAttributeCollectionFactory;
        $this->ksFilterManager = $ksFilterManager;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksLayoutFactory = $ksLayoutFactory;
        $this->ksPresentation = $ksPresentation ?: ObjectManager::getInstance()->get(Presentation::class);
        $this->ksFormDataSerializer = $ksFormDataSerializer
               ?: ObjectManager::getInstance()->get(FormData::class);
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
    }

    /**
     * @return Redirect,
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        try {
            $ksOptionData = $this->ksFormDataSerializer
                ->unserialize($this->getRequest()->getParam('serialized_options', '[]'));
        } catch (\InvalidArgumentException $e) {
            $ksMessage = __("The attribute couldn't be saved due to an error. Verify your information and try again. "
                . "If the error persists, please try again later.");
            $this->messageManager->addErrorMessage($ksMessage);
            return $this->returnResult('catalog/*/edit', ['_current' => true], ['error' => true]);
        }

        $ksData = $this->getRequest()->getPostValue();
        $ksData = array_replace_recursive(
            $ksData,
            $ksOptionData
        );
        $ksRedirect = 'catalog/*/';

        if ($ksData) {
            $ksSet = $this->getRequest()->getParam('set');

            $ksAttributeSet = null;
            if (!empty($ksData['new_attribute_set_name'])) {
                $ksName = $this->ksFilterManager->stripTags($ksData['new_attribute_set_name']);
                $ksName = trim($ksName);

                try {
                    /** @var Set $attributeSet */
                    $ksAttributeSet = $this->ksBuildFactory->create()
                        ->setEntityTypeId($this->_entityTypeId)
                        ->setSkeletonId($ksSet)
                        ->setName($ksName)
                        ->getAttributeSet();
                } catch (AlreadyExistsException $alreadyExists) {
                    $this->messageManager->addErrorMessage(__('An attribute set named \'%1\' already exists.', $ksName));
                    $this->_session->setAttributeData($ksData);
                    return $this->returnResult('catalog/*/edit', ['_current' => true], ['error' => true]);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the attribute.')
                    );
                }
            }

            $ksAttributeId = $this->getRequest()->getParam('attribute_id');

            /** @var ProductAttributeInterface $model */
            $ksModel = $this->ksAttributeFactory->create();
            if ($ksAttributeId) {
                $ksModel->load($ksAttributeId);
                if ($ksModel->getKsSellerId()) {
                    if (str_contains($this->_redirect->getRefererUrl(), 'seller_id')) {
                        $ksRedirect = 'multivendor/seller/edit/seller_id/'.$ksModel->getKsSellerId();
                    } else {
                        $ksRedirect = 'multivendor/productattribute/custom';
                    }
                }
            }
            $ksAttributeCode = $ksModel && $ksModel->getId()
                ? $ksModel->getAttributeCode()
                : $this->getRequest()->getParam('attribute_code');
            if (!$ksAttributeCode) {
                $ksFrontendLabel = $this->getRequest()->getParam('frontend_label')[0] ?? '';
                $ksAttributeCode = $this->generateCode($ksFrontendLabel);
            }

            $ksData['attribute_code'] = $ksAttributeCode;

            if ($this->ksCheckAdminAttributeExist($ksAttributeCode) != 0) {
                $ksData['attribute_code'] = $ksAttributeCode.'_0';
            }
            //validate frontend_input
            if (isset($ksData['frontend_input'])) {
                /** @var Validator $inputType */
                $inputType = $this->ksValidatorFactory->create();
                if (!$inputType->isValid($ksData['frontend_input'])) {
                    foreach ($inputType->getMessages() as $message) {
                        $this->messageManager->addErrorMessage($message);
                    }
                    return $this->returnResult(
                        'catalog/*/edit',
                        ['attribute_id' => $ksAttributeId, '_current' => true],
                        ['error' => true]
                    );
                }
            }

            $ksData = $this->ksPresentation->convertPresentationDataToInputType($ksData);

            if ($ksAttributeId) {
                if (!$ksModel->getId()) {
                    $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                    return $this->returnResult($ksRedirect, [], ['error' => true]);
                }
                // entity type check
                if ($ksModel->getEntityTypeId() != $this->_entityTypeId || array_key_exists('backend_model', $ksData)) {
                    $this->messageManager->addErrorMessage(__('We can\'t update the attribute.'));
                    $this->_session->setAttributeData($ksData);
                    return $this->returnResult($ksRedirect, [], ['error' => true]);
                }

                $ksData['attribute_code'] = $ksModel->getAttributeCode();
                $ksData['is_user_defined'] = $ksModel->getIsUserDefined();
                $ksData['frontend_input'] = $ksData['frontend_input'] ?? $ksModel->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                $ksData['source_model'] = $this->ksProductHelper->getAttributeSourceModelByInputType(
                    $ksData['frontend_input']
                );
                $ksData['backend_model'] = $this->ksProductHelper->getAttributeBackendModelByInputType(
                    $ksData['frontend_input']
                );

                if ($ksModel->getIsUserDefined() === null) {
                    $ksData['backend_type'] = $ksModel->getBackendTypeByInput($ksData['frontend_input']);
                }
            }

            $ksData += ['is_filterable' => 0, 'is_filterable_in_search' => 0];

            $defaultValueField = $ksModel->getDefaultValueByInput($ksData['frontend_input']);
            if ($defaultValueField) {
                $ksData['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$ksModel->getIsUserDefined() && $ksModel->getId()) {
                // Unset attribute field for system attributes
                unset($ksData['apply_to']);
            }

            if ($ksModel->getBackendType() == 'static' && !$ksModel->getIsUserDefined()) {
                $ksData['frontend_class'] = $ksModel->getFrontendClass();
            }

            unset($ksData['entity_type_id']);

            $ksModel->addData($ksData);

            if (!$ksAttributeId) {
                $ksModel->setEntityTypeId($this->_entityTypeId);
                $ksModel->setIsUserDefined(1);
            }

            $ksGroupCode = $this->getRequest()->getParam('group');
            if ($ksSet && $ksGroupCode) {
                // For creating product attribute on product page we need specify attribute set and group
                $ksAttributeSetId = $ksAttributeSet ? $ksAttributeSet->getId() : $ksSet;
                $ksGroupCollection = $this->ksGroupCollectionFactory->create()
                    ->setAttributeSetFilter($ksAttributeSetId)
                    ->addFieldToFilter('attribute_group_code', $ksGroupCode)
                    ->setPageSize(1)
                    ->load();

                $ksGroup = $ksGroupCollection->getFirstItem();
                if (!$ksGroup->getId()) {
                    $ksGroup->setAttributeGroupCode($ksGroupCode);
                    $ksGroup->setSortOrder($this->getRequest()->getParam('groupSortOrder'));
                    $ksGroup->setAttributeGroupName($this->getRequest()->getParam('groupName'));
                    $ksGroup->setAttributeSetId($ksAttributeSetId);
                    $ksGroup->save();
                }

                $ksModel->setAttributeSetId($ksAttributeSetId);
                $ksModel->setAttributeGroupId($ksGroup->getId());
            }

            try {
                $ksModel->save();
                $this->messageManager->addSuccessMessage(__('You saved the product attribute.'));

                $this->_attributeLabelCache->clean();
                $this->_session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = [
                        'attributeId' => $this->getRequest()->getParam('product'),
                        'attribute' => $ksModel->getId(),
                        '_current' => true,
                        'product_tab' => $this->getRequest()->getParam('product_tab'),
                    ];
                    if ($ksAttributeSet !== null) {
                        $requestParams['new_attribute_set_id'] = $ksAttributeSet->getId();
                    }
                    return $this->returnResult('catalog/product/addAttribute', $requestParams, ['error' => false]);
                } elseif ($this->getRequest()->getParam('back', false)) {
                    return $this->returnResult(
                        'catalog/*/edit',
                        ['attribute_id' => $ksModel->getId(), '_current' => true],
                        ['error' => false]
                    );
                }
                return $this->returnResult($ksRedirect, [], ['error' => false]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setAttributeData($ksData);
                return $this->returnResult(
                    'catalog/*/edit',
                    ['attribute_id' => $ksAttributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return $this->returnResult($ksRedirect, [], ['error' => true]);
    }

    /**
     * Provides an initialized Result object.
     *
     * @param string $ksPath
     * @param array $ksParams
     * @param array $ksResponse
     * @return Json|Redirect
     */
    private function returnResult($ksPath = '', array $ksParams = [], array $ksResponse = [])
    {
        if ($this->isAjax()) {
            $ksLayout = $this->ksLayoutFactory->create();
            $ksLayout->initMessages();

            $ksResponse['messages'] = [$ksLayout->getMessagesBlock()->getGroupedHtml()];
            $ksResponse['params'] = $ksParams;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($ksResponse);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($ksPath, $ksParams);
    }

    /**
     * Define whether request is Ajax
     *
     * @return boolean
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * Check Attribute Exist in Admin Accound
     * @param  $ksAttributeCode
     * @param  $ksSellerId
     * @return int
     */
    public function ksCheckAdminAttributeExist($ksAttributeCode)
    {
        return $this->ksAttributeCollectionFactory->create()->addFieldToFilter('attribute_code', $ksAttributeCode)->addFieldToFilter('ks_seller_id', ['neq' => 0 ])->getSize();
    }
}
