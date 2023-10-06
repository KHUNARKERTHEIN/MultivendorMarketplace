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
use Magento\Framework\Controller\ResultFactory;
use Ksolves\MultivendorMarketplace\Helper\KsDataHelper;

/**
 * Post Controller class
 */
class Post extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper
     */
    protected $ksEmailHelper;

    /**
     * @var KsDataHelper
     */
    protected $ksDataHelper;

    protected $ksMessageManager;
    
    /**
     * @param Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper
     * @param KsDataHelper $ksDataHelper
     * @param array $ksData = []
     */
    public function __construct(
        Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsEmailHelper $ksEmailHelper,
        KsDataHelper $ksDataHelper,
        array $ksData = []
    ) {
        $this->ksEmailHelper = $ksEmailHelper;
        $this->ksDataHelper = $ksDataHelper;
        $this->ksMessageManager = $ksContext->getMessageManager();
        parent::__construct($ksContext);
    }
    
    public function execute()
    {
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        $ksData = $this->getRequest()->getParams();
        if ($ksData && !empty($ksData['ks-name']) && !empty($ksData['ks-email']) && !empty($ksData['ks-message'])) {
            try {
                $this->ksMessageManager->addSuccess(__('Thank you for submitting your query.'));
                $ksEmailEnabled = $this->ksDataHelper->getKsConfigValue(
                    'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_submit_query_email',
                    $this->ksDataHelper->getKsCurrentStoreView()
                );
                if ($ksEmailEnabled != "disable") {
                    //Get Sender Info
                    $ksSender = $this->ksDataHelper->getKsConfigValue(
                        'ks_marketplace_promotion/ks_marketplace_promotion_page/ks_email_sender',
                        $this->ksDataHelper->getKsCurrentStoreView()
                    );
                    $ksSenderInfo = $this->ksEmailHelper->getKsSenderInfo($ksSender);
                    //Get Receiver Info
                    $ksReceiverInfo = $this->ksDataHelper->getKsAdminInfo();
                    //Template variables
                    $ksTemplateVariable = [];
                    $ksTemplateVariable['ksAdminName'] = ucwords($ksReceiverInfo['name']);
                    $ksTemplateVariable['ks_customer_name'] = $ksData['ks-name'];
                    $ksTemplateVariable['ks_customer_email'] = $ksData['ks-email'];
                    $ksTemplateVariable['ks_customer_message'] = $ksData['ks-message'];
                    // Send Mail
                    $this->ksEmailHelper->ksSendEmail($ksEmailEnabled, $ksTemplateVariable, $ksSenderInfo, $ksReceiverInfo);
                }
                return $ksResultRedirect;
                /* email code for admin end */
            } catch (\Exception $e) {
                $this->ksMessageManager->addError($e->getMessage());
                return $ksResultRedirect;
            }
        }
    }
}
