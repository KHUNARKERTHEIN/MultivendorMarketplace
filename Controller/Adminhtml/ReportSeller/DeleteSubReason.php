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

namespace Ksolves\MultivendorMarketplace\Controller\Adminhtml\ReportSeller;

use Magento\Framework\Controller\ResultFactory;

/**
 * DeleteSubReason Controller class
 *
 * Class DeleteSubReason
 */
class DeleteSubReason extends \Magento\Backend\App\Action
{
    /**
     * KsReportSellerSubReasonFactory
     *
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportSellerSubReasonFactory
     */
    protected $ksSubReasonFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $ksSession;

    /**
     * @var \Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory
     */
    protected $ksReasonFactory;

    /**
     * Delete constructor.
     *
     * @param Action\Context $ksContext
     * @param \Ksolves\MultivendorMarketplace\Model\KsReportSellerSubReasonFactory $ksReasonFactory
     * @param \Magento\Customer\Model\Session $ksSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $ksContext,
        \Ksolves\MultivendorMarketplace\Model\KsReportSellerReasonsFactory $ksReasonFactory,
        \Magento\Customer\Model\Session $ksSession
    ) {
        $this->ksReasonFactory = $ksReasonFactory;
        $this->ksSession = $ksSession;
        parent::__construct($ksContext);
    }

    /**
     * DeleteSubReason action
     */
    public function execute()
    {
        try {
            $ksId = $this->getRequest()->getParam('id');
            if (!$ksId) {
                $ksId = $this->ksSession->getKsCurrentSubReasonId();
                $this->ksSession->unsKsCurrentSubReasonId();
            }
            $ksCurrentReason = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('id', $ksId)->getFirstItem();
            $ksCollection = $this->ksReasonFactory->create()->getCollection()->addFieldToFilter('ks_reason', $ksCurrentReason->getKsReason());
            /*if no sub reason exist return an error*/
            if ($this->ksReasonFactory->create()->load($ksId)->getKsSubreason()=="") {
                $this->messageManager->addError(
                    __('There does not exist a sub reason to be deleted')
                );
                $this->_redirect('multivendor/reportseller/index');
                return;
            }
            /*if more than one entries are present for the same reason, delete the complete entry*/
            if ($ksCollection->getSize()>1) {
                $this->ksReasonFactory->create()->load($ksId)->delete();
            } elseif ($ksCollection->getSize()==1) {
                /*if more than one entries are not present for the same reason, unset the sub reason*/
                $ksModel = $this->ksReasonFactory->create()->load($ksId);
                $ksModel->setKsSubreason("");
                $ksModel->setKsSubreasonStatus(0);
                $ksModel->save();
            }
            $this->messageManager->addSuccess(
                __('The sub reason was deleted successfully.')
            );
            $this->_redirect('multivendor/reportseller/index');
            return;
        } catch (Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while saving the item data. Please review the error log.')
            );
            $this->_redirect('multivendor/reportseller/index');
            return;
        }
    }
}
