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

namespace Ksolves\MultivendorMarketplace\Controller\Order\Shipment;

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Index Controller class
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var KsSellerHelper 
     */
    protected $ksSellerHelper;
 
    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Vendor Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        if ($ksIsSeller == 1) {
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Shipments'));
            return $ksResultPage;
        }else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/create',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }    
}
