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

namespace Ksolves\MultivendorMarketplace\Cron;

use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;
use Ksolves\MultivendorMarketplace\Helper\KsFavouriteSellerHelper;
use Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmailFactory as KsFavouriteSellerEmailFactory;

/**
 * KsFavSellerEmail controller class
 */
class KsFavSellerEmail
{
    /**
     * XML Path
     */
    public const XML_PATH_PRICE_ALERT_MAIL = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_price_alerts_followers_templates';
    public const XML_PATH_NEW_PRODUCT_ALERT_MAIL = 'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_new_product_alert_followers_templates';

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var KsFavouriteSellerHelper
     */
    protected $ksFavSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsFavouriteSellerEmailFactory
     */
    protected $ksFavSellerEmailFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $ksResource;

    /**
     * @var \Magento\Catalog\Model\ProductFactory $ksProductFactory
     */
    protected $ksProductFactory;

    /**
     * Constructor
     *
     * @param KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsFavouriteSellerHelper $ksFavSellerHelper
     * @param \Magento\Framework\App\ResourceConnection $ksResource
     * @param \Magento\Catalog\Model\ProductFactory $ksProductFactory
     */
    public function __construct(
        KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsFavouriteSellerHelper $ksFavSellerHelper,
        KsFavouriteSellerEmailFactory $ksFavSellerEmailFactory,
        \Magento\Framework\App\ResourceConnection $ksResource,
        \Magento\Catalog\Model\ProductFactory $ksProductFactory
    ) {
        $this->ksDataHelper = $ksDataHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksFavSellerHelper = $ksFavSellerHelper;
        $this->ksFavSellerEmailFactory = $ksFavSellerEmailFactory;
        $this->ksResource = $ksResource;
        $this->ksProductFactory = $ksProductFactory;
    }

    public function execute()
    {
        $ksCollection = $this->ksFavSellerEmailFactory->create()->getCollection();
        $ksProductTable = $this->ksResource->getTableName('ks_product_details');
        
        $ksCollection->getSelect()->join(
            $ksProductTable.' as ksp',
            'main_table.ks_product_id = ksp.ks_product_id',
            [
                'ks_product_approval_status' => 'ks_product_approval_status'
            ]
        );
        $ksCollection->addFieldToFilter('ks_is_email_sent', 0)->addFieldToFilter('ks_product_approval_status', 1);

        if ($this->ksFavSellerHelper->isKsEnabled() && $ksCollection->getSize()) {
            $ksMassSellerIds = [];
            $ksMassIds = [];
            foreach ($ksCollection as $ksItem) {
                $ksMassSellerIds[] = $ksItem->getKsSellerId();
                $ksMassIds[] = $ksItem->getId();
            }

            //unique seller ids
            $ksSellerIds = array_unique($ksMassSellerIds);
            
            foreach ($ksSellerIds as $ksSellerId) {
                $ksFavEmailCollection = $this->ksFavSellerEmailFactory->create()->getCollection()->addFieldToFilter('ks_seller_id', $ksSellerId)->addFieldToFilter('id', ['in' => $ksMassIds]);
                $ksNewIds = [];
                $ksUpdateIds = [];
                
                foreach ($ksFavEmailCollection as $ksItem) {
                    if ($ksItem->getKsProductState() == 0) {
                        $ksNewIds[] = $ksItem->getId();
                    } else {
                        $ksUpdateIds[] = $ksItem->getId();
                    }
                }
        
                if (count($ksNewIds)) {
                    $this->ksSendNewProductEmail($ksSellerId, $ksNewIds);
                }

                if (count($ksUpdateIds)) {
                    $this->ksSendPriceEmail($ksSellerId, $ksUpdateIds);
                }

                $ksIds = array_merge($ksNewIds, $ksUpdateIds);
                
                foreach ($ksIds as $ksId) {
                    $ksModel = $this->ksFavSellerEmailFactory->create()->load($ksId);
                    $ksModel->setKsIsEmailSent(\Ksolves\MultivendorMarketplace\Model\KsFavouriteSellerEmail::KS_STATUS_SENT);
                    $ksModel->save();
                }
            }
        }
    }

