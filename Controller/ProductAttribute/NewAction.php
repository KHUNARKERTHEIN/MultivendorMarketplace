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

namespace Ksolves\MultivendorMarketplace\Controller\ProductAttribute;

use Magento\Framework\App\Action\Action;

/**
 * NewAction Controller Class
 */
class NewAction extends Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $ksResultForwardFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * @param \Magento\Framework\App\Action\ksContext $ksContext
     * @param \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper $ksSellerHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $ksResultForwardFactory
    ) {
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksResultForwardFactory = $ksResultForwardFactory;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $ksIsSeller = $this->ksSellerHelper->ksIsSeller();
        // check for seller
        if ($ksIsSeller == 1) {
            return $this->ksResultForwardFactory->create()->forward('edit');
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/login',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
