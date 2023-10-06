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

namespace Ksolves\MultivendorMarketplace\Controller\FavouriteSeller;

use Ksolves\MultivendorMarketplace\Helper\KsEmailHelper;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerFactory;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsFavouriteSeller\CollectionFactory as KsFavouriteSellerCollectionFactory;

/**
 * Save Controller Class
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * XML Path
     */
    const XML_PATH_WELCOME_EMAIL_NOTIFICATION_FOLLOWERS = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_welcome_email_notification_followers_templates';
    const XML_PATH_NOTIFICATION_NEWLY_ADDED_FOLLOWERS = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_notification_newly_added_followers_templates';

    /**
     * @var KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksFavouriteSellerHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var KsDataHelper
     */
    protected $ksHelperData;

    /**
     * @var KsFavouriteSellerFactory
     */
    protected $ksFavouriteSellerFactory;

    /**
     * @var KsFavouriteSellerCollectionFactory
     */
    protected $ksFavouriteSellerCollectionFactory;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param KsFavouriteSellerFactory $ksFavouriteSellerFactory
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param KsFavouriteSellerHelper $ksFavouriteSellerHelper
     * @param KsFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory
     * @param Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksHelperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        KsFavouriteSellerFactory $ksFavouriteSellerFactory,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        KsFavouriteSellerHelper $ksFavouriteSellerHelper,
        ksFavouriteSellerCollectionFactory $ksFavouriteSellerCollectionFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksHelperData
    ) {
        $this->ksFavouriteSellerFactory = $ksFavouriteSellerFactory;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksFavouriteSellerHelper = $ksFavouriteSellerHelper;
        $this->ksFavouriteSellerCollectionFactory = $ksFavouriteSellerCollectionFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksHelperData = $ksHelperData;
        parent::__construct($ksContext);
    }

    /**
     * Favourite seller save page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->ksFavouriteSellerHelper->getKsCustomerId()) {
            try {
                $ksFavouriteSellerModel = $this->ksFavouriteSellerFactory->create();
                $ksPostData = $this->getRequest()->getParams();

                //Check if seller already added as favourite
                $ksModel = $this->ksFavouriteSellerCollectionFactory->create()->addFieldToFilter('ks_customer_id', $ksPostData['customerid'])->addFieldToFilter('ks_seller_id', $ksPostData['sellerid']);
                
                if ($ksModel->getData()) {
                    $this->ksMessageManager->addSuccess(__('Seller already added as favourite.'));
                } else {
                    $ksStoreId = $this->ksHelperData->getKsCurrentStoreView();
                    $ksWebsiteId = $this->ksHelperData->getKsCurrentWebsiteId();
                    //Save data in ks_favourite_seller table
                    $ksFavouriteSellerModel->setData('ks_customer_id', $ksPostData['customerid']);
                    $ksFavouriteSellerModel->setData('ks_seller_id', $ksPostData['sellerid']);
                    $ksFavouriteSellerModel->setData('ks_store_view_id', $ksStoreId);
                    $ksFavouriteSellerModel->setData('ks_website_id', $ksWebsiteId);
                    $ksFavouriteSellerModel->save();
                    $this->ksSellerHelper->ksFlushCache();

                    $ksStoreName = $this->ksFavouriteSellerHelper->getKsStoreName($ksPostData['sellerid']);

                    $ksWelcomeDisable = $this->ksHelperData->getKsConfigValue(
                        self::XML_PATH_WELCOME_EMAIL_NOTIFICATION_FOLLOWERS,
                        $ksStoreId
                    );

                    //Get Sender Info
                    $ksSender = $this->ksHelperData->getKsConfigValue('ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_sender', $ksStoreId);
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                    //Welcome email notification to customer/follower
                    if ($ksWelcomeDisable != "disable") {
                        $ksFollowerDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksPostData['customerid']);
                        
                        $ksVarsForWelcome = [];
                        $ksVarsForWelcome['ksCustomerName'] = ucwords($this->ksFavouriteSellerHelper->getKsSellerName($ksPostData['customerid']));
                        $ksVarsForWelcome['ksStoreName'] = ucwords($this->ksFavouriteSellerHelper->getKsStoreName($ksPostData['sellerid']));
                        $ksWelcomeFollowerMail = $this->ksEmailHelper->ksSendEmail($ksWelcomeDisable, $ksVarsForWelcome, $ksSenderInfo, $ksFollowerDetails);
                    }

                    //Newly added followers email notification to seller/admin/both
                    $ksToWhoEmailNotification = $this->ksHelperData->getKsConfigValue(
                        'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_templates',
                        $ksStoreId
                    );
                    $ksGetCustomerEmail = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksPostData['customerid']);
                    $ksSellerDetails = $this->ksFavouriteSellerHelper->getKsCustomerAccountInfo($ksPostData['sellerid']);
                    $ksVarsForNewlyAdded = [];
                    
                    $ksVarsForNewlyAdded['ksCustomerName'] = ucwords($this->ksFavouriteSellerHelper->getKsSellerName($ksPostData['customerid']));
                    $ksVarsForNewlyAdded['ksCustomerEmail'] = $ksGetCustomerEmail['email'];
                    $ksVarsForNewlyAdded['ksStoreName'] = ucwords($this->ksFavouriteSellerHelper->getKsStoreName($ksPostData['sellerid']));
                    //Get Receiver Info
                    $ksAdminEmailOption = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_favourite_seller_admin_email_option';
                    $ksAdminSecondaryEmail ='ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_favourite_seller_admin_email';
                    $ksStoreId = $this->ksHelperData->getKsCurrentStoreView();
                    $ksRecieveInfo = $this->ksHelperData->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                    $ksTemplate = $this->ksHelperData->getKsConfigValue(
                        self::XML_PATH_NOTIFICATION_NEWLY_ADDED_FOLLOWERS,
                        $ksStoreId
                    );
                    if ($ksToWhoEmailNotification == "seller") {
                        $ksVarsForNewlyAdded['ksSellerName'] = ucwords($this->ksFavouriteSellerHelper->getKsSellerName($ksPostData['sellerid']));
                        $ksSellerNewlyAddedEmail = $this->ksEmailHelper->ksSendEmail($ksTemplate, $ksVarsForNewlyAdded, $ksSenderInfo, $ksSellerDetails);
                    } elseif ($ksToWhoEmailNotification == "admin") {
                        $ksVarsForNewlyAdded['ksSellerName'] = ucwords($ksRecieveInfo['name']);
                        $ksSellerNewlyAddedEmail = $this->ksEmailHelper->ksSendEmail($ksTemplate, $ksVarsForNewlyAdded, $ksSenderInfo, $ksRecieveInfo);
                    } elseif ($ksToWhoEmailNotification == "no_action") {
                        # code...
                    } elseif ($ksToWhoEmailNotification == "both") {
                        //Send email to seller
                        $ksVarsForNewlyAdded['ksSellerName'] = ucwords($this->ksFavouriteSellerHelper->getKsSellerName($ksPostData['sellerid']));
                        $ksSellerNewlyAddedEmail = $this->ksEmailHelper->ksSendEmail($ksTemplate, $ksVarsForNewlyAdded, $ksSenderInfo, $ksSellerDetails);

                        //Send email to admin
                        $ksVarsForNewlyAdded['ksSellerName'] = ucwords($ksRecieveInfo['name']);
                        $ksAdminNewlyAddedEmail = $this->ksEmailHelper->ksSendEmail($ksTemplate, $ksVarsForNewlyAdded, $ksSenderInfo, $ksRecieveInfo);
                    }
                    $this->ksMessageManager->addSuccess(__("\"%1\" has been added in the favourite sellers listing successfully.", $ksStoreName));
                }
            } catch (\Exception $e) {
                $this->ksMessageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
