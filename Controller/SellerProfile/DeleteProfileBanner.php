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
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;

/**
 * DeleteProfileBanner Controller class
 */
class DeleteProfileBanner extends \Magento\Framework\App\Action\Action
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
     * Save banner information
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get param data
                $ksSellerId = $this->getRequest()->getParam('seller_id');
                $ksStoreId  = $this->getRequest()->getParam('store_id');
                $ksValue    = $this->getRequest()->getParam('value');
                //get model data
                $ksSellerStoreCollection = $this->ksSellerStoreFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('ks_store_id', $ksStoreId); 
                if($ksSellerStoreCollection->getSize() > 0){
                    $ksCollection = $ksSellerStoreCollection->getFirstItem();
                    if($ksValue == "banner") {
                        $ksCollection->setKsStoreBanner(null);
                    } else {
                        $ksCollection->setKsStoreLogo(null);
                    }
                    //save data
                    $ksCollection->save();
                }  
            } catch (\Exception $e) {
                $this->messageManager->addError(__('An error occured while deleting your data.'));
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
