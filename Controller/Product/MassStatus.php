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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Model\KsProduct;

/**
 * Class MassStatus
 * @package Ksolves\MultivendorMarketplace\Controller\Product
 */
class MassStatus extends \Ksolves\MultivendorMarketplace\Controller\Product\MassDelete
{
    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $ksProductIds, $ksStatus)
    {
        if ($ksStatus == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)->isProductsHasSku($ksProductIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }

    /**
     * Check Store Status
     * Execute Action
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            //for check Store Status
            if ($this->getRequest()->getParam('ks_status') == 1) {
                $ksStoreStatus = 'enabled';
            } else {
                $ksStoreStatus = 'disabled';
            }

            //get data
            $ksProductCollection = $this->ksFilter->getCollection($this->ksProductFactory->create()->getCollection());
            $ksProductIds = $ksProductCollection->getAllIds();

            $ksRequestStoreId = $ksStoreId = $this->getRequest()->getParam('store', null);
            $ksFilterRequest = $this->getRequest()->getParam('filters', null);
            $ksStatus = (int) $this->getRequest()->getParam('ks_status');

            if (null === $ksStoreId && null !== $ksFilterRequest) {
                $ksStoreId = (isset($ksFilterRequest['store_id'])) ? (int) $ksFilterRequest['store_id'] : 0;
            }

            try {
                //filter productids by not allowed type or rejected product
                $ksProductIds = $this->ksProductTypeHelper->KsRestrictEnableProductByNotAllowedType($ksProductIds, $ksStatus);


                if (!empty($ksProductIds)) {
                    $this->_validateMassStatus($ksProductIds, $ksStatus);
                    $this->ksProductAction->updateAttributes($ksProductIds, ['status' => $ksStatus], (int) $ksStoreId);
                    $this->ksPriceIndexerProcessor->reindexList($ksProductIds);

                    $this->messageManager->addSuccessMessage(
                        __('A total of %1 product status(s) has been %2 successfully.', count($ksProductIds), $ksStoreStatus)
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while updating the product(s) status.')
                );
            }

            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            //for redirecting url
            return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
