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

use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\PageFactory;
use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;

/**
 * Attribute Abstract Class
 */
abstract class Attribute extends \Magento\Framework\App\Action\Action
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Helper\KsSellerHelper
     */
    protected $ksSellerHelper;

    /**
     * Constructor
     * @param Context $ksContext,
     * @param Registry $ksCoreRegistry,
     * @param KsSellerHelper $ksSellerHelper
     * @param PageFactory $ksResultPageFactory
     */
    public function __construct(
        Context $ksContext,
        Registry $ksCoreRegistry,
        KsSellerHelper $ksSellerHelper,
        PageFactory $ksResultPageFactory
    ) {
        $this->ksCoreRegistry = $ksCoreRegistry;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksResultPageFactory = $ksResultPageFactory;
        parent::__construct($ksContext);
    }

    /**
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $ksResultPage */
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->prepend(__('Product Attributes'));
        return $ksResultPage;
    }
}
