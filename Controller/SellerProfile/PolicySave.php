<?php
/**
 * Ksolves
 *
 * @category  Ksolves
 * @package   Ksolves_MultivendorMarketplace
 * @author    Ksolves Team
 * @copyright Copyright (c) Ksolves India Limited  (https://www.ksolves.com/)
 * @license   https://store.ksolves.com/magento-license
 */

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

use Magento\Framework\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Action;

/**
 * PolicySave Controller class
 */
class PolicySave extends Action
{
    /**
     * @var KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Context $ksContext
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        KsSellerStoreFactory $ksSellerStoreFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }
    
    /**
     * Save policy data
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            //get param data
            $ksData = $this->getRequest()->getParams();
            
            //check param data
            if ($ksData) {
                try {
                    //check store id
                    if ($ksData['ks-store-id'] == 0) {
                        // get all store view collection
                        $ksModelCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                         
                        // check collection size
                        if ($ksModelCollection->getSize() != 0) {
                            foreach ($ksModelCollection as $key => $ksAllStoreviewData) {
                                // get store views collections of the seller except all store view
                                $ksCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', ['neq' => $ksData['ks-store-id']]);
                                
                                if ($ksCollection->getSize() != 0) {
                                    foreach ($ksCollection as $key => $ksStoreviewData) {
                                        
                                        // check the data of all storeview collection and specific store view collection
                                        if ($ksAllStoreviewData->getKsRefundPolicy() == $ksStoreviewData->getKsRefundPolicy()) {
                                            // set data in specific store view
                                            $ksStoreviewData->setKsRefundPolicy(isset($ksData['ks-return-policy-textarea']) ? $ksData['ks-return-policy-textarea'] : null);
                                        }

                                        if ($ksAllStoreviewData->getKsPrivacyPolicy() == $ksStoreviewData->getKsPrivacyPolicy()) {
                                            $ksStoreviewData->setKsPrivacyPolicy(isset($ksData['ks-privacy-policy-textarea']) ? $ksData['ks-privacy-policy-textarea'] : null);
                                        }

                                        if ($ksAllStoreviewData->getKsShippingPolicy() == $ksStoreviewData->getKsShippingPolicy()) {
                                            $ksStoreviewData->setKsShippingPolicy(isset($ksData['ks-shipping-policy-textarea']) ? $ksData['ks-shipping-policy-textarea'] : null);
                                        }

                                        if ($ksAllStoreviewData->getKsLegalNotice() == $ksStoreviewData->getKsLegalNotice()) {
                                            $ksStoreviewData->setKsLegalNotice(isset($ksData['ks-legal-policy-textarea']) ? $ksData['ks-legal-policy-textarea'] : null);
                                        }

                                        if ($ksAllStoreviewData->getKsTermsOfService() == $ksStoreviewData->getKsTermsOfService()) {
                                            $ksStoreviewData->setKsTermsOfService(isset($ksData['ks-terms-of-service-textarea']) ? $ksData['ks-terms-of-service-textarea'] : null);
                                        }

                                        $ksStoreviewData->save();
                                    }
                                }
                                // set data in all store view
                                $ksAllStoreviewData->setKsRefundPolicy(isset($ksData['ks-return-policy-textarea']) ? $ksData['ks-return-policy-textarea'] : null);
                                $ksAllStoreviewData->setKsPrivacyPolicy(isset($ksData['ks-privacy-policy-textarea']) ? $ksData['ks-privacy-policy-textarea'] : null);
                                $ksAllStoreviewData->setKsShippingPolicy(isset($ksData['ks-shipping-policy-textarea']) ? $ksData['ks-shipping-policy-textarea'] : null);
                                $ksAllStoreviewData->setKsLegalNotice(isset($ksData['ks-legal-policy-textarea']) ? $ksData['ks-legal-policy-textarea'] : null);
                                $ksAllStoreviewData->setKsTermsOfService(isset($ksData['ks-terms-of-service-textarea']) ? $ksData['ks-terms-of-service-textarea'] : null);
                                $ksAllStoreviewData->save();
                            }
                        } else {
                            $ksNewData = [
                                'ks_seller_id' => $ksData['ks-seller-id'],
                                'ks_store_id' => $ksData['ks-store-id'],
                                'ks_refund_policy' => isset($ksData['ks-return-policy-textarea']) ? $ksData['ks-return-policy-textarea'] : null,
                                'ks_privacy_policy' => isset($ksData['ks-privacy-policy-textarea']) ? $ksData['ks-privacy-policy-textarea'] : null,
                                'ks_shipping_policy' => isset($ksData['ks-shipping-policy-textarea']) ? $ksData['ks-shipping-policy-textarea'] : null,
                                'ks_legal_notice' => isset($ksData['ks-legal-policy-textarea']) ? $ksData['ks-legal-policy-textarea'] : null,
                                'ks_terms_of_service' => isset($ksData['ks-terms-of-service-textarea']) ? $ksData['ks-terms-of-service-textarea'] : null
                            ];
                            $ksSellerStore = $this->ksSellerStoreFactory->create();
                            $ksSellerStore->addData($ksNewData)->save();
                        }
                    } else {
                        $ksModelCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                        $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                        if (count($ksModelCollection) != 0) {
                            $ksModel = $ksModelCollection->getFirstItem();
                            $ksModel->setKsRefundPolicy(isset($ksData['ks-return-policy-textarea']) ? isset($ksData['ks-return-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsRefundPolicy() : $ksData['ks-return-policy-textarea'] : null);
                            $ksModel->setKsPrivacyPolicy(isset($ksData['ks-privacy-policy-textarea']) ? isset($ksData['ks-privacy-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsPrivacyPolicy() : $ksData['ks-privacy-policy-textarea'] : null);
                            $ksModel->setKsShippingPolicy(isset($ksData['ks-shipping-policy-textarea']) ? isset($ksData['ks-shipping-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsShippingPolicy() : $ksData['ks-shipping-policy-textarea'] : null);
                            $ksModel->setKsLegalNotice(isset($ksData['ks-legal-policy-textarea']) ? isset($ksData['ks-legal-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsLegalNotice() : $ksData['ks-legal-policy-textarea'] : null);
                            $ksModel->setKsTermsOfService(isset($ksData['ks-terms-of-service-textarea']) ? isset($ksData['ks-terms-of-service-textarea-checkbox']) ? $ksAllStoreCollection->getKsTermsOfService() : $ksData['ks-terms-of-service-textarea'] : null);
                            $ksModel->save();
                        } else {
                            // get collection of all storeview
                            $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                            $ksNewData = [];
                            // set data of all store view in an array
                            $ksNewData = $ksAllStoreCollection->getData();
                            unset($ksNewData['id']);
                            // update data of array according to current store
                            $ksNewData['ks_seller_id'] = $ksData['ks-seller-id'];
                            $ksNewData['ks_store_id'] = $ksData['ks-store-id'];
                            $ksNewData['ks_refund_policy'] = isset($ksData['ks-return-policy-textarea']) ? isset($ksData['ks-return-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsRefundPolicy() : $ksData['ks-return-policy-textarea'] : null;
                            $ksNewData['ks_privacy_policy'] = isset($ksData['ks-privacy-policy-textarea']) ? isset($ksData['ks-privacy-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsPrivacyPolicy() : $ksData['ks-privacy-policy-textarea'] : null;
                            $ksNewData['ks_shipping_policy'] = isset($ksData['ks-shipping-policy-textarea']) ? isset($ksData['ks-shipping-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsShippingPolicy() : $ksData['ks-shipping-policy-textarea'] : null;
                            $ksNewData['ks_legal_notice'] = isset($ksData['ks-legal-policy-textarea']) ? isset($ksData['ks-legal-policy-textarea-checkbox']) ? $ksAllStoreCollection->getKsLegalNotice() : $ksData['ks-legal-policy-textarea'] : null;
                            $ksNewData['ks_terms_of_service'] = isset($ksData['ks-terms-of-service-textarea']) ? isset($ksData['ks-terms-of-service-textarea-checkbox']) ? $ksAllStoreCollection->getKsTermsOfService() : $ksData['ks-terms-of-service-textarea'] : null;

                            $ksSellerStore = $this->ksSellerStoreFactory->create();
                            $ksSellerStore->addData($ksNewData)->save();
                        }
                    }
                } catch (\Exception $e) {
                    $ksMessage = __($e->getMessage());
                }
                /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $ksResultRedirect;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
