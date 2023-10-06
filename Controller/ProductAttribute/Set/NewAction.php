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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute\Set;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Action;

class NewAction extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\Registry $ksCoreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     */
    public function __construct(
        Context $ksContext,
        KsSellerHelper $ksSellerHelper,
        Registry $ksCoreRegistry,
        PageFactory $ksResultPageFactory
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksResultPageFactory = $ksResultPageFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller == 1) {
            /** @var \Magento\Framework\Model\View\Result\Page $resultPage */
            $ksResultPage = $this->ksResultPageFactory->create();
            $this->ksCoreRegistry->register('ks_current_seller_id', $this->ksSellerHelper->getKsCustomerId());
            $ksResultPage->getConfig()->getTitle()->prepend(__('New Attribute Set'));
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
