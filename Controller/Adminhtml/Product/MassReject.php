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
 * Mass Reject Controller Class
 */
class MassReject extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Product
{
    /**
     * XML Path
     */
    public const XML_PATH_PRODUCT_REJECT_MAIL = 'ks_marketplace_catalog/ks_product_settings/ks_rejection_email';

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksCount = $ksFlag = 0;

        //get rejected status
        $ksProductStatus = \Ksolves\MultivendorMarketplace\Model\KsProduct::KS_STATUS_REJECTED;
        //get status
        $ksStatus = 2;

        try {
            //get data
            $ksCollection = $this->ksFilter->getCollection($this->ksCollectionFactory->create());
            $ksMassSellerIds = [];
            $ksIds = [];
            foreach ($ksCollection as $ksItem) {
                $ksData = $this->ksProductCollection->create()->load($ksItem->getEntityId(), 'ks_product_id');
                $ksIds[] = $ksItem->getId();
                $ksProductData = $this->ksProductFactory->create()->load($ksItem->getEntityId());

                $ksSellerId = $ksData->getKsSellerId();
                $ksMassSellerIds[] = $ksSellerId;

                $ksProduct = $this->ksProductRepository->getById(
                    $ksItem->getEntityId(),
                    true,
                    $this->getRequest()->getParam('store', 0)
                );

                if ($ksData && $ksProductData) {
                    if ($ksData->getKsProductApprovalStatus() != $ksProductStatus) {
                        $ksData->setKsProductApprovalStatus($ksProductStatus);
                        $ksData->setKsEditMode(1);
                        $ksProductData->setStoreId($this->getRequest()->getParam('store', 0));
                        $ksProductData->setStatus($ksStatus);
                        $ksProductData->setUpdatedAt($this->ksDate->gmtDate());
                        $ksProductData->save();
                        $ksData->setKsRejectionReason("");
                        $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                        $ksData->save();
                        $ksCount++;
                    } else {
                        $ksFlag++;
                    }
                }
            }

            //Mail sent to seller when admin Mass Reject the products
            $ksSellerIds = array_unique($ksMassSellerIds);
            foreach ($ksSellerIds as $ksSellerId) {
                $ksProductCollection = $this->ksProductCollectionFactory->create()->addFieldToFilter('ks_product_id', ['in' => $ksIds])->addFieldToFilter('ks_seller_id', $ksSellerId);

                $ksProductIds = [];
                foreach ($ksProductCollection as $ksCollection) {
                    $ksProductIds[] = $ksCollection->getKsProductId();
                }
                $this->ksSendEmailProductReject($ksSellerId, $ksProductIds);
            }
            if ($ksCount) {
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 product(s) has been rejected successfully. ', $ksCount)
                );
            }

            if ($ksFlag) {
                $this->messageManager->addErrorMessage(
                    __('A total of %1 product(s) has already rejected.', $ksFlag)
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

    /**
     * Send Mail to Seller when Admin Rejects Product Request
     * @param int $ksSellerId, int $ksProductIds
     */
    private function ksSendEmailProductReject($ksSellerId, $ksProductIds)
    {
        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
            'ks_marketplace_catalog/ks_product_settings/ks_rejection_email',
            $this->ksDataHelper->getKsCurrentStoreView()
        );


        if ($ksEmailEnabled != "disable") {
            //Get Sender Info
            $ksSender = $this->ksDataHelper->getKsConfigValue(
                'ks_marketplace_catalog/ks_product_settings/ks_email_sender',
                $this->ksDataHelper->getKsCurrentStoreView()
            );
            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

            //Get Receiver Info
            $ksReceiverDetails = $this->ksProductTypeHelper->getKsSellerInfo($ksSellerId);

            $ksTemplateVariable = [];
            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
            $ksTemplateVariable['ksProductIds'] = $ksProductIds;
            $ksTemplateVariable['ksReason'] = "";

            // Send Mail
            $this->ksEmailHelper->ksProductApproval(
                self::XML_PATH_PRODUCT_REJECT_MAIL,
                $ksTemplateVariable,
                $ksSenderInfo,
                ucwords($ksReceiverDetails['name']),
                $ksReceiverDetails['email']
            );
        }
    }
}
