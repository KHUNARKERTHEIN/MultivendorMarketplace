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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;

/**
 * MassApprove Controller Class
 */
class MassApprove extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
{
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksCount = $ksFlag = $ksEmailErrorCount = 0;
        $ksEmailErrorMessage = "";
        $ksProductIds = [];
        //get product approval status
        $ksProductStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_APPROVED;

        try {
            //get data
            $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
            $ksMassSellerIds = [];
            $ksStoreId = 0;
            $ksIds = [];
            foreach ($ksCollection as $ksItem) {
                $ksProductIds[] = $ksItem->getEntityId();
                $ksIds[] = $ksItem->getId();
                $ksData = $this->ksProductCollection->create()->load($ksItem->getEntityId(), 'ks_product_id');

                $ksSellerId = $ksData->getKsSellerId();
                $ksMassSellerIds[] = $ksSellerId;

                $ksProductData = $this->ksProductRepository->getById(
                    $ksItem->getEntityId(),
                    true,
                    $this->getRequest()->getParam('store', 0)
                );
                $ksStoreId = $ksProductData->getStoreId();

                if ($ksData) {
                    if ($ksData->getKsProductApprovalStatus() != $ksProductStatus) {
                        $ksData->setKsProductApprovalStatus($ksProductStatus);
                        $ksData->setKsEditMode(1);
                        $ksData->setKsRejectionReason("");
                        $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                        $ksData->save();
                        $ksCount++;
                    } else {
                        $ksFlag++;
                    }
                }
            }

            //Mail sent to seller when admin Mass approve the products
            $ksSellerIds = array_unique($ksMassSellerIds);
            foreach ($ksSellerIds as $ksSellerId) {
                $ksProductCollection = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_product_id', ['in' => $ksIds])->addFieldToFilter('ks_seller_id', $ksSellerId);

                $ksMassProIds = [];
                foreach ($ksProductCollection as $ksCollection) {
                    $ksMassProIds[] = $ksCollection->getKsProductId();
                }
                $this->ksEmailHelper->ksSendEmailProductApprove($ksSellerId, $ksMassProIds);
            }

            //reindex data to filter approve seller product
            $this->ksFullTextProcessor->reindexList($ksProductIds);
            $this->ksProductCategoryProcessor->reindexList($ksProductIds);
            $this->ksPriceIndexerProcessor->reindexList($ksProductIds);

            if ($ksCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 product(s) has been approved successfully.', $ksCount)
                );
            }
            if ($ksFlag) {
                $this->messageManager->addErrorMessage(
                    __('A total of %1 product(s) has already approved.', $ksFlag)
                );
            }
            if ($ksEmailErrorCount) {
                $this->messageManager->addErrorMessage(
                    __($ksEmailErrorMessage)
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
