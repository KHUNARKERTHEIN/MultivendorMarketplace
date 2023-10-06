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

use Magento\Framework\Registry;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Action;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Edit Controller Class
 */
class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $ksAttributeSetRepository;

    /**
     * @var Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param Context $ksContext
     * @param Registry $ksCoreRegistry
     * @param PageFactory $ksResultPageFactory
     * @param KsSellerHelper $ksSellerHelper,
     * @param AttributeSetRepositoryInterface $ksAttributeSetRepository
     */
    public function __construct(
        Context $ksContext,
        Registry $ksCoreRegistry,
        PageFactory $ksResultPageFactory,
        KsSellerHelper $ksSellerHelper,
        AttributeSetRepositoryInterface $ksAttributeSetRepository = null
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksAttributeSetRepository = $ksAttributeSetRepository ?:
        ObjectManager::getInstance()->get(AttributeSetRepositoryInterface::class);
        parent::__construct($ksContext);
    }

    /**
     * Execute Controller
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // Check Seller
        if ($ksIsSeller) {
            // Get Attribute Set
            $ksAttributeSet = $this->ksAttributeSetRepository->get($this->getRequest()->getParam('id'));
            // Check Attribute
            if (!$ksAttributeSet->getId()) {
                return $this->resultRedirectFactory->create()->setPath('multivendor/*/index');
            }
            $this->ksCoreRegistry->register('ks_current_seller_login', $this->ksSellerHelper->getKsCustomerId());
            $this->ksCoreRegistry->register('current_attribute_set', $ksAttributeSet);

            /** @var Page $ksResultPage */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->prepend(__('Attribute Sets'));
            $ksResultPage->getConfig()->getTitle()->prepend(
                $ksAttributeSet->getId() ? $ksAttributeSet->getAttributeSetName() : __('New Set')
            );
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
