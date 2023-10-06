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

namespace Ksolves\MultivendorMarketplace\Observer;

use Ksolves\MultivendorMarketplace\Model\KsSellerFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory;
use Ksolves\MultivendorMarketplace\Model\KsSellerSitemapFactory as KsSellerSitemapCollectionFactory;
use Magento\Framework\Event\ObserverInterface;

/**
 * KsCustomerRegisterSuccessObserver Observer Class
*/
class KsCustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

    /**
     * @var KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $ksMessageManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsDataHelper
     */
    protected $ksDataHelper;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $ksCoreSession;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var KsSellerSitemapCollectionFactory
     */
    protected $ksSellerSitemapCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $KsSellerHelper;

    /**
     * @param KsSellerFactory $ksSellerFactory
     * @param KsSellerStoreFactory $ksSellerStoreFactory
     * @param KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Magento\Framework\Session\SessionManagerInterface $ksCoreSession
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $KsSellerHelper
     */
    public function __construct(
        KsSellerFactory $ksSellerFactory,
        KsSellerSitemapCollectionFactory $ksSellerSitemapCollectionFactory,
        KsSellerStoreFactory $ksSellerStoreFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Magento\Framework\Session\SessionManagerInterface $ksCoreSession,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $KsSellerHelper
    ) {
        $this->ksSellerFactory      = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksDate               = $ksDate;
        $this->ksMessageManager     = $ksMessageManager;
        $this->ksDataHelper         = $ksDataHelper;
        $this->ksCoreSession        = $ksCoreSession;
        $this->ksEmailHelper        = $ksEmailHelper;
        $this->ksProductTypeHelper  = $ksProductTypeHelper;
        $this->ksSellerSitemapCollectionFactory = $ksSellerSitemapCollectionFactory;
        $this->KsSellerHelper       = $KsSellerHelper;
    }

    /**
    * customer register event handler.
    *
    * @param \Magento\Framework\Event\Observer $observer
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ksStoreId=0;
        $ksData = $observer['account_controller'];
        try {
            // get param data
            $ksParamData = $ksData->getRequest()->getParams();
            // check data of seller
            if (!empty($ksParamData['ks_become_seller']) && !empty($ksParamData['ks_seller_store_name']) && $ksParamData['ks_become_seller'] == 1) {
                $ksCustomer = $observer->getCustomer();

                $ksSellerCollection = $this->ksSellerFactory->create()
                ->getCollection()
                ->addFieldToFilter('ks_store_url', $ksParamData['ks_seller_store_url']);
                // check collection size
                if (!$ksSellerCollection->getSize()) {
                    // set data in model
                    $ksSellerModel = $this->ksSellerFactory->create();
                    if ($this->ksDataHelper->getKsConfigSellerSetting('ks_seller_approval')) {
                        $ksSellerModel->setData('ks_seller_status', 0);
                        $ksSellerStoreStatus = 0;
                    } else {
                        $ksSellerModel->setData('ks_seller_status', 1);
                        $ksSellerStoreStatus = !$this->ksDataHelper->getKsConfigSellerSetting('ks_store_approval');
                    }
                    $ksSellerModel->setData('ks_store_status', $ksSellerStoreStatus);
                    $ksSellerModel->setData('ks_seller_group_id', $this->ksDataHelper->getKsConfigSellerSetting('ks_seller_group'));
                    $ksSellerModel->setData('ks_store_url', $ksParamData['ks_seller_store_url']);
                    $ksSellerModel->setData('ks_store_name', $ksParamData['ks_seller_store_name']);
                    $ksSellerModel->setData('ks_seller_id', $ksCustomer->getId());
                    $ksSellerModel = $this->KsSellerHelper->ksSaveConfigurationValue($ksSellerModel);
                    $ksSellerModel->setKsCreatedAt($this->ksDate->gmtDate());
                    $ksSellerModel->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksSellerModel->save();

                    $ksStoreModel = $this->ksSellerStoreFactory->create();
                    $ksStoreModel->setData('ks_seller_id', $ksCustomer->getId());
                    $ksStoreModel->save();

                    //Category table entry
                    $this->KsSellerHelper->ksSetCategoryConfiguration($ksCustomer->getId());

                    //Save Product Type
                    $this->KsSellerHelper->ksAddProductTypeInSellerTable($ksCustomer->getId());

                    // url rewrite for seller store
                    $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksCustomer->getId().'/';
                    $ksRequestedPathUrl ="multivendor/".$ksParamData['ks_seller_store_url'];

                    $this->KsSellerHelper->ksSellerStoreUrlRedirect($ksTargetPathUrl, $ksRequestedPathUrl);

                    // Sitemap table entry
                    $this->ksSetSitemapData($ksCustomer->getId(), $ksStoreId);

                    if ($this->ksDataHelper->getKsConfigSellerSetting('ks_seller_approval')) {
                        $this->ksMessageManager->addSuccess(__('Your seller portal account request has been send successfully.'));

                        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue('ks_marketplace_seller/ks_seller_settings/ks_new_seller_templates');

                        if ($ksEmailEnabled != "disable") {
                            $ksSellerId = $ksCustomer->getId();
                            $ksStoreName = $ksParamData['ks_seller_store_name'];
                            $ksStoreUrl = $ksParamData['ks_seller_store_url'];
                            $ksDOb = $this->ksProductTypeHelper->getKsCustomerDOB($ksSellerId);

                            //Get Sender Info
                            $ksSellerInfo = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);

                            //Get Receiver Info
                            $ksAdminEmailOption = 'ks_marketplace_seller/ks_seller_settings/ks_seller_admin_email_option';
                            $ksAdminSecondaryEmail ='ks_marketplace_seller/ks_seller_settings/ks_seller_admin_email';
                            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                            $ksReceiverInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                            //Get Sender Info
                            $ksSender = $this->ksDataHelper->getKsConfigValue(
                                'ks_marketplace_seller/ks_seller_settings/ks_email_sender'
                            );
                            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                            $ksTemplateVariable = [];
                            $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverInfo['name']);
                            $ksTemplateVariable['ks_customer_name'] = ucwords($ksSellerInfo['name']);
                            $ksTemplateVariable['ks_customer_email'] = $ksSellerInfo['email'];
                            $ksTemplateVariable['ks_store_name'] = $ksStoreName;
                            $ksTemplateVariable['ks_store_url'] = $ksStoreUrl;

                            if ($ksDOb == "") {
                                $ksTemplateVariable['ks_dob'] = "N/A";
                            } else {
                                $ksTemplateVariable['ks_dob'] = $ksDOb;
                            }
                            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverInfo);
                        }
                    } else {
                        $this->ksMessageManager->addSuccess(__('Your seller portal account has been approved successfully.'));
                        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_seller/ks_seller_settings/ks_seller_approved_templates'
                        );

                        if ($ksEmailEnabled != "disable") {
                            //Get Sender Info
                            $ksSellerId = $ksCustomer->getId();
                            $ksSender = $this->ksDataHelper->getKsConfigValue(
                                'ks_marketplace_seller/ks_seller_settings/ks_email_sender'
                            );
                            $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);

                            $ksReceiverDetails = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                            $ksTemplateVariable = [];
                            $ksTemplateVariable['ksSellerName'] = ucwords($ksReceiverDetails['name']);
                            $ksTemplateVariable['ks_seller_email'] = $ksReceiverDetails['email'];
                            // Send Mail
                            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverDetails);
                        }
                    }

                    // set the value in session to differentiate the customer registration or login at login observer
                    $this->ksCoreSession->start();
                    $this->ksCoreSession->setIsCustomerRegister(1);
                } else {
                    $this->ksMessageManager->addError(
                        __('This Store URL already Exists.')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->ksMessageManager->addError($e->getMessage());
        }
    }

    /**
     * Add default entry in ks_seller_sitemap table
     * @param $ksSellerId
     * @param #ksStoreId
     *
     * @return void*/
    public function ksSetSitemapData($ksSellerId, $ksStoreId)
    {
        $ksSitemapModel=$this->ksSellerSitemapCollectionFactory->create();
        $ksSitemapModel->setData('ks_seller_id', $ksSellerId);
        $ksSitemapModel->setData('ks_included_sitemap_profile', $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_profile_url', $ksStoreId));
        $ksSitemapModel->setData('ks_included_sitemap_product', $this->ksDataHelper->getKsConfigValue('ks_marketplace_sitemap/ks_sitemap/ks_include_seller_product_url', $ksStoreId));
        $ksSitemapModel->save();
    }
}
