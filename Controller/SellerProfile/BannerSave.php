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
use Ksolves\MultivendorMarketplace\Model\KsBannersFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * BannerSave  Controller class
 */
class BannerSave extends \Magento\Framework\App\Action\Action
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
     * @var KsBannersFactory
     */
    protected $ksBannersFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;
    
    /**
     * @param Context $ksContext
     * @param UploaderFactory $ksUploaderFactory
     * @param AdapterFactory $ksAdapterFactory
     * @param Filesystem $ksFilesystem
     * @param KsBannersFactory $ksBannersFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        UploaderFactory $ksUploaderFactory,
        AdapterFactory $ksAdapterFactory,
        Filesystem $ksFilesystem,
        KsBannersFactory $ksBannersFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksUploaderFactory = $ksUploaderFactory;
        $this->ksAdapterFactory = $ksAdapterFactory;
        $this->ksFilesystem = $ksFilesystem;
        $this->ksBannersFactory = $ksBannersFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Save banner information
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
            $ksBannerFiles = $this->getRequest()->getFiles();
            
            if (isset($ksBannerFiles['ks-banner-upload']['name']) && $ksBannerFiles['ks-banner-upload']['name'] != '') {
                try {
                    $ksUploaderFactories = $this->ksUploaderFactory->create(['fileId' => 'ks-banner-upload']);
                    $ksUploaderFactories->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $ksImageAdapter = $this->ksAdapterFactory->create();
                    $ksUploaderFactories->addValidateCallback('custom_image_upload', $ksImageAdapter, 'validateUploadFile');
                    $ksUploaderFactories->setAllowRenameFiles(true);
                    $ksMediaDirectory = $this->ksFilesystem->getDirectoryRead(DirectoryList::MEDIA);
                    $ksDestinationPath = $ksMediaDirectory->getAbsolutePath('ksolves/multivendor/');
                    $ksResult = $ksUploaderFactories->save($ksDestinationPath);
                    $ksCount = 1;
                    //get model data
                    $ksBannerCollection = $this->ksBannersFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id']);
                    if ($ksData['id']) {
                        $ksCollection = $this->ksBannersFactory->create()->load($ksData['id']);
                        $ksCollection->setKsPicture($ksResult['file']);
                        $ksCollection->setKsTitle($ksData['ks-title']);
                        $ksCollection->setKsText($ksData['ks-description']);
                        $ksCollection->save();
                    } else {
                        $ksCollection = $this->ksBannersFactory->create();
                        $ksData = [
                            "ks_seller_id" => $ksData['ks-seller-id'],
                            "ks_picture" => $ksResult['file'],
                            "ks_title"   => $ksData['ks-title'],
                            "ks_text" => $ksData['ks-description']
                        ];
                        $ksCollection->addData($ksData)->save();
                    }
                } catch (\Exception $e) {
                    if($ksCount == 1){
                        $this->messageManager->addError(__('An error occured while saving your data.'));
                    } else {
                        $this->messageManager->addError(__('Filename is too long.'));
                    }
                }
            } else {
                if ($ksData['id']) {
                    $ksCollection = $this->ksBannersFactory->create()->load($ksData['id']);
                    $ksCollection->setKsTitle($ksData['ks-title']);
                    $ksCollection->setKsText($ksData['ks-description']);
                    $ksCollection->save();
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
