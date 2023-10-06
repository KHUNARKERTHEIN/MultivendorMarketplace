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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute\Set;

use Magento\Framework\App\ObjectManager;

/**
 * Save Controller Class of Attribute Set
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /*
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $ksAttributeSetFactory;

    /*
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $ksResultJsonFactory;

    /*
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $ksLayoutFactory;
    
    /*
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $ksFilterManager;
    
    /*
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $ksJsonHelper;

    /*
     * @var \Magento\Framework\UrlInterface
     */
    protected $ksUrlBuilder;

    /*
     * @var \Magento\Framework\App\Cache\Manager
     */
    protected $ksCacheManager;
    
    /**
     *  \Magento\Framework\App\Action\Context $context,
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
     * @param \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
     * @param \Magento\Framework\View\LayoutFactory $ksLayoutFactory,
     * @param \Magento\Framework\UrlInterface $ksUrlBuilder,
     * @param \Magento\Framework\App\Cache\Manager $ksCacheManager,
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $ksAttributeSetFactory = null,
     * @param \Magento\Framework\Filter\FilterManager $ksFilterManager = null,
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper = null
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Framework\Controller\Result\JsonFactory $ksResultJsonFactory,
        \Magento\Framework\View\LayoutFactory $ksLayoutFactory,
        \Magento\Framework\UrlInterface $ksUrlBuilder,
        \Magento\Framework\App\Cache\Manager $ksCacheManager,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $ksAttributeSetFactory = null,
        \Magento\Framework\Filter\FilterManager $ksFilterManager = null,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper = null
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksResultJsonFactory = $ksResultJsonFactory;
        $this->ksLayoutFactory = $ksLayoutFactory;
        $this->ksUrlBuilder = $ksUrlBuilder;
        $this->ksCacheManager = $ksCacheManager;
        $this->ksAttributeSetFactory =  $ksAttributeSetFactory ?: ObjectManager::getInstance()
        ->get(\Magento\Eav\Model\Entity\Attribute\SetFactory::class);
        $this->ksFilterManager =  $ksFilterManager ?: ObjectManager::getInstance()
        ->get(\Magento\Framework\Filter\FilterManager::class);
        $this->ksJsonHelper =  $ksJsonHelper ?: ObjectManager::getInstance()
        ->get(\Magento\Framework\Json\Helper\Data::class);
        parent::__construct($context);
    }

    /**
     * Save attribute set action
     *
     * Create attribute set from another set and redirect to edit page
     * Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            $ksEntityTypeId = 4;
            $ksHasError = false;
            $ksAttributeSetId = $this->getRequest()->getParam('id', false);
            $ksIsNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';
            /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
            $ksModel = $this->ksAttributeSetFactory->create()->setEntityTypeId($ksEntityTypeId);

            try {
                if ($ksIsNewSet) {
                    //filter html tags
                    $ksName = $this->ksFilterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                    $ksModel->setAttributeSetName(trim($ksName));
                    $ksModel->setKsSellerId($this->ksSellerHelper->getKsCustomerId());
                } else {
                    if ($ksAttributeSetId) {
                        $ksModel->load($ksAttributeSetId);
                    }
                    if (!$ksModel->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('This attribute set no longer exists.')
                        );
                    }
                    $ksData = $this->ksJsonHelper->jsonDecode($this->getRequest()->getPost('data'));

                    //filter html tags
                    $ksData['attribute_set_name'] = $this->ksFilterManager->stripTags($ksData['attribute_set_name']);
                    $ksName = $ksData['attribute_set_name'];
                    $ksModel->organizeData($ksData);
                }

                // $ksModel->validate();
                if ($ksIsNewSet) {
                    $ksModel->save();
                    $ksModel->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
                }
                $ksModel->save();
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
                    $ksResultRedirect = $this->resultRedirectFactory->create();
                    if ($ksHasError) {
                        $ksResultRedirect->setPath('multivendor/productattribute_set/new');
                    } else {
                        $ksResultRedirect->setPath('multivendor/productattribute_set/edit', ['id' => $ksModel->getId()]);
                    }
                    return $ksResultRedirect;
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
                    $ksResponse['url'] = $this->ksUrlBuilder->getUrl('multivendor/productattribute_set/index');
                }
                $this->ksCacheManager->clean($this->ksCacheManager->getAvailableTypes());
                return $this->ksResultJsonFactory->create()->setData($ksResponse);
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
