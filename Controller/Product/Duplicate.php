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

namespace Ksolves\MultivendorMarketplace\Controller\Product;

/**
 * Duplicate Controller class
 */
class Duplicate extends \Ksolves\MultivendorMarketplace\Controller\Product\Save
{
    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();

        $ksResultRedirect = $this->resultRedirectFactory->create();
        $ksProductId = $this->getRequest()->getParam('product_id');

        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksProduct = $this->ksProductFactory->create()->load($ksProductId);
                $ksProduct->unsetData('quantity_and_stock_status');
                $ksNewProduct = $this->ksProductCopier->copy($ksProduct);

                $this->messageManager->addSuccessMessage(__('You duplicated the product.'));
                $ksResultRedirect->setPath(
                    'multivendor/product/edit',
                    ['id' => $ksNewProduct->getId()]
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->ksLogger->critical($e);
                $this->messageManager->addExceptionMessage($e);
                $ksResultRedirect->setPath('multivendor/product');
            } catch (\Exception $e) {
                $this->ksLogger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
                $ksRedirectBack = $ksProductId ? true : 'new';
                $ksResultRedirect->setPath('multivendor/product');
            }
        } else {
            $ksResultRedirect->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        return $ksResultRedirect;
    }
}
