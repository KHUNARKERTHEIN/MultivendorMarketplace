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
 * MassDuplicate Controller Class
 */
class MassDuplicate extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $ksFilter;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $ksDate;

    /**
     * @var KsCommissionRuleCollection
     */
    protected $ksCommissionRuleCollectionFactory;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\commissionRuleFactory
     */
    protected $ksCommissionRuleModel;

    /**
     * @param Context $ksContext
     * @param Filter $ksFilter
     * @param KsCommissionRuleCollection $ksCommissionRuleCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $ksDate
     * @param \Ksolves\MultivendorMarketplace\Model\CommissionRuleFactory $ksCommissionRuleModel
     */
    public function __construct(
        Context $ksContext,
        Filter $ksFilter,
        KsCommissionRuleCollection $ksCommissionRuleCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $ksDate,
        \Ksolves\MultivendorMarketplace\Model\KsCommissionRuleFactory $ksCommissionRuleModel
    ) {
        $this->ksFilter = $ksFilter;
        $this->ksCommissionRuleCollectionFactory = $ksCommissionRuleCollectionFactory;
        $this->ksDate = $ksDate;
        $this->ksCommissionRuleModel = $ksCommissionRuleModel;
        parent::__construct($ksContext);
    }

    /**
     * Execute Action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ksCollection = $this->ksFilter->getCollection($this->ksCommissionRuleCollectionFactory->create())->addFieldToFilter('id', ['neq' => 1]);
        $ksCollectionSize = $ksCollection->getSize();

        $ksCount =0;
        foreach ($ksCollection as $ksRecord) {
            $ksRecordData = $ksRecord->getData();
            unset($ksRecordData['id']);

            try {
                $ksModel = $this->ksCommissionRuleModel->create();
                $ksPriority;
                $ksPriorities = array_column($ksModel->getCollection()->getData(), 'ks_priority');
                sort($ksPriorities);
                $ksPriority = $ksPriorities[count($ksPriorities) - 1];
                $ksModel->setData($ksRecordData);
                $ksModel->setKsCreatedAt($this->ksDate->gmtDate());
                $ksModel->setKsUpdatedAt($this->ksDate->gmtDate());
                $ksModel->setData('ks_rule_name', $ksRecordData['ks_rule_name'] . '(copy)');
                $ksModel->setData('ks_priority', $ksPriority + 1);
                $ksModel->save();
                $ksCount++;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while duplicate the record %1', $ksRecord->getId())
                );
            }
        }
        
        if (!$ksCollectionSize) {
            $this->messageManager->addWarning(__('Default rule can\'t be duplicated.', $ksCount));
        } else {
            $ksCollection = $this->ksFilter->getCollection($this->ksCommissionRuleCollectionFactory->create())->addFieldToFilter('id', ['eq' => 1]);
            $ksCollectionSize = $ksCollection->getSize();
            if (!$ksCollectionSize) {
                $this->messageManager->addSuccess(__('A total of %1 commission rule(s) has been duplicated successfully.', $ksCount));
            } else {
                $this->messageManager->addSuccess(__('A total of %1 commission rule(s) has been duplicated successfully.', $ksCount));
                $this->messageManager->addWarning(__('Default rule can\'t be duplicated.', $ksCount));
            }
        }
        

        /**
        * @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect
        */
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
