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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ProductAttribute\Set;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Override Save Controller of Attribute Set
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Set implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $ksLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /*
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    private $ksAttributeFactory;

    /*
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $ksAttributeSetFactory;

    /*
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $ksFilterManager;

    /*
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $ksJsonHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Framework\View\LayoutFactory $ksLayoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $ksAttributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $ksAttributeSetFactory
     * @param \Magento\Framework\Filter\ksFilterManager $ksFilterManager
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\Registry $ksCoreRegistry,
        \Magento\Framework\View\LayoutFactory $ksLayoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $ksAttributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $ksAttributeSetFactory = null,
        \Magento\Framework\Filter\FilterManager $ksFilterManager = null,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper = null
    ) {
        $this->ksLayoutFactory = $ksLayoutFactory;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksAttributeFactory =  $ksAttributeFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory::class);
        $this->ksAttributeSetFactory =  $ksAttributeSetFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Eav\Model\Entity\Attribute\SetFactory::class);
        $this->ksFilterManager =  $ksFilterManager ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Filter\FilterManager::class);
        $this->ksJsonHelper =  $ksJsonHelper ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Json\Helper\Data::class);
        parent::__construct($ksContext, $ksCoreRegistry);
    }

    /**
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if ($this->_coreRegistry->registry('entityType') === null) {
            $this->_setTypeId();
        }
        return $this->_coreRegistry->registry('entityType');
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $ksEntityTypeId = $this->_getEntityTypeId();
        $ksHasError = false;
        $ksAttributeSetId = $this->getRequest()->getParam('id', false);
        $ksIsNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';
        $ksSellerId = 0;

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $ksModel = $this->ksAttributeSetFactory->create()->setEntityTypeId($ksEntityTypeId);

        try {
            if ($ksIsNewSet) {
                //filter html tags
                $ksName = $this->ksFilterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $ksSellerId = (int)$this->getRequest()->getParam('seller_id', false);
                $ksModel->setAttributeSetName(trim($ksName));
            } else {
                if ($ksAttributeSetId) {
                    $ksModel->load($ksAttributeSetId);
                }
                if (!$ksModel->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This attribute set no longer exists.')
                    );
                }
                $data = $this->ksJsonHelper->jsonDecode($this->getRequest()->getPost('data'));

                //filter html tags
                $data['attribute_set_name'] = $this->ksFilterManager->stripTags($data['attribute_set_name']);
                $ksName = $data['attribute_set_name'];
                $ksModel->organizeData($data);
            }

            //$ksModel->validate();
            if ($ksIsNewSet) {
                $ksModel->setKsSellerId($ksSellerId);
                $ksModel->save();
                $ksModel->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $ksModel->save();
            // If Id exist then enable the attributes
            if ($ksAttributeSetId) {
                $this->ksEnableAttributes($data, $ksAttributeSetId);
            }
            $this->messageManager->addSuccessMessage(__('You saved the attribute set.'));
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $ksMessage = 'A "'.$ksName.'" attribute set name already exists. Create a new name and try again.';
            $this->messageManager->addErrorMessage($ksMessage);
            $ksHasError = true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $ksHasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attribute set.'));
            $ksHasError = true;
        }

        if ($ksIsNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $ksBlock = $this->ksLayoutFactory->create()->getMessagesBlock();
                $ksBlock->setMessages($this->messageManager->getMessages(true));
                $ksBody = [
                    'messages' => $ksBlock->getGroupedHtml(),
                    'error' => $ksHasError,
                    'id' => $ksModel->getId(),
                ];
                return $this->ksResultJsonFactory->create()->setData($ksBody);
            } else {
                $resultRedirect = $this->resultRedirectFactory->create();
                if ($ksHasError) {
                    $resultRedirect->setPath('catalog/*/add');
                } else {
                    $resultRedirect->setPath('catalog/*/edit', ['id' => $ksModel->getId()]);
                }
                return $resultRedirect;
            }
        } else {
            $ksResponse = [];
            if ($ksHasError) {
                $ksLayout = $this->ksLayoutFactory->create();
                $ksLayout->initMessages();
                $ksResponse['error'] = 1;
                $ksResponse['message'] = $ksLayout->getMessagesBlock()->getGroupedHtml();
            } else {
                $ksResponse['error'] = 0;
                $ksResponse['url'] = $this->getUrl('catalog/*/');
            }
            return $this->ksResultJsonFactory->create()->setData($ksResponse);
        }
    }

    /**
     * Enable the Attributes
     * @param $ksData
     * @return void
     */
    public function ksEnableAttributes($ksData, $ksSetId)
    {
        // Get the Default Sets
        $ksDefaultSets = $this->ksDataHelper->getKsDefaultAttributes();
        $ksAttributeStatus = \Ksolves\MultivendorMarketplace\Model\Source\Seller\KsEnabledDisabled::ENABLE_VALUE;
        // Check set is present in default set list
        if (in_array($ksSetId, $ksDefaultSets)) {
            foreach ($ksData['attributes'] as $ksIndex) {
                foreach ($ksIndex as $ksAttribute) {
                    $ksModel = $this->ksAttributeFactory->create()->load($ksAttribute);
                    if ($ksModel->getSize()) {
                        $ksModel->setKsIncludeInMarketplace($ksAttributeStatus);
                        $ksModel->save();
                    }
                }
            }
        }
    }
}
