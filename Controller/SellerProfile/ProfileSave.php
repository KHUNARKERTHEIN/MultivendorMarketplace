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
use Magento\Customer\Model\CustomerFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

/**
 * ProfileSave Controller class
 */
class ProfileSave extends Action
{
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
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        KsSellerFactory $ksSellerFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }
    
    /**
     * Save seller profile data
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
                    $ksAvailableCountries = implode(',', $ksData['ks-available-countries']);
                    $ksCollection = $this->ksSellerFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksData['ks-seller-id'])->getFirstItem();
                    $ksCollection->setKsStoreAvailableCountries($ksAvailableCountries);
                    $ksCollection->save();
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
        return $resultRedirect;
    }
}
