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

/**
 * Delete Controller Class of Set Form
 */
class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $ksAttributeSetRepository;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $ksAttributeSetRepository
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $ksAttributeSetRepository,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksAttributeSetRepository = $ksAttributeSetRepository;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // Checking Seller Login
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller) {
            // Getting Set Id
            $ksSetId = $this->getRequest()->getParam('id');
            // Creating Page
            $ksResultRedirect = $this->resultRedirectFactory->create();
            try {
                // Performing Delete Operation
                $this->ksAttributeSetRepository->deleteById($ksSetId);
                $this->messageManager->addSuccessMessage(__('The attribute set has been removed successfully.'));
                // Redirecting Page to the Listing Url
                $ksResultRedirect->setPath('multivendor/*/index');
            } catch (\Exception $e) {
                // If Any Error Occur then Redirect to the Same Url
                $this->messageManager->addErrorMessage(__('We can\'t delete this set right now.'));
                $ksResultRedirect->setUrl($this->_redirect->getRedirectUrl());
            }
            return $ksResultRedirect;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
