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

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Edit Controller Class
 */
class Edit extends \Ksolves\MultivendorMarketplace\Controller\ProductAttribute\Attribute implements HttpGetActionInterface
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksId = $this->getRequest()->getParam('attribute_id');
                /** @var $ksModel \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
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
                if ($ksId) {
                    $ksModel->load($ksId);
                    if (!$ksModel->getId()) {
                        $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                        $ksResultRedirect = $this->ksResultRedirectFactory->create();
                        return $ksResultRedirect->setPath('multivendormarketplace/productattribute/');
                    }
                    // entity type check
                    if ($ksModel->getEntityTypeId() != $this->ksEntityTypeId) {
                        $this->messageManager->addErrorMessage(__('This attribute cannot be edited.'));
                        $ksResultRedirect = $this->ksResultRedirectFactory->create();
                        return $ksResultRedirect->setPath('multivendormarketplace/productattribute/');
                    }
                }
                // set entered data if was error when we do save
                $ksData = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getAttributeData(true);
                $ksPresentation = $this->_objectManager->get(
                    \Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation::class
                );
                if (!empty($ksData)) {
                    $ksModel->addData($ksData);
                }
                $ksModel->setFrontendInput($ksPresentation->getPresentationInputType($ksModel));
                $ksAttributeData = $this->getRequest()->getParam('attribute');
                if (!empty($ksAttributeData) && $ksId === null) {
                    $ksModel->addData($ksAttributeData);
                }

                $this->ksCoreRegistry->register('entity_attribute', $ksModel);
                $this->ksCoreRegistry->register('ks_seller_login_id', $this->ksSellerHelper->getKsCustomerId());
                $ksItem = $ksId ? __('Edit Product Attribute') : __('New Product Attribute');
                $ksResultPage = $this->createActionPage($ksItem);
                $ksResultPage->getConfig()->getTitle()->prepend($ksId ? $ksModel->getName() : __('New Product Attribute'));
                return $ksResultPage;
            } catch (\Exception $e) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                //for redirecting url
                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
