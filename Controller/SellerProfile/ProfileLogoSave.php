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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * ProfileLogoSave  Controller class
 */
class ProfileLogoSave extends \Magento\Framework\App\Action\Action
{
    /**
     * @var UploaderFactor
     */
    protected $ksUploaderFactory;

    /**
     * @var AdapterFactory
     */
    protected $ksAdapterFactory;

    /**
     * @var Filesystem
     */
    protected $ksFilesystem;
    
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
     * @param UploaderFactory $ksUploaderFactory
     * @param AdapterFactory $ksAdapterFactory
     * @param Filesystem $ksFilesystem
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        UploaderFactory $ksUploaderFactory,
        AdapterFactory $ksAdapterFactory,
        Filesystem $ksFilesystem,
        KsSellerStoreFactory $ksSellerStoreFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksUploaderFactory = $ksUploaderFactory;
        $this->ksAdapterFactory = $ksAdapterFactory;
        $this->ksFilesystem = $ksFilesystem;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Save logo information
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        $ksCount = 0;
        // check for seller
        if ($ksIsSeller == 1) {
            //get param data
            $ksData = $this->getRequest()->getParams();
            $ksLogoFiles = $this->getRequest()->getFiles();
            if (isset($ksLogoFiles['ks-profile-logo-upload']['name']) && $ksLogoFiles['ks-profile-logo-upload']['name'] != '') {
                try {
                    $ksUploaderFactories = $this->ksUploaderFactory->create(['fileId' => 'ks-profile-logo-upload']);
                    $ksUploaderFactories->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $ksImageAdapter = $this->ksAdapterFactory->create();
                    $ksUploaderFactories->addValidateCallback('custom_image_upload', $ksImageAdapter, 'validateUploadFile');
                    $ksUploaderFactories->setAllowRenameFiles(true);
                    $ksMediaDirectory = $this->ksFilesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $ksDestinationPath = $ksMediaDirectory->getAbsolutePath('ksolves/multivendor/');
                    $ksResult = $ksUploaderFactories->save($ksDestinationPath);
                    $ksCount = 1;
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
                                        if ($ksAllStoreviewData->getKsStoreLogo() == $ksStoreviewData->getKsStoreLogo()) {
                                            // set data in specific store view
                                            $ksStoreviewData->setKsStoreLogo($ksResult['file']);
                                        }

                                        $ksStoreviewData->save();
                                    }
                                }
                                // set data in all store view
                                $ksAllStoreviewData->setKsStoreLogo($ksResult['file']);
                                $ksAllStoreviewData->save();                                 
                            }
                         
                        } else {
                            $ksNewData = [
                                'ks_seller_id' => $ksData['ks-seller-id'],
                                'ks_store_id' => $ksData['ks-store-id'],
                                'ks_store_logo' => $ksResult['file']
                            ];
                            $ksSellerStore = $this->ksSellerStoreFactory->create();
                            $ksSellerStore->addData($ksNewData)->save();
                        }
                    } else {
                        $ksSellerStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                        if (count($ksSellerStoreCollection) != 0) {
                            $ksCollection = $ksSellerStoreCollection->getFirstItem();
                            $ksCollection->setKsSellerId($ksData['ks-seller-id']);
                            $ksCollection->setKsStoreId($ksData['ks-store-id']);
                            $ksCollection->setKsStoreLogo($ksResult['file']);
                            $ksCollection->save();
                        } else {
                            $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                            $ksNewData = [];
                            // set data of all store view in an array
                            $ksNewData = $ksAllStoreCollection->getData();
                            unset($ksNewData['id']);
                            // update data of array according to current store
                            $ksNewData['ks_seller_id'] = $ksData['ks-seller-id'];
                            $ksNewData['ks_store_id'] = $ksData['ks-store-id'];
                            $ksNewData['ks_store_logo'] = $ksResult['file'];

                            $ksCollection = $this->ksSellerStoreFactory->create();
                            $ksCollection->addData($ksNewData)->save();
                        } 
                    }
                } catch (\Exception $e) {
                    if($ksCount == 1){
                        $this->messageManager->addError(__('An error occured while saving your data.'));
                    } else {
                        $this->messageManager->addError(__('Filename is too long.'));
                    }
                }
            } else {
                if($ksData['ks-store-id'] != 0){
                    $ksSellerStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', $ksData['ks-store-id']);
                    $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                    if (count($ksSellerStoreCollection) != 0) {
                        $ksCollection = $ksSellerStoreCollection->getFirstItem();
                        $ksCollection->setKsSellerId($ksData['ks-seller-id']);
                        $ksCollection->setKsStoreId($ksData['ks-store-id']);
                        $ksCollection->setKsStoreLogo($ksAllStoreCollection->getKsStoreLogo());
                        $ksCollection->save();
                    } else {
                        $ksAllStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->addFieldToFilter('ks_store_id', 0)->getFirstItem();
                        $ksNewData = [];
                        // set data of all store view in an array
                        $ksNewData = $ksAllStoreCollection->getData();
                        unset($ksNewData['id']);
                        // update data of array according to current store
                        $ksNewData['ks_seller_id'] = $ksData['ks-seller-id'];
                        $ksNewData['ks_store_id'] = $ksData['ks-store-id'];
                        $ksNewData['ks_store_logo'] = $ksAllStoreCollection->getKsStoreLogo();

                        $ksCollection = $this->ksSellerStoreFactory->create();
                        $ksCollection->addData($ksNewData)->save();
                    }
                } else {
                    $this->messageManager->addError(__('An error occured while saving your data.'));
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
}
