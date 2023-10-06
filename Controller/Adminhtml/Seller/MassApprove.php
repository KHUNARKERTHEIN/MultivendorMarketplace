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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultFactory;

/**
 * MassApprove Controller Class
 */
class MassApprove extends \Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller
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
        $ksSellerStatus = \Ksolves\MultivendorMarketplace\Model\KsSeller::KS_STATUS_APPROVED;

        try {
            //get collection
            $ksCollection = $this->ksFilter->getCollection($this->ksSellerCollection->create());
            //get collection size
            $ksCollectionSize = $ksCollection->getSize();

            foreach ($ksCollection as $ksData) {
                $ksSellerId = $ksData->getKsSellerId();
                $ksData->setKsRejectionReason("");
                $ksData->setKsSellerStatus($ksSellerStatus);
                $ksData->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksData->save();

                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_seller/ks_seller_settings/ks_seller_approved_templates'
                );

                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_seller/ks_seller_settings/ks_email_sender'
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                    $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                    $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 seller(s) has been approved successfully.', $ksCollectionSize)
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //for redirecting url
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
