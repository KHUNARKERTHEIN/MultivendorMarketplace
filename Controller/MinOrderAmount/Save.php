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

namespace Ksolves\MultivendorMarketplace\Controller\MinOrderAmount;

use Ksolves\MultivendorMarketplace\Model\KsSeller;

/**
 * Save Controller class
 */
class Save extends \Magento\Framework\App\Action\Action
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
     * @var KsSeller
     */
    protected $ksSellerModel;

    /**
     * @var \Magento\Framework\Registry $ksRegistry
     */
    protected $ksRegistry;
 
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param KsSeller $ksSellerModel
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        KsSeller $ksSellerModel,
        \Magento\Framework\Registry $ksRegistry
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksSellerModel = $ksSellerModel;
        $this->ksRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Vendor Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksSellerId = $this->ksSellerHelper->getKsCustomerId();
        // check for seller
        if ($this->ksSellerHelper->ksIsSeller()) {
            $ksMinAmt = $this->getRequest()->getPost('ks_min_order_amt') ?? null;
            $ksMessage = $this->getRequest()->getPost('ks_min_order_message') ?? null;
            $ksSellerData = $this->ksSellerModel->load($ksSellerId, 'ks_seller_id');
            $ksSellerData->setKsMinOrderAmount($ksMinAmt);
            $ksSellerData->setKsMinOrderMessage($ksMessage);
            $ksSellerData->save();
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $this->messageManager->addSuccess(__('The minimum order amount has been saved.'));
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRefererUrl());
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
