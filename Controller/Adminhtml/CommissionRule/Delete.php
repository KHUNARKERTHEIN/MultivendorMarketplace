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

/**
 * Delete class
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Ksolves\MultivendorMarketplace\Model\commissionRuleFactory
     */
    protected $ksCommissionRuleModel;

    /**
     * @param \Magento\Backend\App\Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $KsCommissionRuleModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $KsCommissionRuleModel
    ) {
        $this->ksCommissionRuleModel = $KsCommissionRuleModel;
        parent::__construct($ksContext);
    }

    /**
     * Delete action
     */
    public function execute()
    {
        try {
            if ($this->getRequest()->getParam('id')) {
                $ksId = $this->getRequest()->getParam('id');
                $ksCollection = $this->ksCommissionRuleModel->create()->getCollection()->addFieldToFilter('id', $ksId);
                foreach ($ksCollection as $ksRecord) {
                    $ksRecord->delete();
                }
                $this->messageManager->addSuccess(
                    __('Rule deleted successfully.')
                );
                $this->_redirect('multivendor/*/');
                return;
            }
        } catch (Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while deleting the item data. Please review the error log.')
            );
            $this->_redirect('multivendor/*/');
            return;
        }
    }
}
