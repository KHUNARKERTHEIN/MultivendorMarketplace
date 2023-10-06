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

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute as AttributeAction;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Product attribute validate controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends AttributeAction implements HttpGetActionInterface, HttpPostActionInterface
{
    const DEFAULT_MESSAGE_KEY = 'message';
    private const RESERVED_ATTRIBUTE_CODES = ['product_type', 'type_id'];

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var array
     */
    private $multipleAttributeList;

    /**
     * @var FormData|null
     */
    private $formDataSerializer;

    /**
     * @var AttributeCodeValidator
     */
    private $attributeCodeValidator;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param FrontendInterface $attributeLabelCache
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param array $multipleAttributeList
     * @param FormData|null $formDataSerializer
     * @param AttributeCodeValidator|null $attributeCodeValidator
     * @param Escaper $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        FrontendInterface $attributeLabelCache,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        array $multipleAttributeList = [],
        FormData $formDataSerializer = null,
        AttributeCodeValidator $attributeCodeValidator = null,
        Escaper $escaper = null
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->multipleAttributeList = $multipleAttributeList;
        $this->formDataSerializer = $formDataSerializer ?: ObjectManager::getInstance()
            ->get(FormData::class);
        $this->attributeCodeValidator = $attributeCodeValidator ?: ObjectManager::getInstance()
            ->get(AttributeCodeValidator::class);
        $this->escaper = $escaper ?: ObjectManager::getInstance()
            ->get(Escaper::class);
    }

    /**
     * @inheritdoc
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $ksResponse = new DataObject();
        $ksResponse->setError(false);
        try {
            $ksOptionsData = $this->formDataSerializer
                ->unserialize($this->getRequest()->getParam('serialized_options', '[]'));
        } catch (\InvalidArgumentException $e) {
            $ksMessage = __(
                "The attribute couldn't be validated due to an error. Verify your information and try again. "
                . "If the error persists, please try again later."
            );
            $this->setMessageToResponse($ksResponse, [$ksMessage]);
            $ksResponse->setError(true);
        }

        $ksAttributeCode = $this->getRequest()->getParam('attribute_code');
        $ksFrontendLabel = $this->getRequest()->getParam('frontend_label');
        $ksAttributeId = $this->getRequest()->getParam('attribute_id');

        if ($ksAttributeId) {
            $ksAttributeId = $this->_objectManager->create(
                Attribute::class
            )->load($ksAttributeId);
            $ksAttributeCode = $ksAttributeId->getAttributeCode();
        } else {
            $ksAttributeCode = $ksAttributeCode ?: $this->generateCode($ksFrontendLabel[0]);
        }

        if (in_array($ksAttributeCode, self::RESERVED_ATTRIBUTE_CODES, true)) {
            $ksMessage = __('Code (%1) is a reserved key and cannot be used as attribute code.', $ksAttributeCode);
            $this->setMessageToResponse($ksResponse, [$ksMessage]);
            $ksResponse->setError(true);
        }

        if (!$this->attributeCodeValidator->isValid($ksAttributeCode)) {
            $this->setMessageToResponse($ksResponse, $this->attributeCodeValidator->getMessages());
            $ksResponse->setError(true);
        }

        if ($this->getRequest()->has('new_attribute_set_name')) {
            $ksSetName = $this->getRequest()->getParam('new_attribute_set_name');
            /** @var $attributeSet Set */
            $ksAttributeSet = $this->_objectManager->create(Set::class);
            $ksAttributeSet->setEntityTypeId($this->_entityTypeId)->load($ksSetName, 'attribute_set_name');
            if ($ksAttributeSet->getId()) {
                $ksSetName = $this->escaper->escapeHtml($ksSetName);
                $this->messageManager->addErrorMessage(__('An attribute set named \'%1\' already exists.', $ksSetName));

                $ksLayout = $this->layoutFactory->create();
                $ksLayout->initMessages();
                $ksResponse->setError(true);
                $ksResponse->setHtmlMessage($ksLayout->getMessagesBlock()->getGroupedHtml());
            }
        }

        $ksMultipleOption = $this->getRequest()->getParam("frontend_input");
        $ksMultipleOption = (null === $ksMultipleOption) ? 'select' : $ksMultipleOption;

        if (isset($this->multipleAttributeList[$ksMultipleOption])) {
            $ksOptions = $ksOptionsData[$this->multipleAttributeList[$ksMultipleOption]] ?? null;
            $this->checkUniqueOption(
                $ksResponse,
                $ksOptions
            );
            $ksValueOptions = (isset($ksOptions['value']) && is_array($ksOptions['value'])) ? $ksOptions['value'] : [];
            foreach (array_keys($ksValueOptions) as $ksKey) {
                if (!empty($ksOptions['delete'][$ksKey])) {
                    unset($ksValueOptions[$ksKey]);
                }
            }
            $this->checkEmptyOption($ksResponse, $ksValueOptions);
        }

        return $this->resultJsonFactory->create()->setJsonData($ksResponse->toJson());
    }

    /**
     * Throws Exception if not unique values into options.
     *
     * @param array $ksOptionValues
     * @param array $deletedOptions
     * @return bool
     */
    private function isUniqueAdminValues(array $ksOptionValues, array $ksDeletedOptions)
    {
        $ksAdminValues = [];
        foreach ($ksOptionValues as $ksOptionKey => $ksValues) {
            if (!(isset($ksDeletedOptions[$ksOptionKey]) && $ksDeletedOptions[$ksOptionKey] === '1')) {
                $ksAdminValues[] = reset($ksValues);
            }
        }
        $ksUniqueValues = array_unique($ksAdminValues);
        return array_diff_assoc($ksAdminValues, $ksUniqueValues);
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($ksResponse, $ksMessages)
    {
        $ksMessageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($ksMessageKey === static::DEFAULT_MESSAGE_KEY) {
            $ksMessages = reset($ksMessages);
        }
        return $ksResponse->setData($ksMessageKey, $ksMessages);
    }

    /**
     * Performs checking the uniqueness of the attribute options.
     *
     * @param DataObject $ksResponse
     * @param array|null $ksOptions
     * @return $this
     */
    private function checkUniqueOption(DataObject $ksResponse, array $ksOptions = null)
    {
        if (is_array($ksOptions)
            && isset($ksOptions['value'])
            && isset($ksOptions['delete'])
            && !empty($ksOptions['value'])
            && !empty($ksOptions['delete'])
        ) {
            $ksDuplicates = $this->isUniqueAdminValues($ksOptions['value'], $ksOptions['delete']);
            if (!empty($ksDuplicates)) {
                $this->setMessageToResponse(
                    $ksResponse,
                    [__('The value of Admin must be unique. (%1)', implode(', ', $ksDuplicates))]
                );
                $ksResponse->setError(true);
            }
        }
        return $this;
    }

    /**
     * Check that admin does not try to create option with empty admin scope option.
     *
     * @param DataObject $response
     * @param array $ksOptionsForCheck
     * @return void
     */
    private function checkEmptyOption(DataObject $ksResponse, array $ksOptionsForCheck = null)
    {
        foreach ($ksOptionsForCheck as $ksOptionValues) {
            if (isset($ksOptionValues[0]) && trim((string)$ksOptionValues[0]) == '') {
                $this->setMessageToResponse($ksResponse, [__("The value of Admin scope can't be empty.")]);
                $ksResponse->setError(true);
            }
        }
    }
}
