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

namespace Ksolves\MultivendorMarketplace\Controller\PriceComparison;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Action;

/**
 * ProductList Controller Class
 */
class ProductList extends Action
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
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        KsSellerHelper $ksSellerHelper
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Price Comparison Product Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Product List'));
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
