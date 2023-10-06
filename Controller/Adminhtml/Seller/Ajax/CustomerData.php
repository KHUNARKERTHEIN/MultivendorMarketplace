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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Seller\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

/**
 * Class CustomerData Controller
 */
class CustomerData extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var Registry
     */
    protected $ksCoreRegistry;
 

    /**
     * Initialize Controller
     *
     * @param Context $ksContext
     * @param PageFactory $ksResultPageFactory
     * @param Registry $ksCoreRegistry
     */
    public function __construct(
        Context $ksContext,
        PageFactory $ksResultPageFactory,
        Registry $ksCoreRegistry
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        parent::__construct($ksContext);
    }

    /**
     * execute action
     */
    public function execute()
    {
        $ksSellerId = $this->getRequest()->getParam('ks_customer_id');
        $this->ksCoreRegistry->register('current_seller_id', $ksSellerId);

        $ksResultPage = $this->ksResultPageFactory->create();
        $ksHtmlContent = $ksResultPage->getLayout()
                ->createBlock('Ksolves\MultivendorMarketplace\Block\Adminhtml\Seller\Tabs\KsOwnerDetails')
                ->setTemplate('Ksolves_MultivendorMarketplace::seller/tab/ks_owner_details.phtml')
                ->toHtml();

        $ksResponse = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'output' => $ksHtmlContent
        ]);

        return $ksResponse;
    }
}
