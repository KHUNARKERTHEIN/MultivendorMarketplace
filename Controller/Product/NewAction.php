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

use Magento\Catalog\Model\Product;

/**
 * New Controller class
 */
class NewAction extends \Ksolves\MultivendorMarketplace\Controller\Product\Edit
{
    /**
     * Seller Product page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksStoreId = $this->getRequest()->getParam('store', 0);
                if (!$ksStoreId) {
                    $ksStoreId = 0;
                }

                $ksAttributeSetId = (int) $this->getRequest()->getParam('set');
                $ksTypeId = $this->getRequest()->getParam('type');

                $ksProduct = $this->createEmptyProduct($ksTypeId, $ksAttributeSetId, $ksStoreId);
                $this->ksRegistry->register('product', $ksProduct);

                $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
                $ksProductCount=$this->ksSellerProductFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter('ks_seller_id', $ksSellerId)
                    ->addFieldToFilter('ks_parent_product_id', ['eq' => 0])
                    ->getSize();

                if ($this->ksDataHelper->getKsConfigProductSetting('ks_seller_product_limit') != null) {
                    // check for product limit
                    if ($ksProductCount >= $this->ksDataHelper->getKsConfigProductSetting('ks_seller_product_limit')) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('Reached product limit.')
                        );
                    }
                }

                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultPage = $this->ksResultPageFactory->create();
                $ksResultPage->getConfig()->getTitle()->set(__('New Product'));
                return $ksResultPage;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * @param int $ksTypeId
     * @param int $attributeSetId
     * @param int $ksStoreId
     * @return \Magento\Catalog\Model\Product
     */
    private function createEmptyProduct($ksTypeId, $ksAttributeSetId, $ksStoreId): Product
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $ksProduct = $this->ksProductFactory->create();
        $ksProduct->setData('_edit_mode', true);

        if ($ksTypeId !== null) {
            $ksProduct->setTypeId($ksTypeId);
        }

        if ($ksStoreId !== null) {
            $ksProduct->setStoreId($ksStoreId);
        }

        if ($ksAttributeSetId) {
            $ksProduct->setAttributeSetId($ksAttributeSetId);
        }

        return $ksProduct;
    }
}
