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
 * SocialMediaSave Controller class
 */
class SocialMediaSave extends Action
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
     * Save social media data
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
                                        if ($ksAllStoreviewData->getKsTwitterId() == $ksStoreviewData->getKsTwitterId()) {
                                            // set data in specific store view
                                            $ksStoreviewData->setKsTwitterId($ksData['ks-twitter-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsFacebookId() == $ksStoreviewData->getKsFacebookId()) {
                                            $ksStoreviewData->setKsFacebookId($ksData['ks-facebook-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsInstagramId() == $ksStoreviewData->getKsInstagramId()) {
                                            $ksStoreviewData->setKsInstagramId($ksData['ks-instagram-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsGoogleplusId() == $ksStoreviewData->getKsGoogleplusId()) {
                                            $ksStoreviewData->setKsGoogleplusId($ksData['ks-google-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsVimeoId() == $ksStoreviewData->getKsVimeoId()) {
                                            $ksStoreviewData->setKsVimeoId($ksData['ks-vimeo-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsPinterestId() == $ksStoreviewData->getKsPinterestId()) {
                                            $ksStoreviewData->setKsPinterestId($ksData['ks-pinterest-id']);
                                        }

                                        if ($ksAllStoreviewData->getKsYoutubeId() == $ksStoreviewData->getKsYoutubeId()) {
                                            $ksStoreviewData->setKsYoutubeId($ksData['ks-youtube-id']);
                                        }

                                        $ksStoreviewData->save();
                                    }
                                }
                                // set data in all store view
                                $ksAllStoreviewData->setKsTwitterId($ksData['ks-twitter-id']);
                                $ksAllStoreviewData->setKsFacebookId($ksData['ks-facebook-id']);
                                $ksAllStoreviewData->setKsInstagramId($ksData['ks-instagram-id']);
                                $ksAllStoreviewData->setKsGoogleplusId($ksData['ks-google-id']);
                                $ksAllStoreviewData->setKsVimeoId($ksData['ks-vimeo-id']);
                                $ksAllStoreviewData->setKsPinterestId($ksData['ks-pinterest-id']);
                                $ksAllStoreviewData->setKsYoutubeId($ksData['ks-youtube-id']);
                                $ksAllStoreviewData->save();                                 
                            }
                         
                        } else {
                            $ksNewData = [
                                'ks_seller_id' => $ksData['ks-seller-id'],
                                'ks_store_id' => $ksData['ks-store-id'],
                                'ks_twitter_id' => $ksData['ks-twitter-id'],
                                'ks_facebook_id' => $ksData['ks-facebook-id'],
                                'ks_instagram_id' => $ksData['ks-instagram-id'],
                                'ks_googleplus_id' => $ksData['ks-google-id'],
                                'ks_vimeo_id' => $ksData['ks-vimeo-id'],
                                'ks_pinterest_id' => $ksData['ks-pinterest-id'],
                                'ks_youtube_id' => $ksData['ks-youtube-id']
                            ];
                            $ksSellerStore = $this->ksSellerStoreFactory->create();
                            $ksSellerStore->addData($ksNewData)->save();
                        }
                    } else {
                        $ksModelCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                        $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                        if (count($ksModelCollection) != 0) {
                            $ksModel = $ksModelCollection->getFirstItem();
                            $ksModel->setKsTwitterId(isset($ksData['ks-twitter-id-checkbox']) ? $ksAllStoreCollection->getKsTwitterId() : $ksData['ks-twitter-id']);
                            $ksModel->setKsFacebookId(isset($ksData['ks-facebook-id-checkbox']) ? $ksAllStoreCollection->getKsFacebookId() : $ksData['ks-facebook-id']);
                            $ksModel->setKsInstagramId(isset($ksData['ks-instagram-id-checkbox']) ? $ksAllStoreCollection->getKsInstagramId() : $ksData['ks-instagram-id']);
                            $ksModel->setKsGoogleplusId(isset($ksData['ks-google-id-checkbox']) ? $ksAllStoreCollection->getKsGoogleplusId() : $ksData['ks-google-id']);
                            $ksModel->setKsVimeoId(isset($ksData['ks-vimeo-id-checkbox']) ? $ksAllStoreCollection->getKsVimeoId() : $ksData['ks-vimeo-id']);
                            $ksModel->setKsPinterestId(isset($ksData['ks-pinterest-id-checkbox']) ? $ksAllStoreCollection->getKsPinterestId() : $ksData['ks-pinterest-id']);
                            $ksModel->setKsYoutubeId(isset($ksData['ks-youtube-id-checkbox']) ? $ksAllStoreCollection->getKsYoutubeId() : $ksData['ks-youtube-id']);
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
                            $ksNewData['ks_twitter_id'] = isset($ksData['ks-twitter-id-checkbox']) ? $ksAllStoreCollection->getKsTwitterId() : $ksData['ks-twitter-id'];
                            $ksNewData['ks_facebook_id'] = isset($ksData['ks-facebook-id-checkbox']) ? $ksAllStoreCollection->getKsFacebookId() : $ksData['ks-facebook-id'];
                            $ksNewData['ks_instagram_id'] = isset($ksData['ks-instagram-id-checkbox']) ? $ksAllStoreCollection->getKsInstagramId() : $ksData['ks-instagram-id'];
                            $ksNewData['ks_googleplus_id'] = isset($ksData['ks-google-id-checkbox']) ? $ksAllStoreCollection->getKsGoogleplusId() : $ksData['ks-google-id'];
                            $ksNewData['ks_vimeo_id'] = isset($ksData['ks-vimeo-id-checkbox']) ? $ksAllStoreCollection->getKsVimeoId() : $ksData['ks-vimeo-id'];
                            $ksNewData['ks_pinterest_id'] = isset($ksData['ks-pinterest-id-checkbox']) ? $ksAllStoreCollection->getKsPinterestId() : $ksData['ks-pinterest-id'];
                            $ksNewData['ks_youtube_id'] = isset($ksData['ks-youtube-id-checkbox']) ? $ksAllStoreCollection->getKsYoutubeId() : $ksData['ks-youtube-id'];

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
