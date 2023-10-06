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

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory as KsCommissionRuleCollection;

/**
 * MassDelete Controller Class
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var CommissionRuleCollection
     */
    protected $ksCommissionRuleCollectionFactory;

    /**
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param CommissionRuleCollection $ksCommissionRuleCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        KsCommissionRuleCollection $ksCommissionRuleCollectionFactory
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCommissionRuleCollectionFactory = $ksCommissionRuleCollectionFactory;
        parent::__construct($ksContext);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksCollection = $this->ksFilter->getCollection($this->ksCommissionRuleCollectionFactory->create())->addFieldToFilter('id', ['neq' => 1]);
        $ksCollectionSize = $ksCollection->getSize();

        foreach ($ksCollection as $ksRecord) {
            $ksRecord->delete();
        }

        if (!$ksCollectionSize) {
            $this->messageManager->addWarning(__('Default rule can\'t be deleted.'));
        } else {
            $ksCollection = $this->ksFilter->getCollection($this->ksCommissionRuleCollectionFactory->create())->addFieldToFilter('id', ['eq' => 1]);
            $ksSize = $ksCollection->getSize();
            if (!$ksSize) {
                $this->messageManager->addSuccess(__('A total of %1 commission rule(s) has been deleted.', $ksCollectionSize));
            } else {
                $this->messageManager->addSuccess(__('A total of %1 commission rule(s) has been deleted.', $ksCollectionSize));
                $this->messageManager->addWarning(__('Default rule can\'t be deleted.'));
            }
        }
        

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $ksResultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $ksResultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ksolves_MultivendorMarketplace::manage_commission');
    }
}
