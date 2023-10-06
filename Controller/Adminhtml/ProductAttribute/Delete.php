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

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete as CatalogDelete;

/**
 * Delete Controller Class (Override Class of Catalog Delete Class)
 */
class Delete extends CatalogDelete
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($ksId) {
            $ksModel = $this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);

            // entity type check
            $ksModel->load($ksId);
            if ($ksModel->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addErrorMessage(__('We can\'t delete the attribute.'));
                if (str_contains($this->_redirect->getRefererUrl(), 'seller_id')) {
                    $ksRedirect = 'multivendor/seller/edit/seller_id/'.$ksModel->getKsSellerId();
                } elseif ($ksModel->getKsSellerId()) {
                    $ksRedirect = 'multivendor/productattribute/custom';
                } else {
                    $ksRedirect = 'catalog/*/';
                }
                return $resultRedirect->setPath($ksRedirect);
            }

            try {
                if (str_contains($this->_redirect->getRefererUrl(), 'seller_id')) {
                    $ksRedirect = 'multivendor/seller/edit/seller_id/'.$ksModel->getKsSellerId();
                } elseif ($ksModel->getKsSellerId()) {
                    $ksRedirect = 'multivendor/productattribute/custom';
                } else {
                    $ksRedirect = 'catalog/*/';
                }
                $ksModel->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the product attribute.'));
                return $resultRedirect->setPath($ksRedirect);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    'catalog/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an attribute to delete.'));
        // Redirect Page
        return $resultRedirect->setPath('catalog/*/');
    }
}
