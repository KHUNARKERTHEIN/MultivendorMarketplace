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

namespace Ksolves\MultivendorMarketplace\Controller\Account;

/**
 * BecomeSellerSave Controller class
 */
class BecomeSellerSave extends \Magento\Framework\App\Action\Action
{
    /**
     * @var KsSellerFactory
     */
    protected $ksSellerFactory;

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
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper
     */
    protected $ksProductTypeHelper;

    /**
     * @var Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory
     */
    protected $ksSellerStoreFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $KsSellerHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Magento\Framework\Message\ManagerInterface $ksMessageManager
     * @param \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $KsSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsSellerFactory $ksSellerFactory,
        \Ksolves\MultivendorMarketplace\Model\KsSellerStoreFactory $ksSellerStoreFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Magento\Framework\Message\ManagerInterface $ksMessageManager,
        \Ksolves\MultivendorMarketplace\Helper\KsDataHelper $ksDataHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsProductTypeHelper $ksProductTypeHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $KsSellerHelper
    ) {
        $this->ksSellerFactory = $ksSellerFactory;
        $this->ksSellerStoreFactory = $ksSellerStoreFactory;
        $this->ksDate = $ksDate;
        $this->ksMessageManager = $ksMessageManager;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksProductTypeHelper = $ksProductTypeHelper;
        $this->KsSellerHelper = $KsSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Became seller save page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                // get param data
                $ksParamData = $this->getRequest()->getParams();

                $ksCustomerId = $this->ksSellerHelper->getKsCustomerId();
                $ksSellerCollection = $this->ksSellerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('ks_store_url', $ksParamData['ks_seller_store_url'])->addFieldToFilter('ks_seller_id', ['neq' => $ksCustomerId]);

                // check collection size
                if (!$ksSellerCollection->getSize()) {
                    // set data in model
                    $ksSellerModel = $this->ksSellerFactory->create();
                    $ksStoreModel = $this->ksSellerStoreFactory->create();

                    $ksCollection = $this->ksSellerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('ks_seller_id', $ksCustomerId);

                    if ($ksCollection->getSize()) {
                        $ksSellerModel = $this->ksSellerFactory->create()->load($ksCollection->getFirstItem()->getId());
                    } else {
                        $ksStoreModel->setData('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
                        $ksStoreModel->save();
                    }
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
                    $ksSellerModel->setData('ks_seller_id', $this->ksSellerHelper->getKsCustomerId());
                    $ksSellerModel = $this->ksSellerHelper->ksSaveConfigurationValue($ksSellerModel);
                    $ksSellerModel->setKsCreatedAt($this->ksDate->gmtDate());
                    $ksSellerModel->setKsUpdatedAt($this->ksDate->gmtDate());
                    $ksSellerModel->save();

                    // url rewrite for seller store
                    $ksTargetPathUrl ="multivendor/sellerprofile/sellerprofile/seller_id/".$ksCustomerId.'/';
                    $ksRequestedPathUrl ="multivendor/".$ksParamData['ks_seller_store_url'];

                    $this->KsSellerHelper->ksSellerStoreUrlRedirect($ksTargetPathUrl, $ksRequestedPathUrl);

                    //Category table entry
                    $this->KsSellerHelper->ksSetCategoryConfiguration($ksCustomerId);

                    //Save Product Type
                    $this->KsSellerHelper->ksAddProductTypeInSellerTable($ksCustomerId);

                    if ($this->ksDataHelper->getKsConfigSellerSetting('ks_seller_approval')) {
                        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_seller/ks_seller_settings/ks_new_seller_templates'
                        );

                        if ($ksEmailEnabled != "disable") {
                            $ksSellerId = $ksSellerModel->getKsSellerId();
                            $ksStoreName = $ksSellerModel->getKsStoreName();
                            $ksStoreUrl = $ksSellerModel->getKsStoreUrl();
                            $ksDOb = $this->ksProductTypeHelper->getKsCustomerDOB($ksSellerId);
                            //Get Seller Info
                            $ksSellerInfo = $this->ksDataHelper->getKsCustomerInfo($ksSellerId);
                            //Get Receiver Info
                            $ksAdminEmailOption = 'ks_marketplace_seller/ks_seller_settings/ks_seller_admin_email_option';
                            $ksAdminSecondaryEmail ='ks_marketplace_seller/ks_seller_settings/ks_seller_admin_email';
                            $ksStoreId = $this->ksDataHelper->getKsCurrentStoreView();
                            $ksReceiverInfo = $this->ksDataHelper->getKsAdminEmail($ksAdminEmailOption, $ksAdminSecondaryEmail, $ksStoreId);
                            //Get sender info
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
                            // Send Mail
                            $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverInfo);
                        }
                        $this->ksMessageManager->addSuccess(__('Your seller portal account request has been send successfully.'));
                    } else {
                        $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                            'ks_marketplace_seller/ks_seller_settings/ks_seller_approved_templates'
                        );
                        if ($ksEmailEnabled != "disable") {
                            //Get Sender Info
                            $ksSellerId = $ksSellerModel->getKsSellerId();
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
                        $this->ksMessageManager->addSuccess(__('Your seller portal account has been approved successfully.'));
                    }
                    return $this->resultRedirectFactory->create()->setPath(
                        'customer/account/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } else {
                    $this->ksMessageManager->addError(__('Store URL already exists.'));
                    return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
                }
            } catch (\Exception $e) {
                $this->ksMessageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setUrl(
                    $this->_redirect->getRefererUrl()
                );
            }
        } else {
            $this->ksMessageManager->addError("something went wrong.");
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        }
    }
}
