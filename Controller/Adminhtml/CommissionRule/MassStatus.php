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
use Ksolves\MultivendorMarketplace\Model\ResourceModel\KsCommissionRule\CollectionFactory as KsCommissionruleCollection;

/**
 * MassStatus
 */
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var ksCommissionRuleCollection
     */
    protected $ksCommissionRuleCollectionFactory;

    /**
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param ksCommissionRuleCollection $ksCommissionRuleCollectionFactory
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        ksCommissionRuleCollection $ksCommissionRuleCollectionFactory
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
        $ksCollection = $this->ksFilter->getCollection($this->ksCommissionRuleCollectionFactory->create());
        $ksCollectionSize = $ksCollection->getSize();
        foreach ($ksCollection as $ksRecord) {
            $ksRecord->setKsStatus($this->getRequest()->getParam('status'))->save();
        }
        if ($this->getRequest()->getParam('status') == 1) {
            $ksStatus = 'enabled';
        } else {
            $ksStatus = 'disabled';
        }
        $this->messageManager->addSuccess(__('A total of %1 commission rule(s) has been %2 successfully.', $ksCollectionSize, $ksStatus));

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
