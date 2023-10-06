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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Magento\Framework\App\Cache\Manager;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsProductHelper;

/**
 * Save Controller Class
 */
class Save extends Action
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_ATTRIBUTE_REQUEST_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_request_email_template';
    public const XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_approval_email_template';

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $ksCacheManager;

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
     * @var ksPresentation
     */
    protected $ksPresentation;

    /**
     * @var FormData|null
     */
    protected $ksFormDataSerializer;

    /**
     * @var FormData|null
     */
    protected $ksDataHelper;

    /**
     * @var KsProductHelper
     */
    protected $ksSellerProductHelper;

    /**
     * @var DataPersistorInterface
     */
    protected $ksDataPersistor;

    /**
     * @var AttributeCollection
     */
    protected $ksAttributeCollectionFactory;

    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksHelper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     *  Constructor
     * @param Context $ksContext,
     * @param KsSellerHelper $ksSellerHelper,
     * @param BuildFactory $ksBuildFactory,
     * @param AttributeFactory $ksAttributeFactory,
     * @param ValidatorFactory $ksValidatorFactory,
     * @param CollectionFactory $ksGroupCollectionFactory
     * @param DataPersistorInterface $ksDataPersistor
     * @param FilterManager $ksFilterManager,
     * @param Product $ksProductHelper,
     * @param Manager $ksCacheManager,
     * @param KsDataHelper $ksDataHelper,
     * @param KsFavouriteSellerHelper $ksHelper,
     * @param KsEmailHelper $ksEmailHelper,
     * @param AttributeCollection $ksAttributeCollectionFactory
     * @param KsProductHelper $ksSellerProductHelper,
     * @param Presentation $ksPresentation = null,
     * @param FormData $ksFormDataSerializer = null
     */
    public function __construct(
        Context $ksContext,
        KsSellerHelper $ksSellerHelper,
        BuildFactory $ksBuildFactory,
        AttributeFactory $ksAttributeFactory,
        ValidatorFactory $ksValidatorFactory,
        CollectionFactory $ksGroupCollectionFactory,
        DataPersistorInterface $ksDataPersistor,
        FilterManager $ksFilterManager,
        Product $ksProductHelper,
        Manager $ksCacheManager,
        KsDataHelper $ksDataHelper,
        KsFavouriteSellerHelper $ksHelper,
        KsEmailHelper $ksEmailHelper,
        AttributeCollection $ksAttributeCollectionFactory,
        KsProductHelper $ksSellerProductHelper,
        Presentation $ksPresentation = null,
        FormData $ksFormDataSerializer = null
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksBuildFactory = $ksBuildFactory;
        $this->ksAttributeFactory = $ksAttributeFactory;
        $this->ksValidatorFactory = $ksValidatorFactory;
        $this->ksGroupCollectionFactory = $ksGroupCollectionFactory;
        $this->ksDataPersistor = $ksDataPersistor;
        $this->ksFilterManager = $ksFilterManager;
        $this->ksProductHelper = $ksProductHelper;
        $this->ksCacheManager = $ksCacheManager;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksHelper = $ksHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksAttributeCollectionFactory = $ksAttributeCollectionFactory;
        $this->ksSellerProductHelper = $ksSellerProductHelper;
        $this->ksPresentation = $ksPresentation ?: ObjectManager::getInstance()->get(Presentation::class);
        $this->ksFormDataSerializer = $ksFormDataSerializer ?: ObjectManager::getInstance()->get(FormData::class);
        parent::__construct($ksContext);
    }

    /**
     * Execute Action for Request Product Type
     */
    public function execute()
    {
        // Check Seller
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // Get Seller Id
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // Get Approval Status
        $ksApprovalAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;
        // Get Not Submitted Status
        $ksNotSubmmitedAttributeStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_NOT_SUBMITTED;
        // Get Pending update status
        $ksPendingUpdateStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING_UPDATE;
        // Get Pending update status
        $ksPendingNewStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_PENDING;
        // Get Pending New Status
        $ksRejectedStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        // Getting Entity Id
        $this->ksEntityTypeId = $this->_objectManager->create(
            \Magento\Eav\Model\Entity::class
        )->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();
        $ksModel = $this->_objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        )->setEntityTypeId(
            $this->ksEntityTypeId
        );
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksOptionData = $this->ksFormDataSerializer
                ->unserialize($this->getRequest()->getParam('serialized_options', '[]'));
            } catch (\InvalidArgumentException $e) {
                $ksMessage = __("The attribute couldn't be saved due to an error. Verify your information and try again. "
                    . "If the error persists, please try again later.");
                $this->messageManager->addErrorMessage($ksMessage);
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                //for redirecting url
                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            }

            // Get Post Data
            $ksData = $this->getRequest()->getPostValue();
            $ksData = array_replace_recursive(
                $ksData,
                $ksOptionData
            );
            if ($ksData) {
                $ksSetId = $this->getRequest()->getParam('set');
                $ksAttributeSet = null;
                if (!empty($ksData['new_attribute_set_name'])) {
                    $name = $this->ksFilterManager->stripTags($ksData['new_attribute_set_name']);
                    $name = trim($name);
                    try {
                        /** @var Set $ksAttributeSet */
                        $ksAttributeSet = $this->ksBuildFactory->create()
                        ->setEntityTypeId($this->ksEntityTypeId)
                        ->setSkeletonId($ksSetId)
                        ->setName($name)
                        ->getAttributeSet();
                    } catch (AlreadyExistsException $alreadyExists) {
                        $this->messageManager->addErrorMessage(__('An attribute set named \'%1\' already exists.', $name));
                        $this->_session->setAttributeData($ksData);
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        //for redirecting url
                        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                    } catch (LocalizedException $e) {
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                        $this->messageManager->addErrorMessage($e->getMessage());
                    } catch (\Exception $e) {
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
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
                }
                $ksAttributeCode = $ksModel && $ksModel->getId()
                ? $ksModel->getAttributeCode()
                : $this->getRequest()->getParam('attribute_code');
                if (!$ksAttributeCode) {
                    $ksFrontendLabel = $this->getRequest()->getParam('frontend_label')[0] ?? '';
                    $ksAttributeCode = $this->ksGenerateCode($ksFrontendLabel);
                }
                $ksData['attribute_code'] = $ksAttributeCode;

                if ($this->ksCheckAdminAttributeExist($ksData['attribute_code']) != 0) {
                    $ksData['attribute_code'] = $ksAttributeCode.'_'.$ksSellerId;
                }

                if ($this->ksCheckSellerAttributeExist($ksData['attribute_code'], $ksSellerId) != 0) {
                    $ksData['attribute_code'] = $ksAttributeCode.'_'.$ksSellerId;
                }

                //validate frontend_input
                if (isset($ksData['frontend_input'])) {
                    /** @var Validator $inputType */
                    $inputType = $this->ksValidatorFactory->create();
                    if (!$inputType->isValid($ksData['frontend_input'])) {
                        foreach ($inputType->getMessages() as $ksMessage) {
                            $this->messageManager->addErrorMessage($ksMessage);
                        }
                        $ksData['attribute_code'] = $ksData['attribute_code'];
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        //for redirecting url
                        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                    }
                }
                $ksData = $this->ksPresentation->convertPresentationDataToInputType($ksData);
                if ($ksAttributeId) {
                    if (!$ksModel->getId()) {
                        $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        //for redirecting url
                        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                    }
                    // entity type check
                    if ($ksModel->getEntityTypeId() != $this->ksEntityTypeId || array_key_exists('backend_model', $ksData)) {
                        $this->messageManager->addErrorMessage(__('We can\'t update the attribute.'));
                        $this->_session->setAttributeData($ksData);
                        $ksData['attribute_code'] = $ksData['attribute_code'];
                        $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        //for redirecting url
                        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
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
                $this->ksEntityTypeId = $this->_objectManager->create(
                    \Magento\Eav\Model\Entity::class
                )->setType(
                    \Magento\Catalog\Model\Product::ENTITY
                )->getTypeId();

                if (!$ksAttributeId) {
                    $ksModel->setEntityTypeId($this->ksEntityTypeId);
                    $ksModel->setIsUserDefined(1);
                }

                $ksGroupCode = $this->getRequest()->getParam('group');
                if ($ksSetId && $ksGroupCode) {
                    // For creating product attribute on product page we need specify attribute set and group
                    $ksAttributeSetId = $ksAttributeSet ? $ksAttributeSet->getId() : $ksSetId;
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
                        $this->ksCacheManager->clean($this->ksCacheManager->getAvailableTypes());
                    }
                    $ksModel->setAttributeSetId($ksAttributeSetId);
                    $ksModel->setAttributeGroupId($ksGroup->getId());
                }
                try {
                    $ksModel->setKsSellerId($ksSellerId);
                    // When save and Save and Continue Pressed
                    if (($this->getRequest()->getParam('save_and_continue_edit', false)) ||
                     ($this->getRequest()->getParam('save', false))) {
                        // check attribute id is present or not
                        if ($ksAttributeId) {
                            // Check is the state is not submmited
                            if ($ksModel->getKsAttributeApprovalStatus() == $ksApprovalAttributeStatus) {
                                // if equal to then update state as pending update
                                if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                    $ksModel->setKsAttributeRejectionReason("");
                                    $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                } else {
                                    $ksModel->setKsAttributeApprovalStatus($ksPendingUpdateStatus);
                                }
                            }
                            // If Id is not present then mark not submitted
                        } else {
                            $ksModel->setKsAttributeApprovalStatus($ksNotSubmmitedAttributeStatus);
                        }
                        // If save and submitted is pressed
                    } else {
                        // If attribute id is present
                        if ($ksAttributeId) {
                            // Check Attribute is not submitted
                            if ($ksModel->getKsAttributeApprovalStatus() == $ksNotSubmmitedAttributeStatus) {
                                if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                    $ksModel->setKsAttributeRejectionReason("");
                                    $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                } else {
                                    $ksModel->setKsAttributeApprovalStatus($ksPendingNewStatus);
                                }
                            } elseif (($ksModel->getKsAttributeApprovalStatus() == $ksApprovalAttributeStatus) || ($ksModel->getKsAttributeApprovalStatus() == $ksRejectedStatus)) {
                                // If the Attribute is approved or rejected
                                if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                    $ksModel->setKsAttributeRejectionReason("");
                                    $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                } elseif ($this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_attribute_status')) {
                                    if (!($this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_update_attribute_status'))) {
                                        $ksModel->setKsAttributeRejectionReason("");
                                        $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                    } else {
                                        if ($this->ksSellerProductHelper->ksUnassignAttributeFromAttributeSet($ksAttributeId)) {
                                            $ksModel->setKsAttributeApprovalStatus($ksPendingUpdateStatus);
                                        } else {
                                            $ksMessage = __("Do not edit the attribute. This attribute is used in configurable products.");
                                            $this->messageManager->addErrorMessage($ksMessage);
                                            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                            //for redirecting url
                                            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                                        }
                                    }
                                } else {
                                    if ($this->ksSellerProductHelper->ksUnassignAttributeFromAttributeSet($ksAttributeId)) {
                                        $ksModel->setKsAttributeApprovalStatus($ksPendingUpdateStatus);
                                    } else {
                                        $ksMessage = __("Do not edit the attribute. This attribute is used in configurable products.");
                                        $this->messageManager->addErrorMessage($ksMessage);
                                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                        //for redirecting url
                                        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                                    }
                                }
                            } else {
                                // Check Approval is on or off
                                if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                    $ksModel->setKsAttributeRejectionReason("");
                                    $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                } else {
                                    if ($ksModel->getKsAttributeApprovalStatus() != $ksPendingNewStatus) {
                                        if (!($this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_update_attribute_status'))) {
                                            $ksModel->setKsAttributeRejectionReason("");
                                            $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                                        } else {
                                            if ($this->ksSellerProductHelper->ksUnassignAttributeFromAttributeSet($ksAttributeId)) {
                                                $ksModel->setKsAttributeApprovalStatus($ksPendingUpdateStatus);
                                            } else {
                                                $ksMessage = __("Do not edit the attribute. This attribute is used in configurable products.");
                                                $this->messageManager->addErrorMessage($ksMessage);
                                                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                                                //for redirecting url
                                                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                                            }
                                        }
                                    }
                                }
                            }
                            // New Attribute Made
                        } else {
                            // Check Approval is on or off
                            if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                $ksModel->setKsAttributeRejectionReason("");
                                $ksModel->setKsAttributeApprovalStatus($ksApprovalAttributeStatus);
                            }
                        }
                    }
                    $ksModel->save();
                    if ($this->getRequest()->getParam('save_and_submit', false)) {
                        if ($ksAttributeId) {
                            if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                $this->ksApprovalMail($ksSellerId, $ksModel);
                            } elseif ($this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_attribute_status')) {
                                if (!($this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_admin_approval_update_attribute_status'))) {
                                    $this->ksApprovalMail($ksSellerId, $ksModel);
                                } else {
                                    $this->ksRequestMail($ksSellerId, $ksModel);
                                }
                            } else {
                                $this->ksRequestMail($ksSellerId, $ksModel);
                            }
                        } else {
                            if ($this->ksSellerHelper->getksProductAttributeAutoAppoval($ksSellerId)) {
                                $this->ksApprovalMail($ksSellerId, $ksModel);
                            } else {
                                $this->ksRequestMail($ksSellerId, $ksModel);
                            }
                        }
                    }
                    $this->ksCacheManager->clean($this->ksCacheManager->getAvailableTypes());
                    $this->ksDataPersistor->clear('ks_product_attribute');
                    $this->messageManager->addSuccessMessage(__('You saved the product attribute.'));

                    if (($this->getRequest()->getParam('save_and_continue_edit', false))) {
                        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        //for redirecting url
                        return $ksResultRedirect->setPath('multivendor/productattribute/edit', ['attribute_id' => $ksModel->getId()]);
                    }
                    $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    //for redirecting url
                    return $ksResultRedirect->setPath('multivendor/productattribute/custom');
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                    $this->ksDataPersistor->set('ks_product_attribute', $ksData);
                    $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    //for redirecting url
                    return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                }
            }
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setPath('multivendor/productattribute/custom');
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Generate code from label
     * @param string $ksLabel
     * @return string
     */
    protected function ksGenerateCode($ksLabel)
    {
        $ksCode = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->_objectManager->create(\Magento\Catalog\Model\Product\Url::class)->formatUrlKey($ksLabel)
            ),
            0,
            30
        );
    
        $ksValidatorAttrCode = new \Laminas\Validator\Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$ksValidatorAttrCode->isValid($ksCode)) {
            $ksCode = 'attr_' . ($ksCode ?: substr(hash("sha256", time()), 0, 8));
        }
        return $ksCode;
    }

    /**
     * Request Email
     * @param  int $ksSellerId
     * @param  collection $ksModel
     * @return void
     */
    protected function ksRequestMail($ksSellerId, $ksModel)
    {
        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_request_email_template', $this->ksDataHelper->getKsCurrentStoreView());
        if ($ksEmailDisable != 'disable') {
            //Get Receiver Info
            $ksAdminEmailOption = 'ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_admin_email_option';
            $ksAdminSecondaryEmail ='ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_admin_email';
            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
            $ksReceiverDetails = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);

            $ksSellerDetails = $this->ksHelper->getKsCustomerAccountInfo($ksSellerId);
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue('ks_marketplace_catalog/ks_product_attribute_settings/ks_product_attribute_sender_email', $this->ksDataHelper->getKsCurrentStoreView());
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            $ksTemplateVariable = [];
            $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
            $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
            $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsRequired());
            $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsUserDefined());
            $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsVisible());
            $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksModel->getIsGlobal());
            $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsSearchable());
            $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksModel->getIsFilterable());
            $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsComparable());
            $ksTemplateVariable['ksSellerName'] = ucwords($ksSellerDetails['name']);
            $this->ksEmailHelper->ksSendRequestProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_REQUEST_MAIL, $ksTemplateVariable, $ksReceiverDetails, $ksSenderInfo);
            $this->messageManager->addSuccessMessage(__("A product attribute request has been send successfully."));
        }
    }

    /**
     * Send Approval Mail
     * @param $ksSellerId
     * @param $ksModel
     * @return void
     */
    protected function ksApprovalMail($ksSellerId, $ksModel)
    {
        $ksEmailDisable = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_approval_email_template', $this->ksDataHelper->getKsCurrentStoreView());
        if ($ksEmailDisable != 'disable') {
            $ksSenderDetails = $this->ksDataHelper->getKsConfigSellerProductAttributeSetting('ks_product_attribute_sender_email', $this->ksDataHelper->getKsCurrentStoreView());
            $ksSellerDetails = $this->ksHelper->getKsCustomerAccountInfo($ksSellerId);
            $ksTemplateVariable = [];
            $ksTemplateVariable['ks-seller-name'] = $ksSellerDetails["name"];
            $ksTemplateVariable['ks_seller_email'] = $ksSellerDetails["email"];
            $ksTemplateVariable['ks-product-attribute-name'] = $ksModel->getFrontendLabel();
            $ksTemplateVariable['ks-attribute-code'] = $ksModel->getAttributeCode();
            $ksTemplateVariable['ks-required'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsRequired());
            $ksTemplateVariable['ks-system'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsUserDefined());
            $ksTemplateVariable['ks-visible'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsVisible());
            $ksTemplateVariable['ks-scope'] = $this->ksSellerHelper->getKsStoreValues($ksModel->getIsGlobal());
            $ksTemplateVariable['ks-searchable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsSearchable());
            $ksTemplateVariable['ks-use-layer-navigation'] = $this->ksSellerHelper->getKsFilterableValues($ksModel->getIsFilterable());
            $ksTemplateVariable['ks-comparable'] = $this->ksSellerHelper->getKsYesNoValue($ksModel->getIsComparable());
            $this->ksEmailHelper->ksSendProductAttributeMail(self::XML_PATH_PRODUCT_ATTRIBUTE_APPROVAL_MAIL, $ksTemplateVariable, $ksSenderDetails, $ksSellerDetails);
        }
    }

    /**
     * Check Attribute Exist in Admin Accound
     * @param  $ksAttributeCode
     * @return int
     */
    public function ksCheckAdminAttributeExist($ksAttributeCode)
    {
        return $this->ksAttributeCollectionFactory->create()->addFieldToFilter('attribute_code', $ksAttributeCode)->addFieldToFilter('ks_seller_id', 0)->getSize();
    }

    /**
     * Check Attribute Exist in Seller Account
     * @param  $ksAttributeCode
     * @param  $ksSellerId
     * @return int
     */
    public function ksCheckSellerAttributeExist($ksAttributeCode, $ksSellerId)
    {
        return $this->ksAttributeCollectionFactory->create()->addFieldToFilter('attribute_code', $ksAttributeCode)->addFieldToFilter('ks_seller_id', ['neq' => $ksSellerId])->getSize();
    }
}
