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

use Ksolves\MultivendorMarketplace\Helper\KsSellerHelper;

/**
 * Sell Controller Class
 */
class Sell extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Controller\Result\ForwardFactory 
     */
    protected $ksForwardFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param KsSellerHelper $ksSellerHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        KsSellerHelper $ksSellerHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $ksForwardFactory
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksSellerHelper = $ksSellerHelper;
        $this->ksForwardFactory = $ksForwardFactory;
        parent::__construct($ksContext);
    }

    /**
     * Sell page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //show sell page or not
        if($this->ksSellerHelper->getKsShowSellPage()){
            /** @var \Magento\Framework\View\Result\Page $ksResultPageFactory */
            $ksResultPage = $this->ksResultPageFactory->create();
            $ksResultPage->getConfig()->getTitle()->set(__('Sell Page'));
            $this->ksSellerHelper->ksFlushCache();
            return $ksResultPage;
        } else {
            $ksResultForward = $this->ksForwardFactory->create();
            $ksResultForward->setController('index');
            $ksResultForward->forward('defaultNoRoute');
            return $ksResultForward;
        }
    }
}
