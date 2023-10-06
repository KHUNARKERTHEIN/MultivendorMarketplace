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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * SaveConfigData Controller class
 */
class SaveConfigData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory 
     */
    protected $ksSellerConfigData;
 
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $ksJsonHelper
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigData
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Json\Helper\Data $ksJsonHelper,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Ksolves\MultivendorMarketplace\Model\KsSellerConfigFactory $ksSellerConfigData,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerConfigData = $ksSellerConfigData;
        $this->messageManager = $messageManager;
        parent::__construct($ksContext);
    }

    /**
     * Save payment details of seller
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            try {
                //get data
                $ksPostData = $this->getRequest()->getPostValue();
                //check array
                if (isset($ksPostData['ks_recently_products_text'])) {
                    //get model
                    $ksModel = $this->ksSellerConfigData->create()->getCollection()->addFieldToFilter('ks_seller_id', (int)$ksPostData['ks_seller_id'])->getFirstItem();

                    if (isset($ksPostData['ks_show_banner'])) {
                        $ksModel->setKsShowBanner(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED);
                    } else {
                        $ksModel->setKsShowBanner(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_DISABLED);
                    }
                    if (isset($ksPostData['ks_show_recently_products'])) {
                        $ksModel->setKsShowRecentlyProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED);
                    } else {
                        $ksModel->setKsShowRecentlyProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_DISABLED);
                    }
                    if (isset($ksPostData['ks_show_best_products'])) {
                        $ksModel->setKsShowBestProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED);
                    } else {
                        $ksModel->setKsShowBestProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_DISABLED);
                    }
                    if (isset($ksPostData['ks_show_discount_products'])) {
                        $ksModel->setKsShowDiscountProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_ENABLED);
                    } else {
                        $ksModel->setKsShowDiscountProducts(\Ksolves\MultivendorMarketplace\Model\KsSellerConfig::KS_STATUS_DISABLED);
                    }

                    $ksModel->setKsRecentlyProductsText($ksPostData['ks_recently_products_text']);
                    $ksModel->setKsRecentlyProductsCount((int)$ksPostData['ks_recently_products_count']);
                    $ksModel->setKsBestProductsText($ksPostData['ks_best_products_text']);
                    $ksModel->setKsBestProductsCount((int)$ksPostData['ks_best_products_count']);
                    $ksModel->setKsDiscountProductsText($ksPostData['ks_discount_products_text']);
                    $ksModel->setKsDiscountProductsCount((int)$ksPostData['ks_discount_products_count']);
                    //save model data
                    $ksModel->save();
                }
                $this->messageManager->addSuccess(__(' The changes have been saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__('An error occured while saving your data.'));
            }
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                //for redirecting url
                return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
