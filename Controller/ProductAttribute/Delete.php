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

use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * Delete Controller Class
 */
class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $ksAttributeFactory;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param ksSellerHelper $ksSellerHelper,
     * @param AttributeFactory $ksAttributeFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsSellerHelper $ksSellerHelper,
        AttributeFactory $ksAttributeFactory
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksAttributeFactory = $ksAttributeFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // get Seller Id
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // Check the Seller
        if ($ksIsSeller == 1) {
            try {
                // Get Attribute Code
                $ksAttributeId = $this->getRequest()->getParam('attribute_id');
                // Get the Id Enitity Id
                $this->ksEntityTypeId = $this->_objectManager->create(
                    \Magento\Eav\Model\Entity::class
                )->setType(
                    \Magento\Catalog\Model\Product::ENTITY
                )->getTypeId();
                // Create Page Factory
                $ksResultRedirect = $this->resultRedirectFactory->create();
                if ($ksAttributeId) {
                    // Create the Attribute Factory
                    $ksModel = $this->ksAttributeFactory->create();
                    // Load Model
                    $ksModel->load($ksAttributeId);
                    if ($ksModel->getEntityTypeId() != $this->ksEntityTypeId) {
                        $this->messageManager->addErrorMessage(__('We can\'t delete the attribute.'));
                        return $ksResultRedirect->setPath('multivendor/*/custom');
                    }
                    try {
                        $ksModel->delete();
                        $this->messageManager->addSuccessMessage(__('You deleted the product attribute.'));
                        return $ksResultRedirect->setPath('multivendor/*/custom');
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        return $ksResultRedirect->setPath(
                            'multivendor/*/edit',
                            ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                        );
                    }
                }
                $this->messageManager->addErrorMessage(__('We can\'t find an attribute to delete.'));
                return $ksResultRedirect->setPath('multivendor/*/custom');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
