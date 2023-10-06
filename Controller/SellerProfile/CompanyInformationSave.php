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
use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * CompanyInformationSave Controller class
 */
class CompanyInformationSave extends Action
{
    /**
     * @var KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;
    
    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Context $ksContext
     * @param KsSellerFactory $ksSellerFactory
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        KsSellerFactory $ksSellerFactory,
        KsSellerStoreFactory $ksSellerStoreFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }
    
    /**
     * Save company information
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
                    $this->ksSaveCompanyInformation($ksData);
                } catch (\Exception $e) {
                    $ksMessage = __($e->getMessage());
                }
            }
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $ksResultRedirect;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
    
    /**
     * Save company information
     *
     * @param $ksData
     * @return void
     */
    public function ksSaveCompanyInformation($ksData)
    {
        //get model data
        $ksModel = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->getFirstItem();
        $ksModel->setKsCompanyName($ksData['ks-company-name']);
        $ksModel->setKsCompanyAddress($ksData['ks-company-address']);
        $ksModel->setKsCompanyContactNo($ksData['ks-company-contact']);
        $ksModel->setKsCompanyContactEmail($ksData['ks-company-email']);
        $ksModel->setKsCompanyPostcode($ksData['ks-company-postcode']);
        $ksModel->setKsCompanyCountry($ksData['country_id']);
        if (isset($ksData['region'])) {
            $ksModel->setKsCompanyStateId($ksData['region']);
        } else {
            $ksModel->setKsCompanyStateId(null);
        }
        $ksModel->setKsCompanyTaxvatNumber($ksData['ks-taxvat-number']);
        //save data
        $ksModel->save();

        //check store id
        if($ksData['ks-store-id'] == 0){
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
                            if ($ksAllStoreviewData->getKsSupportContact() == $ksStoreviewData->getKsSupportContact()) {
                                // set data in specific store view
                                $ksStoreviewData->setKsSupportContact($ksData['ks-support-number']);
                            }

                            if ($ksAllStoreviewData->getKsSupportEmail() == $ksStoreviewData->getKsSupportEmail()) {
                                $ksStoreviewData->setKsSupportEmail($ksData['ks-support-email']);
                            }

                            if ($ksAllStoreviewData->getKsMetaKeyword() == $ksStoreviewData->getKsMetaKeyword()) {
                                $ksStoreviewData->setKsMetaKeyword($ksData['ks-keywords']);
                            }

                            if ($ksAllStoreviewData->getKsMetaDescription() == $ksStoreviewData->getKsMetaDescription()) {
                                $ksStoreviewData->setKsMetaDescription($ksData['ks-description']);
                            }

                            $ksStoreviewData->save();
                        }
                    }
                    // set data in all store view
                    $ksAllStoreviewData->setKsSupportContact($ksData['ks-support-number']);
                    $ksAllStoreviewData->setKsSupportEmail($ksData['ks-support-email']);
                    $ksAllStoreviewData->setKsMetaKeyword($ksData['ks-keywords']);
                    $ksAllStoreviewData->setKsMetaDescription($ksData['ks-description']);
                    $ksAllStoreviewData->save();                                 
                }
             
            } else {
                $ksNewData = [
                    'ks_seller_id' => $ksData['ks-seller-id'],
                    'ks_store_id' => $ksData['ks-store-id'],
                    'ks_support_contact' => $ksData['ks-support-number'],
                    'ks_support_email' => $ksData['ks-support-email'],
                    'ks_meta_keyword' => $ksData['ks-keywords'],
                    'ks_meta_description' => $ksData['ks-description']
                ];
                $ksSellerStore = $this->ksSellerStoreFactory->create();
                $ksSellerStore->addData($ksNewData)->save();
            }
        } else {
            $ksStoreModelCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
            $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
            if (count($ksStoreModelCollection) != 0) {
                $ksStoreModel = $ksStoreModelCollection->getFirstItem();
                $ksStoreModel->setKsSupportContact(isset($ksData['ks-support-number-checkbox']) ? $ksAllStoreCollection->getKsSupportContact() : $ksData['ks-support-number']);
                $ksStoreModel->setKsSupportEmail(isset($ksData['ks-support-email-checkbox']) ? $ksAllStoreCollection->getKsSupportEmail() : $ksData['ks-support-email']);
                $ksStoreModel->setKsMetaKeyword(isset($ksData['ks-keywords-checkbox']) ? $ksAllStoreCollection->getKsMetaKeyword() : $ksData['ks-keywords']);
                $ksStoreModel->setKsMetaDescription(isset($ksData['ks-description-checkbox']) ? $ksAllStoreCollection->getKsMetaDescription() : $ksData['ks-description']);
                $ksStoreModel->save();
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
                $ksNewData['ks_support_contact'] = isset($ksData['ks-support-number-checkbox']) ? $ksAllStoreCollection->getKsSupportContact() : $ksData['ks-support-number'];
                $ksNewData['ks_support_email'] = isset($ksData['ks-support-email-checkbox']) ? $ksAllStoreCollection->getKsSupportEmail() : $ksData['ks-support-email'];
                $ksNewData['ks_meta_keyword'] = isset($ksData['ks-keywords-checkbox']) ? $ksAllStoreCollection->getKsMetaKeyword() : $ksData['ks-keywords'];
                $ksNewData['ks_meta_description'] = isset($ksData['ks-description-checkbox']) ? $ksAllStoreCollection->getKsMetaDescription() : $ksData['ks-description'];

                $ksSellerStore = $this->ksSellerStoreFactory->create();
                $ksSellerStore->addData($ksNewData)->save();
            }
        }
    }
}
