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
 
namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\Banners;
 
use Magento\Framework\Controller\ResultFactory;
 
/**
 * Edit Controller class
 */
class Edit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksCoreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksCoreRegistry
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksCoreRegistry;
        parent::__construct($ksContext);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('id');
        if ($ksId) {
            $this->ksCoreRegistry->register('current_banners_id', $ksId);
        }
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->getConfig()->getTitle()->prepend(__('Banner'));
        return $ksResultPage;
    }
}
