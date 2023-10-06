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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CategoryRequests;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;

/**
 * class CategoryProductDetails
 */
class CategoryProductDetails extends \Magento\Backend\App\Action
{
    /**
     * @var LayoutFactory
     */
    protected $ksResultLayoutFactory;

    /**
     * @param Context $ksContext
     * @param LayoutFactory $ksResultLayoutFactory
     */
    public function __construct(
        Context $ksContext,
        LayoutFactory $ksResultLayoutFactory
    ) {
        $this->ksResultLayoutFactory = $ksResultLayoutFactory;
        parent::__construct($ksContext);
    }

    /**
     * Category Product Details
     *
     * @return \Magento\Framework\Controller\Result\LayoutFactory
     */
    public function execute()
    {
        $ksResultPage = $this->ksResultLayoutFactory->create();
        return $ksResultPage;
    }
}
