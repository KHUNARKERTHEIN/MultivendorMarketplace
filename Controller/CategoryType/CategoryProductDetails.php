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

namespace Ksolves\MultivendorMarketplace\Controller\CategoryType;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * class CategoryProductDetails
 */
class CategoryProductDetails extends \Magento\Framework\App\Action\Action
{
    /**
     * @var LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param Context $ksContext
     * @param LayoutFactory $ksResultLayoutFactory
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     */
    public function __construct(
        Context $ksContext,
        LayoutFactory $ksResultLayoutFactory,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        parent::__construct($ksContext);
    }

    /**
     * Category Product Details
     *
     * @return \Magento\Framework\Controller\Result\LayoutFactory
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            /** @var \Magento\Framework\Controller\Result\LayoutFactory $ksResultLayoutFactory */
            $ksResultPage = $this->ksResultLayoutFactory->create();
            return $ksResultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
