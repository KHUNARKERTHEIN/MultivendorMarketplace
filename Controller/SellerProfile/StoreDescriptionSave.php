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

namespace Ksolves\MultivendorMarketplace\Controller\SellerProfile;

use Magento\Framework\App\Action\Context;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * StoreDescriptionSave Controller Class
 */
class StoreDescriptionSave extends Action
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
                    if($ksData['ks-store-id'] == 0){
                        // get all store view collection
                        $ksCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                        // check collection size
                        if ($ksCollection->getSize() != 0) {
                            foreach ($ksCollection as $key => $ksAllStoreviewData) {
                                // get store views collections of the seller except all store view
                                $ksModelCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', ['neq' => $ksData['ks-store-id']]);

                                if ($ksModelCollection->getSize() != 0) {
                                    foreach ($ksModelCollection as $key => $ksStoreviewData) {
                                        // check the data of all storeview collection and specific store view collection
                                        if ($ksAllStoreviewData->getKsStoreDescription() == $ksStoreviewData->getKsStoreDescription()) {
                                            // set data in specific store view
                                            $ksStoreviewData->setKsStoreDescription($ksData['ks-overview-textarea']);
                                        }

                                        $ksStoreviewData->save();
                                    }
                                }
                                // set data in all store view
                                $ksAllStoreviewData->setKsStoreDescription($ksData['ks-overview-textarea']);
                                $ksAllStoreviewData->save();                                 
                            }
                         
                        } else {
                            $ksNewData = [
                                'ks_seller_id' => $ksData['ks-seller-id'],
                                'ks_store_id' => $ksData['ks-store-id'],
                                'ks_store_description' => $ksData['ks-overview-textarea']
                            ];
                            $ksSellerStore = $this->ksSellerStoreFactory->create();
                            $ksSellerStore->addData($ksNewData)->save();
                        }
                    } else {
                        $ksCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                        $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                        if (count($ksCollection) != 0) {
                            $ksModel = $ksCollection->getFirstItem();
                            $ksModel->setKsStoreDescription(isset($ksData['ks-overview-textarea-checkbox']) ? $ksAllStoreCollection->getKsStoreDescription() : $ksData['ks-overview-textarea']);
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
                            $ksNewData['ks_store_description'] = isset($ksData['ks-overview-textarea-checkbox']) ? $ksAllStoreCollection->getKsStoreDescription() : $ksData['ks-overview-textarea'];

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
