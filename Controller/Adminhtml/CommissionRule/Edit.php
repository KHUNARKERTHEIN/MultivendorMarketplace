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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\CommissionRule;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Edit Controller Class
 */
class Edit extends \Magento\Backend\App\Action implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ksolves_MultivendorMarketplace::commissionrule';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $ksCoreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $ksResultPageFactory;

    /**
     * @param Action\Context $ksContext
     * @param \Magento\Framework\View\Result\PageFactory $ksResultPageFactory
     * @param \Magento\Framework\Registry $ksRegistry
     */
    public function __construct(
        Action\Context $ksContext,
        \Magento\Framework\View\Result\PageFactory $ksResultPageFactory,
        \Magento\Framework\Registry $ksRegistry
    ) {
        $this->ksResultPageFactory = $ksResultPageFactory;
        $this->ksCoreRegistry = $ksRegistry;
        parent::__construct($ksContext);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu
        /***
         * @var \Magento\Backend\Model\View\Result\Page $ksResultPage
         */
        $ksResultPage = $this->ksResultPageFactory->create();
        $ksResultPage->setActiveMenu('Ksolves_MultivendorMarketplace::commission_rule');
        return $ksResultPage;
    }

    /**
     * Execute Action
     */
    public function execute()
    {
        $ksId = $this->getRequest()->getParam('id');
        
        $ksModel = $this->_objectManager->create('Ksolves\MultivendorMarketplace\Model\KsCommissionRule');

        if ($ksId) {
            $ksModel->load($ksId);
            if (!$ksModel->getId()) {
                $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                /**
                 * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
                 */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
       
        $ksModel->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $this->ksCoreRegistry->register('commission_rule', $ksModel);
        $this->ksCoreRegistry->register('form_data', $this->getRequest()->getParam('form_data'));
        $ksResultPage = $this->_initAction();
        $ksResultPage->getConfig()->getTitle()->prepend(
            $ksId ? __($ksModel->getData('ks_rule_name')) : __('New Commission Rule')
        );
        $ksResultPage->addBreadcrumb(
            $ksId ? __($ksModel->getData('ks_rule_name')) : __('New Commission Rule'),
            $ksId ? __($ksModel->getData('ks_rule_name')) : __('New Commission Rule')
        );

        return $ksResultPage;
    }
}
