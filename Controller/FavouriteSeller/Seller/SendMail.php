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

namespace Ksolves\MultivendorMarketplace\Controller\FavouriteSeller\Seller;

use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * SendMail Controller Class
 */
class SendMail extends \Magento\Framework\App\Action\Action
{
    /**
     * XML Path
     */
    const XML_PATH_FOLLOWERS_MAIL = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_followers_mail';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $ksResultRedirectFactory;
    
    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param KsSellerHelper $ksSellerHelper
     * @param PageFactory $ksResultPageFactory
     * @param KsEmailHelper $ksEmailHelper
     * @param \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory
     * @param KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsDataHelper $ksHelperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        PageFactory $ksResultPageFactory,
        KsEmailHelper $ksEmailHelper,
        \Magento\Framework\Controller\Result\RedirectFactory $ksResultRedirectFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        KsDataHelper $ksHelperData
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksResultRedirectFactory  = $ksResultRedirectFactory;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksHelperData = $ksHelperData;
        parent::__construct($context);
    }

    /**
     * SendMail to followers
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                $ksData = (array)$this->getRequest()->getParams();
                $ksStoreId = $this->getRequest()->getParam('store', 0);
                //Get Sender Info
                $ksSender = $this->ksHelperData->getKsConfigValue(
                    'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_sender',
                    $ksStoreId
                );
                $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                $ksTemplate = $this->ksHelperData->getKsConfigValue(
                    self::XML_PATH_FOLLOWERS_MAIL,
                    $ksStoreId
                );
                
                $ksEmailData = explode(",", $ksData['ks-fav-email-address']); //get email address
                $ksVars = [];
                foreach ($ksEmailData as $ksEmail) {
                    $ksVars['ksCustomerName'] = ucwords($this->ksFavouriteSellerHelper->getKsNameByEmail($ksEmail));
                    $ksVars['ks-fav-followers-email-subject'] = $ksData['ks-fav-subject'];
                    $ksVars['ks-fav-followers-email-notification'] = $ksData['ks_message'];
                    
                    //Receiver Info
                    $ksRecieverInfo = [];
                    $ksRecieverInfo['name'] = $ksVars['ksCustomerName'];
                    $ksRecieverInfo['email'] = $ksEmail;
                    
                    $this->ksEmailHelper->ksSendEmail($ksTemplate, $ksVars, $ksSenderInfo, $ksRecieverInfo);
                }
                $this->messageManager->addSuccess(
                    __("An email has been sent successfully.")
                );
                $resultRedirect = $this->ksResultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            } catch (\Exception $e) {
                $this->messageManager->addError("Mail could not be sent");
                $resultRedirect = $this->ksResultRedirectFactory->create();
                return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            }
        } else {
            return $this->ksResultPageFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