    /**
     * Send new product alert email to followers
     *
     * @param  [int] $ksSellerId
     * @param  [int] $ksMassIds
     */
    public function ksSendNewProductEmail($ksSellerId, $ksMassIds)
    {
        //Template variables
        $ksTempVars = [];
        $ksTempVars['ksStore'] = $this->ksFavSellerHelper->getKsStoreName($ksSellerId);

        //Get followers
        $ksFollowersIds = $this->ksFavSellerHelper->getKsAllFollowers($ksSellerId);

        foreach ($ksFollowersIds as $ksFollowerId) {
            //Get follower store id
            $ksStoreId = $this->ksFavSellerHelper->getKsFollowerStoreId($ksSellerId, $ksFollowerId);

            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                self::XML_PATH_NEW_PRODUCT_ALERT_MAIL,
                $ksStoreId
            );

            if ($ksEmailEnabled != "disable") {
                //Get Sender Info
                $ksEmailSender = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_sender',
                    $ksStoreId
                );
                $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksEmailSender);

                $ksIds = $this->ksFavSellerHelper->ksCheckNewProductWebsites($ksFollowerId, $ksMassIds, $ksSellerId);
                if (count($ksIds)) {
                    $ksEmailPrefernce = $this->ksFavSellerHelper->getKsCustomerEmailPreference($ksFollowerId, $ksSellerId);
                    if ($ksEmailPrefernce['seller_new_product'] == 1 && $ksEmailPrefernce['customer_new_product'] == 1) {
                        $ksRecipient = $this->ksDataHelper->getKsCustomerInfo($ksFollowerId);
                        $ksTempVars['ksCustomer'] = ucwords($ksRecipient['name']);
                        $ksTempVars['ksProIds'] = $ksIds;
                        $ksTempVars['ksStoreId'] = $ksStoreId;
                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail(
                            $ksEmailEnabled,
                            $ksTempVars,
                            $ksSenderInfo,
                            $ksRecipient
                        );
                    }
                }
            }
        }
    }

    /**
     * Send price alert email to followers
     *
     * @param  [int] $ksSellerId
     * @param  [int] $ksMassIds
     */
    public function ksSendPriceEmail($ksSellerId, $ksMassIds)
    {
        //Template variables
        $ksTempVars = [];
        $ksTempVars['ksStore'] = $this->ksFavSellerHelper->getKsStoreName($ksSellerId);

        //Get followers
        $ksFollowersIds = $this->ksFavSellerHelper->getKsAllFollowers($ksSellerId);

        foreach ($ksFollowersIds as $ksFollowerId) {
            //Get follower store id
            $ksStoreId = $this->ksFavSellerHelper->getKsFollowerStoreId($ksSellerId, $ksFollowerId);

            $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                self::XML_PATH_PRICE_ALERT_MAIL,
                $ksStoreId
            );

            if ($ksEmailEnabled != "disable") {
                //Get Sender Info
                $ksEmailSender = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_favourite_seller/ks_favourite_seller_settings/ks_email_sender',
                    $ksStoreId
                );
                $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksEmailSender);

                $ksIds = $this->ksFavSellerHelper->ksCheckProductCustomerWebsiteIds($ksFollowerId, $ksMassIds, $ksSellerId);
                
                if (count($ksIds)) {
                    $ksEmailPrefernce = $this->ksFavSellerHelper->getKsCustomerEmailPreference($ksFollowerId, $ksSellerId);
                    if ($ksEmailPrefernce['seller_price_alert'] == 1 && $ksEmailPrefernce['customer_price_alert'] == 1) {
                        $ksRecipient = $this->ksDataHelper->getKsCustomerInfo($ksFollowerId);
                        $ksTempVars['ksCustomer'] = ucwords($ksRecipient['name']);
                        $ksTempVars['ksProIds'] = $ksIds;
                        $ksTempVars['ksStoreId'] = $ksStoreId;
                        // Send Mail
                        $this->ksEmailHelper->ksSendEmail(
                            $ksEmailEnabled,
                            $ksTempVars,
                            $ksSenderInfo,
                            $ksRecipient
                        );
                    }
                }
            }
        }
    }
}
